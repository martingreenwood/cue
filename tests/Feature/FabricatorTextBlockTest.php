<?php

use App\Filament\Fabricator\PageBlocks\Text;
use Illuminate\Support\Facades\Blade;

it('normalizes text block buttons and alignment', function () {
    $data = Text::mutateData([
        'content' => '<p>Legacy body.</p>',
        'alignment' => 'sideways',
        'buttons' => [
            [
                'text' => 'Visit',
                'variant' => 'secondary',
                'link_type' => 'url',
                'url' => 'https://example.com',
                'target' => '_blank',
            ],
        ],
    ]);

    expect($data['text'])->toBe('<p>Legacy body.</p>')
        ->and($data['alignment'])->toBe('left')
        ->and($data['buttons'])->toHaveCount(1)
        ->and($data['buttons'][0]['url'])->toBe('https://example.com')
        ->and($data['buttons'][0]['target'])->toBe('_blank');
});

it('renders text block title subtitle text buttons and alignment', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.text
            title="Support Us"
            subtitle="Make theatre happen"
            text="<p>Your gift keeps the curtain rising.</p>"
            alignment="right"
            :buttons="$buttons"
        />',
        [
            'buttons' => [
                [
                    'text' => 'Donate',
                    'url' => 'https://example.com/donate',
                    'target' => '_self',
                    'variant' => 'primary',
                ],
            ],
        ],
    );

    expect($html)->toContain('Support Us')
        ->and($html)->toContain('Make theatre happen')
        ->and($html)->toContain('Your gift keeps the curtain rising.')
        ->and($html)->toContain('href="https://example.com/donate"')
        ->and($html)->toContain('text-right')
        ->and($html)->toContain('justify-end');
});
