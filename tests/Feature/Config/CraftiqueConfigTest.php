<?php

declare(strict_types=1);

namespace Tests\Feature\Config;

use App\Domains\Shared\Config\CraftiqueConfig;
use Illuminate\Config\Repository;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * The point of a typed config object is that a typo or a wrong type fails
 * loudly at boot rather than silently becoming null three milestones later,
 * inside a money calculation. These tests pin that behaviour.
 */
final class CraftiqueConfigTest extends TestCase
{
    public function test_it_resolves_from_the_container_as_a_singleton(): void
    {
        $first = app(CraftiqueConfig::class);
        $second = app(CraftiqueConfig::class);

        $this->assertInstanceOf(CraftiqueConfig::class, $first);
        $this->assertSame($first, $second);
    }

    public function test_it_exposes_the_market_settings(): void
    {
        $config = app(CraftiqueConfig::class);

        $this->assertSame('USD', $config->currency);
        $this->assertSame('US', $config->country);
        $this->assertSame('en', $config->locale);
    }

    public function test_it_exposes_typed_business_rules(): void
    {
        $config = app(CraftiqueConfig::class);

        $this->assertSame('CRQ', $config->orders->numberPrefix);
        $this->assertSame(15, $config->orders->stockReservationMinutes);
        $this->assertSame(60, $config->orders->cancelUnpaidAfterMinutes);
        $this->assertSame(7, $config->orders->autoCompleteAfterDeliveryDays);

        $this->assertSame(10.0, $config->money->defaultCommissionRate);
        $this->assertSame(7, $config->money->payoutHoldDays);

        $this->assertSame(3, $config->catalog->maxOptionAxes);
        $this->assertSame(100, $config->catalog->maxVariants);

        $this->assertSame(30, $config->chat->messagesPerMinute);
        $this->assertSame(10, $config->customRequests->maxFiles);
        $this->assertSame(1, $config->vendors->storesPerUser);
        $this->assertSame(30, $config->guestCartLifetimeDays);
    }

    public function test_commission_rate_is_a_float_so_percentages_never_truncate(): void
    {
        // A commission rate silently cast to int would turn 12.5% into 12%.
        $this->assertIsFloat(app(CraftiqueConfig::class)->money->defaultCommissionRate);
    }

    public function test_it_rejects_a_missing_configuration_file(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CraftiqueConfig::fromRepository(new Repository([]));
    }

    public function test_it_rejects_a_value_of_the_wrong_type(): void
    {
        $values = config('craftique');
        $values['orders']['stock_reservation_minutes'] = 'fifteen';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stock_reservation_minutes must be an integer');

        CraftiqueConfig::fromRepository(new Repository(['craftique' => $values]));
    }

    public function test_it_rejects_an_empty_market_setting(): void
    {
        $values = config('craftique');
        $values['currency'] = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('currency must be a non-empty string');

        CraftiqueConfig::fromRepository(new Repository(['craftique' => $values]));
    }
}
