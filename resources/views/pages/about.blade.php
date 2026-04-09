@extends('layouts.app')

@section('content')
@php
    $about = $site['about'] ?? [];
@endphp
<section class="page-hero">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>About Us</span>
        </p>
        <h1>{{ $about['hero_title'] ?? 'About Colldett Trace Limited' }}</h1>
        <p>{{ $about['hero_intro'] ?? 'Colldett Trace Limited is a dynamic and forward-thinking debt recovery firm formed by experienced professionals with strong backgrounds in financial institutions, legal practice, and corporate governance.' }}</p>
    </div>
</section>

<section class="about-page">

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Mission, Vision &amp; Core Values</h2>
            <div class="about-mvv-grid">
                <article class="about-card">
                    <h3>Mission</h3>
                    <p>{{ $about['mission_text'] ?? "To deliver innovative, ethical, and results-driven debt recovery solutions that enhance our clients' cash flow, reduce financial risk, and preserve business relationships." }}</p>
                </article>
                <article class="about-card">
                    <h3>Vision</h3>
                    <p>{{ $about['vision_text'] ?? 'To be a leading debt recovery firm in Africa and beyond, recognized for excellence, integrity, and a refined approach to recovery grounded in legal and financial expertise.' }}</p>
                </article>
                <article class="about-card">
                    <h3>Core Values</h3>
                    <ul class="about-icon-list">
                        @foreach(($about['core_values'] ?? ['Integrity', 'Professionalism', 'Respect', 'Confidentiality', 'Accountability', 'Results-Driven Performance']) as $value)
                            <li><span>●</span> {{ $value }}</li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container about-story-grid">
            <div>
                <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
                <h2>Our Story</h2>
                <p>{{ $about['story_intro'] ?? 'Colldett Trace Limited was established to redefine the debt recovery landscape through a structured, ethical, and professional approach.' }}</p>
                <p>{{ $about['story_paragraph_2'] ?? 'The firm was formed by a team of experienced professionals who transitioned from a structured law firm environment into an independent, specialized debt recovery practice.' }}</p>
                <p>{{ $about['story_paragraph_3'] ?? 'With over 8 years of hands-on recovery experience, we bring a deep understanding of legal processes, financial systems, and debtor behavior.' }}</p>
                <div class="about-points">
                    @foreach(($about['story_points'] ?? ['Legal intelligence', 'Financial insight', 'Human-centered negotiation', 'Data-driven recovery strategies']) as $point)
                        <span>{{ $point }}</span>
                    @endforeach
                </div>
                <div class="about-story-actions">
                    <a href="{{ route('services') }}" class="btn btn-gold about-story-btn">
                        <span>View Products &amp; Services</span>
                        <i aria-hidden="true">→</i>
                    </a>
                </div>
            </div>
            <div class="about-story-media" role="img" aria-label="Professional team strategy session"></div>
        </div>
    </section>

    <section class="section about-section reveal" id="our-people">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Our People</h2>
            <p class="about-intro">Our greatest asset is our people. We are a team of experienced professionals committed to delivering structured, ethical, and results-driven recovery solutions.</p>
            <div class="about-team-grid portraits-grid">
                @foreach($site['team'] as $member)
                    @php
                        $isRecoveryManager = str_contains(strtolower($member['role']), 'manager - debt recovery');
                    @endphp
                    <article class="about-team-card portrait-card {{ $isRecoveryManager ? '' : 'team-card-featured' }}">
                        <div class="portrait-media">
                            @include('partials.team-member-photo', ['member' => $member, 'variant' => 'card'])
                        </div>
                        <div class="portrait-content">
                            <h3>{{ $member['name'] }}</h3>
                            <p class="team-role {{ $isRecoveryManager ? '' : 'team-role-featured' }}">{{ $member['role'] }}</p>
                            <a href="{{ route('team.show', $member['slug']) }}" class="team-profile-link" aria-label="View full profile for {{ $member['name'] }}">
                                <span class="team-profile-link__label">View profile</span>
                                <span class="team-profile-link__icon" aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="about-support-note">Supported by a team of junior recovery officers, we ensure continuous engagement, structured follow-ups, and efficient case management.</p>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Our Reach</h2>
            <div class="about-reach-grid">
                <article class="about-reach-panel">
                    <p class="about-reach-lead">{{ $about['reach_lead'] ?? 'Our recovery network is designed for reliable, timely execution across local and regional jurisdictions.' }}</p>
                    <div class="about-reach-row">
                        @foreach(($about['reach_chips'] ?? ['Kenya - Nationwide Coverage', 'East Africa Region', 'International Clients']) as $chip)
                            <div class="reach-chip">{{ $chip }}</div>
                        @endforeach
                    </div>
                </article>
                <aside class="about-relations">
                    <p>We maintain strong working relationships with:</p>
                    <div class="about-points">
                        @foreach(($about['reach_relations'] ?? ['Legal Practitioners', 'Tracing Agents', 'Financial Institutions', 'Auctioneering Firms']) as $relation)
                            <span>{{ $relation }}</span>
                        @endforeach
                    </div>
                </aside>
                <div class="about-reach-highlights">
                    <div class="reach-stat">
                        <strong>National Coverage</strong>
                        <p>Structured field support in major counties and commercial centers.</p>
                    </div>
                    <div class="reach-stat">
                        <strong>Cross-Border Support</strong>
                        <p>Regional tracing and enforcement coordination through verified partners.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>What We Do</h2>
            <div class="about-do-grid">
                <div class="about-do-content">
                    <p class="about-intro">{{ $about['what_we_do_intro'] ?? 'We provide integrated recovery, tracing, and enforcement support designed for institutional performance and compliant execution.' }}</p>
                    <div class="about-services-grid">
                        @foreach(($about['what_we_do_services'] ?? [
                            'Debt Recovery Advisory',
                            'Pre-Legal Collections',
                            'Legal Collections',
                            'Payment Structuring',
                            'Execution of Court Decrees',
                            'Tracing and Skip Tracing',
                            'Asset Tracing',
                            'Insurance Tracing',
                            'Car Tracking and Monitoring',
                        ]) as $service)
                            <div class="about-service-card"><span>◉</span>{{ $service }}</div>
                        @endforeach
                    </div>
                </div>
                <aside class="about-do-media" role="img" aria-label="Professional debt recovery operations and team coordination"></aside>
            </div>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Our Recovery Process</h2>
            <p class="about-intro">Every assignment follows a disciplined process that balances speed, compliance, and measurable recovery outcomes.</p>
            <div class="about-timeline">
                <article>
                    <strong>1</strong>
                    <h3>Assessment and Debtor Profiling</h3>
                    <p>We evaluate account history, risk level, and enforceability signals to determine the right recovery pathway.</p>
                </article>
                <article>
                    <strong>2</strong>
                    <h3>Engagement and Negotiation</h3>
                    <p>Our team initiates structured debtor engagement and negotiates practical repayment arrangements.</p>
                </article>
                <article>
                    <strong>3</strong>
                    <h3>Monitoring and Follow-Up</h3>
                    <p>We track commitments, enforce timelines, and maintain disciplined follow-through across each file.</p>
                </article>
                <article>
                    <strong>4</strong>
                    <h3>Legal Escalation (When Necessary)</h3>
                    <p>Where voluntary recovery stalls, we coordinate legal escalation for enforceable and compliant outcomes.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Compliance and Regulatory Framework</h2>
            <div class="about-compliance-grid">
                <div class="about-compliance-content">
                    <p class="about-intro">{{ $about['compliance_intro'] ?? 'Our compliance model ensures that every recovery action is lawful, documented, and aligned with established regulatory requirements.' }}</p>
                    <ul class="about-icon-list about-compliance-list">
                        @foreach(($about['compliance_list'] ?? [
                            'Central Bank of Kenya Guidelines',
                            'Data Protection Act 2019',
                            'Consumer Protection Act',
                            'Insolvency Act',
                            'Civil Procedure Act and Rules',
                            'Credit Reference Bureau Regulations',
                        ]) as $rule)
                            <li><span>✓</span> {{ $rule }}</li>
                        @endforeach
                    </ul>
                    <div class="about-points">
                        @foreach(($about['compliance_points'] ?? ['Confidentiality by design', 'Ethical collection practice', 'Transparent case records', 'Lawful debtor data handling']) as $point)
                            <span>{{ $point }}</span>
                        @endforeach
                    </div>
                </div>
                <aside class="about-compliance-media" role="img" aria-label="Regulatory compliance documentation and legal governance"></aside>
            </div>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Our Experience</h2>
            <div class="about-experience-grid">
                <article class="about-experience-clients">
                    <p class="about-intro">{{ $about['experience_intro'] ?? 'Selected organizations and institutions we have supported through structured recovery and tracing mandates.' }}</p>
                    <ul class="about-client-list">
                        @foreach(($about['experience_clients'] ?? ['Style Industries','Watervale Limited','Hotpoint Appliances','Modern Lithographic','Dawa Lifesciences','Wellsprings International']) as $client)
                            <li>{{ $client }}</li>
                        @endforeach
                    </ul>
                </article>
                <aside class="about-experience-summary">
                    <h3>Proven Delivery Standards</h3>
                    <p>{{ $about['experience_summary'] ?? 'Our operating model is built around disciplined execution, practical escalation pathways, and transparent reporting for client decision-making.' }}</p>
                    <div class="about-points">
                        <span>Multi-sector case handling</span>
                        <span>Compliance-led execution</span>
                        <span>Client-ready reporting</span>
                        <span>Consistent follow-through</span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="section about-section reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>Why Clients Choose Colldett Trace Limited</h2>
            <div class="about-choose-grid">
                <div class="about-choose-content">
                    <p class="about-intro">{{ $about['why_choose_intro'] ?? 'Clients trust us for disciplined execution, strategic recovery insight, and a practical operating model that delivers measurable outcomes.' }}</p>
                    <div class="about-services-grid about-choose-cards">
                        @foreach(($about['why_choose_reasons'] ?? [
                            'Experienced team with legal and financial background',
                            'Proven track record in debt recovery',
                            'Strong regional and international network',
                            'Ethical and professional recovery approach',
                            'Seamless integration with legal processes',
                            'Tailored recovery strategies',
                        ]) as $reason)
                            <div class="about-service-card about-choose-card"><span>◆</span>{{ $reason }}</div>
                        @endforeach
                    </div>
                </div>
                <aside class="about-choose-side">
                    <h3>Client Confidence Drivers</h3>
                    <p>Our framework is built to protect reputation, accelerate recovery cycles, and provide clear reporting throughout the engagement lifecycle.</p>
                    <div class="about-points">
                        @foreach(($about['confidence_points'] ?? ['Institutional quality standards', 'Risk-aware recovery workflows', 'Transparent reporting cadence']) as $point)
                            <span>{{ $point }}</span>
                        @endforeach
                    </div>
                    <a href="{{ route('contact') }}" class="btn btn-gold about-choose-btn">
                        <span>Start a Recovery Conversation</span>
                        <i aria-hidden="true">→</i>
                    </a>
                </aside>
            </div>
            <p class="about-closing">{{ $about['closing_text'] ?? 'We do not merely collect debts. We create structured pathways to recovery.' }}</p>
        </div>
    </section>

    <section class="section about-cta reveal">
        <div class="container">
            <div class="brand-arrow-accent" aria-hidden="true"><span class="bar"></span><span class="chevrons"><i></i><i class="c2"></i><i class="c3"></i></span></div>
            <h2>{{ $about['cta_title'] ?? 'Partner With a Trusted Recovery Firm' }}</h2>
            <p>{{ $about['cta_text'] ?? 'Let us help you recover outstanding debts efficiently, professionally, and lawfully.' }}</p>
            <a href="{{ route('contact') }}" class="btn btn-gold">Contact Us</a>
        </div>
    </section>
</section>
@endsection
