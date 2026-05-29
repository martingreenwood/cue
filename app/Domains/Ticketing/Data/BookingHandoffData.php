<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class BookingHandoffData
{
    public function __construct(
        public string $url,
        public string $embedScriptUrl,
    ) {}
}
