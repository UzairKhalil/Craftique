<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Craftique
|--------------------------------------------------------------------------
|
| Business rules that vary by market or by policy. Everything here is read
| through App\Domains\Shared\Config\CraftiqueConfig rather than config() calls
| scattered through the domains, so the values are typed once and validated
| once (PROJECT_PLAN §8.4: no magic values in code).
|
| The launch-market assumptions recorded in TASKS.md live here, which is what
| makes them cheap to change.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Market
    |--------------------------------------------------------------------------
    |
    | The schema is multi-currency and i18n ready; v1 enforces a single active
    | currency and locale. Changing market is a config change plus reseeding
    | tax and shipping zones.
    |
    */

    'currency' => env('CRAFTIQUE_CURRENCY', 'USD'),
    'country' => env('CRAFTIQUE_COUNTRY', 'US'),
    'locale' => env('CRAFTIQUE_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    'orders' => [
        // Human-readable but not sequential-looking (FR-ORDER-2).
        'number_prefix' => env('CRAFTIQUE_ORDER_PREFIX', 'CRQ'),

        // FR-CART-8: stock is held while payment completes, then released.
        'stock_reservation_minutes' => 15,

        // FR-ORDER-10: auto-transitions.
        'cancel_unpaid_after_minutes' => 60,
        'auto_complete_after_delivery_days' => 7,
        'vendor_accept_deadline_hours' => 48,

        // FR-ORDER-6: free customer cancellation ends when production starts.
        'return_window_days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Money and settlement
    |--------------------------------------------------------------------------
    |
    | ADR-0004: all amounts are integer minor units. Rates are percentages.
    |
    */

    'money' => [
        'default_commission_rate' => (float) env('CRAFTIQUE_COMMISSION_RATE', 10.0),
        'payout_hold_days' => (int) env('CRAFTIQUE_PAYOUT_HOLD_DAYS', 7),
        'minimum_payout_amount' => (int) env('CRAFTIQUE_MIN_PAYOUT', 5000),
        'support_refund_ceiling' => (int) env('CRAFTIQUE_SUPPORT_REFUND_CEILING', 20000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalog limits
    |--------------------------------------------------------------------------
    |
    | FR-CAT-5, FR-CAT-2, FR-CAT-9.
    |
    */

    'catalog' => [
        'max_option_axes' => 3,
        'max_variants' => 100,
        'max_images' => 12,
        'max_secondary_categories' => 4,
        // FR-CAT-4: after this many approved products a vendor is auto-trusted
        // and moderation becomes spot-check.
        'auto_trust_after_approved_products' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    'cart' => [
        'guest_lifetime_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom requests
    |--------------------------------------------------------------------------
    |
    | FR-CUSTOM-6 and the brief upload limits from §20.2.
    |
    */

    'custom_requests' => [
        'max_files' => 10,
        'max_file_size_kb' => 10240,
        'expire_after_days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
    |
    | FR-CHAT-11 and the abuse limits from §21.6.
    |
    */

    'chat' => [
        'messages_per_minute' => 30,
        'new_conversations_per_day' => 100,
        'notify_after_unread_minutes' => 10,
        'notification_throttle_minutes' => 60,
        // allow | warn | block (FR-CHAT-10)
        'contact_sharing_policy' => env('CRAFTIQUE_CONTACT_SHARING', 'warn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendors
    |--------------------------------------------------------------------------
    */

    'vendors' => [
        'stores_per_user' => 1,
        'default_lead_time_min_days' => 1,
        'default_lead_time_max_days' => 3,
    ],

];
