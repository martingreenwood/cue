<?php

use App\Filament\Fabricator\PageBlocks\TextWithMedia;
use Illuminate\Support\Facades\Blade;

it('normalizes text with media assets layout and buttons', function () {
    $data = TextWithMedia::mutateData([
        'media_type' => 'video',
        'video_source' => 'upload',
        'video_upload_path' => 'page-blocks/videos/interview.mp4',
        'layout' => 'sideways',
        'buttons' => [
            [
                'text' => 'Learn more',
                'variant' => 'secondary',
                'link_type' => 'url',
                'url' => 'https://example.com/learn',
                'target' => '_blank',
            ],
        ],
    ]);

    expect($data['media_type'])->toBe('video')
        ->and($data['video_src'])->toBe('http://cue.test/storage/page-blocks/videos/interview.mp4')
        ->and($data['layout'])->toBe('media_left')
        ->and($data['buttons'])->toHaveCount(1)
        ->and($data['buttons'][0]['url'])->toBe('https://example.com/learn');
});

it('renders media above text on mobile and supports text left desktop layout', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.text-with-media
            title="Behind the scenes"
            subtitle="Meet the company"
            content="&lt;p&gt;Step into rehearsal.&lt;/p&gt;"
            media_type="image"
            image_src="https://example.com/rehearsal.jpg"
            media_alt="A rehearsal"
            layout="text_left"
            :buttons="$buttons"
        />',
        [
            'buttons' => [
                [
                    'text' => 'Read more',
                    'url' => 'https://example.com/read',
                    'target' => '_self',
                    'variant' => 'primary',
                ],
            ],
        ],
    );

    expect($html)->toContain('Behind the scenes')
        ->and($html)->toContain('Meet the company')
        ->and($html)->toContain('<p>Step into rehearsal.</p>')
        ->and($html)->not->toContain('&lt;p&gt;Step into rehearsal.&lt;/p&gt;')
        ->and($html)->toContain('src="https://example.com/rehearsal.jpg"')
        ->and($html)->toContain('alt="A rehearsal"')
        ->and($html)->toContain('order-1 md:order-2')
        ->and($html)->toContain('order-2 md:order-1')
        ->and($html)->toContain('href="https://example.com/read"');
});

it('renders video media for text with media', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.text-with-media
            content="<p>Watch the story.</p>"
            media_type="video"
            video_src="https://example.com/story.mp4"
            layout="media_left"
        />',
    );

    expect($html)->toContain('<video')
        ->and($html)->toContain('controls')
        ->and($html)->toContain('<source src="https://example.com/story.mp4"')
        ->and($html)->toContain('order-1 md:order-1')
        ->and($html)->toContain('order-2 md:order-2');
});
