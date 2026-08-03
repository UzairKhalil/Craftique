<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Shared\Config\CraftiqueConfig;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton so the config validation runs once per request rather than
        // on every read.
        $this->app->singleton(
            CraftiqueConfig::class,
            static fn ($app): CraftiqueConfig => CraftiqueConfig::fromRepository($app->make(Repository::class)),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->enforceModelDiscipline();
    }

    /**
     * PROJECT_PLAN §26.1: N+1 queries are a defect, not a performance nuance.
     * Outside production they throw, so they surface in development and CI
     * instead of in a customer's checkout.
     */
    private function enforceModelDiscipline(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());
    }
}
