@props(['media_url' => null, 'media_alt' => null, 'caption' => null])
<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto max-w-5xl">
        @if (filled($media_url ?? null))
            <img src="{{ $media_url }}" alt="{{ $media_alt ?? '' }}" class="w-full rounded border border-[#171511]/10" loading="lazy">
        @endif
        @if (filled($caption ?? null))
            <p class="mt-3 text-sm text-[#5d5549]">{{ $caption }}</p>
        @endif
    </div>
</section>
