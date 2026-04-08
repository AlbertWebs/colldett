@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Industries</span>
        </p>
        <h1>Industries We Serve</h1>
        <p>We support financial institutions, corporates, and professional firms with structured recovery and tracing execution.</p>
    </div>
</section>
<section class="section industries-section reveal">
    <div class="container">
        <div class="industries-grid">
            <article class="industries-main">
                <p class="eyebrow">Coverage Focus</p>
                <h2>Sector-aligned recovery support with practical execution standards</h2>
                <p class="industries-lead">We align our recovery and tracing approach to each sector's risk profile, compliance environment, and operational realities.</p>
                <div class="industries-cards">
                    @foreach($site['industries'] as $industry)
                        <article class="industry-card">
                            <h3>{{ $industry }}</h3>
                            <p>Tailored recovery and tracing workflows for {{ strtolower($industry) }} institutions.</p>
                        </article>
                    @endforeach
                </div>
            </article>
            <aside class="industries-side">
                <h3>How We Add Value</h3>
                <ul class="checklist">
                    <li>Sector-specific case handling strategies</li>
                    <li>Compliance-led engagement frameworks</li>
                    <li>Structured reporting for decision-makers</li>
                    <li>Trace and enforcement support where needed</li>
                </ul>
                <a href="{{ route('contact') }}" class="btn btn-gold">Discuss Your Industry Needs</a>
            </aside>
        </div>
    </div>
</section>
@endsection
