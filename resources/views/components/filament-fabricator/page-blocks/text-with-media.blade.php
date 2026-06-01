@props(['content' => null, 'media_url' => null, 'media_alt' => null])
<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto grid max-w-5xl items-center gap-8 md:grid-cols-2">
        <div class="prose prose-stone max-w-none">{!! $content ?? '' !!}</div>
        @if (filled($media_url ?? null))
            <img src="{{ $media_url }}" alt="{{ $media_alt ?? '' }}" class="w-full rounded border border-[#171511]/10" loading="lazy">
        @endif
    </div>
</section>
