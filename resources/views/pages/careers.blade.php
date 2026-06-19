@extends('layouts.app')

@section('content')
@php
    use App\Support\DocumentPlainText;
    use App\Support\RichContentHtml;
@endphp
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Careers</span>
        </p>
        <h1>Careers</h1>
        <p>Join a disciplined team committed to ethical recovery, tracing, and professional client outcomes.</p>
    </div>
</section>

<section class="section careers-page-section reveal">
    <div class="container">
        <div class="careers-list-head">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <p class="eyebrow">Work with us</p>
            <h2>Open positions</h2>
            <p class="careers-list-lead">Explore current opportunities and apply with your CV.</p>
        </div>

        @if($careers->isEmpty())
            <div class="careers-empty">
                <h3>No open positions right now</h3>
                <p>We are not actively hiring for listed roles at the moment. Check back soon or contact us to express general interest.</p>
                <a class="btn btn-gold" href="{{ route('contact') }}">Contact us</a>
            </div>
        @else
            <div class="careers-grid">
                @foreach($careers as $career)
                    <article class="service-card career-card reveal">
                        <div class="career-card-meta">
                            @if($career->location)
                                <span>{{ DocumentPlainText::fromHtml($career->location) }}</span>
                            @endif
                            @if($career->employment_type)
                                <span>{{ DocumentPlainText::fromHtml($career->employment_type) }}</span>
                            @endif
                            @if($career->department)
                                <span>{{ DocumentPlainText::fromHtml($career->department) }}</span>
                            @endif
                        </div>
                        <h3>{{ DocumentPlainText::fromHtml($career->title) }}</h3>
                        @if(RichContentHtml::hasVisibleContent($career->excerpt))
                            <p>{{ RichContentHtml::plainExcerpt($career->excerpt, 220) }}</p>
                        @endif
                        <a class="career-cta" href="{{ route('careers.show', $career->slug) }}">View &amp; apply <i aria-hidden="true">→</i></a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
