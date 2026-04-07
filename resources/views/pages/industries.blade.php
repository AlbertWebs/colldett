@extends('layouts.app')

@section('content')
<section class="page-head"><div class="container"><h1>Industries We Serve</h1></div></section>
<section class="section"><div class="container">
<p>We support commercial clients, financial institutions, and corporates with risk-aware recovery, tracing, and investigations.</p>
<ul class="tags">
@foreach($site['industries'] as $industry)
<li>{{ $industry }}</li>
@endforeach
</ul>
</div></section>
@endsection
