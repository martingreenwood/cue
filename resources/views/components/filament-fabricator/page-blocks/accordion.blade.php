@props([
    'title' => null,
    'subtitle' => null,
    'items' => [],
])

<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto max-w-5xl">
        @if (filled($title ?? null) || filled($subtitle ?? null))
            <div class="mb-8 max-w-3xl">
                @if (filled($title ?? null))
                    <h2 class="text-3xl font-semibold tracking-tight text-[#171511] sm:text-4xl">
                        {{ $title }}
                    </h2>
                @endif

                @if (filled($subtitle ?? null))
                    <p class="mt-4 text-base leading-7 text-[#5d5549] sm:text-lg">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        @endif

        <div class="space-y-3">
            @foreach (($items ?? []) as $item)
                <details class="rounded border border-[#171511]/15 bg-white p-4">
                    <summary class="cursor-pointer text-base font-semibold text-[#171511]">{{ data_get($item, 'title') }}</summary>
                    <div class="prose prose-stone mt-3 max-w-none">{!! data_get($item, 'content') !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
