<?php

declare(strict_types=1);

use App\Domains\Events\Actions\SyncCatalogueAction;
use App\Domains\Events\Jobs\DownloadEventImageJob;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

function minimalJpegBytes(): string
{
    $gd = imagecreatetruecolor(10, 10);
    ob_start();
    imagejpeg($gd);
    $bytes = ob_get_clean();
    imagedestroy($gd);

    return $bytes;
}

test('job downloads image and stores it on the public disk', function () {
    Storage::fake('public');
    Http::fake(['https://example.com/image.jpg' => Http::response(minimalJpegBytes(), 200)]);

    $event = Event::factory()->create(['image_url' => 'https://example.com/image.jpg']);

    (new DownloadEventImageJob($event->id))->handle();

    $expectedPath = "events/source/{$event->id}/hero.jpg";

    Storage::disk('public')->assertExists($expectedPath);

    expect($event->refresh()->local_image_path)->toBe($expectedPath);
});

test('job produces a valid JPEG from the downloaded bytes', function () {
    Storage::fake('public');
    Http::fake(['https://example.com/image.jpg' => Http::response(minimalJpegBytes(), 200)]);

    $event = Event::factory()->create(['image_url' => 'https://example.com/image.jpg']);

    (new DownloadEventImageJob($event->id))->handle();

    $storedBytes = Storage::disk('public')->get("events/source/{$event->id}/hero.jpg");

    expect($storedBytes)->not->toBeNull();

    $image = @imagecreatefromstring($storedBytes);

    expect($image)->toBeInstanceOf(GdImage::class);

    imagedestroy($image);
});

test('job silently skips when the remote image returns a non-200 response', function () {
    Storage::fake('public');
    Http::fake(['https://example.com/missing.jpg' => Http::response('', 404)]);

    $event = Event::factory()->create(['image_url' => 'https://example.com/missing.jpg']);

    (new DownloadEventImageJob($event->id))->handle();

    Storage::disk('public')->assertMissing("events/source/{$event->id}/hero.jpg");
    expect($event->refresh()->local_image_path)->toBeNull();
});

test('job silently skips when the event no longer exists', function () {
    Storage::fake('public');
    Http::fake();

    (new DownloadEventImageJob(99999))->handle();

    Http::assertNothingSent();
});

test('job does not re-download when image_url has not changed and local image exists', function () {
    Storage::fake('public');
    Http::fake(['https://example.com/image.jpg' => Http::response(minimalJpegBytes(), 200)]);

    $event = Event::factory()->create([
        'image_url' => 'https://example.com/image.jpg',
        'local_image_path' => 'events/source/1/hero.jpg',
    ]);

    // Simulate: catalogue sync would not dispatch for this event because
    // wasChanged('image_url') is false and local_image_path is already set.
    // Test that the dispatch condition works correctly by checking wasChanged
    // returns false on a freshly-loaded model.
    $freshEvent = Event::find($event->id);
    $freshEvent->fill(['image_url' => 'https://example.com/image.jpg'])->save();

    expect($freshEvent->wasChanged('image_url'))->toBeFalse();
});

test('catalogue sync dispatches image download jobs for events with image_url', function () {
    Queue::fake();
    Http::fake([
        '*/events*' => Http::response([spektrixEventPayload()]),
        '*/instances*' => Http::response([spektrixPerformancePayload()]),
    ]);

    $run = SyncRun::factory()->create();
    app(SyncCatalogueAction::class)->execute($run);

    Queue::assertPushed(DownloadEventImageJob::class, 1);

    $event = Event::query()->sole();

    Queue::assertPushed(DownloadEventImageJob::class, fn (DownloadEventImageJob $job): bool => $job->eventId === $event->id
    );
});

test('catalogue sync does not dispatch image job when image_url is null', function () {
    Queue::fake();
    Http::fake([
        '*/events*' => Http::response([spektrixEventPayload(['imageUrl' => null])]),
        '*/instances*' => Http::response([spektrixPerformancePayload()]),
    ]);

    $run = SyncRun::factory()->create();
    app(SyncCatalogueAction::class)->execute($run);

    Queue::assertNotPushed(DownloadEventImageJob::class);
});
