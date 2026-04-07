<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle ?? config('colldett.company.name') }}</title>
    <meta name="description" content="{{ $metaDescription ?? config('colldett.company.description') }}">
    <meta name="theme-color" content="#215e1d">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle ?? config('colldett.company.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? config('colldett.company.description') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('colldett.company.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="topbar-meta">
    <div class="container topbar-meta-wrap">
        <span>Professional Recovery & Tracing Services</span>
        <span>{{ $site['company']['phone'] }} | {{ $site['company']['email'] }}</span>
    </div>
</div>
<header class="topbar" data-topbar>
    <div class="container nav-wrap">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark" aria-hidden="true"></span>
            <span class="brand-text">Colldett <span>Trace</span> Limited</span>
        </a>
        <button class="menu-toggle" aria-label="Toggle menu" data-menu-toggle>Menu</button>
        <nav class="nav" data-menu>
            <a class="nav-link" href="{{ route('home') }}">Home</a>
            <a class="nav-link" href="{{ route('about') }}">Who we are</a>
            <details class="nav-dropdown">
                <summary>Our Capibilities</summary>
                <div class="nav-dropdown-menu">
                    <a class="nav-sub-link" href="{{ route('services') }}">All Services</a>
                    <a class="nav-sub-link" href="{{ route('services') }}#car-tracking">Car Tracking</a>
                    <a class="nav-sub-link" href="{{ route('services') }}#debt-recovery">Debt Recovery</a>
                    <a class="nav-sub-link" href="{{ route('services') }}#asset-tracing">Asset Tracing</a>
                </div>
            </details>
            <a class="nav-link" href="{{ route('industries') }}">Industries</a>
            <a class="nav-link" href="{{ route('insights') }}">Insigts</a>
            <a href="{{ route('contact') }}" class="btn btn-gold header-btn-primary">
                <span>Request Service</span>
                <i aria-hidden="true">→</i>
            </a>
            <a href="{{ route('contact') }}" class="btn header-btn-secondary">
                <span>Get in touch</span>
                <i aria-hidden="true">↗</i>
            </a>
        </nav>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="footer">
    <div class="container footer-main">
        <div class="footer-brand">
            <a href="{{ route('home') }}" class="brand footer-brand-link">
                <span class="brand-mark" aria-hidden="true"></span>
                <span class="brand-text">Colldett <span>Trace</span> Limited</span>
            </a>
            <p>{{ $site['company']['description'] }}</p>
            <a class="btn btn-gold footer-brand-cta" href="{{ route('contact') }}">Request Recovery Support</a>
        </div>

        <div class="footer-links">
            <h4>Company</h4>
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">Who we are</a>
            <a href="{{ route('services') }}">Our Capibilities</a>
            <a href="{{ route('industries') }}">Industries</a>
            <a href="{{ route('insights') }}">Insigts</a>
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
            <a href="{{ route('about') }}#affiliate">Affiliate Legal Partner</a>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-wrap">
            <p>&copy; {{ now()->year }} {{ $site['company']['name'] }}. All rights reserved.</p>
            <div>
                <a href="{{ route('contact') }}">Privacy</a>
                <a href="{{ route('contact') }}">Terms</a>
                <a href="{{ route('contact') }}">Compliance</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
