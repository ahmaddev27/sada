<!DOCTYPE html>
<?php
    $s        = app(\App\Services\SiteSettingsService::class)->all();
    $name     = $s['site_name']        ?? 'صدى';
    $slogan   = $s['site_slogan']      ?? 'منصة التسويق الذكي للسوق الخليجي';
    $metaT    = $s['meta_title']       ?? "{$name} — {$slogan}";
    $desc     = $s['meta_description'] ?? 'منصة صدى للتسويق الرقمي بالذكاء الاصطناعي — توليد محتوى خليجي بـ 7 لهجات، حملات موسمية، جدولة Meta، وإعلانات ممولة.';
    $kw       = $s['meta_keywords']    ?? 'تسويق رقمي, ذكاء اصطناعي, محتوى عربي, خليج, سوشيال ميديا';
    $ogImg    = $s['og_image_path']    ?? null;
    $favicon  = $s['favicon_path']     ?? null;
    $twSite   = !empty($s['social_twitter'])   ? '@' . ltrim(basename((string)$s['social_twitter']), '@') : null;
    $gaId     = $s['google_analytics'] ?? null;
    $gtmId    = $s['gtm_id']           ?? null;
    $canonical = rtrim(request()->url(), '/');
?>
<html lang="ar" dir="rtl">
<head>
    {{-- Theme flash-prevention (must be first) --}}
    <script>(function(){var t=localStorage.getItem('sada-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

    {{-- Google Tag Manager --}}
    @if($gtmId)
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @endif

    {{-- Google Analytics --}}
    @if($gaId && !$gtmId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $gaId }}');</script>
    @endif

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- Favicon --}}
    @if($favicon)
    <link rel="icon" type="image/png" href="{{ $favicon }}" />
    <link rel="shortcut icon" href="{{ $favicon }}" />
    @else
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.svg" />
    @endif

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $canonical }}" />

    {{-- Primary SEO --}}
    <title>{{ $metaT }}</title>
    <meta name="description" content="{{ $desc }}" />
    <meta name="keywords"    content="{{ $kw }}" />
    <meta name="robots"      content="index, follow" />

    {{-- Open Graph --}}
    <meta property="og:type"        content="website" />
    <meta property="og:site_name"   content="{{ $name }}" />
    <meta property="og:locale"      content="ar_SA" />
    <meta property="og:url"         content="{{ $canonical }}" />
    <meta property="og:title"       content="{{ $metaT }}" />
    <meta property="og:description" content="{{ $desc }}" />
    @if($ogImg)
    <meta property="og:image"        content="{{ $ogImg }}" />
    <meta property="og:image:width"  content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt"    content="{{ $name }}" />
    @endif

    {{-- Twitter / X Card --}}
    <meta name="twitter:card"        content="{{ $ogImg ? 'summary_large_image' : 'summary' }}" />
    <meta name="twitter:title"       content="{{ $metaT }}" />
    <meta name="twitter:description" content="{{ $desc }}" />
    @if($twSite)
    <meta name="twitter:site"        content="{{ $twSite }}" />
    @endif
    @if($ogImg)
    <meta name="twitter:image"       content="{{ $ogImg }}" />
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    {{-- Inertia page-level overrides (title + component meta) — rendered by JS --}}
    @inertiaHead
</head>
<body>
    {{-- Google Tag Manager (noscript) --}}
    @if($gtmId)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @inertia
</body>
</html>
