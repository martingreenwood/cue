<?php

use App\Filament\Fabricator\PageBlocks\TextColumns;
use Illuminate\Support\Facades\Blade;

it('normalizes text columns buttons and alignment', function () {
    $data = TextColumns::mutateData([
        'alignment' => 'sideways',
        'buttons' => [
            [
                'text' => 'Explore',
                'variant' => 'link',
                'link_type' => 'url',
                'url' => 'https://example.com/explore',
                'target' => '_blank',
            ],
        ],
    ]);

    expect($data['alignment'])->toBe('left')
        ->and($data['buttons'])->toHaveCount(1)
        ->and($data['buttons'][0]['url'])->toBe('https://example.com/explore')
        ->and($data['buttons'][0]['variant'])->toBe('link')
        ->and($data['buttons'][0]['target'])->toBe('_blank');
});

it('renders text columns title subtitle columns buttons and alignment', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.text-columns
            title="Your Visit"
            subtitle="Everything you need before curtain up"
            left_content="&lt;p&gt;Plan your journey.&lt;/p&gt;"
            right_content="&lt;p&gt;Book your interval drinks.&lt;/p&gt;"
            alignment="center"
            :buttons="$buttons"
        />',
        [
            'buttons' => [
                [
                    'text' => 'Plan ahead',
                    'url' => 'https://example.com/visit',
                    'target' => '_self',
                    'variant' => 'secondary',
                ],
            ],
        ],
    );

    expect($html)->toContain('Your Visit')
        ->and($html)->toContain('Everything you need before curtain up')
        ->and($html)->toContain('Plan your journey.')
        ->and($html)->toContain('Book your interval drinks.')
        ->and($html)->toContain('<p>Plan your journey.</p>')
        ->and($html)->not->toContain('&lt;p&gt;Plan your journey.&lt;/p&gt;')
        ->and($html)->toContain('href="https://example.com/visit"')
        ->and($html)->toContain('text-center')
        ->and($html)->toContain('justify-center');
});
