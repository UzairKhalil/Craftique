<?php

declare(strict_types=1);

namespace App\Domains\Shared\Config;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

/**
 * Typed access to config/craftique.php.
 *
 * Domains read business rules through this object rather than calling
 * config('craftique.…') directly. That buys three things: the values are typed
 * once instead of being cast at every call site, a typo becomes a failing test
 * rather than a silent null, and every tunable rule has one discoverable home
 * (PROJECT_PLAN §8.4 — no magic values in code).
 *
 * Bound as a singleton in AppServiceProvider, so the validation below runs once
 * per request rather than on every read.
 */
final readonly class CraftiqueConfig
{
    public function __construct(
        public string $currency,
        public string $country,
        public string $locale,
        public OrderRules $orders,
        public MoneyRules $money,
        public CatalogLimits $catalog,
        public ChatRules $chat,
        public CustomRequestRules $customRequests,
        public VendorRules $vendors,
        public int $guestCartLifetimeDays,
    ) {}

    public static function fromRepository(Repository $config): self
    {
        /** @var array<string, mixed> $values */
        $values = $config->get('craftique', []);

        if ($values === []) {
            throw new InvalidArgumentException(
                'config/craftique.php is missing or empty. Did the config cache go stale?',
            );
        }

        return new self(
            currency: self::string($values, 'currency'),
            country: self::string($values, 'country'),
            locale: self::string($values, 'locale'),
            orders: OrderRules::fromArray(self::section($values, 'orders')),
            money: MoneyRules::fromArray(self::section($values, 'money')),
            catalog: CatalogLimits::fromArray(self::section($values, 'catalog')),
            chat: ChatRules::fromArray(self::section($values, 'chat')),
            customRequests: CustomRequestRules::fromArray(self::section($values, 'custom_requests')),
            vendors: VendorRules::fromArray(self::section($values, 'vendors')),
            guestCartLifetimeDays: self::int(self::section($values, 'cart'), 'guest_lifetime_days'),
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function section(array $values, string $key): array
    {
        $section = $values[$key] ?? null;

        if (! is_array($section)) {
            throw new InvalidArgumentException("craftique.{$key} must be an array.");
        }

        /** @var array<string, mixed> $section */
        return $section;
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
