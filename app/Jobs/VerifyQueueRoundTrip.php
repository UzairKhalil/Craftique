<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/**
 * Proves the whole queue path works end to end: dispatch -> Redis -> worker
 * -> execution -> cache write. "Redis answers PING" is a much weaker claim
 * than "a job actually made it through a worker", and the difference is where
 * serialization and connection-prefix bugs hide.
 *
 * Used for manual verification and by the CI queue smoke test:
 *
 *   php artisan tinker --execute="dispatch(new App\Jobs\VerifyQueueRoundTrip('x'));"
 *   php artisan queue:work --stop-when-empty
 *
 * @see docs/runbooks/environment.md
 */
final class VerifyQueueRoundTrip implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
    ) {}

    public function handle(): void
    {
        Cache::put($this->cacheKey(), $this->token, now()->addMinutes(5));
    }

    public function cacheKey(): string
    {
        return "doctor:queue-round-trip:{$this->token}";
    }
}
