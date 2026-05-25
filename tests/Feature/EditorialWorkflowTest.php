<?php

declare(strict_types=1);

use App\Domains\Events\Actions\CreateSlugRedirectAction;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventEditorial;
use App\Domains\Events\Models\EventRedirect;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    config(['ticketing.event_path_prefix' => '/events']);
});

// --- CreateSlugRedirectAction unit tests ---

test('action creates a 301 redirect when the slug changes', function () {
    $event = Event::factory()->create(['slug' => 'provider-slug']);

    app(CreateSlugRedirectAction::class)->execute($event, 'old-slug', 'new-slug');

    expect(EventRedirect::query()->sole())
        ->source_path->toBe('/events/old-slug')
        ->destination_path->toBe('/events/new-slug')
        ->status_code->toBe(301)
        ->is_active->toBeTrue();
});

test('action returns null and creates no redirect when slugs are identical', function () {
    $event = Event::factory()->create();

    $result = app(CreateSlugRedirectAction::class)->execute($event, 'same-slug', 'same-slug');

    expect($result)->toBeNull();
    expect(EventRedirect::query()->count())->toBe(0);
});

test('action updates destination when the source path already has a redirect', function () {
    $event = Event::factory()->create();
    EventRedirect::factory()->for($event)->create([
        'source_path' => '/events/old-slug',
        'destination_path' => '/events/intermediate-slug',
    ]);

    app(CreateSlugRedirectAction::class)->execute($event, 'old-slug', 'final-slug');

    expect(EventRedirect::query()->count())->toBe(1);
    expect(EventRedirect::query()->sole()->destination_path)->toBe('/events/final-slug');
});

test('action respects a custom event path prefix from config', function () {
    config(['ticketing.event_path_prefix' => '/whats-on']);
    $event = Event::factory()->create();

    app(CreateSlugRedirectAction::class)->execute($event, 'old', 'new');

    expect(EventRedirect::query()->sole()->source_path)->toBe('/whats-on/old');
});

// --- EditEvent integration tests ---

test('saving a changed editorial slug creates a redirect from the old slug', function () {
    $event = Event::factory()->create(['slug' => 'provider-slug']);
    EventEditorial::factory()->for($event)->create(['slug' => 'old-editorial-slug']);

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm(['editorial' => ['slug' => 'new-editorial-slug']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(EventRedirect::query()->sole())
        ->source_path->toBe('/events/old-editorial-slug')
        ->destination_path->toBe('/events/new-editorial-slug');
});

test('setting an editorial slug for the first time redirects from the provider slug', function () {
    $event = Event::factory()->create(['slug' => 'provider-slug']);
    // No editorial record yet.

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm(['editorial' => ['slug' => 'custom-editorial-slug']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(EventRedirect::query()->sole())
        ->source_path->toBe('/events/provider-slug')
        ->destination_path->toBe('/events/custom-editorial-slug');
});

test('setting editorial slug to the same value as the provider slug creates no redirect', function () {
    $event = Event::factory()->create(['slug' => 'provider-slug']);

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm(['editorial' => ['slug' => 'provider-slug']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(EventRedirect::query()->count())->toBe(0);
});

test('clearing an editorial slug redirects from the old editorial path to the provider slug', function () {
    $event = Event::factory()->create(['slug' => 'provider-slug']);
    EventEditorial::factory()->for($event)->create(['slug' => 'old-editorial-slug']);

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm(['editorial' => ['slug' => null]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(EventRedirect::query()->sole())
        ->source_path->toBe('/events/old-editorial-slug')
        ->destination_path->toBe('/events/provider-slug');
});

test('saving with an unchanged editorial slug creates no redirect', function () {
    $event = Event::factory()->create(['slug' => 'provider-slug']);
    EventEditorial::factory()->for($event)->create(['slug' => 'editorial-slug']);

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm(['editorial' => ['slug' => 'editorial-slug']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(EventRedirect::query()->count())->toBe(0);
});
