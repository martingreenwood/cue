<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class BookingHandoffRequestData
{
    public function __construct(
        public string $performanceExternalId,
        public ?string $webPerformanceId,
    ) {}
}
