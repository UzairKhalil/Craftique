<?php

declare(strict_types=1);

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Pins the Inertia wiring from ADR-0002. A 200 response proves very little —
 * these assert that pages are genuinely served as Inertia components with
 * shared props, which is what the rest of the application is built on.
 */
final class InertiaScaffoldingTest extends TestCase
{
    public function test_the_login_page_is_rendered_through_inertia(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/Login'));
    }

    public function test_the_register_page_is_rendered_through_inertia(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/Register'));
    }

    public function test_the_welcome_page_is_rendered_through_inertia(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Welcome'));
    }

    public function test_shared_props_expose_the_authenticated_user(): void
    {
        $this->get('/login')->assertInertia(
            fn (AssertableInertia $page) => $page->where('auth.user', null),
        );
    }
}
