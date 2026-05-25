<?php

declare(strict_types=1);

namespace App\Domains\Events\Pricing;

use App\Domains\Ticketing\Data\PerformancePriceData;
use Illuminate\Support\Collection;

final class StandardPriceDisplayPolicy
{
    /**
     * @param  Collection<int, PerformancePriceData>  $prices
     */
    public function headlinePrice(Collection $prices): ?PerformancePriceData
    {
        return $prices
            ->filter(fn (PerformancePriceData $price): bool => $price->isBandDefault)
            ->sortBy(fn (PerformancePriceData $price): int => $price->amountMinor)
            ->first();
    }
}
