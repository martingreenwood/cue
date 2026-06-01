@props(['title' => null, 'subtitle' => null])
<section class="px-5 py-12 sm:px-8 lg:px-12">
    <div class="mx-auto max-w-5xl">
        <h1 class="text-4xl font-semibold tracking-tight text-[#171511]">{{ $title ?? 'Hero title' }}</h1>
        @if (filled($subtitle ?? null))
            <p class="mt-4 max-w-3xl text-base leading-7 text-[#5d5549]">{{ $subtitle }}</p>
        @endif
    </div>
</section>
