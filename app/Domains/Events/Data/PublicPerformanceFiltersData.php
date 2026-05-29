<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

final readonly class PublicPerformanceFiltersData
{
    /**
     * @param  list<string>  $access
     */
    public function __construct(
        public ?string $date,
        public string $dateWindow,
        public array $access,
    ) {}

    public function isApplied(): bool
    {
        return $this->date !== null || $this->dateWindow !== 'all' || $this->access !== [];
    }
}
