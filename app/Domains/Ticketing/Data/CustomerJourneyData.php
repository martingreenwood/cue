<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class CustomerJourneyData
{
    public function __construct(
        public string $iframeUrl,
        public string $embedScriptUrl,
    ) {}
}
