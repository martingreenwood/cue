<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Models\FilterTerm;
use Illuminate\Database\Seeder;

class FilterTermVocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->terms() as $term) {
            FilterTerm::query()->updateOrCreate(
                ['slug' => $term['slug']],
                $term,
            );
        }
    }

    /**
     * @return list<array{filter_group: FilterGroup, name: string, slug: string, description: string, sort_order: int}>
     */
    private function terms(): array
    {
        return [
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Drama',
                'slug' => 'drama',
                'description' => 'Scripted theatre, new writing and classic plays for the main programme.',
                'sort_order' => 10,
            ],
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Comedy',
                'slug' => 'comedy',
                'description' => 'Stand-up, sketch, comic theatre and lighter entertainment.',
                'sort_order' => 20,
            ],
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Dance',
                'slug' => 'dance',
                'description' => 'Contemporary, classical and community dance performances.',
                'sort_order' => 30,
            ],
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Music',
                'slug' => 'music',
                'description' => 'Concerts, gigs, musical theatre and live music events.',
                'sort_order' => 40,
            ],
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Family',
                'slug' => 'family',
                'description' => 'Events suitable for children, young people and family groups.',
                'sort_order' => 50,
            ],
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Talks And Ideas',
                'slug' => 'talks-and-ideas',
                'description' => 'Author events, talks, panels, lectures and discussion-led programme.',
                'sort_order' => 60,
            ],
            [
                'filter_group' => FilterGroup::What,
                'name' => 'Workshops',
                'slug' => 'workshops',
                'description' => 'Participatory classes, learning activity and creative workshops.',
                'sort_order' => 70,
            ],
            [
                'filter_group' => FilterGroup::Offers,
                'name' => 'Members Priority',
                'slug' => 'members-priority',
                'description' => 'Events with a member priority period or member-specific campaign.',
                'sort_order' => 10,
            ],
            [
                'filter_group' => FilterGroup::Offers,
                'name' => 'Under 26 Tickets',
                'slug' => 'under-26-tickets',
                'description' => 'Events with a young-person ticket offer or age-based promotion.',
                'sort_order' => 20,
            ],
            [
                'filter_group' => FilterGroup::Offers,
                'name' => 'Schools Offer',
                'slug' => 'schools-offer',
                'description' => 'Events with school group rates, matinee education offers or classroom support.',
                'sort_order' => 30,
            ],
            [
                'filter_group' => FilterGroup::Offers,
                'name' => 'Pay What You Can',
                'slug' => 'pay-what-you-can',
                'description' => 'Events with a pay-what-you-can, choose-your-price or supported ticket offer.',
                'sort_order' => 40,
            ],
            [
                'filter_group' => FilterGroup::Offers,
                'name' => 'Group Discounts',
                'slug' => 'group-discounts',
                'description' => 'Events with group booking incentives or larger-party pricing.',
                'sort_order' => 50,
            ],
            [
                'filter_group' => FilterGroup::Access,
                'name' => 'Audio Described',
                'slug' => 'audio-described',
                'description' => 'Specific performances with live audio description for blind and visually impaired audiences.',
                'sort_order' => 10,
            ],
            [
                'filter_group' => FilterGroup::Access,
                'name' => 'Captioned',
                'slug' => 'captioned',
                'description' => 'Specific performances with caption units or integrated captions.',
                'sort_order' => 20,
            ],
            [
                'filter_group' => FilterGroup::Access,
                'name' => 'BSL Interpreted',
                'slug' => 'bsl-interpreted',
                'description' => 'Specific performances with a British Sign Language interpreter.',
                'sort_order' => 30,
            ],
            [
                'filter_group' => FilterGroup::Access,
                'name' => 'Relaxed Performance',
                'slug' => 'relaxed-performance',
                'description' => 'Specific performances adapted for audiences who benefit from a more relaxed environment.',
                'sort_order' => 40,
            ],
            [
                'filter_group' => FilterGroup::Access,
                'name' => 'Touch Tour',
                'slug' => 'touch-tour',
                'description' => 'Specific performances or dates with a pre-show tactile tour.',
                'sort_order' => 50,
            ],
            [
                'filter_group' => FilterGroup::Access,
                'name' => 'Dementia Friendly',
                'slug' => 'dementia-friendly',
                'description' => 'Specific performances adapted for people living with dementia and their companions.',
                'sort_order' => 60,
            ],
        ];
    }
}
