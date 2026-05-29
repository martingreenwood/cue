<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

use Illuminate\Support\Collection;

final readonly class PublicPerformanceListingData
{
    /**
     * @param  Collection<int, PublicPerformanceData>  $performances
     * @param  Collection<int, PublicFilterTermData>  $accessOptions
     */
    public function __construct(
        public Collection $performances,
        public Collection $accessOptions,
    ) {}
}
