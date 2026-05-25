<?php

declare(strict_types=1);

namespace App\Infrastructure\Ticketing\Spektrix;

use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\EventData;
use App\Domains\Ticketing\Data\PerformanceData;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use LogicException;
use UnexpectedValueException;

final class SpektrixTicketingProvider implements TicketingProvider
{
    public function providerKey(): string
    {
        return 'spektrix';
    }

    /**
     * @return Collection<int, EventData>
     */
    public function events(CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        return collect($this->request('events', [
            'instanceStart_from' => $from->toDateString(),
            'instanceStart_to' => $until->toDateString(),
        ]))->map(fn (array $payload): EventData => $this->mapEvent($payload));
    }

    /**
     * @return Collection<int, PerformanceData>
     */
    public function performances(CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        return collect($this->request('instances', [
            'startFrom' => $from->toDateString(),
            'startTo' => $until->toDateString(),
        ]))->map(fn (array $payload): PerformanceData => $this->mapPerformance($payload));
    }

    /**
     * @param  array<string, string>  $query
     * @return list<array<string, mixed>>
     */
    private function request(string $resource, array $query): array
    {
        $payload = $this->client()
            ->get($resource, $query)
            ->throw()
            ->json();

        if (! is_array($payload)) {
            throw new UnexpectedValueException("Spektrix {$resource} response is not a JSON array.");
        }

        $records = [];

        foreach ($payload as $record) {
            if (! is_array($record)) {
                throw new UnexpectedValueException("Spektrix {$resource} response includes an invalid record.");
            }

            $records[] = $record;
        }

        return $records;
    }

    private function client(): PendingRequest
    {
        $baseUrl = config('ticketing.providers.spektrix.base_url');

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new LogicException('A Spektrix API base URL must be configured.');
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->connectTimeout((int) config('ticketing.providers.spektrix.connect_timeout', 5))
            ->timeout((int) config('ticketing.providers.spektrix.timeout', 20))
            ->retry([200, 500, 1000]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapEvent(array $payload): EventData
    {
        return new EventData(
            externalId: $this->requiredString($payload, 'id'),
            title: $this->requiredString($payload, 'name'),
            summary: $this->nullableString($payload, 'description'),
            descriptionHtml: $this->nullableString($payload, 'htmlDescription'),
            durationMinutes: is_int($payload['duration'] ?? null) ? $payload['duration'] : null,
            imageUrl: $this->nullableString($payload, 'imageUrl'),
            thumbnailUrl: $this->nullableString($payload, 'thumbnailUrl'),
            imageAlt: $this->nullableString($payload, 'altText'),
            isOnSale: (bool) ($payload['isOnSale'] ?? false),
            firstPerformanceAt: $this->dateTimeOrNull($payload['firstInstanceDateTimeUtc'] ?? null),
            lastPerformanceAt: $this->dateTimeOrNull($payload['lastInstanceDateTimeUtc'] ?? null),
            sourcePayload: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapPerformance(array $payload): PerformanceData
    {
        $event = $payload['event'] ?? null;
        $priceList = $payload['priceList'] ?? null;

        if (! is_array($event)) {
            throw new UnexpectedValueException('Spektrix instance response does not include an event reference.');
        }

        return new PerformanceData(
            externalId: $this->requiredString($payload, 'id'),
            eventExternalId: $this->requiredString($event, 'id'),
            webId: $this->nullableString($payload, 'webInstanceId'),
            externalPlanId: $this->nullableString($payload, 'planId'),
            externalPriceListId: is_array($priceList) ? $this->nullableString($priceList, 'id') : null,
            startsAt: $this->dateTimeOrNull($payload['startUtc'] ?? null)
                ?? throw new UnexpectedValueException('Spektrix instance response does not include a UTC start date.'),
            salesStartAt: $this->dateTimeOrNull($payload['startSellingAtWebUtc'] ?? null),
            salesEndAt: $this->dateTimeOrNull($payload['stopSellingAtWebUtc'] ?? null),
            isOnSale: (bool) ($payload['isOnSale'] ?? false),
            isCancelled: (bool) ($payload['cancelled'] ?? false),
            sourcePayload: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requiredString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new UnexpectedValueException("Spektrix payload field {$key} must be a non-empty string.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function nullableString(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function dateTimeOrNull(mixed $value): ?CarbonImmutable
    {
        return is_string($value) && $value !== ''
            ? CarbonImmutable::parse($value)->utc()
            : null;
    }
}
