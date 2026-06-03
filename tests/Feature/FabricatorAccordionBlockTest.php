<?php

use Illuminate\Support\Facades\Blade;

it('renders accordion title subtitle and items', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.accordion
            title="Frequently asked questions"
            subtitle="Useful details before your visit."
            :items="$items"
        />',
        [
            'items' => [
                [
                    'title' => 'Can I download a risk assessment?',
                    'content' => '<p>Yes, documents are available on the visit page.</p>',
                ],
            ],
        ],
    );

    expect($html)->toContain('Frequently asked questions')
        ->and($html)->toContain('Useful details before your visit.')
        ->and($html)->toContain('Can I download a risk assessment?')
        ->and($html)->toContain('Yes, documents are available on the visit page.');
});
