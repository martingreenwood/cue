<?php

declare(strict_types=1);

use App\Domains\CMS\Models\PublicSiteSetting;
use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Jobs\SyncTicketingCatalogue;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventEditorial;
use App\Domains\Events\Models\EventRedirect;
use App\Domains\Events\Models\FilterTerm;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\PerformancePrice;
use App\Filament\Pages\ContentStrings;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Filament\Resources\Events\RelationManagers\PerformancesRelationManager;
use App\Filament\Resources\Events\RelationManagers\RedirectsRelationManager;
use App\Filament\Resources\FilterTerms\Pages\CreateFilterTerm;
use App\Filament\Resources\FilterTerms\Pages\ListFilterTerms;
use App\Filament\Resources\Performances\Pages\EditPerformance;
use App\Filament\Resources\Performances\Pages\ViewPerformance;
use App\Filament\Resources\Performances\RelationManagers\PricesRelationManager;
use App\Filament\Resources\SyncRuns\Pages\ListSyncRuns;
use App\Models\User;
use Database\Seeders\FilterTermVocabularySeeder;
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

test('editors may customise public availability and booking language', function () {
    Livewire::test(ContentStrings::class)
        ->fillForm([
            'listing_kicker' => 'Booking now',
            'guide_price_label' => 'Indicative ticket price',
            'guide_price_prefix' => 'Tickets from',
            'prices_confirmed_in_booking' => 'See booking for prices',
            'dynamic_price_suffix' => 'Subject to change',
            'stale_price_suffix' => 'Confirm when booking',
            'performance_freshness_notice' => 'Our performance guide is updated regularly. Confirm current seats when booking.',
            'booking_cta_label' => 'Select seats',
            'online_booking_unavailable_label' => 'Contact the box office',
            'secure_booking_prefix' => 'Select current seats securely for',
            'footer_availability_notice' => 'Ticket availability is confirmed at booking.',
            'customer_logged_in_label' => 'Hello',
            'customer_logged_out_label' => 'Log in to your account',
            'customer_basket_label' => 'My basket',
            'basket_membership_upsell' => 'Log in for member savings.',
            'customer_donate_label' => 'Support us',
            'customer_gift_vouchers_label' => 'Buy gift vouchers',
            'customer_memberships_label' => 'Join membership',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $settings = PublicSiteSetting::query()->sole();

    expect($settings->listing_kicker)->toBe('Booking now')
        ->and($settings->booking_cta_label)->toBe('Select seats')
        ->and($settings->footer_availability_notice)->toBe('Ticket availability is confirmed at booking.')
        ->and($settings->customer_logged_out_label)->toBe('Log in to your account')
        ->and($settings->customer_basket_label)->toBe('My basket')
        ->and($settings->customer_donate_label)->toBe('Support us')
        ->and($settings->customer_gift_vouchers_label)->toBe('Buy gift vouchers')
        ->and($settings->customer_memberships_label)->toBe('Join membership');
});

test('editors may define public filter terms', function () {
    Livewire::test(CreateFilterTerm::class)
        ->fillForm([
            'filter_group' => FilterGroup::Access->value,
            'name' => 'Audio Described',
            'slug' => 'audio-described',
            'description' => 'For performances with audio description provision.',
            'sort_order' => 10,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $term = FilterTerm::query()->sole();

    expect($term->filter_group)->toBe(FilterGroup::Access)
        ->and($term->name)->toBe('Audio Described')
        ->and($term->slug)->toBe('audio-described');
});

test('editors can review the seeded representative public filter vocabulary', function () {
    $this->seed(FilterTermVocabularySeeder::class);

    expect(FilterTerm::query()->count())->toBe(18)
        ->and(FilterTerm::query()->where('filter_group', FilterGroup::What)->orderBy('sort_order')->pluck('slug')->all())
        ->toBe([
            'drama',
            'comedy',
            'dance',
            'music',
            'family',
            'talks-and-ideas',
            'workshops',
        ])
        ->and(FilterTerm::query()->where('filter_group', FilterGroup::Offers)->orderBy('sort_order')->pluck('slug')->all())
        ->toBe([
            'members-priority',
            'under-26-tickets',
            'schools-offer',
            'pay-what-you-can',
            'group-discounts',
        ])
        ->and(FilterTerm::query()->where('filter_group', FilterGroup::Access)->orderBy('sort_order')->pluck('slug')->all())
        ->toBe([
            'audio-described',
            'captioned',
            'bsl-interpreted',
            'relaxed-performance',
            'touch-tour',
            'dementia-friendly',
        ]);

    Livewire::test(ListFilterTerms::class)
        ->set('tableRecordsPerPage', 25)
        ->assertCanSeeTableRecords(FilterTerm::query()->get())
        ->assertSee('Drama')
        ->assertSee('Members Priority')
        ->assertSee('Audio Described')
        ->assertSee('Specific performances with live audio description');

    $this->seed(FilterTermVocabularySeeder::class);

    expect(FilterTerm::query()->count())->toBe(18);
});

test('editors assign what and offers terms to events without mutating imported data', function () {
    $event = Event::factory()->create(['title' => 'Synced Source Title']);
    $whatTerm = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::What,
        'name' => 'Drama',
        'slug' => 'drama',
    ]);
    $offerTerm = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Offers,
        'name' => 'Members',
        'slug' => 'members',
    ]);

    Livewire::test(EditEvent::class, ['record' => $event->getKey()])
        ->fillForm([
            'whatTerms' => [$whatTerm->getKey()],
            'offerTerms' => [$offerTerm->getKey()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($event->refresh()->title)->toBe('Synced Source Title')
        ->and($event->whatTerms()->pluck('filter_terms.name')->all())->toBe(['Drama'])
        ->and($event->offerTerms()->pluck('filter_terms.name')->all())->toBe(['Members']);
});

test('editors assign access terms to individual performances', function () {
    $performance = Performance::factory()->create();
    $accessTerm = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Access,
        'name' => 'Captioned',
        'slug' => 'captioned',
    ]);

    Livewire::test(EditPerformance::class, ['record' => $performance->getKey()])
        ->fillForm(['accessTerms' => [$accessTerm->getKey()]])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect($performance->accessTerms()->pluck('filter_terms.name')->all())->toBe(['Captioned']);
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

test('editors can publish and unpublish an imported event from its view page', function () {
    $event = Event::factory()->create();

    Livewire::test(ViewEvent::class, ['record' => $event->getKey()])
        ->callAction(TestAction::make('publishNow'))
        ->assertNotified();

    $editorial = EventEditorial::query()->sole();

    expect($editorial->is_published)->toBeTrue()
        ->and($editorial->published_at)->not->toBeNull();

    Livewire::test(ViewEvent::class, ['record' => $event->getKey()])
        ->callAction(TestAction::make('unpublish'))
        ->assertNotified();

    expect($editorial->refresh()->is_published)->toBeFalse();
});

test('editors can publish and unpublish imported events from the listing', function () {
    $event = Event::factory()->create();

    Livewire::test(ListEvents::class)
        ->callAction(TestAction::make('publishNow')->table($event))
        ->assertNotified();

    $editorial = EventEditorial::query()->sole();

    expect($editorial->is_published)->toBeTrue();

    Livewire::test(ListEvents::class)
        ->callAction(TestAction::make('unpublish')->table($event))
        ->assertNotified();

    expect($editorial->refresh()->is_published)->toBeFalse();
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
