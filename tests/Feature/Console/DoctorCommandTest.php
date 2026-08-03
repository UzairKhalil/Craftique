<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;
use Tests\TestCase;

/**
 * The doctor command is the gate a fresh clone passes through, and CI gates on
 * its exit code. These tests pin the contract: it runs, it reports every
 * required extension, and it fails loudly rather than silently.
 */
final class DoctorCommandTest extends TestCase
{
    public function test_it_succeeds_on_a_correctly_configured_machine(): void
    {
        $this->artisan('craftique:doctor')->assertSuccessful();
    }

    public function test_it_reports_every_required_extension(): void
    {
        $required = [
            'pdo_mysql', 'mbstring', 'openssl', 'curl', 'fileinfo', 'tokenizer',
            'xml', 'ctype', 'json', 'bcmath', 'intl', 'zip', 'exif', 'redis',
        ];

        $output = $this->runJson();

        $reported = array_column($output['checks'], 'check');

        foreach ($required as $extension) {
            $this->assertContains($extension, $reported, "The doctor never checked for [{$extension}].");
        }
    }

    public function test_every_required_extension_is_actually_loaded(): void
    {
        $output = $this->runJson();

        $extensionFailures = array_filter(
            $output['checks'],
            static fn (array $check): bool => $check['group'] === 'Extensions' && $check['status'] === 'FAIL',
        );

        $this->assertSame(
            [],
            array_values($extensionFailures),
            'Missing PHP extension(s). See docs/runbooks/environment.md.',
        );
    }

    public function test_it_verifies_the_database_is_mysql_8_and_not_mariadb(): void
    {
        $output = $this->runJson();

        $database = array_values(array_filter(
            $output['checks'],
            static fn (array $check): bool => $check['group'] === 'Database',
        ));

        $this->assertNotEmpty($database, 'The doctor performed no database checks.');

        $server = array_values(array_filter(
            $database,
            static fn (array $check): bool => $check['check'] === 'server',
        ));

        $this->assertNotEmpty($server, 'The doctor never identified the database server.');
        $this->assertSame('PASS', $server[0]['status'], 'Database server check failed: '.$server[0]['detail']);
        $this->assertStringContainsString('MySQL', $server[0]['detail']);
        $this->assertStringNotContainsString('MariaDB', $server[0]['detail']);
    }

    /**
     * The command is only useful if a broken machine actually fails the gate.
     * Point the connection at a dead port and assert it reports FAIL and exits
     * non-zero, rather than warning politely and letting CI through.
     */
    public function test_it_fails_when_the_database_is_unreachable(): void
    {
        config()->set('database.connections.mysql.port', 59999);
        // Without a short connect timeout this test spends ~20s waiting on TCP.
        config()->set('database.connections.mysql.options', [PDO::ATTR_TIMEOUT => 1]);
        DB::purge('mysql');

        $exitCode = Artisan::call('craftique:doctor', ['--json' => true]);
        $output = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode, 'A machine that cannot reach its database must fail the gate.');
        $this->assertFalse($output['ok']);
        $this->assertGreaterThan(0, $output['failures']);
    }

    /**
     * Redis is only load-bearing when something is pointed at it. When it is,
     * an unreachable server must fail the gate — a warning would let CI go
     * green on a machine where the queue silently does nothing.
     */
    public function test_unreachable_redis_fails_only_when_a_driver_depends_on_it(): void
    {
        config()->set('database.redis.default.port', 59998);

        // Nothing points at Redis: a warning is the correct, non-blocking answer.
        config()->set(['cache.default' => 'array', 'queue.default' => 'sync', 'session.driver' => 'array', 'broadcasting.default' => 'log']);
        $this->assertSame('WARN', $this->redisStatus());

        // Now the cache depends on it, so the same outage must be fatal.
        config()->set('cache.default', 'redis');
        $this->assertSame('FAIL', $this->redisStatus());
    }

    private function redisStatus(): string
    {
        $checks = array_values(array_filter(
            $this->runJson(false)['checks'],
            static fn (array $check): bool => $check['check'] === 'redis server',
        ));

        $this->assertNotEmpty($checks, 'The doctor never checked the Redis server.');

        return $checks[0]['status'];
    }

    public function test_json_output_is_machine_readable(): void
    {
        $output = $this->runJson();

        $this->assertArrayHasKey('ok', $output);
        $this->assertArrayHasKey('failures', $output);
        $this->assertArrayHasKey('warnings', $output);
        $this->assertArrayHasKey('checks', $output);
        $this->assertTrue($output['ok']);
        $this->assertSame(0, $output['failures']);

        foreach ($output['checks'] as $check) {
            $this->assertArrayHasKey('group', $check);
            $this->assertArrayHasKey('check', $check);
            $this->assertArrayHasKey('status', $check);
            $this->assertArrayHasKey('detail', $check);
            $this->assertContains($check['status'], ['PASS', 'WARN', 'FAIL']);
        }
    }

    /**
     * @return array{ok: bool, failures: int, warnings: int, checks: list<array{group: string, check: string, status: string, detail: string}>}
     */
    private function runJson(bool $assertHealthy = true): array
    {
        Artisan::call('craftique:doctor', ['--json' => true]);

        $decoded = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($decoded);

        if ($assertHealthy) {
            $this->assertSame(0, $decoded['failures'], 'Environment is not healthy; run php artisan craftique:doctor');
        }

        /** @var array{ok: bool, failures: int, warnings: int, checks: list<array{group: string, check: string, status: string, detail: string}>} $decoded */
        return $decoded;
    }
}
