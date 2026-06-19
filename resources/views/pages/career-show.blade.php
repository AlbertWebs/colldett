@extends('layouts.app')

@section('content')
@php
    use App\Support\DocumentPlainText;
    use App\Support\RichContentHtml;
@endphp
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('careers') }}">Careers</a>
            <span>/</span>
            <span>{{ DocumentPlainText::fromHtml($career->title) }}</span>
        </p>
        <h1>{{ DocumentPlainText::fromHtml($career->title) }}</h1>
        <div class="career-hero-meta">
            @if($career->location)
                <span>{{ DocumentPlainText::fromHtml($career->location) }}</span>
            @endif
            @if($career->employment_type)
                <span>{{ DocumentPlainText::fromHtml($career->employment_type) }}</span>
            @endif
            @if($career->department)
                <span>{{ DocumentPlainText::fromHtml($career->department) }}</span>
            @endif
            @if($career->closes_at)
                <span>Closes {{ $career->closes_at->format('j M Y') }}</span>
            @endif
        </div>
        @if(RichContentHtml::hasVisibleContent($career->excerpt))
            <p>{{ RichContentHtml::plainExcerpt($career->excerpt, 280) }}</p>
        @endif
    </div>
</section>

<section class="section career-detail-section reveal">
    <div class="container career-detail-grid">
        <article class="career-detail-main">
            @if(RichContentHtml::hasVisibleContent($career->description))
                <div class="career-detail-prose about-rich-text">
                    {!! RichContentHtml::toPublicHtml($career->description) !!}
                </div>
            @endif
        </article>

        <aside class="career-apply-card">
            <h2>Apply for this role</h2>
            <p class="career-apply-lead">Submit your details and supporting documents (CV, certificates, etc.). All fields marked with * are required.</p>

            @if(session('status'))
                <p class="notice">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('careers.apply', $career->slug) }}" class="career-apply-form contact-form" enctype="multipart/form-data" novalidate>
                @csrf

                <div>
                    <label for="name">Full name *</label>
                    <input id="name" type="text" name="name" placeholder="Jane Doe" value="{{ old('name') }}" required>
                    @error('name')<small class="error">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label for="email">Email address *</label>
                    <input id="email" type="email" name="email" placeholder="name@company.com" value="{{ old('email') }}" required>
                    @error('email')<small class="error">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label for="phone">Phone number</label>
                    <input id="phone" type="text" name="phone" placeholder="+254..." value="{{ old('phone') }}">
                    @error('phone')<small class="error">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label for="cover_letter">Cover letter</label>
                    <textarea id="cover_letter" name="cover_letter" rows="5" placeholder="Brief introduction and why you are a fit for this role...">{{ old('cover_letter') }}</textarea>
                    @error('cover_letter')<small class="error">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label for="documents">Documents (PDF, DOC, DOCX) *</label>
                    <input id="documents" type="file" name="documents[]" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple required>
                    <small class="block mt-1 text-xs opacity-80">Upload up to 5 files (max 5MB each), e.g. CV, cover letter, certificates.</small>
                    @error('documents')<small class="error">{{ $message }}</small>@enderror
                    @error('documents.*')<small class="error">{{ $message }}</small>@enderror
                </div>

                <button class="btn btn-gold mt-2 w-full min-h-11 px-6 py-3 text-sm md:w-auto md:text-base" type="submit">Submit application</button>
            </form>
        </aside>
    </div>
</section>
@endsection
