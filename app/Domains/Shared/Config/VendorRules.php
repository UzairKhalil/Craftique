<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use InvalidArgumentException;

/**
 * Vendor defaults (FR-VENDOR-1, FR-VENDOR-8).
 *
 * @see config/craftique.php
 */
final readonly class VendorRules
{
    public function __construct(
        public int $storesPerUser,
        public int $defaultLeadTimeMinDays,
        public int $defaultLeadTimeMaxDays,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            storesPerUser: self::int($values, 'stores_per_user'),
            defaultLeadTimeMinDays: self::int($values, 'default_lead_time_min_days'),
            defaultLeadTimeMaxDays: self::int($values, 'default_lead_time_max_days'),
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
}
