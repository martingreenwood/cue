<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domains\CMS\Models\DonationFund;
use App\Domains\CMS\Models\Membership;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JourneySyncHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '30s';

    public function getStats(): array
    {
        $provider = app(TicketingProvider::class);
        $providerKey = $provider->providerKey();
        $staleAfterMinutes = max((int) config('ticketing.journeys.stale_after_minutes', 180), 1);

        $latestFundSync = DonationFund::query()
            ->where('provider', $providerKey)
            ->max('synced_at');
        $latestMembershipSync = Membership::query()
            ->where('provider', $providerKey)
            ->max('synced_at');
        $latestSync = collect([$latestFundSync, $latestMembershipSync])
            ->filter()
            ->map(static fn (mixed $value): CarbonImmutable => CarbonImmutable::parse((string) $value))
            ->max();

        $visibleFundIds = DonationFund::query()
            ->where('provider', $providerKey)
            ->where('is_visible', true)
            ->pluck('external_id');
        $visibleMembershipIds = Membership::query()
            ->where('provider', $providerKey)
            ->where('is_visible', true)
            ->pluck('external_id');

        $providerFundIds = $provider->funds()
            ->pluck('externalId');
        $providerMembershipIds = $provider->memberships()
            ->pluck('externalId');

        $fundMismatchCount = $visibleFundIds->diff($providerFundIds)->count()
            + $providerFundIds->diff($visibleFundIds)->count();
        $membershipMismatchCount = $visibleMembershipIds->diff($providerMembershipIds)->count()
            + $providerMembershipIds->diff($visibleMembershipIds)->count();
        $totalMismatchCount = $fundMismatchCount + $membershipMismatchCount;

        return [
            Stat::make('Journey sync freshness', $latestSync !== null ? $latestSync->diffForHumans() : 'Never synced')
                ->description($latestSync !== null
                    ? "Expected within {$staleAfterMinutes} min"
                    : 'Run ticketing:sync-journeys to import memberships and funds'
                )
                ->descriptionIcon($latestSync !== null ? 'heroicon-m-arrow-path' : 'heroicon-m-clock')
                ->color($latestSync !== null && $latestSync->gt(now()->subMinutes($staleAfterMinutes)) ? 'success' : 'warning'),

            Stat::make('Journey set mismatches', (string) $totalMismatchCount)
                ->description("Funds: {$fundMismatchCount}, memberships: {$membershipMismatchCount}")
                ->descriptionIcon($totalMismatchCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($totalMismatchCount > 0 ? 'warning' : 'success'),
        ];
    }
}
