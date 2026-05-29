<?php

declare(strict_types=1);

use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\FilterTerm;
use App\Domains\Events\Models\Performance;
use Database\Seeders\RepresentativeFilterAssignmentSeeder;

test('it assigns representative event and performance filter terms idempotently', function () {
    $familyEvent = Event::factory()->create([
        'title' => 'Family Comedy Night',
        'summary' => 'A funny evening for children and grown-ups.',
        'first_performance_at' => now()->addDays(7),
    ]);
    $musicEvent = Event::factory()->create([
        'title' => 'Late Night Concert',
        'summary' => 'Live music from local artists.',
        'first_performance_at' => now()->addDays(8),
    ]);
    Event::factory()->create([
        'title' => 'Schools Matinee Project',
        'summary' => 'A new theatre project for classes.',
        'first_performance_at' => now()->addDays(9),
    ]);
    Event::factory()->create([
        'title' => 'New Writing Festival',
        'summary' => 'A weekend of contemporary plays.',
        'first_performance_at' => now()->addDays(10),
    ]);
    Event::factory()->create([
        'title' => 'Community Dance Showcase',
        'summary' => 'Movement and dance from local groups.',
        'first_performance_at' => now()->addDays(11),
    ]);

    Performance::factory()->for($familyEvent)->create(['starts_at' => now()->addDays(7)->setTime(14, 0)]);
    Performance::factory()->for($familyEvent)->create(['starts_at' => now()->addDays(8)->setTime(19, 30)]);
    Performance::factory()->for($familyEvent)->create(['starts_at' => now()->addDays(9)->setTime(19, 30)]);
    Performance::factory()->for($musicEvent)->create(['starts_at' => now()->addDays(8)->setTime(20, 0)]);

    $this->seed(RepresentativeFilterAssignmentSeeder::class);

    expect($familyEvent->whatTerms()->pluck('filter_terms.slug')->all())->toBe(['comedy', 'family'])
        ->and($musicEvent->whatTerms()->pluck('filter_terms.slug')->all())->toBe(['music']);

    expect(FilterTerm::query()->where('filter_group', FilterGroup::Offers)->whereHas('offerEvents')->count())
        ->toBeGreaterThanOrEqual(2);

    expect($familyEvent->performances()->firstOrFail()->accessTerms()->pluck('filter_terms.slug')->all())
        ->toBe(['audio-described', 'touch-tour'])
        ->and($familyEvent->performances()->orderBy('starts_at')->skip(1)->firstOrFail()->accessTerms()->count())
        ->toBe(0)
        ->and($familyEvent->performances()->orderBy('starts_at')->skip(2)->firstOrFail()->accessTerms()->pluck('filter_terms.slug')->all())
        ->toBe(['bsl-interpreted']);

    $this->seed(RepresentativeFilterAssignmentSeeder::class);

    expect(FilterTerm::query()->count())->toBe(18)
        ->and($familyEvent->whatTerms()->count())->toBe(2)
        ->and($familyEvent->performances()->firstOrFail()->accessTerms()->count())->toBe(2);
});
