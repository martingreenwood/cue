<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

use App\Domains\Events\Models\FilterTerm;
use Illuminate\Support\Collection;

final readonly class PublicEventFilterOptionsData
{
    /**
     * @param  Collection<int, FilterTerm>  $what
     * @param  Collection<int, FilterTerm>  $offers
     * @param  Collection<int, FilterTerm>  $access
     */
    public function __construct(
        public Collection $what,
        public Collection $offers,
        public Collection $access,
    ) {}
}
