@extends('layouts.app')

@section('content')
@php
    use App\Support\DocumentPlainText;
    use App\Support\RichContentHtml;

    $descriptionHtml = RichContentHtml::toParagraphHtml($capability['description'] ?? '');
    $hasAdminDescription = RichContentHtml::hasVisibleContent($capability['description'] ?? '');
    $heroIntro = RichContentHtml::plainExcerpt($capability['description'] ?? '', 240);
    if ($heroIntro === '') {
        $heroIntro = trim(DocumentPlainText::fromHtml((string) ($capability['description'] ?? '')));
    }
@endphp
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
        @if($heroIntro !== '')
            <p>{{ $heroIntro }}</p>
        @endif
    </div>
</section>

<section class="section capability-detail-section reveal">
    <div class="container capability-detail-grid">
        <article class="capability-detail-main">
            @if($hasAdminDescription)
                <div class="capability-detail-prose">
                    {!! $descriptionHtml !!}
                </div>
            @elseif(!empty($capability['content']))
                @php
                    $content = is_array($capability['content']) ? $capability['content'] : [];
                    $intro = (string) ($content['intro'] ?? '');
                    $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
                @endphp
                @if($intro !== '')
                    <div class="capability-detail-prose">
                        {!! RichContentHtml::toParagraphHtml($intro) !!}
                    </div>
                @endif
                @foreach($sections as $section)
                    @php
                        $title = (string) ($section['title'] ?? '');
                        $body = (string) ($section['body'] ?? '');
                        $bullets = is_array($section['bullets'] ?? null) ? $section['bullets'] : [];
                    @endphp
                    @if($title !== '')
                        <h2 class="mt-6">{{ DocumentPlainText::fromHtml($title) }}</h2>
                    @endif
                    @if($body !== '')
                        <div class="capability-detail-prose mt-3">
                            {!! RichContentHtml::toParagraphHtml($body) !!}
                        </div>
                    @endif
                    @if($bullets !== [])
                        <ul class="checklist mt-4">
                            @foreach($bullets as $b)
                                <li>{{ DocumentPlainText::fromHtml((string) $b) }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endforeach
            @else
                <h2>How this capability works</h2>
                <p class="capability-detail-para">We execute this capability through disciplined workflows, practical escalation controls, and outcome-focused case management aligned to institutional standards.</p>
                <ul class="checklist">
                    @foreach($capabilityDetails as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @endif
        </article>
        <aside class="capability-detail-side">
            @if(!empty($capability['featured']))
                <p class="capability-pill">Featured Capability</p>
            @elseif(!empty($capability['coming_soon']))
                <p class="capability-pill">Coming Soon</p>
            @endif
            @if(!empty($capability['image']))
                @php
                    $image = (string) $capability['image'];
                    $imageUrl = str_starts_with($image, 'http') ? $image : asset(ltrim($image, '/'));
                @endphp
                <img src="{{ $imageUrl }}" alt="{{ $capability['name'] }}" class="mb-4 w-full rounded-xl border border-[#d5ded8] object-cover" loading="lazy" />
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
