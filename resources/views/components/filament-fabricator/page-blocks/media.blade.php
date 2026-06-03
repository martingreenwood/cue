@props([
    'media_type' => 'image',
    'media_url' => null,
    'image_src' => null,
    'video_src' => null,
    'media_alt' => null,
    'caption' => null,
])

@php
    $mediaType = in_array($media_type, ['image', 'video'], true) ? $media_type : 'image';
    $imageSrc = $image_src ?? $media_url;
@endphp

<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto max-w-5xl">
        @if ($mediaType === 'video' && filled($video_src ?? null))
            <video class="w-full rounded border border-[#171511]/10" controls playsinline>
                <source src="{{ $video_src }}">
            </video>
        @elseif (filled($imageSrc ?? null))
            <img src="{{ $imageSrc }}" alt="{{ $media_alt ?? '' }}" class="w-full rounded border border-[#171511]/10" loading="lazy">
        @endif

        @if (filled($caption ?? null))
            <p class="mt-3 text-sm text-[#5d5549]">{{ $caption }}</p>
        @endif
    </div>
</section>
