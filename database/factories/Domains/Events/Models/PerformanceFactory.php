<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\Performance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Performance>
 */
class PerformanceFactory extends Factory
{
    /**
     * @var class-string<Performance>
     */
    protected $model = Performance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'provider' => 'spektrix',
            'external_id' => fake()->unique()->bothify('performance-########'),
            'web_id' => null,
            'external_plan_id' => fake()->bothify('plan-########'),
            'external_price_list_id' => fake()->bothify('prices-########'),
            'starts_at' => now()->addWeek(),
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addWeek()->subHour(),
            'is_on_sale' => true,
            'is_cancelled' => false,
            'display_from_price_minor' => null,
            'display_currency' => null,
            'has_dynamic_pricing' => false,
            'prices_synced_at' => null,
            'source_payload' => [],
            'synced_at' => now(),
        ];
    }
}
