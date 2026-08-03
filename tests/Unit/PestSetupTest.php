<?php

declare(strict_types=1);

/**
 * Proves the Pest bootstrap in tests/Pest.php is loaded: the functional DSL
 * works and the project's custom expectations are registered. If tests/Pest.php
 * stops being picked up, every domain expectation silently disappears — this
 * fails loudly instead.
 */
it('runs tests written in the Pest DSL', function (): void {
    expect(true)->toBeTrue();
});

it('registers the toBeMinorUnits expectation from tests/Pest.php', function (): void {
    expect(4599)->toBeMinorUnits(4599);
});

it('rejects a float posing as money', function (): void {
    expect(fn () => expect(45.99)->toBeMinorUnits(4599))->toThrow(Exception::class);
});
