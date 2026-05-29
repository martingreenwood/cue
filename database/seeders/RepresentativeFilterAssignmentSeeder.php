<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\FilterTerm;
use App\Domains\Events\Models\Performance;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RepresentativeFilterAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(FilterTermVocabularySeeder::class);

        /** @var Collection<string, FilterTerm> $terms */
        $terms = FilterTerm::query()
            ->get()
            ->keyBy('slug');

        $eventIndex = 0;

        Event::query()
            ->with(['performances' => fn ($performances) => $performances->orderBy('starts_at')->orderBy('id')])
            ->orderBy('first_performance_at')
            ->orderBy('id')
            ->each(function (Event $event) use ($terms, &$eventIndex): void {
                $this->assignEventTerms($event, $terms, $eventIndex);
                $this->assignPerformanceAccessTerms($event, $terms, $eventIndex);

                $eventIndex++;
            });
    }

    /**
     * @param  Collection<string, FilterTerm>  $terms
     */
    private function assignEventTerms(Event $event, Collection $terms, int $index): void
    {
        $event->whatTerms()->syncWithoutDetaching(
            $this->idsForSlugs($terms, $this->whatSlugsForEvent($event, $index)),
        );

        $event->offerTerms()->syncWithoutDetaching(
            $this->idsForSlugs($terms, $this->offerSlugsForEvent($index)),
        );
    }

    /**
     * @param  Collection<string, FilterTerm>  $terms
     */
    private function assignPerformanceAccessTerms(Event $event, Collection $terms, int $eventIndex): void
    {
        $accessSlugs = [
            'audio-described',
            'captioned',
            'bsl-interpreted',
            'relaxed-performance',
            'touch-tour',
            'dementia-friendly',
        ];

        /** @var EloquentCollection<int, Performance> $performances */
        $performances = $event->performances;

        $performances
            ->values()
            ->each(function (Performance $performance, int $performanceIndex) use ($accessSlugs, $eventIndex, $terms): void {
                if ($performanceIndex > 0 && $performanceIndex % 2 !== 0) {
                    return;
                }

                $primarySlug = $accessSlugs[($eventIndex + $performanceIndex) % count($accessSlugs)];
                $slugs = [$primarySlug];

                if ($primarySlug === 'audio-described') {
                    $slugs[] = 'touch-tour';
                }

                $performance->accessTerms()->syncWithoutDetaching(
                    $this->idsForSlugs($terms, $slugs),
                );
            });
    }

    /**
     * @return list<string>
     */
    private function whatSlugsForEvent(Event $event, int $index): array
    {
        $source = Str::lower(implode(' ', array_filter([
            $event->title,
            $event->summary,
            strip_tags((string) $event->description_html),
        ])));

        $matches = collect([
            'family' => ['family', 'children', 'child', 'kids', 'young people', 'panto', 'christmas'],
            'comedy' => ['comedy', 'comic', 'stand-up', 'stand up', 'funny'],
            'dance' => ['dance', 'ballet', 'choreography', 'movement'],
            'music' => ['music', 'concert', 'gig', 'opera', 'musical', 'choir', 'orchestra'],
            'talks-and-ideas' => ['talk', 'conversation', 'lecture', 'author', 'panel', 'ideas'],
            'workshops' => ['workshop', 'class', 'course', 'participatory', 'learning'],
            'drama' => ['drama', 'play', 'theatre', 'classic', 'new writing'],
        ])
            ->filter(fn (array $needles): bool => collect($needles)
                ->contains(fn (string $needle): bool => str_contains($source, $needle)))
            ->keys()
            ->take(2)
            ->values()
            ->all();

        if ($matches !== []) {
            return $matches;
        }

        $fallbacks = ['drama', 'comedy', 'dance', 'music', 'family', 'talks-and-ideas', 'workshops'];

        return [$fallbacks[$index % count($fallbacks)]];
    }

    /**
     * @return list<string>
     */
    private function offerSlugsForEvent(int $index): array
    {
        return collect([
            2 => 'members-priority',
            3 => 'under-26-tickets',
            5 => 'schools-offer',
            7 => 'pay-what-you-can',
            11 => 'group-discounts',
        ])
            ->filter(fn (string $slug, int $divisor): bool => ($index + 1) % $divisor === 0)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<string, FilterTerm>  $terms
     * @param  list<string>  $slugs
     * @return list<int>
     */
    private function idsForSlugs(Collection $terms, array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug): ?int => $terms->get($slug)?->getKey())
            ->filter()
            ->values()
            ->all();
    }
}
