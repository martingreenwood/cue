<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class FundData
{
    /**
     * @param  array<string, mixed>  $sourcePayload
     */
    public function __construct(
        public string $externalId,
        public string $name,
        public ?string $description,
        public ?string $code,
        public ?int $defaultDonationAmountMinor,
        public array $sourcePayload,
    ) {}
}
