<?php

use App\Filament\Fabricator\PageBlocks\Hero;
use Illuminate\Support\Facades\Blade;
use Z3d0X\FilamentFabricator\Models\Page;

it('normalizes hero media and button links', function () {
    $page = Page::query()->create([
        'title' => 'About us',
        'slug' => 'about',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $data = Hero::mutateData([
        'image_source' => 'upload',
        'image_upload_path' => 'page-blocks/images/stage.jpg',
        'video_source' => 'url',
        'video_url' => 'https://cdn.example.com/hero.mp4',
        'alignment' => 'sideways',
        'buttons' => [
            [
                'text' => 'About us',
                'variant' => 'primary',
                'link_type' => 'page',
                'page_id' => $page->id,
                'target' => '_self',
            ],
            [
                'text' => 'External',
                'variant' => 'secondary',
                'link_type' => 'url',
                'url' => 'https://example.com',
                'target' => '_blank',
            ],
            [
                'text' => '',
                'link_type' => 'url',
                'url' => 'https://example.com/empty',
            ],
        ],
    ]);

    expect($data['image_src'])->toBe('http://cue.test/storage/page-blocks/images/stage.jpg')
        ->and($data['video_src'])->toBe('https://cdn.example.com/hero.mp4')
        ->and($data['buttons'])->toHaveCount(2)
        ->and($data['buttons'][0]['url'])->toBe('/about')
        ->and($data['buttons'][1]['target'])->toBe('_blank')
        ->and($data['alignment'])->toBe('left');
});

it('renders hero video overlay text and buttons', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.hero
            title="Opening Night"
            subtitle="A bold new season"
            text="<p>Join us in the main house.</p>"
            video_src="https://cdn.example.com/hero.mp4"
            :video_autoplay="true"
            :video_muted="true"
            :video_loop="true"
            :overlay_enabled="true"
            alignment="center"
            :buttons="$buttons"
        />',
        [
            'buttons' => [
                [
                    'text' => 'Book now',
                    'url' => 'https://example.com/book',
                    'target' => '_blank',
                    'variant' => 'primary',
                ],
            ],
        ],
    );

    expect($html)->toContain('<video')
        ->and($html)->toContain('autoplay')
        ->and($html)->toContain('muted')
        ->and($html)->toContain('loop')
        ->and($html)->toContain('Opening Night')
        ->and($html)->toContain('Join us in the main house.')
        ->and($html)->toContain('href="https://example.com/book"')
        ->and($html)->toContain('mx-auto text-center')
        ->and($html)->toContain('justify-center')
        ->and($html)->toContain('rel="noopener noreferrer"');
});
