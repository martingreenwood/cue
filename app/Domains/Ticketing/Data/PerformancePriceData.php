<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

final readonly class PerformancePriceData
{
    /** @param array<string, mixed> $sourcePayload */
    public function __construct(
        public string $externalId,
        public string $ticketTypeExternalId,
        public string $ticketTypeName,
        public string $priceBandExternalId,
        public string $priceBandName,
        public int $amountMinor,
        public string $currency,
        public bool $isBandDefault,
        public bool $isDynamicPricingEligible,
        public array $sourcePayload,
    ) {}
}
