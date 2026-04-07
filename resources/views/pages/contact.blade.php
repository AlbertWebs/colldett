@extends('layouts.app')

@section('content')
<section class="page-head"><div class="container"><h1>Contact Us</h1></div></section>
<section class="section"><div class="container grid two-col">
<div>
<h2>Speak to a Specialist</h2>
<p>{{ $site['company']['phone'] }}</p>
<p>{{ $site['company']['email'] }}</p>
<p>{{ $site['company']['address'] }}</p>
<iframe title="Map" loading="lazy" class="map" src="https://www.google.com/maps?q=Nairobi%20Kenya&output=embed"></iframe>
</div>
<div>
@if(session('status'))<p class="notice">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('contact.store') }}" class="contact-form">
@csrf
<input type="text" name="name" placeholder="Full Name" value="{{ old('name') }}" required>
<input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
<input type="text" name="phone" placeholder="Phone Number" value="{{ old('phone') }}">
<select name="service_interest" required>
<option value="">Service of Interest</option>
@foreach($site['services'] as $service)
<option value="{{ $service['name'] }}" @selected(old('service_interest') === $service['name'])>{{ $service['name'] }}</option>
@endforeach
</select>
<textarea name="message" rows="5" placeholder="How can we assist?" required>{{ old('message') }}</textarea>
<button class="btn btn-gold" type="submit">Request Assistance</button>
@error('name')<small class="error">{{ $message }}</small>@enderror
@error('email')<small class="error">{{ $message }}</small>@enderror
@error('service_interest')<small class="error">{{ $message }}</small>@enderror
@error('message')<small class="error">{{ $message }}</small>@enderror
</form>
</div>
</div></section>
@endsection
