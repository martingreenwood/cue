<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Contracts;

use App\Domains\Ticketing\Data\BookingHandoffData;
use App\Domains\Ticketing\Data\BookingHandoffRequestData;
use App\Domains\Ticketing\Data\CustomerAuthenticationData;
use App\Domains\Ticketing\Data\CustomerJourneyData;
use App\Domains\Ticketing\Data\CustomerSessionData;
use App\Domains\Ticketing\Data\EventData;
use App\Domains\Ticketing\Data\FundData;
use App\Domains\Ticketing\Data\MembershipData;
use App\Domains\Ticketing\Data\PerformanceData;
use App\Domains\Ticketing\Data\PerformancePriceData;
use App\Domains\Ticketing\Enums\CustomerJourney;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface TicketingProvider
{
    public function providerKey(): string;

    public function bookingHandoff(BookingHandoffRequestData $performance): ?BookingHandoffData;

    public function customerAuthentication(): ?CustomerAuthenticationData;

    public function customerJourney(CustomerJourney $journey): ?CustomerJourneyData;

    public function customerSession(): ?CustomerSessionData;

    /**
     * @return Collection<int, FundData>
     */
    public function funds(): Collection;

    /**
     * @return Collection<int, MembershipData>
     */
    public function memberships(): Collection;

    /**
     * @return Collection<int, EventData>
     */
    public function events(CarbonImmutable $from, CarbonImmutable $until): Collection;

    /**
     * @return Collection<int, PerformanceData>
     */
    public function performances(CarbonImmutable $from, CarbonImmutable $until): Collection;

    /**
     * @return Collection<int, PerformancePriceData>
     */
    public function performancePrices(string $performanceExternalId): Collection;
}
