<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use InvalidArgumentException;

/**
 * Bespoke commission limits (FR-CUSTOM-6, §20.2).
 *
 * @see config/craftique.php
 */
final readonly class CustomRequestRules
{
    public function __construct(
        public int $maxFiles,
        public int $maxFileSizeKb,
        public int $expireAfterDays,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            maxFiles: self::int($values, 'max_files'),
            maxFileSizeKb: self::int($values, 'max_file_size_kb'),
            expireAfterDays: self::int($values, 'expire_after_days'),
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
