<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @var class-string<Event>
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'spektrix',
            'external_id' => fake()->unique()->bothify('event-########'),
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(3),
            'summary' => fake()->sentence(),
            'description_html' => '<p>'.fake()->sentence().'</p>',
            'duration_minutes' => fake()->numberBetween(45, 180),
            'image_url' => fake()->imageUrl(),
            'thumbnail_url' => fake()->imageUrl(),
            'image_alt' => fake()->sentence(3),
            'is_on_sale' => true,
            'first_performance_at' => now()->addWeek(),
            'last_performance_at' => now()->addWeeks(2),
            'source_payload' => [],
            'synced_at' => now(),
        ];
    }
}
