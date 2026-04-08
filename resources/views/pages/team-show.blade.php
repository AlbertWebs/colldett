@extends('layouts.app')

@php
    $memberImage = str_starts_with($member['image'], 'http') ? $member['image'] : asset($member['image']);
@endphp

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $member['name'],
    'jobTitle' => $member['role'],
    'worksFor' => [
        '@type' => 'Organization',
        'name' => config('colldett.company.name'),
        'url' => url('/'),
    ],
    'description' => $member['seo_description'] ?? $member['bio'],
    'image' => $memberImage,
    'email' => isset($member['email']) ? 'mailto:' . $member['email'] : null,
    'url' => url()->current(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('about') }}">About Us</a>
            <span>/</span>
            <span>{{ $member['name'] }}</span>
        </p>
        <h1>{{ $member['name'] }}</h1>
        <p>{{ $member['role'] }}{{ !empty($member['department']) ? ' • '.$member['department'] : '' }}</p>
    </div>
</section>

<section class="section team-profile-section reveal">
    <div class="container team-profile-grid">
        <div class="team-profile-media">
            <img src="{{ $memberImage }}" alt="{{ $member['name'] }} portrait">
        </div>
        <article class="team-profile-content">
            <h2>{{ $member['name'] }}</h2>
            <p class="team-profile-role">{{ $member['role'] }}</p>
            <div class="team-profile-meta">
                @if(!empty($member['experience_years']))
                    <span>{{ $member['experience_years'] }}+ years experience</span>
                @endif
                @if(!empty($member['location']))
                    <span>{{ $member['location'] }}</span>
                @endif
                @if(!empty($member['department']))
                    <span>{{ $member['department'] }}</span>
                @endif
            </div>
            <p class="team-profile-bio">{{ $member['bio'] }}</p>

            <h3>Key Focus Areas</h3>
            <div class="about-points">
                @foreach($member['specialties'] as $specialty)
                    <span>{{ $specialty }}</span>
                @endforeach
            </div>

            <h3>Professional Credentials</h3>
            <ul class="team-profile-list">
                @foreach(($member['credentials'] ?? []) as $credential)
                    <li>{{ $credential }}</li>
                @endforeach
            </ul>

            <h3>Industries Served</h3>
            <div class="about-points">
                @foreach(($member['industries'] ?? []) as $industry)
                    <span>{{ $industry }}</span>
                @endforeach
            </div>

            <h3>Professional Principles</h3>
            <ul class="team-profile-list">
                @foreach(($member['principles'] ?? []) as $principle)
                    <li>{{ $principle }}</li>
                @endforeach
            </ul>

            <div class="team-profile-actions">
                <a href="{{ route('contact') }}" class="btn btn-gold">Work With {{ strtok($member['name'], ' ') }}</a>
                <a href="{{ route('about') }}#our-people" class="btn btn-soft">Back to Team</a>
            </div>
        </article>
    </div>
</section>
@endsection
