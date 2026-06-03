<?php

use App\Filament\Fabricator\PageBlocks\RelatedContent;
use Illuminate\Support\Facades\Blade;
use Z3d0X\FilamentFabricator\Models\Page;

it('normalizes related content from selected page', function () {
    $page = Page::query()->create([
        'title' => 'Plan your visit',
        'slug' => 'visit',
        'layout' => 'default',
        'blocks' => [],
        'featured_image_path' => 'pages/featured-images/visit.jpg',
        'featured_image_alt' => 'Audience arriving at the theatre',
    ]);

    $data = RelatedContent::mutateData([
        'page_id' => $page->id,
        'eyebrow' => '',
        'button_text' => '',
    ]);

    expect($data['related_title'])->toBe('Plan your visit')
        ->and($data['related_url'])->toBe('/visit')
        ->and($data['eyebrow'])->toBe('Related content')
        ->and($data['button_text'])->toBe('Read more')
        ->and($data['featured_image_src'])->toBe('http://cue.test/storage/pages/featured-images/visit.jpg')
        ->and($data['featured_image_alt'])->toBe('Audience arriving at the theatre');
});

it('renders a related content cta', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.related-content
            eyebrow="You may also be interested in"
            related_title="Memberships"
            related_url="/memberships"
            featured_image_src="https://example.com/memberships.jpg"
            featured_image_alt="Members in the foyer"
            text="Support the theatre and unlock priority booking."
            button_text="Explore memberships"
        />',
    );

    expect($html)->toContain('You may also be interested in')
        ->and($html)->toContain('Memberships')
        ->and($html)->toContain('Support the theatre and unlock priority booking.')
        ->and($html)->toContain('md:grid-cols-[minmax(14rem,22rem)_minmax(0,1fr)]')
        ->and($html)->toContain('src="https://example.com/memberships.jpg"')
        ->and($html)->toContain('alt="Members in the foyer"')
        ->and($html)->toContain('aspect-[16/10] h-full w-full object-cover md:aspect-auto')
        ->and($html)->toContain('href="/memberships"')
        ->and($html)->toContain('Explore memberships');
});

it('does not render without a selected page url', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.related-content
            related_title="Missing"
        />',
    );

    expect(trim($html))->toBe('');
});
