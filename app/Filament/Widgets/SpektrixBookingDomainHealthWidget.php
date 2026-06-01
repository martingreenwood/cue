<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Infrastructure\Ticketing\Spektrix\SpektrixCustomDomainReadiness;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpektrixBookingDomainHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 2;

    protected ?string $pollingInterval = null;

    public function getStats(): array
    {
        $readiness = new SpektrixCustomDomainReadiness;
        $hostname = $readiness->hostname();

        if ($hostname === null) {
            return [
                Stat::make('Booking custom domain', 'Not configured')
                    ->description('Configure the customer-facing Spektrix URL before booking launch.')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        if (! $readiness->usesCustomDomain()) {
            return [
                Stat::make('Booking custom domain', $hostname)
                    ->description('System domain is suitable for demo use only; custom domain required for launch.')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        if (! $readiness->usesHttps() || ! $readiness->hasClientPath()) {
            return [
                Stat::make('Booking custom domain', $hostname)
                    ->description('Use an HTTPS custom URL including the Spektrix client name path.')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        if (! $readiness->isConfirmedBySpektrix()) {
            return [
                Stat::make('Booking custom domain', $hostname)
                    ->description('Custom domain configured; await Spektrix confirmation before cutover.')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),
            ];
        }

        return [
            Stat::make('Booking custom domain', $hostname)
                ->description('Confirmed custom domain used for iframe and integrate.js.')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
