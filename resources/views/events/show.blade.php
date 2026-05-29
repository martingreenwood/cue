@extends('layouts.public', [
    'metaTitle' => $event->seoTitle ?? $event->title,
    'metaDescription' => $event->seoDescription ?? $event->summary,
    'canonicalUrl' => route('events.show', ['slug' => $event->slug]),
    'embedScriptUrl' => $bookingPerformance?->bookingEmbedScriptUrl,
])

@section('content')
    <article class="px-5 pb-14 pt-9 sm:px-8 lg:px-12 lg:pt-14">
        <div class="mx-auto max-w-7xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-[#5d5549] hover:text-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                <span aria-hidden="true">&larr;</span> All events
            </a>

            <div class="mt-9 grid gap-10 lg:grid-cols-[minmax(18rem,0.8fr)_minmax(24rem,1fr)] lg:gap-16">
                <div>
                    @if ($event->imagePath !== null)
                        <img class="aspect-[4/5] w-full bg-[#e8e0d2] object-cover" src="{{ asset('storage/'.$event->imagePath) }}" alt="{{ $event->imageAlt ?? '' }}">
                    @else
                        <div class="flex aspect-[4/5] items-end bg-[#24211d] p-8 text-[#ede5d7]" aria-hidden="true">
                            <span class="text-sm uppercase tracking-[0.28em]">Cue Programme</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-9">
                    <header class="flex flex-col gap-5">
                        <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Event</p>
                        <h1 class="text-[clamp(3rem,7vw,5.7rem)] font-semibold leading-[0.92] tracking-[-0.065em]">{{ $event->title }}</h1>
                        @if ($event->summary !== null)
                            <p class="max-w-2xl text-xl leading-8 text-[#5d5549]">{{ $event->summary }}</p>
                        @endif
                    </header>

                    @if ($event->description !== null)
                        <p class="max-w-[65ch] text-base leading-8 text-[#322e28]">{{ $event->description }}</p>
                    @endif

                    <div class="flex flex-wrap gap-x-10 gap-y-5 border-y border-[#171511]/12 py-6 text-sm">
                        @if ($event->durationMinutes !== null)
                            <div>
                                <p class="uppercase tracking-[0.18em] text-[#766c5f]">Running time</p>
                                <p class="mt-2 text-base font-medium">{{ $event->durationMinutes }} minutes</p>
                            </div>
                        @endif
                        @if ($event->fromPrice !== null)
                            <div>
                                <p class="uppercase tracking-[0.18em] text-[#766c5f]">{{ $siteCopy->guidePriceLabel }}</p>
                                <p class="mt-2 text-base font-medium">{{ $siteCopy->guidePricePrefix }} {{ $event->fromPrice }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <section aria-labelledby="performances-heading" class="mt-16 lg:mt-24">
                <div class="max-w-3xl">
                    <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Choose a date</p>
                    <h2 id="performances-heading" class="mt-4 text-4xl font-semibold tracking-[-0.045em]">Performances</h2>
                </div>

                @if ($event->performances->isEmpty())
                    <p class="mt-9 max-w-xl border-y border-[#171511]/12 py-9 text-lg leading-8 text-[#5d5549]">
                        There are no future performances available for booking at present.
                    </p>
                @else
                    <form action="{{ route('events.show', ['slug' => $event->slug]) }}" method="GET" class="mt-9 max-w-4xl border border-[#171511]/12 bg-[#efe7da] p-5 sm:p-7" aria-label="Filter performances">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="flex flex-col gap-2 text-sm font-semibold text-[#403a31]">
                                Select date
                                <input
                                    type="date"
                                    name="date"
                                    value="{{ $performanceFilters->date }}"
                                    class="min-h-12 rounded-sm border border-[#171511]/16 bg-[#fffaf2] px-4 py-3 text-base font-normal text-[#322e28] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]"
                                >
                            </label>
                            <label class="flex flex-col gap-2 text-sm font-semibold text-[#403a31]">
                                Quick dates
                                <select name="when" class="min-h-12 rounded-sm border border-[#171511]/16 bg-[#fffaf2] px-4 py-3 text-base font-normal text-[#322e28] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                                    <option value="all" @selected($performanceFilters->dateWindow === 'all')>All upcoming</option>
                                    <option value="today" @selected($performanceFilters->dateWindow === 'today')>Today</option>
                                    <option value="this-week" @selected($performanceFilters->dateWindow === 'this-week')>This week</option>
                                    <option value="this-month" @selected($performanceFilters->dateWindow === 'this-month')>This month</option>
                                </select>
                            </label>
                        </div>

                        @if ($performanceListing->accessOptions->isNotEmpty())
                            <fieldset class="mt-6">
                                <legend class="mb-3 text-sm font-semibold text-[#403a31]">Access provisions</legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($performanceListing->accessOptions as $accessOption)
                                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ in_array($accessOption->slug, $performanceFilters->access, true) ? 'border-[#a4432e] bg-[#a4432e] text-[#fffaf2]' : 'border-[#171511]/16 bg-[#fffaf2] text-[#403a31] hover:border-[#a4432e]/50' }}">
                                            <input type="checkbox" name="access[]" value="{{ $accessOption->slug }}" @checked(in_array($accessOption->slug, $performanceFilters->access, true)) class="size-4 accent-[#a4432e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]">
                                            {{ $accessOption->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        <div class="mt-7 flex flex-wrap items-center gap-5">
                            <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-sm bg-[#a4432e] px-6 py-3 text-sm font-semibold text-[#fffaf2] transition hover:bg-[#853321] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                Filter performances
                            </button>
                            @if ($performanceFilters->isApplied())
                                <a href="{{ route('events.show', ['slug' => $event->slug]) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                    Clear filters
                                </a>
                            @endif
                        </div>
                    </form>

                    @if ($performanceListing->performances->isEmpty())
                        <p class="mt-9 max-w-4xl border-y border-[#171511]/12 py-9 text-lg leading-8 text-[#5d5549]">
                            No upcoming performances match your filters. Try another date or access provision, or clear the filters.
                        </p>
                    @else
                        <p class="mt-6 max-w-3xl text-sm leading-6 text-[#5d5549]">
                            {{ $siteCopy->performanceFreshnessNotice }}
                        </p>

                        @php
                            $initialPerformances = $performanceListing->performances->take(8);
                            $additionalPerformances = $performanceListing->performances->slice(8);
                        @endphp

                        <ul class="mt-9 flex max-w-4xl flex-col divide-y divide-[#171511]/12 border-y border-[#171511]/12">
                            @foreach ($initialPerformances as $performance)
                                <x-events.performance-row :event="$event" :performance="$performance" :site-copy="$siteCopy" />
                            @endforeach
                        </ul>

                        @if ($additionalPerformances->isNotEmpty())
                            <details class="group max-w-4xl border-b border-[#171511]/12">
                                <summary class="min-h-12 cursor-pointer py-5 text-sm font-semibold text-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                    View {{ $additionalPerformances->count() }} more performances
                                </summary>
                                <ul class="flex flex-col divide-y divide-[#171511]/12 border-t border-[#171511]/12">
                                    @foreach ($additionalPerformances as $performance)
                                        <x-events.performance-row :event="$event" :performance="$performance" :site-copy="$siteCopy" />
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    @endif
                @endif
            </section>

            @if ($bookingPerformance !== null)
                <section id="booking" aria-labelledby="booking-heading" class="mt-16 scroll-mt-6 lg:mt-24">
                    <div class="mb-8 max-w-3xl">
                        <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure booking</p>
                        <h2 id="booking-heading" class="mt-4 text-4xl font-semibold tracking-[-0.045em]">Book tickets</h2>
                        <p class="mt-4 text-base leading-7 text-[#5d5549]">
                            {{ $siteCopy->secureBookingPrefix }} {{ $bookingPerformance->startsAt->format('l j F Y') }} at {{ $bookingPerformance->startsAt->format('g:ia') }}.
                        </p>
                    </div>

                    <div class="overflow-hidden border border-[#171511]/12 bg-white">
                        <iframe
                            id="SpektrixIFrame"
                            name="SpektrixIFrame"
                            title="Book tickets for {{ $event->title }} on {{ $bookingPerformance->startsAt->format('l j F Y \a\t g:ia') }}"
                            src="{{ $bookingPerformance->bookingUrl }}"
                            class="block h-[1000px] w-full bg-white"
                            height="1000"
                            loading="eager"
                        ></iframe>
                    </div>
                </section>
            @endif
        </div>
    </article>
@endsection
