<?php

declare(strict_types=1);

use App\Domains\CMS\Models\DonationFund;
use App\Domains\CMS\Models\Membership;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config()->set('ticketing.providers.spektrix.base_url', 'https://system.spektrix.com/apitesting/api/v3');
});

test('ticketing journey sync imports donation funds and memberships', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/funds*' => Http::response([
            [
                'id' => 'fund-1',
                'name' => 'Support Fund',
                'description' => 'Keep creativity going.',
                'code' => 'SUP',
                'defaultDonationAmount' => 10.00,
            ],
        ]),
        '*/memberships*' => Http::response([
            [
                'id' => 'member-1',
                'name' => 'Supporter',
                'description' => 'Member benefits',
                'htmlDescription' => '<p>Member benefits</p>',
                'imageUrl' => 'https://example.com/member.jpg',
                'thumbnailUrl' => 'https://example.com/member-thumb.jpg',
            ],
        ]),
    ]);

    $this->artisan('ticketing:sync-journeys')
        ->expectsOutputToContain('Synced 1 donation funds and 1 memberships.')
        ->assertSuccessful();

    expect(DonationFund::query()->count())->toBe(1)
        ->and(DonationFund::query()->sole()->external_id)->toBe('fund-1')
        ->and(DonationFund::query()->sole()->default_donation_amount_minor)->toBe(1000)
        ->and(Membership::query()->count())->toBe(1)
        ->and(Membership::query()->sole()->external_id)->toBe('member-1');
});

test('ticketing journey sync removes stale records no longer returned by provider', function () {
    DonationFund::factory()->create([
        'provider' => 'spektrix',
        'external_id' => 'stale-fund',
    ]);
    Membership::factory()->create([
        'provider' => 'spektrix',
        'external_id' => 'stale-membership',
    ]);

    Http::preventStrayRequests();
    Http::fake([
        '*/funds*' => Http::response([]),
        '*/memberships*' => Http::response([]),
    ]);

    $this->artisan('ticketing:sync-journeys')->assertSuccessful();

    expect(DonationFund::query()->count())->toBe(0)
        ->and(Membership::query()->count())->toBe(0);
});

test('ticketing journey sync is scheduled with default cadence and overlap protection', function () {
    config([
        'ticketing.journeys.sync_enabled' => true,
        'ticketing.journeys.sync_cron' => '*/30 * * * *',
    ]);

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($scheduledEvent): bool => str_contains($scheduledEvent->command, 'ticketing:sync-journeys'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('*/30 * * * *')
        ->and($event->withoutOverlapping)->toBeTrue()
        ->and($event->onOneServer)->toBeTrue()
        ->and($event->filtersPass(app()))->toBeTrue();
});

test('ticketing journey sync retries transient failures using configured retry policy', function () {
    config([
        'ticketing.journeys.retry_attempts' => 2,
        'ticketing.journeys.retry_backoff_seconds' => [1],
    ]);

    $provider = mock(TicketingProvider::class);
    $provider->shouldReceive('providerKey')
        ->times(2)
        ->andReturn('spektrix');
    $provider->shouldReceive('funds')
        ->once()
        ->andThrow(new RuntimeException('Temporary outage'));
    $provider->shouldReceive('funds')
        ->once()
        ->andReturn(collect());
    $provider->shouldReceive('memberships')
        ->once()
        ->andReturn(collect());

    app()->instance(TicketingProvider::class, $provider);

    $this->artisan('ticketing:sync-journeys')->assertSuccessful();
});

test('ticketing journey sync logs stale-data alerts with cooldown', function () {
    config([
        'ticketing.journeys.stale_after_minutes' => 60,
        'ticketing.journeys.stale_alert_cooldown_minutes' => 60,
    ]);

    DonationFund::factory()->create([
        'provider' => 'spektrix',
        'synced_at' => now()->subHours(4),
    ]);

    Log::spy();
    Http::preventStrayRequests();
    Http::fake([
        '*/funds*' => Http::response([]),
        '*/memberships*' => Http::response([]),
    ]);

    $this->artisan('ticketing:sync-journeys')->assertSuccessful();
    $this->artisan('ticketing:sync-journeys')->assertSuccessful();

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Ticketing journey data appears stale before sync run.'
            && ($context['provider'] ?? null) === 'spektrix'
            && ($context['stale_after_minutes'] ?? null) === 60);
});
