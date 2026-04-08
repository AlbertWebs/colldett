@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>Terms and Conditions</span>
        </p>
        <h1>Terms and Conditions</h1>
        <p>General terms governing use of this website and engagement with Colldett Trace Limited.</p>
    </div>
</section>

<section class="section">
    <div class="container insight-article">
        <p>By accessing this website, you agree to use it lawfully and in accordance with these terms. Website content is provided for general information and may be updated without prior notice.</p>
        <p>Any service engagement is subject to formal agreement terms, including scope, confidentiality, fees, reporting expectations, and compliance obligations.</p>
        <p>Users may not reproduce, republish, or misuse materials from this site without prior written permission from Colldett Trace Limited.</p>
        <p>Colldett Trace Limited is not liable for indirect or consequential losses arising from website use. For service-specific obligations, the signed service agreement prevails.</p>
    </div>
</section>
@endsection
