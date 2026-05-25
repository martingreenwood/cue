<?php

declare(strict_types=1);

use App\Domains\Events\Jobs\SyncTicketingCatalogue;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventEditorial;
use App\Domains\Events\Models\EventRedirect;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\PerformancePrice;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\RelationManagers\PerformancesRelationManager;
use App\Filament\Resources\Events\RelationManagers\RedirectsRelationManager;
use App\Filament\Resources\Performances\Pages\ViewPerformance;
use App\Filament\Resources\Performances\RelationManagers\PricesRelationManager;
use App\Filament\Resources\SyncRuns\Pages\ListSyncRuns;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('events are editable only through Cue-owned editorial presentation fields', function () {
    $event = Event::factory()->create(['title' => 'Synced Provider Title']);

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm([
            'editorial' => [
                'title' => 'Published Editorial Title',
                'slug' => 'published-editorial-title',
                'summary' => 'An editorial introduction.',
                'description_html' => '<p>Editorial body copy.</p>',
                'seo_title' => 'Editorial SEO Title',
                'seo_description' => 'Editorial metadata.',
                'is_published' => true,
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($event->refresh()->title)->toBe('Synced Provider Title');

    $editorial = EventEditorial::query()->sole();

    expect($editorial->event_id)->toBe($event->getKey())
        ->and($editorial->title)->toBe('Published Editorial Title')
        ->and($editorial->slug)->toBe('published-editorial-title')
        ->and($editorial->is_published)->toBeTrue();
});

test('event and performance inspectors expose synchronized related data', function () {
    $event = Event::factory()->create();
    $performance = Performance::factory()->for($event)->create([
        'display_from_price_minor' => 2000,
        'display_currency' => 'GBP',
        'has_dynamic_pricing' => true,
    ]);
    $price = PerformancePrice::factory()->for($performance)->create();

    Livewire::test(PerformancesRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])->assertCanSeeTableRecords([$performance]);

    Livewire::test(PricesRelationManager::class, [
        'ownerRecord' => $performance,
        'pageClass' => ViewPerformance::class,
    ])->assertCanSeeTableRecords([$price]);
});

test('editors may create redirects attached to an imported event', function () {
    $event = Event::factory()->create();

    Livewire::test(RedirectsRelationManager::class, [
        'ownerRecord' => $event,
        'pageClass' => EditEvent::class,
    ])
        ->callAction(TestAction::make('create')->table(), [
            'source_path' => '/whats-on/old-title',
            'destination_path' => '/events/new-title',
            'status_code' => 301,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    $redirect = EventRedirect::query()->sole();

    expect($redirect->event_id)->toBe($event->getKey())
        ->and($redirect->source_path)->toBe('/whats-on/old-title')
        ->and($redirect->destination_path)->toBe('/events/new-title')
        ->and($redirect->is_active)->toBeTrue();
});

test('sync run actions dispatch domain synchronization work', function () {
    Bus::fake();

    Performance::factory()->create(['starts_at' => now()->addDay()]);

    Livewire::test(ListSyncRuns::class)
        ->callAction(TestAction::make('syncCatalogue'))
        ->callAction(TestAction::make('syncPrices'));

    Bus::assertDispatched(SyncTicketingCatalogue::class);

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 1;
    });
});

test('synced resources do not expose create routes', function () {
    expect(EventResource::getPages())->not->toHaveKey('create');
});
