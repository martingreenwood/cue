<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\Performance;
use App\Domains\Ticketing\Data\BookingHandoffData;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Collection;

final readonly class PublicEventData
{
    /**
     * @param  Collection<int, PublicPerformanceData>  $performances
     */
    public function __construct(
        public string $slug,
        public string $title,
        public ?string $summary,
        public ?string $description,
        public ?string $imagePath,
        public ?string $imageAlt,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public ?int $durationMinutes,
        public ?CarbonImmutable $firstPerformanceAt,
        public ?CarbonImmutable $lastPerformanceAt,
        public Collection $performances,
        public ?string $fromPrice,
    ) {}

    /**
     * @param  (Closure(Performance): ?BookingHandoffData)|null  $bookingHandoff
     */
    public static function fromModel(Event $event, ?Closure $bookingHandoff = null): self
    {
        $editorial = $event->editorial;
        $timezone = (string) config('ticketing.display_timezone', 'Europe/London');
        $firstPerformanceAt = $event->first_performance_at?->setTimezone($timezone);
        $lastPerformanceAt = $event->last_performance_at?->setTimezone($timezone);
        $performances = $event->performances
            ->map(fn (Performance $performance): PublicPerformanceData => PublicPerformanceData::fromModel(
                $performance,
                $bookingHandoff?->__invoke($performance),
            ));
        $lowestPricedPerformance = $performances
            ->whereNotNull('displayFromPriceMinor')
            ->sortBy('displayFromPriceMinor')
            ->first();

        return new self(
            slug: $editorial?->slug ?: $event->slug,
            title: $editorial?->title ?: $event->title,
            summary: $editorial?->summary ?: $event->summary,
            description: self::plainText($editorial?->description_html ?: $event->description_html),
            imagePath: $editorial?->hero_image_path ?: $event->local_image_path,
            imageAlt: $editorial?->hero_image_alt ?: $event->image_alt,
            seoTitle: $editorial?->seo_title,
            seoDescription: $editorial?->seo_description,
            durationMinutes: $event->duration_minutes,
            firstPerformanceAt: $firstPerformanceAt,
            lastPerformanceAt: $lastPerformanceAt,
            performances: $performances,
            fromPrice: $lowestPricedPerformance?->displayPrice,
        );
    }

    public function bookingPerformance(?int $performanceId): ?PublicPerformanceData
    {
        if ($performanceId === null) {
            return null;
        }

        $performance = $this->performances->first(
            fn (PublicPerformanceData $performance): bool => $performance->id === $performanceId
                && $performance->isOnSale
                && $performance->bookingUrl !== null,
        );

        return $performance instanceof PublicPerformanceData ? $performance : null;
    }

    private static function plainText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $description = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $description !== '' ? $description : null;
    }
}
