<?php

declare(strict_types=1);

namespace App\Infrastructure\Ticketing\Spektrix;

use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Domains\Ticketing\Data\BookingHandoffData;
use App\Domains\Ticketing\Data\BookingHandoffRequestData;
use App\Domains\Ticketing\Data\CustomerAuthenticationData;
use App\Domains\Ticketing\Data\CustomerJourneyData;
use App\Domains\Ticketing\Data\CustomerSessionData;
use App\Domains\Ticketing\Data\EventData;
use App\Domains\Ticketing\Data\PerformanceData;
use App\Domains\Ticketing\Data\PerformancePriceData;
use App\Domains\Ticketing\Enums\CustomerJourney;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use LogicException;
use UnexpectedValueException;

final class SpektrixTicketingProvider implements TicketingProvider
{
    public function __construct(
        private readonly SpektrixCustomDomainReadiness $customDomainReadiness = new SpektrixCustomDomainReadiness,
    ) {}

    public function providerKey(): string
    {
        return 'spektrix';
    }

    public function bookingHandoff(BookingHandoffRequestData $performance): ?BookingHandoffData
    {
        $baseUrl = $this->customDomainReadiness->activeBookingBaseUrl();

        if ($baseUrl === null) {
            return null;
        }

        $query = $this->bookingHandoffQuery($performance);

        if ($query === null) {
            return null;
        }

        return new BookingHandoffData(
            url: Uri::of(rtrim($baseUrl, '/').'/website/ChooseSeats.aspx')
                ->withQuery([...$query, 'resize' => 'true'])
                ->value(),
            embedScriptUrl: Uri::of(rtrim($baseUrl, '/').'/website/scripts/integrate.js')->value(),
        );
    }

    public function customerSession(): ?CustomerSessionData
    {
        return $this->customDomainReadiness->customerSession();
    }

    public function customerAuthentication(): ?CustomerAuthenticationData
    {
        $baseUrl = $this->customDomainReadiness->activeBookingBaseUrl();

        if ($baseUrl === null) {
            return null;
        }

        return new CustomerAuthenticationData(
            authenticateUrl: Uri::of(rtrim($baseUrl, '/').'/api/v3/customer/authenticate')->value(),
            createCustomerUrl: Uri::of(rtrim($baseUrl, '/').'/api/v3/customer')->value(),
            sendMagicLinkUrl: Uri::of(rtrim($baseUrl, '/').'/api/v3/customer/send-magic-link')->value(),
            authenticateMagicLinkUrl: Uri::of(rtrim($baseUrl, '/').'/api/v3/customer/authenticate-magic-link')->value(),
        );
    }

    public function customerJourney(CustomerJourney $journey): ?CustomerJourneyData
    {
        $baseUrl = $this->customDomainReadiness->activeBookingBaseUrl();

        if ($baseUrl === null) {
            return null;
        }

        $path = match ($journey) {
            CustomerJourney::Basket => '/website/Basket2.aspx',
            CustomerJourney::Checkout => '/website/secure/Checkout.aspx',
            CustomerJourney::PasswordReset => '/website/Secure/SetPassword.aspx',
            CustomerJourney::Redeem => '/website/secure/RedeemGift.aspx',
            CustomerJourney::Renew => '/website/Memberships.aspx',
        };

        return new CustomerJourneyData(
            iframeUrl: Uri::of(rtrim($baseUrl, '/').$path)->value(),
            embedScriptUrl: Uri::of(rtrim($baseUrl, '/').'/website/scripts/integrate.js')->value(),
        );
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
     * @return Collection<int, PerformancePriceData>
     */
    public function performancePrices(string $performanceExternalId): Collection
    {
        $payload = $this->requestObject('instances/'.rawurlencode($performanceExternalId).'/price-list');
        $prices = $payload['prices'] ?? null;

        if (! is_array($prices)) {
            throw new UnexpectedValueException('Spektrix price-list response does not include prices.');
        }

        return collect($prices)->map(function (mixed $price): PerformancePriceData {
            if (! is_array($price)) {
                throw new UnexpectedValueException('Spektrix price-list response includes an invalid price.');
            }

            return $this->mapPerformancePrice($price);
        });
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

    /**
     * @return array<string, mixed>
     */
    private function requestObject(string $resource): array
    {
        $payload = $this->client()
            ->get($resource)
            ->throw()
            ->json();

        if (! is_array($payload)) {
            throw new UnexpectedValueException("Spektrix {$resource} response is not a JSON object.");
        }

        return $payload;
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
    private function mapPerformancePrice(array $payload): PerformancePriceData
    {
        $ticketType = $payload['ticketType'] ?? null;
        $priceBand = $payload['priceBand'] ?? null;

        if (! is_array($ticketType) || ! is_array($priceBand)) {
            throw new UnexpectedValueException('Spektrix price-list response does not include ticket type and price band references.');
        }

        return new PerformancePriceData(
            externalId: $this->requiredString($payload, 'id'),
            ticketTypeExternalId: $this->requiredString($ticketType, 'id'),
            ticketTypeName: $this->requiredString($ticketType, 'name'),
            priceBandExternalId: $this->requiredString($priceBand, 'id'),
            priceBandName: $this->requiredString($priceBand, 'name'),
            amountMinor: $this->amountMinor($payload['amount'] ?? null),
            currency: $this->currency(),
            isBandDefault: (bool) ($payload['isBandDefault'] ?? false),
            isDynamicPricingEligible: (bool) ($ticketType['attribute_EligibleForDynamicPricing'] ?? false),
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

    private function amountMinor(mixed $amount): int
    {
        if (is_int($amount)) {
            return $amount * 100;
        }

        if (is_float($amount)) {
            $amount = number_format($amount, 2, '.', '');
        }

        if (! is_string($amount) || preg_match('/^\d+(?:\.\d{1,2})?$/', $amount) !== 1) {
            throw new UnexpectedValueException('Spektrix price amount is not a valid positive currency value.');
        }

        [$major, $minor] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $major * 100) + (int) str_pad($minor, 2, '0');
    }

    private function currency(): string
    {
        $currency = config('ticketing.pricing.currency');

        if (! is_string($currency) || preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new LogicException('A three-letter uppercase ticketing pricing currency must be configured.');
        }

        return $currency;
    }

    /**
     * @return array<string, string>|null
     */
    private function bookingHandoffQuery(BookingHandoffRequestData $performance): ?array
    {
        if ($performance->webPerformanceId !== null && $performance->webPerformanceId !== '') {
            return ['WebInstanceId' => $performance->webPerformanceId];
        }

        if (preg_match('/^\d+/', $performance->performanceExternalId, $matches) !== 1) {
            return null;
        }

        return ['EventInstanceId' => $matches[0]];
    }
}
