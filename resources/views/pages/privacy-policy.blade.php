@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Privacy Policy</span>
        </p>
        <h1>Privacy Policy</h1>
        <p>How Colldett Trace Limited collects, uses, and protects personal and operational information.</p>
    </div>
</section>

<section class="section">
    <div class="container insight-article">
        <p>Colldett Trace Limited is committed to protecting the confidentiality of client and user information. We collect only the information required to deliver recovery, tracing, investigations, and support services effectively.</p>
        <p>Information submitted through our forms, email, or phone channels is used for inquiry processing, service delivery, compliance, and communication. We do not sell personal data to third parties.</p>
        <p>Access to information is restricted to authorized personnel and trusted service providers supporting our operations. Security controls are applied to reduce unauthorized access, misuse, or disclosure.</p>
        <p>If you have questions about data usage, retention, or correction requests, please contact us through the official contact details listed on this website.</p>
    </div>
</section>
@endsection
