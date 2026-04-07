@extends('layouts.app')

@section('content')
<section class="page-head"><div class="container"><h1>Insights & Resources</h1></div></section>
<section class="section"><div class="container grid cards">
@foreach($site['insights'] as $article)
<article class="card"><p class="eyebrow">{{ $article['date'] }}</p><h2>{{ $article['title'] }}</h2><p>{{ $article['excerpt'] }}</p></article>
@endforeach
</div></section>
@endsection
