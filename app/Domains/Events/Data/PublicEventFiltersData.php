<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

final readonly class PublicEventFiltersData
{
    /**
     * @param  list<string>  $what
     * @param  list<string>  $offers
     * @param  list<string>  $access
     */
    public function __construct(
        public ?string $query,
        public string $dateWindow = 'all',
        public array $what = [],
        public array $offers = [],
        public array $access = [],
    ) {}

    public function isApplied(): bool
    {
        return $this->query !== null
            || $this->dateWindow !== 'all'
            || $this->what !== []
            || $this->offers !== []
            || $this->access !== [];
    }
}
