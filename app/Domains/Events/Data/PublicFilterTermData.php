<?php

declare(strict_types=1);

namespace App\Domains\Events\Data;

use App\Domains\Events\Models\FilterTerm;

final readonly class PublicFilterTermData
{
    public function __construct(
        public string $name,
        public string $slug,
    ) {}

    public static function fromModel(FilterTerm $term): self
    {
        return new self(
            name: $term->name,
            slug: $term->slug,
        );
    }
}
