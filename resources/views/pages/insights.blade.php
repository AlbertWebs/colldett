@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Insights</span>
        </p>
        <h1>Insights & Resources</h1>
        <p>Professional commentary and operational guidance on recovery strategy, tracing, and enforcement readiness.</p>
    </div>
</section>

<section class="section insights-page-section reveal">
    <div class="container">
        <div class="insights-list-head">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <p class="eyebrow">Insights / Resources</p>
            <h2>Latest articles &amp; briefs</h2>
            <p class="insights-list-lead">Browse practical notes on recovery execution, tracing, and compliance readiness.</p>
        </div>
        <div class="insights-grid">
            @foreach($site['insights'] as $article)
                <article class="service-card insight-card reveal">
                    <div class="insight-meta">
                        <span class="insight-date">{{ $article['date'] }}</span>
                        <span class="insight-tag">Expert Brief</span>
                    </div>
                    <h3>{{ $article['title'] }}</h3>
                    <p>{{ $article['excerpt'] }}</p>
                    <a class="insight-cta" href="{{ route('insights.show', $article['slug']) }}">Read Insight <i aria-hidden="true">→</i></a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
