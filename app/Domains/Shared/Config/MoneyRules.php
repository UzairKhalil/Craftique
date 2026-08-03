<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use InvalidArgumentException;

/**
 * Settlement rules. All amounts are integer minor units (ADR-0004).
 *
 * @see config/craftique.php
 */
final readonly class MoneyRules
{
    public function __construct(
        public float $defaultCommissionRate,
        public int $payoutHoldDays,
        public int $minimumPayoutAmount,
        public int $supportRefundCeiling,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            defaultCommissionRate: self::float($values, 'default_commission_rate'),
            payoutHoldDays: self::int($values, 'payout_hold_days'),
            minimumPayoutAmount: self::int($values, 'minimum_payout_amount'),
            supportRefundCeiling: self::int($values, 'support_refund_ceiling'),
        );
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private static function int(array $values, string $key): int
    {
        $value = $values[$key] ?? null;

        if (! is_int($value)) {
            throw new InvalidArgumentException("craftique.{$key} must be an integer.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private static function float(array $values, string $key): float
    {
        $value = $values[$key] ?? null;

        if (! is_float($value) && ! is_int($value)) {
            throw new InvalidArgumentException("craftique.{$key} must be numeric.");
        }

        return (float) $value;
    }
}
