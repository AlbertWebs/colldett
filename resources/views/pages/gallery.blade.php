@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Gallery</span>
        </p>
        <h1>Gallery</h1>
        <p>Recent field work, engagements, and operational highlights.</p>
    </div>
</section>

<section class="section gallery-section">
    <div class="container gallery-grid">
        @forelse($items as $item)
            @php
                $src = str_starts_with($item->image_path, 'http')
                    ? $item->image_path
                    : asset(ltrim($item->image_path, '/'));
                $alt = trim((string) ($item->caption ?? 'Gallery image'));
            @endphp
            <figure class="gallery-card reveal">
                <a class="gallery-link" href="{{ $src }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ $src }}" alt="{{ $alt !== '' ? $alt : 'Gallery image' }}" loading="lazy" decoding="async" />
                </a>
                @if(!empty(trim((string) $item->caption)))
                    <figcaption>{{ $item->caption }}</figcaption>
                @endif
            </figure>
        @empty
            <div class="gallery-empty">
                <h2>Gallery coming soon</h2>
                <p>We’ll publish curated engagement highlights shortly.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection

