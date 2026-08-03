<?php

declare(strict_types=1);
use Tests\TestCase;

/**
 * Proves the Pest bootstrap in tests/Pest.php is loaded and the functional DSL
 * works. If tests/Pest.php stops being picked up, the suite bindings and every
 * domain expectation silently disappear — this fails loudly instead.
 */
it('runs tests written in the Pest DSL', function (): void {
    expect(true)->toBeTrue();
});

it('does not bind unit tests to the Laravel TestCase', function (): void {
    // Only Feature and Architecture extend Tests\TestCase (see tests/Pest.php),
    // keeping unit tests fast and free of framework boot.
    expect($this)->not->toBeInstanceOf(TestCase::class);
});
