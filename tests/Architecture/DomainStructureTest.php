<?php

declare(strict_types=1);

/**
 * The modular monolith from ADR-0014 only works if the modules actually exist
 * and stay documented. A domain silently disappearing, or appearing without a
 * stated responsibility, is how a modular monolith becomes a big ball of mud.
 */
const DOMAINS = [
    'Analytics',
    'Cart',
    'Catalog',
    'Content',
    'CustomOrder',
    'Identity',
    'Inventory',
    'Messaging',
    'Notification',
    'Ordering',
    'Payment',
    'Platform',
    'Pricing',
    'Review',
    'Search',
    'Shared',
    'Shipping',
    'Vendor',
];

it('has every domain module from PROJECT_PLAN §9.3', function (string $domain): void {
    expect(app_path("Domains/{$domain}"))->toBeDirectory();
})->with(DOMAINS);

it('documents what each domain is responsible for', function (string $domain): void {
    $readme = app_path("Domains/{$domain}/README.md");

    expect($readme)->toBeReadableFile();
    expect(file_get_contents($readme))
        ->toContain('## Owns')
        ->toContain('## Does not own')
        ->toContain('## Boundaries');
})->with(DOMAINS);

it('autoloads the Domains namespace under the App PSR-4 prefix', function (): void {
    $composer = json_decode(
        (string) file_get_contents(base_path('composer.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($composer['autoload']['psr-4'])
        ->toHaveKey('App\\')
        ->and($composer['autoload']['psr-4']['App\\'])->toBe('app/');
});

it('defines exactly the documented set of domains, no more', function (): void {
    $actual = array_map(
        'basename',
        array_filter((array) glob(app_path('Domains/*')), 'is_dir'),
    );

    sort($actual);

    expect($actual)->toBe(DOMAINS);
});
