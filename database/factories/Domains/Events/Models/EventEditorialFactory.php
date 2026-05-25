<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventEditorial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventEditorial>
 */
class EventEditorialFactory extends Factory
{
    /**
     * @var class-string<EventEditorial>
     */
    protected $model = EventEditorial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'summary' => fake()->paragraph(),
            'description_html' => '<p>'.fake()->paragraph().'</p>',
            'hero_image_path' => null,
            'hero_image_alt' => fake()->sentence(3),
            'seo_title' => fake()->sentence(5),
            'seo_description' => fake()->sentence(10),
            'is_published' => false,
            'published_at' => null,
        ];
    }
}
