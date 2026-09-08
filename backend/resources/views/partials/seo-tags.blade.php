@php
    $seoTitle = $seo['title'] ?? config('seo.default.title');
    $seoDescription = $seo['description'] ?? config('seo.default.description');
    $seoType = $seo['type'] ?? config('seo.default.type');
    $seoUrl = $seo['url'] ?? url()->current();
    $seoSiteName = config('seo.site_name');
    $seoNoindex = $seo['noindex'] ?? false;
@endphp
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoNoindex ? 'noindex,nofollow' : 'index,follow' }}">
<link rel="canonical" href="{{ $seoUrl }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:site_name" content="{{ $seoSiteName }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if(!empty($seoLd))
<script type="application/ld+json">
{!! json_encode($seoLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
