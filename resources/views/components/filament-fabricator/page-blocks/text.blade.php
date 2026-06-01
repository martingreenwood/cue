@props(['content' => null])
<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="prose prose-stone mx-auto max-w-3xl">
        {!! $content ?? '' !!}
    </div>
</section>
