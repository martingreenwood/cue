<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Jobs\DownloadEventImageJob;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\EventData;
use App\Domains\Ticketing\Data\PerformanceData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class SyncCatalogueAction
{
    public function __construct(private readonly TicketingProvider $provider) {}

    public function execute(SyncRun $syncRun): SyncRun
    {
        $from = CarbonImmutable::today('UTC')
            ->subDays(max(0, (int) config('ticketing.catalogue.past_days', 0)));
        $until = CarbonImmutable::today('UTC')
            ->addDays(max(1, (int) config('ticketing.catalogue.future_days', 730)));

        $syncRun->update([
            'status' => SyncRunStatus::Running,
            'started_at' => now(),
            'context' => [
                'from' => $from->toDateString(),
                'until' => $until->toDateString(),
            ],
        ]);

        try {
            $events = $this->provider->events($from, $until);
            $performances = $this->provider->performances($from, $until);

            /** @var list<int> $eventsNeedingImages */
            $eventsNeedingImages = [];

            DB::transaction(function () use ($events, $performances, &$eventsNeedingImages): void {
                /** @var Collection<string, Event> $persistedEvents */
                $persistedEvents = $events->mapWithKeys(fn (EventData $event): array => [
                    $event->externalId => $this->persistEvent($event),
                ]);

                $eventsNeedingImages = $persistedEvents
                    ->filter(fn (Event $event): bool => $event->image_url !== null
                        && ($event->local_image_path === null || $event->wasChanged('image_url'))
                    )
                    ->map(fn (Event $event): int => (int) $event->getKey())
                    ->values()
                    ->all();

                $performances->each(function (PerformanceData $performance) use ($persistedEvents): void {
                    $event = $persistedEvents->get($performance->eventExternalId);

                    if ($event === null) {
                        throw new RuntimeException("Cannot sync performance {$performance->externalId}: its event was not returned by the provider.");
                    }

                    $this->persistPerformance($performance, $event);
                });
            });

            foreach ($eventsNeedingImages as $eventId) {
                DownloadEventImageJob::dispatch($eventId);
            }

            $syncRun->update([
                'status' => SyncRunStatus::Succeeded,
                'finished_at' => now(),
                'events_synced' => $events->count(),
                'performances_synced' => $performances->count(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $syncRun->update([
                'status' => SyncRunStatus::Failed,
                'finished_at' => now(),
                'error_message' => Str::limit($exception->getMessage(), 1000),
            ]);

            throw $exception;
        }

        return $syncRun->refresh();
    }

    private function persistEvent(EventData $data): Event
    {
        $event = Event::firstOrNew([
            'provider' => $this->provider->providerKey(),
            'external_id' => $data->externalId,
        ]);

        if (! $event->exists) {
            $event->slug = $this->newSlug($data);
        }

        $event->fill([
            'title' => $data->title,
            'summary' => $data->summary,
            'description_html' => $data->descriptionHtml,
            'duration_minutes' => $data->durationMinutes,
            'image_url' => $data->imageUrl,
            'thumbnail_url' => $data->thumbnailUrl,
            'image_alt' => $data->imageAlt,
            'is_on_sale' => $data->isOnSale,
            'first_performance_at' => $data->firstPerformanceAt,
            'last_performance_at' => $data->lastPerformanceAt,
            'source_payload' => $data->sourcePayload,
            'synced_at' => now(),
        ])->save();

        return $event;
    }

    private function persistPerformance(PerformanceData $data, Event $event): Performance
    {
        return Performance::updateOrCreate(
            [
                'provider' => $this->provider->providerKey(),
                'external_id' => $data->externalId,
            ],
            [
                'event_id' => $event->getKey(),
                'web_id' => $data->webId,
                'external_plan_id' => $data->externalPlanId,
                'external_price_list_id' => $data->externalPriceListId,
                'starts_at' => $data->startsAt,
                'sales_start_at' => $data->salesStartAt,
                'sales_end_at' => $data->salesEndAt,
                'is_on_sale' => $data->isOnSale,
                'is_cancelled' => $data->isCancelled,
                'source_payload' => $data->sourcePayload,
                'synced_at' => now(),
            ],
        );
    }

    private function newSlug(EventData $event): string
    {
        $base = Str::slug($event->title);
        $suffix = Str::lower(Str::substr($event->externalId, -8));

        return ($base !== '' ? $base : 'event').'-'.$suffix;
    }
}
