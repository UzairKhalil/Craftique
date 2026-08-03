<?php

declare(strict_types=1);
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature and Architecture tests boot the Laravel application; Unit tests do
| not, which keeps them fast and forces genuinely unit-testable design for the
| money, tax, commission and state-machine code that PROJECT_PLAN §8.4 requires
| 100% coverage on.
|
| RefreshDatabase is deliberately NOT applied globally. Most Feature tests here
| never touch the database, and tests that deliberately break the connection
| (see DoctorCommandTest) cannot participate in a wrapping transaction. Tests
| needing a clean database opt in with `uses(RefreshDatabase::class)`.
|
*/

pest()->extend(TestCase::class)->in('Feature');
pest()->extend(TestCase::class)->in('Architecture');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Domain-specific expectations live here so assertions read as the business
| rule they enforce rather than as plumbing.
|
*/

/**
 * Money is stored in integer minor units (ADR-0004). Floats must never appear
 * in a monetary value, so assert the type as well as the amount.
 */
expect()->extend('toBeMinorUnits', function (int $expected) {
    expect($this->value)->toBeInt()->toBe($expected);

    return $this;
});
