@props(['event', 'performance', 'siteCopy'])

<li class="flex flex-col justify-between gap-5 py-6 sm:flex-row sm:items-center">
    <div class="flex items-start gap-6">
        <div class="min-w-16 text-center">
            <p class="text-xs font-medium uppercase tracking-[0.18em] text-[#766c5f]">{{ $performance->startsAt->format('M') }}</p>
            <p class="mt-1 text-3xl font-semibold tracking-tight">{{ $performance->startsAt->format('j') }}</p>
        </div>
        <div>
            <p class="text-lg font-medium">{{ $performance->startsAt->format('l') }}, {{ $performance->startsAt->format('g:ia') }}</p>
            @if ($performance->displayPrice !== null)
                <p class="mt-2 text-sm text-[#5d5549]">
                    {{ $siteCopy->guidePricePrefix }} {{ $performance->displayPrice }}
                    @if ($performance->hasDynamicPricing)
                        <span>&middot; {{ $siteCopy->dynamicPriceSuffix }}</span>
                    @endif
                    @if ($performance->priceIsStale)
                        <span>&middot; {{ $siteCopy->stalePriceSuffix }}</span>
                    @endif
                </p>
            @else
                <p class="mt-2 text-sm text-[#5d5549]">{{ $siteCopy->pricesConfirmedInBooking }}</p>
            @endif
            @if ($performance->accessProvisions->isNotEmpty())
                <ul class="mt-3 flex flex-wrap gap-2" aria-label="Access provisions">
                    @foreach ($performance->accessProvisions as $accessProvision)
                        <li class="rounded-full bg-[#e8e0d2] px-3 py-1 text-xs font-semibold text-[#403a31]">
                            {{ $accessProvision->name }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @if ($performance->isOnSale && $performance->bookingUrl !== null)
        <a href="{{ route('events.show', ['slug' => $event->slug, 'performance' => $performance->id]) }}#booking" class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#a4432e] px-6 py-3 text-sm font-semibold text-[#fdf7ee] transition hover:bg-[#853321] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]" aria-label="{{ $siteCopy->bookingCtaLabel }} {{ $event->title }} on {{ $performance->startsAt->format('l j F \a\t g:ia') }}">
            {{ $siteCopy->bookingCtaLabel }}
        </a>
    @else
        <span class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#e5dccd] px-6 py-3 text-sm font-semibold text-[#5d5549]">
            {{ $siteCopy->onlineBookingUnavailableLabel }}
        </span>
    @endif
</li>
