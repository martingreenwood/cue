<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\PerformancePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformancePrice>
 */
class PerformancePriceFactory extends Factory
{
    /**
     * @var class-string<PerformancePrice>
     */
    protected $model = PerformancePrice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'performance_id' => Performance::factory(),
            'provider' => 'spektrix',
            'external_id' => fake()->unique()->bothify('price-########'),
            'ticket_type_external_id' => 'full-price',
            'ticket_type_name' => 'Full Price',
            'price_band_external_id' => fake()->bothify('band-########'),
            'price_band_name' => 'Band A',
            'amount_minor' => 2500,
            'currency' => 'GBP',
            'is_band_default' => true,
            'is_dynamic_pricing_eligible' => false,
            'source_payload' => [],
            'synced_at' => now(),
        ];
    }
}
