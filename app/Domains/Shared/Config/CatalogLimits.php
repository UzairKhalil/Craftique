<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use InvalidArgumentException;

/**
 * Catalog shape limits (FR-CAT-2, FR-CAT-5, FR-CAT-9).
 *
 * @see config/craftique.php
 */
final readonly class CatalogLimits
{
    public function __construct(
        public int $maxOptionAxes,
        public int $maxVariants,
        public int $maxImages,
        public int $maxSecondaryCategories,
        public int $autoTrustAfterApprovedProducts,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            maxOptionAxes: self::int($values, 'max_option_axes'),
            maxVariants: self::int($values, 'max_variants'),
            maxImages: self::int($values, 'max_images'),
            maxSecondaryCategories: self::int($values, 'max_secondary_categories'),
            autoTrustAfterApprovedProducts: self::int($values, 'auto_trust_after_approved_products'),
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
