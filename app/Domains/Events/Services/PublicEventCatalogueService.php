<?php

declare(strict_types=1);

namespace App\Domains\Events\Services;

use App\Domains\Events\Data\PublicEventData;
use App\Domains\Events\Data\PublicEventFilterOptionsData;
use App\Domains\Events\Data\PublicEventFiltersData;
use App\Domains\Events\Data\PublicFilterTermData;
use App\Domains\Events\Data\PublicPerformanceData;
use App\Domains\Events\Data\PublicPerformanceFiltersData;
use App\Domains\Events\Data\PublicPerformanceListingData;
use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventRedirect;
use App\Domains\Events\Models\FilterTerm;
use App\Domains\Events\Models\Performance;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\BookingHandoffData;
use App\Domains\Ticketing\Data\BookingHandoffRequestData;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PublicEventCatalogueService
{
    public function __construct(private readonly TicketingProvider $ticketingProvider) {}

    /**
     * @return LengthAwarePaginator<int, PublicEventData>
     */
    public function paginateUpcoming(PublicEventFiltersData $filters): LengthAwarePaginator
    {
        $query = $this->publishedEvents()
            ->where('last_performance_at', '>=', now());

        $this->applyFilters($query, $filters);

        return $query
            ->with($this->publicRelations())
            ->orderBy('first_performance_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Event $event): PublicEventData => $this->toPublicData($event));
    }

    public function findBySlug(string $slug): ?PublicEventData
    {
        $event = $this->publishedEvents()
            ->where(function (Builder $query) use ($slug): void {
                $query
                    ->whereHas('editorial', fn (Builder $editorial): Builder => $editorial->where('slug', $slug))
                    ->orWhere(function (Builder $providerSlugQuery) use ($slug): void {
                        $providerSlugQuery
                            ->where('slug', $slug)
                            ->whereHas(
                                'editorial',
                                fn (Builder $editorial): Builder => $editorial->whereNull('slug'),
                            );
                    });
            })
            ->with($this->publicRelations())
            ->first();

        return $event instanceof Event ? $this->toPublicData($event) : null;
    }

    public function filterOptions(): PublicEventFilterOptionsData
    {
        $terms = FilterTerm::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return new PublicEventFilterOptionsData(
            what: $terms->filter(fn (FilterTerm $term): bool => $term->filter_group === FilterGroup::What)->values(),
            offers: $terms->filter(fn (FilterTerm $term): bool => $term->filter_group === FilterGroup::Offers)->values(),
            access: $terms->filter(fn (FilterTerm $term): bool => $term->filter_group === FilterGroup::Access)->values(),
        );
    }

    public function performanceListing(PublicEventData $event, PublicPerformanceFiltersData $filters): PublicPerformanceListingData
    {
        $accessOptions = $event->performances
            ->flatMap(fn (PublicPerformanceData $performance) => $performance->accessProvisions)
            ->unique(fn (PublicFilterTermData $term): string => $term->slug)
            ->sortBy(fn (PublicFilterTermData $term): string => $term->name)
            ->values();

        $performances = $event->performances
            ->filter(fn (PublicPerformanceData $performance): bool => $this->performanceMatchesFilters($performance, $filters))
            ->values();

        return new PublicPerformanceListingData(
            performances: $performances,
            accessOptions: $accessOptions,
        );
    }

    public function redirectForPath(string $path): ?EventRedirect
    {
        return EventRedirect::query()
            ->where('source_path', '/'.ltrim($path, '/'))
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return Builder<Event>
     */
    private function publishedEvents(): Builder
    {
        return Event::query()
            ->whereHas('editorial', function (Builder $editorial): void {
                $editorial
                    ->where('is_published', true)
                    ->where(function (Builder $publishedAt): void {
                        $publishedAt
                            ->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    });
            });
    }

    /**
     * @return array<int|string, mixed>
     */
    private function publicRelations(): array
    {
        return [
            'editorial',
            'performances' => function ($performances): void {
                $performances
                    ->where('starts_at', '>=', now())
                    ->where('is_cancelled', false)
                    ->orderBy('starts_at');
            },
            'performances.accessTerms',
        ];
    }

    /**
     * @param  Builder<Event>  $query
     */
    private function applyFilters(Builder $query, PublicEventFiltersData $filters): void
    {
        if ($filters->query !== null) {
            $this->applyPublicTextSearch($query, $filters->query);
        }

        match ($filters->dateWindow) {
            'next-30-days' => $query->where('first_performance_at', '<=', now()->addDays(30)),
            'next-90-days' => $query->where('first_performance_at', '<=', now()->addDays(90)),
            default => null,
        };

        if ($filters->what !== []) {
            $query->whereHas(
                'whatTerms',
                fn (Builder $terms): Builder => $terms->whereIn('filter_terms.slug', $filters->what),
            );
        }

        if ($filters->offers !== []) {
            $query->whereHas(
                'offerTerms',
                fn (Builder $terms): Builder => $terms->whereIn('filter_terms.slug', $filters->offers),
            );
        }

        if ($filters->access !== []) {
            $query->whereHas('performances', function (Builder $performances) use ($filters): void {
                $performances
                    ->where('starts_at', '>=', now())
                    ->where('is_cancelled', false)
                    ->whereHas(
                        'accessTerms',
                        fn (Builder $terms): Builder => $terms->whereIn('filter_terms.slug', $filters->access),
                    );
            });
        }
    }

    /**
     * @param  Builder<Event>  $query
     */
    private function applyPublicTextSearch(Builder $query, string $term): void
    {
        $likeTerm = "%{$term}%";

        $query->where(function (Builder $search) use ($likeTerm): void {
            $search
                ->where(function (Builder $titles) use ($likeTerm): void {
                    $titles
                        ->whereHas('editorial', fn (Builder $editorial): Builder => $editorial
                            ->whereNotNull('title')
                            ->where('title', '<>', '')
                            ->whereLike('title', $likeTerm))
                        ->orWhere(function (Builder $sourceTitle) use ($likeTerm): void {
                            $sourceTitle
                                ->whereLike('title', $likeTerm)
                                ->whereHas('editorial', fn (Builder $editorial): Builder => $editorial
                                    ->where(function (Builder $override): void {
                                        $override->whereNull('title')->orWhere('title', '');
                                    }));
                        });
                })
                ->orWhere(function (Builder $summaries) use ($likeTerm): void {
                    $summaries
                        ->whereHas('editorial', fn (Builder $editorial): Builder => $editorial
                            ->whereNotNull('summary')
                            ->where('summary', '<>', '')
                            ->whereLike('summary', $likeTerm))
                        ->orWhere(function (Builder $sourceSummary) use ($likeTerm): void {
                            $sourceSummary
                                ->whereLike('summary', $likeTerm)
                                ->whereHas('editorial', fn (Builder $editorial): Builder => $editorial
                                    ->where(function (Builder $override): void {
                                        $override->whereNull('summary')->orWhere('summary', '');
                                    }));
                        });
                });
        });
    }

    private function performanceMatchesFilters(PublicPerformanceData $performance, PublicPerformanceFiltersData $filters): bool
    {
        if ($filters->date !== null && $performance->startsAt->format('Y-m-d') !== $filters->date) {
            return false;
        }

        $now = CarbonImmutable::now($performance->startsAt->getTimezone());
        $withinWindow = match ($filters->dateWindow) {
            'today' => $performance->startsAt->isSameDay($now),
            'this-week' => $performance->startsAt->lessThanOrEqualTo($now->endOfWeek()),
            'this-month' => $performance->startsAt->lessThanOrEqualTo($now->endOfMonth()),
            default => true,
        };

        if (! $withinWindow) {
            return false;
        }

        if ($filters->access === []) {
            return true;
        }

        return $performance->accessProvisions->contains(
            fn (PublicFilterTermData $term): bool => in_array($term->slug, $filters->access, true),
        );
    }

    private function toPublicData(Event $event): PublicEventData
    {
        return PublicEventData::fromModel(
            $event,
            fn (Performance $performance): ?BookingHandoffData => $this->ticketingProvider->bookingHandoff(
                new BookingHandoffRequestData(
                    performanceExternalId: $performance->external_id,
                    webPerformanceId: $performance->web_id,
                ),
            ),
        );
    }
}
