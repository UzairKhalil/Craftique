<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use InvalidArgumentException;

/**
 * Order lifecycle rules (FR-ORDER, FR-CART-8).
 *
 * @see config/craftique.php
 */
final readonly class OrderRules
{
    public function __construct(
        public string $numberPrefix,
        public int $stockReservationMinutes,
        public int $cancelUnpaidAfterMinutes,
        public int $autoCompleteAfterDeliveryDays,
        public int $vendorAcceptDeadlineHours,
        public int $returnWindowDays,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            numberPrefix: self::string($values, 'number_prefix'),
            stockReservationMinutes: self::int($values, 'stock_reservation_minutes'),
            cancelUnpaidAfterMinutes: self::int($values, 'cancel_unpaid_after_minutes'),
            autoCompleteAfterDeliveryDays: self::int($values, 'auto_complete_after_delivery_days'),
            vendorAcceptDeadlineHours: self::int($values, 'vendor_accept_deadline_hours'),
            returnWindowDays: self::int($values, 'return_window_days'),
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
    private static function string(array $values, string $key): string
    {
        $value = $values[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("craftique.{$key} must be a non-empty string.");
        }

        return $value;
    }
}
