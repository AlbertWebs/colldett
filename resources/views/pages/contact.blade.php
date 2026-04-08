@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Contact</span>
        </p>
        <h1>Contact Us</h1>
        <p>Connect with our team for debt recovery, tracing, investigation, and vehicle tracking support.</p>
    </div>
</section>
<section class="section">
    <div class="container contact-page-grid">
        <aside class="contact-details-card">
            <h2>Speak to a Specialist</h2>
            <p class="contact-details-intro">
                Our team responds to all inquiries and support requests as quickly as possible.
            </p>

            <div class="contact-detail-list">
                <a class="contact-detail-item" href="tel:{{ preg_replace('/\s+/', '', $site['company']['phone']) }}">
                    <span>Call us</span>
                    <strong>{{ $site['company']['phone'] }}</strong>
                </a>
                <a class="contact-detail-item" href="mailto:{{ $site['company']['email'] }}">
                    <span>Email</span>
                    <strong>{{ $site['company']['email'] }}</strong>
                </a>
                <div class="contact-detail-item">
                    <span>Office</span>
                    <strong>{{ $site['company']['address'] }}</strong>
                </div>
            </div>

            <div class="contact-support-tags" aria-label="Support highlights">
                <span>Debt Recovery</span>
                <span>Asset Tracing</span>
                <span>Investigation Services</span>
                <span>Vehicle Tracking</span>
            </div>

            <iframe title="Map" loading="lazy" class="map" src="https://www.google.com/maps?q=Nairobi%20Kenya&output=embed"></iframe>
        </aside>

        <div class="contact-form-card">
            <h2>Send an Inquiry</h2>
            <p class="contact-form-lead">Share your case details and we will recommend the best next step.</p>

            @if(session('status'))
                <p class="notice">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="contact-form" novalidate>
                @csrf

                <div class="contact-form-grid">
                    <div>
                        <label for="name">Full name</label>
                        <input id="name" type="text" name="name" placeholder="Jane Doe" value="{{ old('name') }}" required>
                        @error('name')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" placeholder="name@company.com" value="{{ old('email') }}" required>
                        @error('email')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="contact-form-grid">
                    <div>
                        <label for="phone">Phone number</label>
                        <input id="phone" type="text" name="phone" placeholder="+254..." value="{{ old('phone') }}">
                    </div>
                    <div>
                        <label for="service_interest">Service of interest</label>
                        <select id="service_interest" name="service_interest" required>
                            <option value="">Select a service</option>
                            @foreach($site['services'] as $service)
                                <option value="{{ $service['name'] }}" @selected(old('service_interest') === $service['name'])>{{ $service['name'] }}</option>
                            @endforeach
                        </select>
                        @error('service_interest')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div>
                    <label for="message">How can we assist?</label>
                    <textarea id="message" name="message" rows="6" placeholder="Provide a brief summary of your request..." required>{{ old('message') }}</textarea>
                    @error('message')<small class="error">{{ $message }}</small>@enderror
                </div>

                <button class="btn btn-gold mt-2 w-full min-h-11 px-6 py-3 text-sm md:w-auto md:text-base" type="submit">Request Assistance</button>
            </form>
        </div>
    </div>
</section>
@endsection
