@extends('layouts.public', [
    'metaTitle' => 'What is on',
    'metaDescription' => 'Explore upcoming theatre and live events.',
    'canonicalUrl' => route('events.index'),
])

@section('content')
    <section class="px-5 pb-14 pt-12 sm:px-8 sm:pt-16 lg:px-12 lg:pt-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-sm font-medium uppercase tracking-[0.26em] text-[#a4432e]">{{ $siteCopy->listingKicker }}</p>
            <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,48rem)_1fr] lg:items-end">
                <h1 class="text-[clamp(3.1rem,8vw,6.7rem)] font-semibold leading-[0.9] tracking-[-0.07em]">
                    What is on
                </h1>
                <p class="max-w-sm pb-2 text-base leading-7 text-[#5d5549]">
                    Performances, productions and one-night experiences. Book from our locally managed programme.
                </p>
            </div>
        </div>
    </section>

    <section aria-label="Upcoming events" class="px-5 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">
            <form method="GET" action="{{ route('events.index') }}" role="search" class="mb-12 border-y border-[#171511]/12 bg-[#ede7dd] px-5 py-6 sm:px-6">
                <div class="grid gap-4 sm:grid-cols-[minmax(18rem,1fr)_12rem_auto] sm:items-end">
                    <div>
                        <label for="event-search" class="mb-2 block text-sm font-medium text-[#403a31]">Search events</label>
                        <input id="event-search" type="search" name="q" value="{{ $filters->query ?? '' }}" placeholder="Title or description" class="block w-full rounded-sm border border-[#171511]/18 bg-[#fffaf2] px-4 py-3 text-[#171511] placeholder:text-[#756d61] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                    </div>
                    <div>
                        <label for="event-date-window" class="mb-2 block text-sm font-medium text-[#403a31]">When</label>
                        <select id="event-date-window" name="when" class="block w-full rounded-sm border border-[#171511]/18 bg-[#fffaf2] px-4 py-3 text-[#171511] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                            <option value="all" @selected($filters->dateWindow === 'all')>All upcoming</option>
                            <option value="next-30-days" @selected($filters->dateWindow === 'next-30-days')>Next 30 days</option>
                            <option value="next-90-days" @selected($filters->dateWindow === 'next-90-days')>Next 90 days</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="submit" class="rounded-sm bg-[#a4432e] px-6 py-3 font-semibold text-[#fffaf2] transition hover:bg-[#843322] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">Apply filters</button>
                        @if ($filters->isApplied())
                            <a href="{{ route('events.index') }}" class="text-sm font-semibold text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">Clear</a>
                        @endif
                    </div>
                </div>

                @if ($filterOptions->what->isNotEmpty() || $filterOptions->offers->isNotEmpty() || $filterOptions->access->isNotEmpty())
                    <div class="mt-7 grid gap-7 border-t border-[#171511]/10 pt-6 md:grid-cols-3">
                        @if ($filterOptions->what->isNotEmpty())
                            <fieldset>
                                <legend class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-[#403a31]">What</legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($filterOptions->what as $term)
                                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ in_array($term->slug, $filters->what, true) ? 'border-[#a4432e] bg-[#a4432e] text-[#fffaf2]' : 'border-[#171511]/16 bg-[#fffaf2] text-[#403a31] hover:border-[#a4432e]/50' }}">
                                            <input type="checkbox" name="what[]" value="{{ $term->slug }}" @checked(in_array($term->slug, $filters->what, true)) class="size-4 accent-[#a4432e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                                            {{ $term->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        @if ($filterOptions->offers->isNotEmpty())
                            <fieldset>
                                <legend class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-[#403a31]">Offers</legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($filterOptions->offers as $term)
                                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ in_array($term->slug, $filters->offers, true) ? 'border-[#a4432e] bg-[#a4432e] text-[#fffaf2]' : 'border-[#171511]/16 bg-[#fffaf2] text-[#403a31] hover:border-[#a4432e]/50' }}">
                                            <input type="checkbox" name="offers[]" value="{{ $term->slug }}" @checked(in_array($term->slug, $filters->offers, true)) class="size-4 accent-[#a4432e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                                            {{ $term->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        @if ($filterOptions->access->isNotEmpty())
                            <fieldset>
                                <legend class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-[#403a31]">Access</legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($filterOptions->access as $term)
                                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ in_array($term->slug, $filters->access, true) ? 'border-[#a4432e] bg-[#a4432e] text-[#fffaf2]' : 'border-[#171511]/16 bg-[#fffaf2] text-[#403a31] hover:border-[#a4432e]/50' }}">
                                            <input type="checkbox" name="access[]" value="{{ $term->slug }}" @checked(in_array($term->slug, $filters->access, true)) class="size-4 accent-[#a4432e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                                            {{ $term->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif
                    </div>
                @endif
            </form>

            @if ($events->isEmpty())
                <div class="max-w-2xl border-y border-[#171511]/12 py-16">
                    @if ($filters->isApplied())
                        @if ($filters->access !== [])
                            <h2 class="text-3xl font-semibold tracking-tight">No upcoming performances match your selected access filters.</h2>
                            <p class="mt-4 text-lg leading-8 text-[#5d5549]">Access provisions are applied to individual performance dates. Try another access option or clear the filters to view the full programme.</p>
                        @else
                            <h2 class="text-3xl font-semibold tracking-tight">No events match your search.</h2>
                            <p class="mt-4 text-lg leading-8 text-[#5d5549]">Try clearing a filter or searching for another production.</p>
                        @endif
                        <a href="{{ route('events.index') }}" class="mt-7 inline-block text-sm font-semibold text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">Clear filters</a>
                    @else
                        <h2 class="text-3xl font-semibold tracking-tight">No events are currently published.</h2>
                        <p class="mt-4 text-lg leading-8 text-[#5d5549]">Our next programme will appear here as soon as it is announced.</p>
                    @endif
                </div>
            @else
                @if ($filters->isApplied())
                    <p class="mb-9 text-sm uppercase tracking-[0.18em] text-[#5d5549]">{{ trans_choice('{1} :count event found|[2,*] :count events found', $events->total(), ['count' => $events->total()]) }}</p>
                @endif
                @php($hasPrioritisedImage = false)
                <div class="grid grid-cols-1 gap-x-7 gap-y-14 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($events as $event)
                        <article class="group flex flex-col gap-5">
                            <a href="{{ route('events.show', ['slug' => $event->slug]) }}" aria-label="View details for {{ $event->title }}" class="block overflow-hidden bg-[#e8e0d2] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                @if ($event->imagePath !== null)
                                    @php($prioritiseImage = ! $hasPrioritisedImage)
                                    @php($hasPrioritisedImage = true)
                                    <img class="aspect-[4/5] w-full object-cover transition duration-500 motion-reduce:transition-none group-hover:scale-[1.025]" src="{{ asset('storage/'.$event->imagePath) }}" alt="{{ $event->imageAlt ?? '' }}" width="640" height="800" loading="{{ $prioritiseImage ? 'eager' : 'lazy' }}" fetchpriority="{{ $prioritiseImage ? 'high' : 'low' }}" decoding="async">
                                @else
                                    <div class="flex aspect-[4/5] items-end bg-[#24211d] p-7 text-[#ede5d7]" aria-hidden="true">
                                        <span class="text-sm uppercase tracking-[0.28em]">Cue Programme</span>
                                    </div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col gap-3">
                                <p class="text-sm uppercase tracking-[0.16em] text-[#5d5549]">
                                    {{ $event->firstPerformanceAt?->format('j M') }}@if ($event->lastPerformanceAt !== null && ! $event->lastPerformanceAt->isSameDay($event->firstPerformanceAt)) - {{ $event->lastPerformanceAt->format('j M Y') }}@else {{ $event->firstPerformanceAt?->format('Y') }}@endif
                                </p>
                                <h2 class="text-3xl font-semibold leading-[1.05] tracking-[-0.04em]">
                                    <a href="{{ route('events.show', ['slug' => $event->slug]) }}" class="focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">{{ $event->title }}</a>
                                </h2>
                                @if ($event->summary !== null)
                                    <p class="line-clamp-3 text-base leading-7 text-[#5d5549]">{{ $event->summary }}</p>
                                @endif
                                <div class="mt-auto flex items-center justify-between gap-4 border-t border-[#171511]/10 pt-4">
                                    <span class="text-sm font-medium text-[#5d5549]">
                                        @if ($event->fromPrice !== null)
                                            {{ $siteCopy->guidePricePrefix }} {{ $event->fromPrice }}
                                        @else
                                            {{ $siteCopy->pricesConfirmedInBooking }}
                                        @endif
                                    </span>
                                    <span class="text-sm font-semibold text-[#a4432e]">Details</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-16">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
