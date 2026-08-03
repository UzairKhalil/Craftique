<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Verifies that the machine can actually run Craftique.
 *
 * A missing PHP extension or a database pointed at the wrong server surfaces
 * as a confusing runtime error many milestones after the mistake was made.
 * This command turns all of that into one answer, so a fresh clone either
 * works or says precisely why it does not.
 *
 * Exit code is non-zero when any check FAILs, so CI can gate on it.
 *
 * @see docs/runbooks/environment.md
 */
final class DoctorCommand extends Command
{
    protected $signature = 'craftique:doctor {--json : Emit machine-readable JSON instead of a table}';

    protected $description = 'Check that PHP, the database, cache and filesystem are ready to run Craftique';

    private const STATUS_PASS = 'PASS';

    private const STATUS_WARN = 'WARN';

    private const STATUS_FAIL = 'FAIL';

    /** Minimum PHP version Laravel 12 and this codebase require. */
    private const PHP_MINIMUM = 80200;

    /** Preferred PHP version (ADR-0010) — below this we warn but continue. */
    private const PHP_RECOMMENDED = 80300;

    private const MYSQL_MINIMUM = '8.0.0';

    /**
     * Extensions without which the application cannot boot or a core feature is
     * silently broken. Keyed by extension name, valued by what depends on it.
     *
     * @var array<string, string>
     */
    private const REQUIRED_EXTENSIONS = [
        'pdo_mysql' => 'database access',
        'mbstring' => 'string handling',
        'openssl' => 'encryption, TLS',
        'curl' => 'payment and carrier APIs',
        'fileinfo' => 'upload MIME sniffing',
        'tokenizer' => 'framework internals',
        'xml' => 'framework internals',
        'ctype' => 'framework internals',
        'json' => 'framework internals',
        'bcmath' => 'money arithmetic (ADR-0004)',
        'intl' => 'money and date formatting',
        'zip' => 'imports, exports, backups',
        'exif' => 'image orientation on upload',
        'redis' => 'cache, queue, locks, broadcasting',
    ];

    /**
     * Nice to have. Missing ones produce a warning, never a failure.
     *
     * @var array<string, string>
     */
    private const RECOMMENDED_EXTENSIONS = [
        'imagick' => 'higher quality image conversions than gd',
        'sodium' => 'modern cryptography primitives',
        'opcache' => 'production performance',
    ];

    /** @var list<array{group: string, check: string, status: string, detail: string}> */
    private array $results = [];

    public function handle(): int
    {
        // The console kernel reuses command instances, so a second invocation
        // in the same process would otherwise append to the previous run's
        // results and report every check twice.
        $this->results = [];

        $this->checkPhp();
        $this->checkExtensions();
        $this->checkDatabase();
        $this->checkRedis();
        $this->checkFilesystem();
        $this->checkApplication();

        return $this->option('json') ? $this->renderJson() : $this->renderTable();
    }

    private function checkPhp(): void
    {
        $version = PHP_VERSION;

        if (PHP_VERSION_ID < self::PHP_MINIMUM) {
            $this->record('PHP', 'version', self::STATUS_FAIL, "{$version} — 8.2 or newer required");

            return;
        }

        if (PHP_VERSION_ID < self::PHP_RECOMMENDED) {
            $this->record('PHP', 'version', self::STATUS_WARN, "{$version} — 8.3+ recommended (ADR-0010)");

            return;
        }

        $this->record('PHP', 'version', self::STATUS_PASS, $version);
    }

    private function checkExtensions(): void
    {
        foreach (self::REQUIRED_EXTENSIONS as $extension => $purpose) {
            $this->record(
                'Extensions',
                $extension,
                extension_loaded($extension) ? self::STATUS_PASS : self::STATUS_FAIL,
                extension_loaded($extension) ? $purpose : "MISSING — needed for {$purpose}",
            );
        }

        // MediaLibrary needs at least one image driver; either satisfies it.
        $hasImageDriver = extension_loaded('gd') || extension_loaded('imagick');
        $this->record(
            'Extensions',
            'image driver',
            $hasImageDriver ? self::STATUS_PASS : self::STATUS_FAIL,
            $hasImageDriver
                ? implode(' + ', array_filter(['gd' => extension_loaded('gd') ? 'gd' : null, 'imagick' => extension_loaded('imagick') ? 'imagick' : null]))
                : 'MISSING — gd or imagick required for image conversions',
        );

        foreach (self::RECOMMENDED_EXTENSIONS as $extension => $purpose) {
            if (! extension_loaded($extension)) {
                $this->record('Extensions', $extension, self::STATUS_WARN, "not loaded — {$purpose}");
            }
        }
    }

    private function checkDatabase(): void
    {
        try {
            $connection = DB::connection();
            $version = (string) $connection->selectOne('select version() as v')->v;
            $port = (string) $connection->selectOne('select @@port as p')->p;
            $collation = (string) $connection->selectOne('select @@collation_database as c')->c;
        } catch (Throwable $e) {
            $this->record('Database', 'connection', self::STATUS_FAIL, $this->firstLine($e->getMessage()));
            $this->record('Database', 'hint', self::STATUS_FAIL, 'Is MySQL 8 running? scripts/mysql8.sh start');

            return;
        }

        $this->record('Database', 'connection', self::STATUS_PASS, $connection->getDatabaseName()." on port {$port}");

        // ADR-0009: MariaDB is explicitly unsupported — it lacks SKIP LOCKED
        // before 10.6 and the utf8mb4_0900_ai_ci collation entirely.
        if (str_contains($version, 'MariaDB')) {
            $this->record('Database', 'server', self::STATUS_FAIL, "{$version} — MariaDB is unsupported (ADR-0009), use MySQL 8");

            return;
        }

        $this->record(
            'Database',
            'server',
            version_compare($version, self::MYSQL_MINIMUM, '>=') ? self::STATUS_PASS : self::STATUS_FAIL,
            version_compare($version, self::MYSQL_MINIMUM, '>=') ? "MySQL {$version}" : "MySQL {$version} — 8.0+ required",
        );

        $this->record(
            'Database',
            'collation',
            $collation === 'utf8mb4_0900_ai_ci' ? self::STATUS_PASS : self::STATUS_WARN,
            $collation === 'utf8mb4_0900_ai_ci' ? $collation : "{$collation} — expected utf8mb4_0900_ai_ci",
        );

        $this->checkTestDatabaseExists();
    }

    private function checkTestDatabaseExists(): void
    {
        $testDatabase = (string) (env('DB_TEST_DATABASE') ?? config('database.connections.mysql.database').'_test');

        try {
            $exists = DB::selectOne(
                'select schema_name from information_schema.schemata where schema_name = ?',
                [$testDatabase],
            );
        } catch (Throwable) {
            return; // Connection already reported above; nothing to add.
        }

        $this->record(
            'Database',
            'test database',
            $exists !== null ? self::STATUS_PASS : self::STATUS_WARN,
            $exists !== null ? $testDatabase : "{$testDatabase} missing — the test suite will fail",
        );
    }

    private function checkRedis(): void
    {
        if (! extension_loaded('redis')) {
            return; // Already reported as a failed required extension.
        }

        $drivers = [
            'cache' => (string) config('cache.default'),
            'queue' => (string) config('queue.default'),
            'session' => (string) config('session.driver'),
            'broadcast' => (string) config('broadcasting.default'),
        ];

        $this->record(
            'Cache & Queue',
            'drivers',
            self::STATUS_PASS,
            implode('  ', array_map(static fn (string $k, string $v): string => "{$k}={$v}", array_keys($drivers), $drivers)),
        );

        // Redis is only load-bearing if something is actually pointed at it.
        // When it is, an unreachable server is a hard failure rather than a
        // polite warning — otherwise CI would go green on a broken machine.
        $redisIsRequired = in_array('redis', $drivers, true);

        try {
            Redis::connection()->ping();
            $this->record(
                'Cache & Queue',
                'redis server',
                self::STATUS_PASS,
                (string) config('database.redis.default.host').':'.config('database.redis.default.port'),
            );
        } catch (Throwable $e) {
            $this->record(
                'Cache & Queue',
                'redis server',
                $redisIsRequired ? self::STATUS_FAIL : self::STATUS_WARN,
                'unreachable — '.$this->firstLine($e->getMessage())
                    .($redisIsRequired ? ' (required: '.implode(', ', array_keys($drivers, 'redis', true)).')' : ''),
            );

            return;
        }

        $this->checkCacheRoundTrip();
    }

    /**
     * Pinging Redis proves the server answers. It does not prove the configured
     * cache store works — wrong prefix, wrong database, serialization problems
     * all survive a PING. So write, read back, and clean up.
     */
    private function checkCacheRoundTrip(): void
    {
        $key = 'doctor:round-trip:'.bin2hex(random_bytes(4));
        $expected = 'ok-'.$key;

        try {
            Cache::put($key, $expected, 60);
            $actual = Cache::get($key);
            Cache::forget($key);
        } catch (Throwable $e) {
            $this->record('Cache & Queue', 'cache round-trip', self::STATUS_FAIL, $this->firstLine($e->getMessage()));

            return;
        }

        $this->record(
            'Cache & Queue',
            'cache round-trip',
            $actual === $expected ? self::STATUS_PASS : self::STATUS_FAIL,
            $actual === $expected
                ? 'write, read and forget via '.class_basename(Cache::store()->getStore())
                : 'value did not survive the round trip',
        );
    }

    private function checkFilesystem(): void
    {
        $paths = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($paths as $label => $path) {
            $writable = is_dir($path) && is_writable($path);
            $this->record('Filesystem', $label, $writable ? self::STATUS_PASS : self::STATUS_FAIL, $writable ? 'writable' : 'not writable');
        }
    }

    private function checkApplication(): void
    {
        $key = (string) config('app.key');
        $this->record(
            'Application',
            'APP_KEY',
            $key !== '' ? self::STATUS_PASS : self::STATUS_FAIL,
            $key !== '' ? 'set' : 'missing — run php artisan key:generate',
        );

        if (app()->environment('production') && config('app.debug') === true) {
            $this->record('Application', 'APP_DEBUG', self::STATUS_FAIL, 'debug is enabled in production');
        }
    }

    private function record(string $group, string $check, string $status, string $detail): void
    {
        $this->results[] = compact('group', 'check', 'status', 'detail');
    }

    private function renderTable(): int
    {
        $this->newLine();
        $this->line('  <options=bold>Craftique environment doctor</>');

        $currentGroup = null;

        foreach ($this->results as $result) {
            if ($result['group'] !== $currentGroup) {
                $this->newLine();
                $this->line('  <fg=gray>'.$result['group'].'</>');
                $currentGroup = $result['group'];
            }

            $this->components->twoColumnDetail(
                '  '.$result['check'].' <fg=gray>'.$result['detail'].'</>',
                $this->colour($result['status']),
            );
        }

        $failures = $this->countStatus(self::STATUS_FAIL);
        $warnings = $this->countStatus(self::STATUS_WARN);

        $this->newLine();

        if ($failures > 0) {
            $this->components->error("{$failures} check(s) failed. See docs/runbooks/environment.md");

            return self::FAILURE;
        }

        $warnings > 0
            ? $this->components->warn("Ready, with {$warnings} warning(s).")
            : $this->components->success('All checks passed.');

        return self::SUCCESS;
    }

    private function renderJson(): int
    {
        $failures = $this->countStatus(self::STATUS_FAIL);

        $this->output->writeln((string) json_encode([
            'ok' => $failures === 0,
            'failures' => $failures,
            'warnings' => $this->countStatus(self::STATUS_WARN),
            'checks' => $this->results,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function countStatus(string $status): int
    {
        return count(array_filter($this->results, static fn (array $r): bool => $r['status'] === $status));
    }

    private function colour(string $status): string
    {
        return match ($status) {
            self::STATUS_PASS => '<fg=green;options=bold>PASS</>',
            self::STATUS_WARN => '<fg=yellow;options=bold>WARN</>',
            default => '<fg=red;options=bold>FAIL</>',
        };
    }

    private function firstLine(string $message): string
    {
        return trim(strtok($message, "\n") ?: $message);
    }
}
