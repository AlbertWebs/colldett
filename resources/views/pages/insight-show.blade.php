@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('insights') }}">Insights</a>
            <span>/</span>
            <span>{{ $insight['title'] }}</span>
        </p>
        <h1>{{ $insight['title'] }}</h1>
        <p>{{ $insight['excerpt'] }}</p>
    </div>
</section>

<section class="section insights-article-section reveal">
    <div class="container insight-article">
        <p class="eyebrow">{{ $insight['date'] }}</p>
        @foreach($insight['content'] as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
        <a class="btn btn-soft" href="{{ route('insights') }}">Back to Insights</a>
    </div>
</section>
@endsection
