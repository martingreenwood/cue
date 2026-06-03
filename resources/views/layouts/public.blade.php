<x-public-shell
    :meta-title="$metaTitle ?? 'What is on'"
    :meta-description="$metaDescription ?? null"
    :canonical-url="$canonicalUrl ?? null"
    :embed-script-url="$embedScriptUrl ?? null"
    :customer-session="$customerSession ?? null"
    :site-copy="$siteCopy ?? null"
>
    @yield('content')
</x-public-shell>
