@extends('layouts.app')

@section('content')
<section class="page-hero page-hero-services">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Our Capabilities</span>
        </p>
        <h1>Our Capabilities</h1>
        <p>Professional debt recovery, tracing, investigations, and vehicle tracking capabilities for commercial and institutional clients.</p>
    </div>
</section>

<section class="section services-page-section">
    <div class="container services-page-wrap">
        @php($servicesList = $services ?? $site['services'])
        <div class="services-page-intro">
            <p class="eyebrow">Capabilities</p>
            <h2>Structured services designed for recovery certainty</h2>
            <p>Our dedicated and experienced team executes debt recovery, tracing, and investigative mandates with clear workflows, strict confidentiality, and measurable outcomes for institutions and corporates.</p>
            <div class="services-quick-nav" aria-label="Service quick navigation">
                @foreach($servicesList as $service)
                    <a href="{{ route('capabilities.show', $service['slug']) }}">{{ $service['name'] }}</a>
                @endforeach
            </div>
        </div>

        <div class="services-accordion">
            @foreach($servicesList as $service)
                <details class="service-row {{ !empty($service['featured']) ? 'is-featured' : '' }} {{ !empty($service['coming_soon']) ? 'is-coming-soon' : '' }}" id="{{ $service['slug'] }}">
                    <summary>
                        <span class="service-row-icon">{{ strtoupper(substr($service['name'], 0, 1)) }}</span>
                        <span class="service-row-title-wrap">
                            <span class="service-row-title">{{ $service['name'] }}</span>
                            @if(!empty($service['featured']))
                                <span class="service-row-tag">Featured</span>
                            @endif
                            @if(!empty($service['coming_soon']))
                                <span class="service-row-tag coming-soon">Coming Soon</span>
                            @endif
                        </span>
                        <span class="service-row-toggle" aria-hidden="true">+</span>
                    </summary>
                    <div class="service-row-content">
                        <p>{{ $service['description'] }}</p>
                        @if($service['slug'] === 'car-tracking')
                            <ul class="checklist compact">
                                <li>Vehicle tracking device installation</li>
                                <li>Real-time tracking and monitoring</li>
                                <li>Fleet tracking and reporting</li>
                                <li>Remote engine immobilization</li>
                                <li>Security monitoring support</li>
                            </ul>
                        @endif
                        <a class="service-explore-link" href="{{ route('capabilities.show', $service['slug']) }}">Explore {{ $service['name'] }} <i aria-hidden="true">→</i></a>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection
