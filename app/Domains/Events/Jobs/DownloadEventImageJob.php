<?php

declare(strict_types=1);

namespace App\Domains\Events\Jobs;

use App\Domains\Events\Models\Event;
use GdImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class DownloadEventImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30];

    public function __construct(public readonly int $eventId) {}

    public function handle(): void
    {
        $event = Event::find($this->eventId);

        if ($event === null || $event->image_url === null) {
            return;
        }

        $response = Http::timeout(15)->get($event->image_url);

        if (! $response->successful()) {
            Log::warning("DownloadEventImageJob: non-successful response for event {$this->eventId}.", [
                'url' => $event->image_url,
                'status' => $response->status(),
            ]);

            return;
        }

        $image = @imagecreatefromstring($response->body());

        if (! $image instanceof GdImage) {
            Log::warning("DownloadEventImageJob: could not decode image for event {$this->eventId}.", [
                'url' => $event->image_url,
            ]);

            return;
        }

        $image = $this->resizeToMaxWidth($image, 1400);

        $storagePath = "events/source/{$this->eventId}/hero.jpg";

        $jpegBytes = $this->renderJpeg($image);
        imagedestroy($image);

        if ($jpegBytes === null) {
            Log::warning("DownloadEventImageJob: JPEG rendering failed for event {$this->eventId}.");

            return;
        }

        Storage::disk('public')->put($storagePath, $jpegBytes);

        $event->update(['local_image_path' => $storagePath]);
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return [
            'media',
            'media:download',
            'event:'.$this->eventId,
        ];
    }

    private function resizeToMaxWidth(GdImage $image, int $maxWidth): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            return $image;
        }

        $newHeight = (int) round($height * ($maxWidth / $width));
        $resized = imagecreatetruecolor($maxWidth, $newHeight);

        // Preserve transparency for PNG sources before sampling.
        imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    private function renderJpeg(GdImage $image): ?string
    {
        ob_start();

        $success = imagejpeg($image, null, 85);

        $bytes = ob_get_clean();

        if (! $success || $bytes === false || $bytes === '') {
            return null;
        }

        return $bytes;
    }
}
