@extends('layouts.app')

@section('content')
<section class="page-hero page-hero-services">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Our Capibilities</span>
        </p>
        <h1>Our Capibilities</h1>
        <p>Professional debt recovery, tracing, investigations, and vehicle tracking capabilities for commercial and institutional clients.</p>
    </div>
</section>

<section class="section services-page-section">
    <div class="container services-page-wrap">
        <div class="services-page-intro">
            <p class="eyebrow">Capabilities</p>
            <h2>Structured services designed for recovery certainty</h2>
            <p>Our dedicated and experienced team executes debt recovery, tracing, and investigative mandates with clear workflows, strict confidentiality, and measurable outcomes for institutions and corporates.</p>
        </div>

        <div class="services-accordion">
            @foreach($site['services'] as $service)
                <details class="service-row" id="{{ $service['slug'] }}">
                    <summary>
                        <span class="service-row-icon">{{ strtoupper(substr($service['name'], 0, 1)) }}</span>
                        <span class="service-row-title">{{ $service['name'] }}</span>
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
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection
