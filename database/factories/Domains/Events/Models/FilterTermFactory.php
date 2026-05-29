<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Models\FilterTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FilterTerm>
 */
class FilterTermFactory extends Factory
{
    /**
     * @var class-string<FilterTerm>
     */
    protected $model = FilterTerm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filter_group' => FilterGroup::What,
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => null,
            'sort_order' => 0,
        ];
    }
}
