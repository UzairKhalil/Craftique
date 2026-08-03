<?php

declare(strict_types=1);

/**
 * ADR-0014 keeps Craftique a modular monolith rather than a big ball of mud.
 * A domain may depend on Shared and may react to other domains via events, but
 * it may never reach into another domain's internals.
 *
 * This is scanned from source rather than expressed with Pest's arch() helpers
 * because most domains are still empty, and arch() over an empty namespace
 * asserts nothing at all — a rule that silently passes is worse than no rule.
 */
function appPath(string $relative = ''): string
{
    // Resolved from __DIR__ rather than appPath(): datasets are built before
    // the Laravel application boots.
    return dirname(__DIR__, 2).'/app'.($relative === '' ? '' : '/'.$relative);
}

/**
 * @return list<string>
 */
function domainNames(): array
{
    $names = array_map('basename', array_filter((array) glob(appPath('Domains/*')), 'is_dir'));
    sort($names);

    return $names;
}

/**
 * @return list<array{file: string, imported: string}>
 */
function crossDomainImports(string $domain): array
{
    $violations = [];
    $others = array_diff(domainNames(), [$domain, 'Shared']);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(appPath("Domains/{$domain}"), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        foreach ($others as $other) {
            // Events are the sanctioned cross-domain channel; anything else in
            // another domain's namespace is a boundary violation.
            if (preg_match("#^use\s+App\\\\Domains\\\\{$other}\\\\(?!Events\\\\)#m", $source) === 1) {
                $violations[] = [
                    'file' => str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $file->getPathname()),
                    'imported' => $other,
                ];
            }
        }
    }

    return $violations;
}

it('never imports another domain\'s internals', function (string $domain): void {
    $violations = crossDomainImports($domain);

    $message = implode(', ', array_map(
        static fn (array $v): string => "{$v['file']} imports {$v['imported']}",
        $violations,
    ));

    expect($violations)->toBe([], "Cross-domain import in {$domain}: {$message}");
})->with(domainNames());

it('bans debugging helpers from shipped code', function (): void {
    // Word-boundary anchored: a naive substring check matches `add(` for `dd(`
    // and `->dump(` inside legitimate identifiers.
    $banned = ['dd', 'dump', 'ray', 'var_dump', 'print_r', 'dispatch_sync_debug'];
    $found = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(appPath(), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        foreach ($banned as $needle) {
            if (preg_match('#(?<![\w>$\\\\])'.preg_quote($needle, '#').'\s*\(#', $source) === 1) {
                $found[] = str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $file->getPathname()).": {$needle}()";
            }
        }
    }

    expect($found)->toBe([]);
});

it('declares strict types in every PHP file under app/', function (): void {
    $missing = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(appPath(), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        if (! str_contains((string) file_get_contents($file->getPathname()), 'declare(strict_types=1)')) {
            $missing[] = str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }

    expect($missing)->toBe([]);
});
