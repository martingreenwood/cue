<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

use App\Domains\Events\Models\FilterTerm;
use App\Domains\Events\Models\Performance;
use App\Domains\Ticketing\Data\BookingHandoffData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

final readonly class PublicPerformanceData
{
    /**
     * @param  Collection<int, PublicFilterTermData>  $accessProvisions
     */
    public function __construct(
        public int $id,
        public CarbonImmutable $startsAt,
        public bool $isOnSale,
        public ?int $displayFromPriceMinor,
        public ?string $displayPrice,
        public bool $hasDynamicPricing,
        public bool $priceIsStale,
        public ?string $bookingUrl,
        public ?string $bookingEmbedScriptUrl,
        public Collection $accessProvisions,
    ) {}

    public static function fromModel(Performance $performance, ?BookingHandoffData $bookingHandoff = null): self
    {
        $startsAt = $performance->starts_at->setTimezone(
            (string) config('ticketing.display_timezone', 'Europe/London'),
        );
        $displayFromPriceMinor = $performance->display_from_price_minor;
        $currency = $performance->display_currency ?? (string) config('ticketing.pricing.currency', 'GBP');
        $pricesSyncedAt = $performance->prices_synced_at;

        return new self(
            id: $performance->getKey(),
            startsAt: $startsAt,
            isOnSale: (bool) $performance->is_on_sale,
            displayFromPriceMinor: $displayFromPriceMinor,
            displayPrice: self::formatPrice($displayFromPriceMinor, $currency),
            hasDynamicPricing: (bool) $performance->has_dynamic_pricing,
            priceIsStale: $pricesSyncedAt === null || $pricesSyncedAt->isBefore(
                now()->subMinutes((int) config('ticketing.pricing.stale_after_minutes', 60)),
            ),
            bookingUrl: $bookingHandoff?->url,
            bookingEmbedScriptUrl: $bookingHandoff?->embedScriptUrl,
            accessProvisions: $performance->accessTerms
                ->sortBy('sort_order')
                ->map(fn (FilterTerm $term): PublicFilterTermData => PublicFilterTermData::fromModel($term))
                ->values(),
        );
    }

    private static function formatPrice(?int $amountMinor, string $currency): ?string
    {
        if ($amountMinor === null) {
            return null;
        }

        if ($amountMinor === 0 && config('ticketing.pricing.zero_price_display', 'free') === 'free') {
            return 'Free';
        }

        return Number::currency($amountMinor / 100, in: $currency);
    }
}
