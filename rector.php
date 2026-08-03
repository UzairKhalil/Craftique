<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetList;

/**
 * Rector runs in --dry-run in CI (T-M0-013): it reports drift rather than
 * rewriting code behind anyone's back. Applying changes stays a deliberate,
 * reviewed act via `vendor/bin/rector`.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
    )
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_IF_HELPERS,
    ])
    ->withSkip([
        // Pest test files are closures at the top level; Rector's class-shaped
        // rules produce noise rather than improvements there.
        __DIR__.'/tests/Pest.php',
        __DIR__.'/bootstrap/cache',
    ])
    ->withImportNames(removeUnusedImports: true);
