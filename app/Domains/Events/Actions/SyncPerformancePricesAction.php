<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\PerformancePrice;
use App\Domains\Events\Models\SyncRun;
use App\Domains\Events\Pricing\StandardPriceDisplayPolicy;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\PerformancePriceData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SyncPerformancePricesAction
{
    public function __construct(
        private readonly TicketingProvider $provider,
        private readonly StandardPriceDisplayPolicy $displayPolicy,
    ) {}

    public function execute(Performance $performance, SyncRun $syncRun): void
    {
        $prices = $this->provider->performancePrices($performance->external_id);
        $syncedAt = now();

        DB::transaction(function () use ($performance, $prices, $syncedAt): void {
            $this->persistPrices($performance, $prices, $syncedAt);

            $headlinePrice = $this->displayPolicy->headlinePrice($prices);

            $performance->update([
                'display_from_price_minor' => $headlinePrice?->amountMinor,
                'display_currency' => $headlinePrice?->currency,
                'has_dynamic_pricing' => $prices->contains(
                    fn (PerformancePriceData $price): bool => $price->isDynamicPricingEligible,
                ),
                'prices_synced_at' => $syncedAt,
            ]);
        });

        $syncRun->increment('performances_synced');
        $syncRun->increment('prices_synced', $prices->count());
    }

    /**
     * @param  Collection<int, PerformancePriceData>  $prices
     */
    private function persistPrices(Performance $performance, Collection $prices, CarbonInterface $syncedAt): void
    {
        $externalIds = [];

        $prices->each(function (PerformancePriceData $price) use ($performance, $syncedAt, &$externalIds): void {
            $externalIds[] = $price->externalId;

            PerformancePrice::updateOrCreate(
                [
                    'performance_id' => $performance->getKey(),
                    'provider' => $this->provider->providerKey(),
                    'external_id' => $price->externalId,
                ],
                [
                    'ticket_type_external_id' => $price->ticketTypeExternalId,
                    'ticket_type_name' => $price->ticketTypeName,
                    'price_band_external_id' => $price->priceBandExternalId,
                    'price_band_name' => $price->priceBandName,
                    'amount_minor' => $price->amountMinor,
                    'currency' => $price->currency,
                    'is_band_default' => $price->isBandDefault,
                    'is_dynamic_pricing_eligible' => $price->isDynamicPricingEligible,
                    'source_payload' => $price->sourcePayload,
                    'synced_at' => $syncedAt,
                ],
            );
        });

        $stalePrices = $performance->prices();

        if ($externalIds !== []) {
            $stalePrices->whereNotIn('external_id', $externalIds);
        }

        $stalePrices->delete();
    }
}
