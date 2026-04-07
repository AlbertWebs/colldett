@extends('layouts.app')

@section('content')
<section class="hero" style="--hero-image: url('{{ asset('uploads/hero.webp') }}');">
    <div class="container hero-grid">
        <div class="reveal">
            <p class="eyebrow">National Debt Recovery and Tracing Experts</p>
            <h1>Authority in Recovery. Precision in Tracing. Confidence in Results.</h1>
            <p class="lead">Colldett Trace Limited delivers disciplined debt recovery, asset tracing, investigations, and vehicle security services for banks, financial institutions, and corporates.</p>
            <div class="actions">
                <a class="btn hero-btn-primary" href="{{ route('contact') }}">
                    <span>Recover Your Debt</span>
                    <i aria-hidden="true">→</i>
                </a>
                <a class="btn btn-ghost hero-btn-secondary" href="{{ route('services') }}">
                    <span>Track Your Asset</span>
                    <i aria-hidden="true">↗</i>
                </a>
            </div>
        </div>
        <div class="hero-panel reveal">
            <div class="stat"><strong>Nationwide</strong><span>Operational reach and field capability</span></div>
            <div class="stat"><strong>Structured</strong><span>Process-led recovery workflows</span></div>
            <div class="stat"><strong>Confidential</strong><span>Secure handling for sensitive matters</span></div>
        </div>
    </div>
    <div class="hero-scroll-wrap">
        <a href="#welcome" class="hero-scroll-indicator" aria-label="Scroll to welcome section">
            <span>Scroll</span>
            <svg class="hero-arrow" viewBox="0 0 24 48" aria-hidden="true" focusable="false">
                <path d="M12 2V34" />
                <path d="M5 28L12 38L19 28" />
            </svg>
        </a>
    </div>
</section>

<section class="section welcome-section" id="welcome">
    <div class="container welcome-grid">
        <div class="welcome-media reveal">
            <video autoplay muted loop playsinline poster="https://images.unsplash.com/photo-1521791136064-7986c2920216?q=80&w=1400&auto=format&fit=crop">
                <source src="https://cdn.coverr.co/videos/coverr-working-on-a-laptop-1579/1080p.mp4" type="video/mp4">
            </video>
            <div class="welcome-media-overlay">
                <p>Disciplined Recovery Operations</p>
                <strong>Field + Legal + Data</strong>
            </div>
        </div>
        <div class="welcome-content reveal">
            <div class="brand-arrow-accent" aria-hidden="true">
                <span class="bar"></span>
                <span class="chevrons">
                    <i class="c1"></i><i class="c2"></i><i class="c3"></i>
                </span>
            </div>
            <p class="eyebrow">Welcome to Colldett Trace</p>
            <h2>Professional recovery and tracing support built for institutional confidence</h2>
            <p>We combine field intelligence, structured workflows, and legal-aligned execution to help clients recover value and secure assets with speed, discipline, and accountability.</p>
            <p>From debt recovery and investigations to vehicle tracking and skip tracing, our team operates with a results-first mindset and strict confidentiality standards.</p>
            <div class="welcome-points">
                <span>Structured Case Handling</span>
                <span>Nationwide Coverage</span>
                <span>Confidential Reporting</span>
            </div>
            <div class="welcome-actions">
                <a class="btn welcome-btn-primary" href="{{ route('about') }}">
                    <span>Learn About Our Approach</span>
                    <i aria-hidden="true">→</i>
                </a>
                <a class="btn btn-soft welcome-btn-secondary" href="{{ route('contact') }}">
                    <span>Speak to a Specialist</span>
                    <i aria-hidden="true">↗</i>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section section-services" id="services">
    <div class="container">
        <div class="section-title services-head reveal">
            <div class="services-title-wrap">
                <div class="brand-arrow-accent" aria-hidden="true">
                    <span class="bar"></span>
                    <span class="chevrons">
                        <i class="c1"></i><i class="c2"></i><i class="c3"></i>
                    </span>
                </div>
                <p class="eyebrow">Core Services</p>
                <h2>Recovery and tracing services built for institutional performance</h2>
            </div>
            <a class="btn btn-soft services-head-cta" href="{{ route('services') }}">View Full Capibilities</a>
        </div>
        <div class="services-meta reveal">
            <span>Commercial Recovery</span>
            <span>Investigations</span>
            <span>Tracing Services</span>
            <span>Portfolio Management</span>
        </div>
        <div class="services-grid">
            @foreach($site['services'] as $service)
                @if($service['slug'] !== 'car-tracking')
                    <article class="service-card reveal {{ !empty($service['coming_soon']) ? 'service-card-soon' : '' }}" id="{{ $service['slug'] }}">
                        <div class="icon-dot"></div>
                        <p class="service-kicker">{{ strtoupper(str_replace('-', ' ', $service['slug'])) }}</p>
                        <h3>{{ $service['name'] }}</h3>
                        @if(!empty($service['coming_soon']))<span class="badge">COMING SOON</span>@endif
                        <p>{{ $service['description'] }}</p>
                    </article>
                @endif
            @endforeach
        </div>
        <article class="service-feature-card reveal">
            <div>
                <p class="eyebrow">Featured Capability</p>
                <h3>Debt Recovery and Portfolio Performance</h3>
                <p>Structured debt recovery workflows designed to accelerate collections, improve portfolio quality, and protect client relationships through compliant execution.</p>
            </div>
            <div class="service-feature-metrics">
                <span>Case Prioritization</span>
                <span>Negotiation & Settlement</span>
                <span>Portfolio Recovery Strategy</span>
            </div>
            <a class="btn btn-gold" href="{{ route('contact') }}">Start Recovery</a>
        </article>
    </div>
</section>

<section class="section section-contrast">
    <div class="container two-col reveal">
        <div class="why-content">
            <div class="brand-arrow-accent" aria-hidden="true">
                <span class="bar"></span>
                <span class="chevrons">
                    <i class="c1"></i><i class="c2"></i><i class="c3"></i>
                </span>
            </div>
            <p class="eyebrow">Why Colldett Trace</p>
            <h2>Operational discipline that protects client confidence</h2>
            <p class="why-lead">We combine legal-aligned recovery workflows, national field capability, and confidential reporting to deliver dependable outcomes for institutions and corporates.</p>
            <ul class="checklist">
                <li>Experienced recovery professionals with legal support alignment</li>
                <li>Structured case execution from intake to closeout</li>
                <li>Confidential handling and reporting for sensitive portfolios</li>
                <li>Results-focused engagement with measurable performance</li>
            </ul>
            <div class="why-metrics">
                <span>Nationwide Reach</span>
                <span>Strict Confidentiality</span>
                <span>Structured Processes</span>
            </div>
        </div>
        <div class="info-card">
            <h3>Industries Served</h3>
            <ul class="tags">
                @foreach($site['industries'] as $industry)
                    <li>{{ $industry }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="section car-highlight" style="--car-tracking-image: url('{{ asset('uploads/car-tracking.jpg') }}');">
    <div class="container car-grid">
        <div class="reveal">
            <div class="brand-arrow-accent" aria-hidden="true">
                <span class="bar"></span>
                <span class="chevrons">
                    <i class="c1"></i><i class="c2"></i><i class="c3"></i>
                </span>
            </div>
            <p class="eyebrow">Car Tracking</p>
            <h2>Technology-enabled vehicle security and control</h2>
            <p>Our Car Tracking service integrates modern monitoring workflows with rapid response capability for lenders, fleet owners, and corporates.</p>
            <ul class="checklist compact">
                <li>GPS tracking device fitting and calibration</li>
                <li>Real-time monitoring and movement intelligence</li>
                <li>Fleet visibility dashboards and security alerts</li>
                <li>Remote engine immobilization controls</li>
            </ul>
            <a class="btn btn-gold" href="{{ route('contact') }}">Secure Your Vehicle</a>
        </div>
        <div class="tech-panel reveal">
            <div class="pulse"><span class="pulse-dot"></span></div>
            <div class="tech-lines">
                <span>Live GPS</span><span>Engine Lock</span><span>Fleet Monitor</span>
            </div>
        </div>
    </div>
</section>

<section class="section affiliate-wrap">
    <div class="container reveal">
        <div class="affiliate-box premium-box">
            <div class="affiliate-grid">
                <div class="affiliate-content">
                    <div class="brand-arrow-accent" aria-hidden="true">
                        <span class="bar"></span>
                        <span class="chevrons">
                            <i class="c1"></i><i class="c2"></i><i class="c3"></i>
                        </span>
                    </div>
                    <p class="eyebrow">Affiliate Legal Partner</p>
                    <h2>{{ $site['company']['affiliate_law_firm']['name'] }}</h2>
                    <p>Our strategic legal affiliate supports litigation, enforcement actions, and complex legal recovery pathways, strengthening execution certainty for high-value matters.</p>
                    <div class="affiliate-points">
                        <span>Litigation Support</span>
                        <span>Enforcement Advisory</span>
                        <span>Legal Recovery Actions</span>
                    </div>
                    <div class="affiliate-actions">
                        <a class="btn affiliate-btn-primary" href="{{ route('about') }}">
                            <span>Learn More</span>
                            <i aria-hidden="true">→</i>
                        </a>
                        <a class="btn btn-soft affiliate-btn-secondary" href="{{ route('contact') }}">
                            <span>Consult Legal Recovery</span>
                            <i aria-hidden="true">↗</i>
                        </a>
                    </div>
                </div>
                <div class="affiliate-image" style="--affiliate-image: url('{{ asset('uploads/legal.jpeg') }}');" role="img" aria-label="Professional legal partner meeting"></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-insights">
    <div class="container">
        <div class="section-title insights-head reveal">
            <div>
                <div class="brand-arrow-accent" aria-hidden="true">
                    <span class="bar"></span>
                    <span class="chevrons">
                        <i class="c1"></i><i class="c2"></i><i class="c3"></i>
                    </span>
                </div>
                <p class="eyebrow">Insights / Resources</p>
                <h2>Professional perspectives on recovery and tracing</h2>
            </div>
            <a class="btn btn-soft insights-head-cta" href="{{ route('insights') }}">Visit Insights Hub</a>
        </div>
        <div class="insights-grid">
            @foreach($site['insights'] as $article)
                <article class="service-card insight-card reveal">
                    <div class="insight-meta">
                        <span class="insight-date">{{ $article['date'] }}</span>
                        <span class="insight-tag">Expert Brief</span>
                    </div>
                    <h3>{{ $article['title'] }}</h3>
                    <p>{{ $article['excerpt'] }}</p>
                    <a class="insight-cta" href="{{ route('insights') }}">Read Insight <i aria-hidden="true">→</i></a>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container cta-box reveal">
        <h2>Start a structured recovery engagement today</h2>
        <p>Partner with a disciplined team trusted by financial and commercial clients.</p>
        <div class="actions">
            <a class="btn" href="{{ route('contact') }}">Request Assistance</a>
            <a class="btn btn-gold" href="{{ route('contact') }}">Start Recovery</a>
        </div>
    </div>
</section>

<section class="section section-contact" id="contact">
    <div class="container two-col">
        <div class="reveal">
            <p class="eyebrow">Contact</p>
            <h2>Speak to a specialist</h2>
            <p>{{ $site['company']['phone'] }}</p>
            <p>{{ $site['company']['email'] }}</p>
            <p>{{ $site['company']['address'] }}</p>
        </div>
        <div class="reveal">
            <a href="{{ route('contact') }}" class="contact-portal">
                Open secure inquiry portal
                <span>Submit debt recovery, tracing, or vehicle security requests.</span>
            </a>
        </div>
    </div>
</section>
@endsection
