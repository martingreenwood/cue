@props([
    'eyebrow' => 'Related content',
    'related_title' => null,
    'related_url' => null,
    'featured_image_src' => null,
    'featured_image_alt' => null,
    'text' => null,
    'button_text' => 'Read more',
])

@if (filled($related_title ?? null) && filled($related_url ?? null))
    <section class="px-5 py-8 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-5xl">
            <div class="overflow-hidden rounded-3xl border border-[#171511]/10 bg-[#f8f3ea]">
                <div class="grid gap-0 md:grid-cols-[minmax(14rem,22rem)_minmax(0,1fr)]">
                    @if (filled($featured_image_src ?? null))
                        <img
                            src="{{ $featured_image_src }}"
                            alt="{{ $featured_image_alt ?? '' }}"
                            class="aspect-[16/10] h-full w-full object-cover md:aspect-auto"
                            loading="lazy"
                        >
                    @endif

                    <div class="p-6 sm:p-8">
                        @if (filled($eyebrow ?? null))
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#8f3328]">{{ $eyebrow }}</p>
                        @endif

                        <div class="mt-4 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                            <div class="max-w-3xl">
                                <h2 class="text-3xl font-semibold tracking-tight text-[#171511] sm:text-4xl">
                                    {{ $related_title }}
                                </h2>

                                @if (filled($text ?? null))
                                    <p class="mt-4 text-base leading-7 text-[#5d5549] sm:text-lg">
                                        {{ $text }}
                                    </p>
                                @endif
                            </div>

                            <a
                                href="{{ $related_url }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#171511] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2a261f]"
                            >
                                {{ $button_text }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
