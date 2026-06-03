<?php

use App\Filament\Fabricator\PageBlocks\Media;
use Illuminate\Support\Facades\Blade;

it('normalizes media image library and upload sources', function () {
    $libraryData = Media::mutateData([
        'media_type' => 'image',
        'image_source' => 'library',
        'image_library_path' => 'page-blocks/images/gallery.jpg',
    ]);

    $uploadData = Media::mutateData([
        'media_type' => 'image',
        'image_source' => 'upload',
        'image_upload_path' => ['page-blocks/images/uploaded.jpg'],
    ]);

    expect($libraryData['image_src'])->toBe('http://cue.test/storage/page-blocks/images/gallery.jpg')
        ->and($uploadData['image_src'])->toBe('http://cue.test/storage/page-blocks/images/uploaded.jpg');
});

it('normalizes media video sources', function () {
    $data = Media::mutateData([
        'media_type' => 'video',
        'video_source' => 'upload',
        'video_upload_path' => 'page-blocks/videos/trailer.mp4',
    ]);

    expect($data['media_type'])->toBe('video')
        ->and($data['video_src'])->toBe('http://cue.test/storage/page-blocks/videos/trailer.mp4');
});

it('renders media image and video blocks', function () {
    $imageHtml = Blade::render(
        '<x-filament-fabricator.page-blocks.media
            media_type="image"
            image_src="https://example.com/gallery.jpg"
            media_alt="Gallery"
            caption="A production still"
        />',
    );

    $videoHtml = Blade::render(
        '<x-filament-fabricator.page-blocks.media
            media_type="video"
            video_src="https://example.com/trailer.mp4"
            caption="Watch the trailer"
        />',
    );

    expect($imageHtml)->toContain('<img')
        ->and($imageHtml)->toContain('src="https://example.com/gallery.jpg"')
        ->and($imageHtml)->toContain('alt="Gallery"')
        ->and($imageHtml)->toContain('A production still')
        ->and($videoHtml)->toContain('<video')
        ->and($videoHtml)->toContain('controls')
        ->and($videoHtml)->toContain('<source src="https://example.com/trailer.mp4"')
        ->and($videoHtml)->toContain('Watch the trailer');
});
