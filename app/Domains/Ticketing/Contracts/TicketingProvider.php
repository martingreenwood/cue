<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Contracts;

use App\Domains\Ticketing\Data\EventData;
use App\Domains\Ticketing\Data\PerformanceData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface TicketingProvider
{
    public function providerKey(): string;

    /**
     * @return Collection<int, EventData>
     */
    public function events(CarbonImmutable $from, CarbonImmutable $until): Collection;

    /**
     * @return Collection<int, PerformanceData>
     */
    public function performances(CarbonImmutable $from, CarbonImmutable $until): Collection;
}
