<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

use Carbon\CarbonImmutable;

final readonly class PerformanceData
{
    /**
     * @param  array<string, mixed>  $sourcePayload
     */
    public function __construct(
        public string $externalId,
        public string $eventExternalId,
        public ?string $webId,
        public ?string $externalPlanId,
        public ?string $externalPriceListId,
        public CarbonImmutable $startsAt,
        public ?CarbonImmutable $salesStartAt,
        public ?CarbonImmutable $salesEndAt,
        public bool $isOnSale,
        public bool $isCancelled,
        public array $sourcePayload,
    ) {}
}
