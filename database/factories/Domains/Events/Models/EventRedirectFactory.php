<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventRedirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRedirect>
 */
class EventRedirectFactory extends Factory
{
    /**
     * @var class-string<EventRedirect>
     */
    protected $model = EventRedirect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'source_path' => '/whats-on/'.fake()->unique()->slug(),
            'destination_path' => '/events/'.fake()->unique()->slug(),
            'status_code' => 301,
            'is_active' => true,
            'notes' => null,
        ];
    }
}
