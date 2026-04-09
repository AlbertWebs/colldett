<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', strtolower(config('colldett.seo.locale', 'en_KE'))) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoLocale = config('colldett.seo.locale', 'en_KE');
        $canonical = $canonicalUrl ?? request()->url();
        $pageTitle = $metaTitle ?? config('colldett.company.name');
        $pageDesc = $metaDescription ?? config('colldett.company.description');
        $ogTypeVal = $ogType ?? 'website';
        $defaultOgImage = $site['branding']['logo'] ?? asset('uploads/logo.png');
        $ogImageVal = $metaImage ?? $defaultOgImage;
        $ogImageAltVal = $ogImageAlt ?? ($pageTitle.' — '.$site['company']['name']);
        $robotsVal = $metaRobots ?? (filter_var(env('SEO_INDEX', true), FILTER_VALIDATE_BOOL)
            ? config('colldett.seo.robots_default')
            : 'noindex,nofollow');
        $twitterSite = config('colldett.seo.twitter_site');
        $twitterCreator = config('colldett.seo.twitter_creator');
        $geoRegion = config('colldett.seo.geo_region');
        $geoPlace = config('colldett.seo.geo_placename');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    @if(!empty($metaKeywords))
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <meta name="robots" content="{{ $robotsVal }}">
    <meta name="author" content="{{ $site['company']['name'] }}">
    @if(!empty($geoRegion))
        <meta name="geo.region" content="{{ $geoRegion }}">
    @endif
    @if(!empty($geoPlace))
        <meta name="geo.placename" content="{{ $geoPlace }}">
    @endif
    <meta name="theme-color" content="{{ config('colldett.pwa.site.theme_color', '#215e1d') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('colldett.pwa.site.short_name', 'Colldett') }}">
    <link rel="manifest" href="{{ asset('manifest-site.webmanifest') }}">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="alternate" hreflang="{{ str_replace('_', '-', $seoLocale) }}" href="{{ $canonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ route('home', absolute: true) }}">

    <meta property="og:type" content="{{ $ogTypeVal }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ $site['company']['name'] }}">
    <meta property="og:locale" content="{{ $seoLocale }}">
    <meta property="og:image" content="{{ $ogImageVal }}">
    <meta property="og:image:secure_url" content="{{ $ogImageVal }}">
    <meta property="og:image:alt" content="{{ $ogImageAltVal }}">
    @if($ogTypeVal === 'article' && !empty($articlePublishedTime))
        <meta property="article:published_time" content="{{ $articlePublishedTime }}">
    @endif
    @if($ogTypeVal === 'article' && !empty($articleModifiedTime))
        <meta property="article:modified_time" content="{{ $articleModifiedTime }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image" content="{{ $ogImageVal }}">
    <meta name="twitter:image:alt" content="{{ $ogImageAltVal }}">
    @if(!empty($twitterSite))
        <meta name="twitter:site" content="{{ str_starts_with($twitterSite, '@') ? $twitterSite : '@'.$twitterSite }}">
    @endif
    @if(!empty($twitterCreator))
        <meta name="twitter:creator" content="{{ str_starts_with($twitterCreator, '@') ? $twitterCreator : '@'.$twitterCreator }}">
    @endif

    <link rel="icon" type="image/png" href="{{ $site['branding']['favicon'] ?? asset('uploads/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ $site['branding']['favicon'] ?? asset('uploads/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if(!empty($seoJsonLd))
        <script type="application/ld+json">{!! json_encode($seoJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
    @stack('head')
</head>
@php
    $waDigits = preg_replace('/\D+/', '', $site['company']['phone'] ?? '');
    $whatsappHref = $waDigits !== ''
        ? 'https://wa.me/'.$waDigits.'?text='.rawurlencode('Hello, I would like to request Colldett services.')
        : route('contact');
    $metaPhone = trim((string) ($site['company']['phone'] ?? ''));
    $metaEmail = strtolower(trim((string) ($site['company']['email'] ?? '')));
    $metaTelHref = $metaPhone !== '' ? 'tel:'.preg_replace('/\s+/', '', $metaPhone) : '';
    $topbarSocialItems = [
        ['href' => $site['social']['linkedin'] ?? '', 'label' => 'LinkedIn', 'abbr' => 'in'],
        ['href' => $site['social']['facebook'] ?? '', 'label' => 'Facebook', 'abbr' => 'f'],
        ['href' => $site['social']['twitter'] ?? '', 'label' => 'X', 'abbr' => 'x'],
        ['href' => $site['social']['instagram'] ?? '', 'label' => 'Instagram', 'abbr' => 'ig'],
        ['href' => $site['social']['youtube'] ?? '', 'label' => 'YouTube', 'abbr' => 'yt'],
    ];
    $hasTopbarSocial = collect($topbarSocialItems)->contains(fn (array $s) => ! empty($s['href']) && $s['href'] !== '#');
@endphp
<body class="{{ request()->routeIs('home') ? 'is-home' : 'is-inner' }} has-mobile-bottom-nav min-w-0 overflow-x-hidden antialiased" data-pwa-context="site">
@if(request()->routeIs('home'))
<div class="site-header-fixed" data-site-header-fixed>
@endif
<div class="topbar-meta">
    <div class="container topbar-meta-wrap">
        <span class="topbar-meta__tagline">Professional Recovery & Tracing Services</span>
        @if($metaPhone !== '' || $metaEmail !== '' || $hasTopbarSocial)
        <div class="topbar-meta__right">
            @if($metaPhone !== '' || $metaEmail !== '')
                <div class="topbar-meta__contacts">
                    @if($metaPhone !== '')
                        <a href="{{ $metaTelHref }}" class="topbar-meta__contact">{{ $metaPhone }}</a>
                    @endif
                    @if($metaPhone !== '' && $metaEmail !== '')
                        <span class="topbar-meta__sep" aria-hidden="true">|</span>
                    @endif
                    @if($metaEmail !== '')
                        <a href="mailto:{{ $metaEmail }}" class="topbar-meta__contact">{{ $metaEmail }}</a>
                    @endif
                </div>
            @endif
            @if($hasTopbarSocial)
            <div class="topbar-meta__social" aria-label="Social media">
                @foreach($topbarSocialItems as $s)
                    @if(!empty($s['href']) && $s['href'] !== '#')
                        <a href="{{ $s['href'] }}" aria-label="{{ $s['label'] }}" title="{{ $s['label'] }}" target="_blank" rel="noopener noreferrer">{{ $s['abbr'] }}</a>
                    @endif
                @endforeach
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
<header class="topbar" data-topbar>
    <div class="container nav-wrap">
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ $site['branding']['logo'] ?? asset('uploads/logo.png') }}" alt="{{ $site['company']['name'] }} logo" class="brand-logo brand-logo-default">
            <img src="{{ $site['branding']['footer_logo'] ?? asset('uploads/logo-white.png') }}" alt="{{ $site['company']['name'] }} logo" class="brand-logo brand-logo-white">
        </a>
        <div class="nav-backdrop" data-nav-backdrop aria-hidden="true"></div>
        <nav class="nav" id="site-nav" data-menu aria-label="Primary">
            <a class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">Home</a>
            <a class="nav-link {{ request()->routeIs('about') || request()->routeIs('team.show') ? 'is-active' : '' }}" href="{{ route('about') }}">Who we are</a>
            <details class="nav-dropdown {{ request()->routeIs('services') || request()->routeIs('capabilities.show') ? 'is-active' : '' }}" @if(request()->routeIs('services') || request()->routeIs('capabilities.show')) open @endif>
                <summary>Our Capabilities</summary>
                <div class="nav-dropdown-menu">
                    <a class="nav-sub-link" href="{{ route('services') }}">All Services</a>
                    @foreach($site['services'] as $service)
                        <a class="nav-sub-link" href="{{ route('capabilities.show', $service['slug']) }}">{{ $service['name'] }}</a>
                    @endforeach
                </div>
            </details>
            <a class="nav-link {{ request()->routeIs('industries') ? 'is-active' : '' }}" href="{{ route('industries') }}">Industries</a>
            <a class="nav-link {{ request()->routeIs('insights') || request()->routeIs('insights.show') ? 'is-active' : '' }}" href="{{ route('insights') }}">Insights</a>
            <a href="{{ route('contact') }}" class="btn btn-gold header-btn-primary">
                <span>Request Service</span>
                <i aria-hidden="true">→</i>
            </a>
            <a href="{{ route('contact') }}" class="btn header-btn-secondary">
                <span>Get in touch</span>
                <i aria-hidden="true">↗</i>
            </a>
            @if($hasTopbarSocial)
            <div class="nav-mobile-socials" aria-label="Social media">
                @foreach($topbarSocialItems as $s)
                    @if(!empty($s['href']) && $s['href'] !== '#')
                        <a href="{{ $s['href'] }}" class="nav-mobile-socials__link" title="{{ $s['label'] }}" aria-label="{{ $s['label'] }}" target="_blank" rel="noopener noreferrer">
                            @switch($s['label'])
                                @case('LinkedIn')
                                    <svg class="nav-mobile-socials__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 1 1 0-4.125 2.063 2.063 0 0 1 0 4.125zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                    @break
                                @case('Facebook')
                                    <svg class="nav-mobile-socials__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    @break
                                @case('X')
                                    <svg class="nav-mobile-socials__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    @break
                                @case('Instagram')
                                    <svg class="nav-mobile-socials__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 4.782-2.618 6.98-6.98.058-1.28.072-1.689.072-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.98-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                                    @break
                                @case('YouTube')
                                    <svg class="nav-mobile-socials__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    @break
                                @default
                                    <span class="nav-mobile-socials__fallback" aria-hidden="true">{{ $s['abbr'] }}</span>
                            @endswitch
                        </a>
                    @endif
                @endforeach
            </div>
            @endif
        </nav>
        <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="site-nav" data-menu-toggle>
            <span class="menu-toggle__icon" aria-hidden="true">
                <span class="menu-toggle__bar"></span>
                <span class="menu-toggle__bar"></span>
                <span class="menu-toggle__bar"></span>
            </span>
        </button>
    </div>
</header>
@if(request()->routeIs('home'))
</div>
@endif

<main class="min-w-0">
    @yield('content')
</main>

<button class="scroll-top-btn" type="button" aria-label="Scroll to top" data-scroll-top>↑</button>

<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
    <div class="mobile-bottom-nav__inner">
        <a class="mobile-bottom-nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">
            <svg class="mobile-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Home</span>
        </a>
        <a class="mobile-bottom-nav__link {{ request()->routeIs('services') || request()->routeIs('capabilities.show') ? 'is-active' : '' }}" href="{{ route('services') }}">
            <svg class="mobile-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Services</span>
        </a>
        <a class="mobile-bottom-nav__fab" href="{{ $whatsappHref }}" aria-label="{{ str_starts_with($whatsappHref, 'https://wa.me') ? 'Request service on WhatsApp' : 'Request service — contact form' }}" @if(str_starts_with($whatsappHref, 'https://wa.me')) target="_blank" rel="noopener noreferrer" @endif>
            <span class="mobile-bottom-nav__fab-icon" aria-hidden="true">
                <svg class="mobile-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            </span>
            <span class="mobile-bottom-nav__fab-label">Request<br>Service</span>
        </a>
        <a class="mobile-bottom-nav__link {{ request()->routeIs('insights') || request()->routeIs('insights.show') ? 'is-active' : '' }}" href="{{ route('insights') }}">
            <svg class="mobile-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
            <span>Insights</span>
        </a>
        <a class="mobile-bottom-nav__link {{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">
            <svg class="mobile-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <span>Contact</span>
        </a>
    </div>
</nav>

<footer class="footer">
    <div class="container footer-main">
        <div class="footer-brand">
            <a href="{{ route('home') }}" class="brand footer-brand-link">
                <img src="{{ $site['branding']['footer_logo'] ?? asset('uploads/logo-white.png') }}" alt="{{ $site['company']['name'] }} logo" class="brand-logo footer-logo">
            </a>
            <p>{{ $site['company']['description'] }}</p>
            <div class="footer-brand-tags">
                <span>Debt Recovery</span>
                <span>Asset Tracing</span>
                <span>Investigations</span>
                <span>Car Tracking</span>
            </div>
            <div class="footer-socials" aria-label="Social media links">
                <a href="{{ $site['social']['linkedin'] ?? '#' }}" aria-label="LinkedIn" title="LinkedIn" target="_blank" rel="noopener noreferrer">in</a>
                <a href="{{ $site['social']['facebook'] ?? '#' }}" aria-label="Facebook" title="Facebook" target="_blank" rel="noopener noreferrer">f</a>
                <a href="{{ $site['social']['twitter'] ?? '#' }}" aria-label="X" title="X" target="_blank" rel="noopener noreferrer">x</a>
                <a href="{{ $site['social']['instagram'] ?? '#' }}" aria-label="Instagram" title="Instagram" target="_blank" rel="noopener noreferrer">ig</a>
                <a href="{{ $site['social']['youtube'] ?? '#' }}" aria-label="YouTube" title="YouTube" target="_blank" rel="noopener noreferrer">yt</a>
            </div>
        </div>

        <div class="footer-links">
            <h4>Company</h4>
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">Who we are</a>
            <a href="{{ route('services') }}">Our Capabilities</a>
            <a href="{{ route('industries') }}">Industries</a>
            <a href="{{ route('insights') }}">Insights</a>
            <a href="{{ route('contact') }}">Contact</a>
        </div>

        <div class="footer-links">
            <h4>Capabilities</h4>
            <a href="{{ route('services') }}#debt-recovery">Debt Recovery</a>
            <a href="{{ route('services') }}#asset-tracing">Asset Tracing</a>
            <a href="{{ route('services') }}#insurance-tracing">Insurance Tracing</a>
            <a href="{{ route('services') }}#car-tracking">Car Tracking</a>
            <a href="{{ route('services') }}#investigations">Investigations</a>
            <a href="{{ route('services') }}#skip-tracing">Skip Tracing</a>
            <a href="{{ route('services') }}#debt-portfolio-management">Debt Portfolio Management</a>
        </div>

        <div class="footer-links">
            <h4>Industries</h4>
            <a href="{{ route('industries') }}">Banks</a>
            <a href="{{ route('industries') }}">Microfinance Institutions</a>
            <a href="{{ route('industries') }}">SACCOs</a>
            <a href="{{ route('industries') }}">Insurance Companies</a>
            <a href="{{ route('industries') }}">Corporates</a>
            <a href="{{ route('industries') }}">Law Firms</a>
        </div>

        <div class="footer-links footer-contact">
            <h4>Contact</h4>
            <a href="tel:{{ preg_replace('/\s+/', '', $site['company']['phone']) }}">{{ $site['company']['phone'] }}</a>
            <a href="mailto:{{ $site['company']['email'] }}">{{ $site['company']['email'] }}</a>
            <p>{{ $site['company']['address'] }}</p>
            <a href="{{ route('contact') }}">Open Inquiry Portal</a>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-wrap">
            <p>&copy; {{ now()->year }} {{ $site['company']['name'] }}. All rights reserved. <span class="footer-powered">Powered by <a href="http://designekta.com/" target="_blank" rel="noopener noreferrer">Designekta Studios</a></span></p>
            <div class="footer-bottom-links">
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <span class="footer-bottom-sep" aria-hidden="true">|</span>
                <a href="{{ route('terms') }}">Terms and Conditions</a>
                <span class="footer-bottom-sep" aria-hidden="true">|</span>
                <a href="{{ route('compliance') }}">Compliance</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
