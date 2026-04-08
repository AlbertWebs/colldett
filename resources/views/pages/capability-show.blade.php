@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('services') }}">Our Capabilities</a>
            <span>/</span>
            <span>{{ $capability['name'] }}</span>
        </p>
        <h1>{{ $capability['name'] }}</h1>
        <p>{{ $capability['description'] }}</p>
    </div>
</section>

<section class="section capability-detail-section reveal">
    <div class="container capability-detail-grid">
        <article class="capability-detail-main">
            <h2>How this capability works</h2>
            <p>We execute this capability through disciplined workflows, practical escalation controls, and outcome-focused case management aligned to institutional standards.</p>
            <ul class="checklist">
                @foreach($capabilityDetails as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>
        <aside class="capability-detail-side">
            @if(!empty($capability['featured']))
                <p class="capability-pill">Featured Capability</p>
            @elseif(!empty($capability['coming_soon']))
                <p class="capability-pill">Coming Soon</p>
            @endif
            <h3>Need this capability?</h3>
            <p>Engage our team for a tailored execution plan based on your portfolio, timelines, and risk profile.</p>
            <div class="capability-actions">
                <a href="{{ route('contact') }}" class="btn btn-gold">Request Service</a>
                <a href="{{ route('services') }}" class="btn btn-soft">Back to Capabilities</a>
            </div>
        </aside>
    </div>
</section>
@endsection
