<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\VerifyQueueRoundTrip;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class VerifyQueueRoundTripTest extends TestCase
{
    public function test_handling_the_job_writes_its_token_to_the_cache(): void
    {
        $job = new VerifyQueueRoundTrip('token-123');

        $this->assertNull(Cache::get($job->cacheKey()));

        $job->handle();

        $this->assertSame('token-123', Cache::get($job->cacheKey()));
    }

    public function test_the_cache_key_is_unique_per_token(): void
    {
        $this->assertNotSame(
            (new VerifyQueueRoundTrip('a'))->cacheKey(),
            (new VerifyQueueRoundTrip('b'))->cacheKey(),
        );
    }

    public function test_it_is_queued_rather_than_run_inline(): void
    {
        Queue::fake();

        $this->assertInstanceOf(ShouldQueue::class, new VerifyQueueRoundTrip('x'));

        dispatch(new VerifyQueueRoundTrip('x'));

        Queue::assertPushed(VerifyQueueRoundTrip::class);
    }
}
