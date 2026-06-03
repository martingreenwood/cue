<?php

use Illuminate\Support\Facades\Route;

it('renders filament peek previews inline to avoid cached model serialization', function () {
    expect(config('filament-peek.internalPreviewUrl.enabled'))->toBeFalse()
        ->and(Route::has('filament-peek.preview'))->toBeFalse();
});
