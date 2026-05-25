<?php

declare(strict_types=1);

namespace App\Domains\Ticketing\Data;

use Carbon\CarbonImmutable;

final readonly class EventData
{
    /**
     * @param  array<string, mixed>  $sourcePayload
     */
    public function __construct(
        public string $externalId,
        public string $title,
        public ?string $summary,
        public ?string $descriptionHtml,
        public ?int $durationMinutes,
        public ?string $imageUrl,
        public ?string $thumbnailUrl,
        public ?string $imageAlt,
        public bool $isOnSale,
        public ?CarbonImmutable $firstPerformanceAt,
        public ?CarbonImmutable $lastPerformanceAt,
        public array $sourcePayload,
    ) {}
}
