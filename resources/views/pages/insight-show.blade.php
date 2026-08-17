@extends('layouts.app')

@section('content')
@php
    use App\Support\DocumentPlainText;
    use App\Support\RichContentHtml;
    $insightExcerpt = DocumentPlainText::fromHtml((string) ($insight['excerpt'] ?? ''));
    $insightBody = is_array($insight['content'] ?? null)
        ? implode("\n\n", $insight['content'])
        : (string) ($insight['content'] ?? '');
@endphp
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('insights') }}">Insights</a>
            <span>/</span>
            <span>{{ DocumentPlainText::fromHtml($insight['title'] ?? '') }}</span>
        </p>
        <h1>{{ DocumentPlainText::fromHtml($insight['title'] ?? '') }}</h1>
        @if($insightExcerpt !== '')
            <p>{{ $insightExcerpt }}</p>
        @endif
    </div>
</section>

<section class="section insights-article-section reveal">
    <div class="container insight-article">
        <p class="eyebrow">{{ $insight['date'] }}</p>
        @if(RichContentHtml::hasVisibleContent($insightBody))
            <div class="about-rich-text">
                {!! RichContentHtml::toParagraphHtml($insightBody) !!}
            </div>
        @endif
        <a class="btn btn-soft" href="{{ route('insights') }}">Back to Insights</a>
    </div>
</section>
@endsection
