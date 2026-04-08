@extends('admin.layouts.app')

@section('content')
<section class="space-y-6">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">About Page Content</h2>
            <p class="text-sm text-admin-muted">Manage About-page sections that are not covered by other CRUD modules.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.about-content.update') }}" class="space-y-6">
        @csrf

        <article class="admin-card p-6 space-y-4">
            <h3 class="admin-card-title">Hero & Mission</h3>
            <input class="admin-input" name="hero_title" value="{{ old('hero_title', $content['hero_title']) }}" placeholder="Hero title" />
            <textarea class="admin-input" name="hero_intro" rows="3" placeholder="Hero intro">{{ old('hero_intro', $content['hero_intro']) }}</textarea>
            <textarea class="admin-input" name="mission_text" rows="3" placeholder="Mission">{{ old('mission_text', $content['mission_text']) }}</textarea>
            <textarea class="admin-input" name="vision_text" rows="3" placeholder="Vision">{{ old('vision_text', $content['vision_text']) }}</textarea>
            <textarea class="admin-input" name="core_values_text" rows="4" placeholder="Core values (one per line)">{{ old('core_values_text', implode(PHP_EOL, $content['core_values'] ?? [])) }}</textarea>
        </article>

        <article class="admin-card p-6 space-y-4">
            <h3 class="admin-card-title">Story & Reach</h3>
            <textarea class="admin-input" name="story_intro" rows="3" placeholder="Story intro">{{ old('story_intro', $content['story_intro']) }}</textarea>
            <textarea class="admin-input" name="story_paragraph_2" rows="3" placeholder="Story paragraph 2">{{ old('story_paragraph_2', $content['story_paragraph_2']) }}</textarea>
            <textarea class="admin-input" name="story_paragraph_3" rows="3" placeholder="Story paragraph 3">{{ old('story_paragraph_3', $content['story_paragraph_3']) }}</textarea>
            <textarea class="admin-input" name="story_points_text" rows="4" placeholder="Story points (one per line)">{{ old('story_points_text', implode(PHP_EOL, $content['story_points'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="reach_lead" rows="2" placeholder="Reach lead">{{ old('reach_lead', $content['reach_lead']) }}</textarea>
            <textarea class="admin-input" name="reach_chips_text" rows="3" placeholder="Reach chips (one per line)">{{ old('reach_chips_text', implode(PHP_EOL, $content['reach_chips'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="reach_relations_text" rows="3" placeholder="Reach relations (one per line)">{{ old('reach_relations_text', implode(PHP_EOL, $content['reach_relations'] ?? [])) }}</textarea>
        </article>

        <article class="admin-card p-6 space-y-4">
            <h3 class="admin-card-title">Services, Compliance & Experience</h3>
            <textarea class="admin-input" name="what_we_do_intro" rows="2" placeholder="What we do intro">{{ old('what_we_do_intro', $content['what_we_do_intro']) }}</textarea>
            <textarea class="admin-input" name="what_we_do_services_text" rows="5" placeholder="What we do services (one per line)">{{ old('what_we_do_services_text', implode(PHP_EOL, $content['what_we_do_services'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="compliance_intro" rows="2" placeholder="Compliance intro">{{ old('compliance_intro', $content['compliance_intro']) }}</textarea>
            <textarea class="admin-input" name="compliance_list_text" rows="4" placeholder="Compliance list (one per line)">{{ old('compliance_list_text', implode(PHP_EOL, $content['compliance_list'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="compliance_points_text" rows="4" placeholder="Compliance points (one per line)">{{ old('compliance_points_text', implode(PHP_EOL, $content['compliance_points'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="experience_intro" rows="2" placeholder="Experience intro">{{ old('experience_intro', $content['experience_intro']) }}</textarea>
            <textarea class="admin-input" name="experience_clients_text" rows="4" placeholder="Experience clients (one per line)">{{ old('experience_clients_text', implode(PHP_EOL, $content['experience_clients'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="experience_summary" rows="3" placeholder="Experience summary">{{ old('experience_summary', $content['experience_summary']) }}</textarea>
        </article>

        <article class="admin-card p-6 space-y-4">
            <h3 class="admin-card-title">Why Choose Us & CTA</h3>
            <textarea class="admin-input" name="why_choose_intro" rows="2" placeholder="Why choose intro">{{ old('why_choose_intro', $content['why_choose_intro']) }}</textarea>
            <textarea class="admin-input" name="why_choose_reasons_text" rows="5" placeholder="Reasons (one per line)">{{ old('why_choose_reasons_text', implode(PHP_EOL, $content['why_choose_reasons'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="confidence_points_text" rows="3" placeholder="Confidence points (one per line)">{{ old('confidence_points_text', implode(PHP_EOL, $content['confidence_points'] ?? [])) }}</textarea>
            <textarea class="admin-input" name="closing_text" rows="2" placeholder="Closing text">{{ old('closing_text', $content['closing_text']) }}</textarea>
            <input class="admin-input" name="cta_title" value="{{ old('cta_title', $content['cta_title']) }}" placeholder="CTA title" />
            <textarea class="admin-input" name="cta_text" rows="2" placeholder="CTA text">{{ old('cta_text', $content['cta_text']) }}</textarea>
        </article>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="sticky bottom-3 z-10 flex justify-end">
            <div class="rounded-xl border border-admin-border bg-white/95 p-2 shadow-lg backdrop-blur">
                <button type="submit" class="admin-btn-primary">Save About Content</button>
            </div>
        </div>
    </form>
</section>
@endsection
