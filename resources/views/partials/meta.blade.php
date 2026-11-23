{{--
  Shared social/SEO tags.

  WhatsApp and other link previews read og:title / og:description / og:image,
  so every public page passes its own copy. `$image` may be absolute or a path
  under public/.
--}}
@php
    $ogTitle = $title ?? config('app.name');
    $ogDescription = $description ?? 'سينما بلاس — احجز تذاكر السينما وصالات الألعاب والنادي الاجتماعي ومناسبات المقهى في مكان واحد.';
    $ogUrl = $url ?? url()->current();
    $ogImage = null;

    if (! empty($image)) {
        $ogImage = str_starts_with($image, 'http') ? $image : asset(ltrim($image, '/'));
    } else {
        $ogImage = asset('img/og-cover.jpg');
    }
@endphp
<meta name="description" content="{{ $ogDescription }}">
<link rel="canonical" href="{{ $ogUrl }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="سينما بلاس">
<meta property="og:locale" content="ar_LY">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
