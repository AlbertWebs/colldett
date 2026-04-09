<?php

namespace App\Http\Controllers;

use App\Models\Capability;
use App\Models\ContactDetail;
use App\Models\Insight;
use App\Support\AdminStoredSettings;
use App\Support\TeamDirectory;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function robots(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $lines = [
            'User-agent: *',
            'Disallow:',
            '',
            'Sitemap: '.$base.'/sitemap.xml',
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $entries = $this->buildSitemapEntries();

        return response()
            ->view('sitemap', ['entries' => $entries], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function home(): View
    {
        $data = $this->viewData('Home');
        $site = $data['site'];
        $company = $site['company']['name'];

        $metaTitle = 'Debt Recovery, Asset Tracing & Investigations in Kenya | '.$company;
        $metaDescription = 'Colldett Trace Limited provides professional debt recovery, asset tracing, investigations, and car tracking for banks, MFIs, SACCOs, and corporates in Kenya and East Africa — structured, confidential, compliance-led.';
        $heroImage = asset('uploads/hero.webp');

        $data['metaTitle'] = $metaTitle;
        $data['metaDescription'] = $metaDescription;
        $data['metaImage'] = $heroImage;
        $data['ogImageAlt'] = $company.' — debt recovery, asset tracing, and investigations in Kenya';
        $data['metaKeywords'] = 'debt recovery Kenya, debt collection Nairobi, asset tracing Kenya, skip tracing, corporate debt recovery, bank collections Kenya, car tracking Kenya, investigations Kenya, enforcement readiness, Colldett Trace';
        $data['canonicalUrl'] = route('home', absolute: true);
        $data['ogType'] = 'website';
        $data['seoJsonLd'] = $this->homeStructuredData($site, $metaDescription, $heroImage);

        return view('pages.home', $data);
    }

    public function about(): View
    {
        $data = $this->viewData('About Us');
        $intro = $data['site']['about']['hero_intro'] ?? $data['metaDescription'];
        $data['metaDescription'] = Str::limit(strip_tags((string) $intro), 158);
        $data['metaKeywords'] = 'about Colldett Trace, debt recovery firm Kenya, recovery team Nairobi, mission vision Colldett';
        $data['canonicalUrl'] = route('about', absolute: true);
        $data['ogImageAlt'] = 'About '.$data['site']['company']['name'];

        return view('pages.about', $data);
    }

    public function services(): View
    {
        $data = $this->viewData('Our Capabilities');
        $data['services'] = $this->getCapabilities();
        $data['metaDescription'] = 'Explore Colldett capabilities: debt recovery, asset tracing, insurance tracing, investigations, skip tracing, portfolio management, and car tracking — built for institutional clients in Kenya.';
        $data['metaKeywords'] = 'debt recovery services, asset tracing services, car tracking Kenya, skip tracing, insurance tracing, portfolio management collections';
        $data['canonicalUrl'] = route('services', absolute: true);
        $data['ogImageAlt'] = $data['site']['company']['name'].' — our capabilities';

        return view('pages.services', $data);
    }

    public function capabilityShow(string $slug): View
    {
        $capability = collect($this->getCapabilities())->firstWhere('slug', $slug);
        abort_unless($capability, 404);

        $data = $this->viewData($capability['name']);
        $data['metaDescription'] = Str::limit(strip_tags((string) ($capability['description'] ?? config('colldett.company.description'))), 158);
        $data['metaKeywords'] = $capability['name'].', debt recovery Kenya, asset tracing, Colldett Trace';
        $data['canonicalUrl'] = route('capabilities.show', $slug, absolute: true);
        $data['ogImageAlt'] = $capability['name'].' — '.$data['site']['company']['name'];
        $data['capability'] = $capability;
        $data['capabilityDetails'] = $capability['details'] ?? $this->capabilityDetails($slug);

        return view('pages.capability-show', $data);
    }

    public function industries(): View
    {
        $data = $this->viewData('Industries');
        $data['metaDescription'] = 'Sector-aligned debt recovery and tracing for banks, MFIs, SACCOs, corporates, insurance, and law firms in Kenya — practical execution and compliance-led engagement.';
        $data['metaKeywords'] = 'debt recovery banks Kenya, corporate collections, MFI recovery, SACCO collections, law firm tracing support';
        $data['canonicalUrl'] = route('industries', absolute: true);
        $data['ogImageAlt'] = 'Industries we serve — '.$data['site']['company']['name'];

        return view('pages.industries', $data);
    }

    public function insights(): View
    {
        $data = $this->viewData('Insights');
        $data['metaDescription'] = 'Insights and briefs on debt recovery strategy, asset tracing, enforcement readiness, and operational discipline from Colldett Trace Limited.';
        $data['metaKeywords'] = 'debt recovery insights, asset tracing articles, collections strategy Kenya, enforcement readiness';
        $data['canonicalUrl'] = route('insights', absolute: true);
        $data['ogImageAlt'] = 'Insights and resources — '.$data['site']['company']['name'];

        return view('pages.insights', $data);
    }

    public function insightShow(string $slug): View
    {
        $insight = collect($this->getInsights())->firstWhere('slug', $slug);
        abort_unless($insight, 404);

        $data = $this->viewData($insight['title']);
        $data['metaDescription'] = Str::limit(strip_tags((string) ($insight['excerpt'] ?? '')), 158);
        $data['canonicalUrl'] = route('insights.show', $slug, absolute: true);
        $data['ogType'] = 'article';
        $published = $this->insightIsoDate($insight['date'] ?? null);
        $data['articlePublishedTime'] = $published;
        $data['articleModifiedTime'] = $published;
        $data['ogImageAlt'] = $insight['title'].' — '.$data['site']['company']['name'];
        $data['insight'] = $insight;
        $root = rtrim((string) config('app.url'), '/');
        $articleLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $insight['title'],
            'description' => $data['metaDescription'],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('insights.show', $slug, absolute: true),
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => $data['site']['company']['name'],
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $data['site']['company']['name'],
                'url' => $root.'/',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $data['site']['branding']['logo'] ?? asset('uploads/logo.png'),
                ],
            ],
        ];
        if ($published !== null) {
            $articleLd['datePublished'] = $published;
            $articleLd['dateModified'] = $published;
        }
        $data['seoJsonLd'] = $articleLd;

        return view('pages.insight-show', $data);
    }

    public function contact(): View
    {
        $data = $this->viewData('Contact');
        $data['metaDescription'] = 'Contact Colldett Trace Limited for debt recovery, asset tracing, and investigations. Request a consultation — Nairobi, Kenya.';
        $data['metaKeywords'] = 'contact Colldett Trace, debt recovery contact Kenya, tracing services enquiry';
        $data['canonicalUrl'] = route('contact', absolute: true);
        $data['ogImageAlt'] = 'Contact '.$data['site']['company']['name'];

        return view('pages.contact', $data);
    }

    public function teamShow(string $slug): View
    {
        $member = collect(TeamDirectory::all())->firstWhere('slug', $slug);
        abort_unless($member && ($member['is_active'] ?? true), 404);

        $data = $this->viewData($member['name']);
        $data['metaDescription'] = Str::limit(strip_tags((string) ($member['seo_description'] ?? ($member['bio'] ?? config('colldett.company.description')))), 158);
        $memberImage = $member['image'] ?? null;
        $data['metaImage'] = $memberImage
            ? (str_starts_with($memberImage, 'http') ? $memberImage : asset($memberImage))
            : null;
        $data['canonicalUrl'] = route('team.show', $slug, absolute: true);
        $data['ogImageAlt'] = $member['name'].' — '.$data['site']['company']['name'];
        $data['member'] = $member;

        return view('pages.team-show', $data);
    }

    public function privacy(): View
    {
        $data = $this->viewData('Privacy Policy');
        $data['metaDescription'] = 'Privacy Policy for Colldett Trace Limited — how we handle personal data in line with the Data Protection Act and our service operations.';
        $data['canonicalUrl'] = route('privacy', absolute: true);
        $data['ogImageAlt'] = 'Privacy Policy — '.$data['site']['company']['name'];

        return view('pages.privacy-policy', $data);
    }

    public function terms(): View
    {
        $data = $this->viewData('Terms and Conditions');
        $data['metaDescription'] = 'Terms and Conditions for using the Colldett Trace Limited website and related digital services.';
        $data['canonicalUrl'] = route('terms', absolute: true);
        $data['ogImageAlt'] = 'Terms and Conditions — '.$data['site']['company']['name'];

        return view('pages.terms-and-conditions', $data);
    }

    public function compliance(): View
    {
        $data = $this->viewData('Compliance');
        $data['metaDescription'] = 'Compliance framework at Colldett Trace Limited — regulatory alignment, ethical recovery practice, and documentation standards.';
        $data['canonicalUrl'] = route('compliance', absolute: true);
        $data['ogImageAlt'] = 'Compliance — '.$data['site']['company']['name'];

        return view('pages.compliance', $data);
    }

    private function viewData(string $title): array
    {
        $site = config('colldett');
        $saved = $this->readAdminSettings();
        $site['company']['name'] = $saved['company_name'] ?? $site['company']['name'];
        $site['company']['tagline'] = $saved['company_tagline'] ?? $site['company']['tagline'];
        $site['company']['email'] = $saved['company_email'] ?? $site['company']['email'];
        $site['company']['phone'] = $saved['company_phone'] ?? $site['company']['phone'];
        $site['company']['address'] = $saved['company_address'] ?? $site['company']['address'];
        $site['company']['description'] = $saved['company_description'] ?? $site['company']['description'];
        $site['branding'] = [
            'logo' => $this->resolveMediaUrl($saved['company_logo'] ?? 'uploads/logo.png'),
            'footer_logo' => $this->resolveMediaUrl($saved['footer_logo'] ?? 'uploads/logo-white.png'),
            'favicon' => $this->resolveMediaUrl($saved['favicon'] ?? 'uploads/favicon.png'),
        ];
        $site['social'] = [
            'facebook' => $saved['social_facebook'] ?? '#',
            'twitter' => $saved['social_twitter'] ?? '#',
            'linkedin' => $saved['social_linkedin'] ?? '#',
            'instagram' => $saved['social_instagram'] ?? '#',
            'youtube' => $saved['social_youtube'] ?? '#',
        ];
        $site['services'] = $this->getCapabilities();
        $site['insights'] = $this->getInsights();
        $site['team'] = TeamDirectory::forPublicSite();
        $site['about'] = $this->aboutContent();
        $site = $this->mergeContactDetailsFromDatabase($site);

        return [
            'metaTitle' => $title.' | '.$site['company']['name'],
            'metaDescription' => $site['company']['description'],
            'site' => $site,
            'canonicalUrl' => null,
            'ogType' => 'website',
            'metaKeywords' => null,
            'metaRobots' => null,
            'metaImage' => null,
            'ogImageAlt' => null,
            'articlePublishedTime' => null,
            'articleModifiedTime' => null,
            'seoJsonLd' => null,
        ];
    }

    private function readAdminSettings(): array
    {
        return AdminStoredSettings::all();
    }

    /**
     * Overlay phone, email, address, and map embed from contact_details when present.
     *
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    private function mergeContactDetailsFromDatabase(array $site): array
    {
        if (! Schema::hasTable('contact_details')) {
            $site['company']['map_embed_url'] = (string) (config('colldett.company.map_embed_url') ?? '');

            return $site;
        }

        $db = ContactDetail::query()->first();
        if ($db === null) {
            $site['company']['map_embed_url'] = (string) (config('colldett.company.map_embed_url') ?? '');

            return $site;
        }

        if (filled($db->phone)) {
            $site['company']['phone'] = $db->phone;
        }
        if (filled($db->email)) {
            $site['company']['email'] = $db->email;
        }
        if (filled($db->address)) {
            $site['company']['address'] = $db->address;
        }
        if (filled($db->map_embed_url)) {
            $site['company']['map_embed_url'] = $db->map_embed_url;
        } else {
            $site['company']['map_embed_url'] = (string) (config('colldett.company.map_embed_url') ?? '');
        }

        return $site;
    }

    private function resolveMediaUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    /**
     * @return list<array{loc: string, changefreq: string, priority: string}>
     */
    private function buildSitemapEntries(): array
    {
        $base = rtrim((string) config('app.url'), '/');
        $entries = [];

        $push = static function (string $loc, string $changefreq, string $priority) use (&$entries): void {
            $entries[] = ['loc' => $loc, 'changefreq' => $changefreq, 'priority' => $priority];
        };

        $push($base.'/', 'weekly', '1.0');
        $push($base.'/about', 'monthly', '0.9');
        $push($base.'/services', 'weekly', '0.95');
        $push($base.'/industries', 'monthly', '0.85');
        $push($base.'/insights', 'weekly', '0.85');
        $push($base.'/contact', 'monthly', '0.9');
        $push($base.'/privacy-policy', 'yearly', '0.3');
        $push($base.'/terms-and-conditions', 'yearly', '0.3');
        $push($base.'/compliance', 'yearly', '0.5');

        foreach ($this->getCapabilities() as $cap) {
            if (! empty($cap['slug'])) {
                $push($base.'/capabilities/'.$cap['slug'], 'monthly', '0.8');
            }
        }

        foreach ($this->getInsights() as $row) {
            if (! empty($row['slug'])) {
                $push($base.'/insights/'.$row['slug'], 'monthly', '0.75');
            }
        }

        foreach (TeamDirectory::forPublicSite() as $member) {
            if (! empty($member['slug'])) {
                $push($base.'/team/'.$member['slug'], 'monthly', '0.65');
            }
        }

        return $entries;
    }

    private function homeStructuredData(array $site, string $metaDescription, string $heroImageUrl): array
    {
        $root = rtrim((string) config('app.url'), '/');
        $socialLinks = collect($site['social'] ?? [])
            ->filter(fn ($url) => ! empty($url) && $url !== '#')
            ->values()
            ->all();

        $logoUrl = $site['branding']['logo'] ?? asset('uploads/logo.png');

        $serviceItems = collect($site['services'] ?? [])
            ->filter(fn ($s) => is_array($s) && ! empty($s['slug']))
            ->take(12)
            ->values()
            ->map(function (array $s, int $i): array {
                return [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $s['name'] ?? 'Service',
                    'url' => route('capabilities.show', $s['slug'], absolute: true),
                ];
            })
            ->all();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $root.'/#organization',
                    'name' => $site['company']['name'],
                    'url' => $root.'/',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $logoUrl,
                    ],
                    'image' => [$logoUrl, $heroImageUrl],
                    'email' => $site['company']['email'] ?? null,
                    'telephone' => $site['company']['phone'] ?? null,
                    'sameAs' => $socialLinks,
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'contactType' => 'customer service',
                        'email' => $site['company']['email'] ?? null,
                        'telephone' => $site['company']['phone'] ?? null,
                        'areaServed' => ['KE', 'East Africa'],
                        'availableLanguage' => ['English', 'Swahili'],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $root.'/#website',
                    'url' => $root.'/',
                    'name' => $site['company']['name'],
                    'description' => $metaDescription,
                    'inLanguage' => config('colldett.seo.locale', 'en_KE'),
                    'publisher' => [
                        '@id' => $root.'/#organization',
                    ],
                ],
                [
                    '@type' => 'ProfessionalService',
                    '@id' => $root.'/#professional-service',
                    'name' => $site['company']['name'],
                    'url' => $root.'/',
                    'image' => [$heroImageUrl, $logoUrl],
                    'description' => $metaDescription,
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'Kenya',
                    ],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => $site['company']['address'] ?? null,
                        'addressCountry' => 'KE',
                    ],
                    'email' => $site['company']['email'] ?? null,
                    'telephone' => $site['company']['phone'] ?? null,
                    'provider' => [
                        '@id' => $root.'/#organization',
                    ],
                ],
                [
                    '@type' => 'ItemList',
                    '@id' => $root.'/#core-capabilities',
                    'name' => 'Core capabilities',
                    'itemListElement' => $serviceItems,
                ],
            ],
        ];
    }

    private function insightIsoDate(?string $displayDate): ?string
    {
        if ($displayDate === null || trim($displayDate) === '') {
            return null;
        }

        try {
            return Carbon::parse('1 '.$displayDate)->startOfMonth()->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }

    private function getCapabilities(): array
    {
        if (! Schema::hasTable('capabilities')) {
            return config('colldett.services');
        }

        $rows = Capability::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'name',
                'slug',
                'description',
                'details',
                'featured',
                'coming_soon',
            ]);

        if ($rows->isEmpty()) {
            return config('colldett.services');
        }

        return $rows->map(fn (Capability $item) => [
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'details' => $item->details,
            'featured' => $item->featured,
            'coming_soon' => $item->coming_soon,
        ])->all();
    }

    private function getInsights(): array
    {
        if (! Schema::hasTable('insights')) {
            return config('colldett.insights');
        }

        $rows = Insight::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['title', 'slug', 'excerpt', 'date', 'content']);

        if ($rows->isEmpty()) {
            return config('colldett.insights');
        }

        return $rows->map(fn (Insight $item) => [
            'title' => $item->title,
            'slug' => $item->slug,
            'excerpt' => $item->excerpt,
            'date' => $item->date,
            'content' => is_array($item->content) ? $item->content : [],
        ])->all();
    }

    private function capabilityDetails(string $slug): array
    {
        $map = [
            'debt-recovery' => [
                'Structured case assessment and prioritization',
                'Negotiation-led repayment engagement',
                'Escalation to legal pathways where needed',
            ],
            'asset-tracing' => [
                'Movable and immovable asset intelligence',
                'Ownership signal verification',
                'Enforcement-readiness support',
            ],
            'insurance-tracing' => [
                'Policy-linked tracing investigations',
                'Claims and fraud signal support',
                'Evidence-led reporting for recovery teams',
            ],
            'investigations' => [
                'Field verification and data validation',
                'Operational intelligence gathering',
                'Case-specific investigative reporting',
            ],
            'skip-tracing' => [
                'Debtor location through lawful data points',
                'Contact and address validation workflows',
                'Follow-through for active collections teams',
            ],
            'debt-portfolio-management' => [
                'Portfolio segmentation and strategy design',
                'Action planning by risk and age bucket',
                'Performance visibility and reporting cadence',
            ],
            'car-tracking' => [
                'Vehicle tracker fitting and activation',
                'Real-time monitoring and fleet visibility',
                'Remote immobilization support controls',
            ],
            'colldett-microfinance' => [
                'Product strategy under development',
                'Operational model preparation',
                'Launch-readiness and compliance structuring',
            ],
        ];

        return $map[$slug] ?? [
            'Structured delivery methodology',
            'Compliance-led operational controls',
            'Transparent reporting and accountability',
        ];
    }

    private function aboutContent(): array
    {
        $defaults = [
            'hero_title' => 'About Colldett Trace Limited',
            'hero_intro' => 'Colldett Trace Limited is a dynamic and forward-thinking debt recovery firm formed by experienced professionals with strong backgrounds in financial institutions, legal practice, and corporate governance.',
            'mission_text' => "To deliver innovative, ethical, and results-driven debt recovery solutions that enhance our clients' cash flow, reduce financial risk, and preserve business relationships.",
            'vision_text' => 'To be a leading debt recovery firm in Africa and beyond, recognized for excellence, integrity, and a refined approach to recovery grounded in legal and financial expertise.',
            'core_values' => ['Integrity', 'Professionalism', 'Respect', 'Confidentiality', 'Accountability', 'Results-Driven Performance'],
            'story_intro' => 'Colldett Trace Limited was established to redefine the debt recovery landscape through a structured, ethical, and professional approach.',
            'story_paragraph_2' => 'The firm was formed by a team of experienced professionals who transitioned from a structured law firm environment into an independent, specialized debt recovery practice.',
            'story_paragraph_3' => 'With over 8 years of hands-on recovery experience, we bring a deep understanding of legal processes, financial systems, and debtor behavior.',
            'story_points' => ['Legal intelligence', 'Financial insight', 'Human-centered negotiation', 'Data-driven recovery strategies'],
            'reach_lead' => 'Our recovery network is designed for reliable, timely execution across local and regional jurisdictions.',
            'reach_chips' => ['Kenya - Nationwide Coverage', 'East Africa Region', 'International Clients'],
            'reach_relations' => ['Legal Practitioners', 'Tracing Agents', 'Financial Institutions', 'Auctioneering Firms'],
            'what_we_do_intro' => 'We provide integrated recovery, tracing, and enforcement support designed for institutional performance and compliant execution.',
            'what_we_do_services' => [
                'Debt Recovery Advisory',
                'Pre-Legal Collections',
                'Legal Collections',
                'Payment Structuring',
                'Execution of Court Decrees',
                'Tracing and Skip Tracing',
                'Asset Tracing',
                'Insurance Tracing',
                'Car Tracking and Monitoring',
            ],
            'compliance_intro' => 'Our compliance model ensures that every recovery action is lawful, documented, and aligned with established regulatory requirements.',
            'compliance_list' => [
                'Central Bank of Kenya Guidelines',
                'Data Protection Act 2019',
                'Consumer Protection Act',
                'Insolvency Act',
                'Civil Procedure Act and Rules',
                'Credit Reference Bureau Regulations',
            ],
            'compliance_points' => ['Confidentiality by design', 'Ethical collection practice', 'Transparent case records', 'Lawful debtor data handling'],
            'experience_intro' => 'Selected organizations and institutions we have supported through structured recovery and tracing mandates.',
            'experience_clients' => ['Style Industries', 'Watervale Limited', 'Hotpoint Appliances', 'Modern Lithographic', 'Dawa Lifesciences', 'Wellsprings International'],
            'experience_summary' => 'Our operating model is built around disciplined execution, practical escalation pathways, and transparent reporting for client decision-making.',
            'why_choose_intro' => 'Clients trust us for disciplined execution, strategic recovery insight, and a practical operating model that delivers measurable outcomes.',
            'why_choose_reasons' => [
                'Experienced team with legal and financial background',
                'Proven track record in debt recovery',
                'Strong regional and international network',
                'Ethical and professional recovery approach',
                'Seamless integration with legal processes',
                'Tailored recovery strategies',
            ],
            'confidence_points' => ['Institutional quality standards', 'Risk-aware recovery workflows', 'Transparent reporting cadence'],
            'closing_text' => 'We do not merely collect debts. We create structured pathways to recovery.',
            'cta_title' => 'Partner With a Trusted Recovery Firm',
            'cta_text' => 'Let us help you recover outstanding debts efficiently, professionally, and lawfully.',
        ];

        if (! Storage::disk('local')->exists('admin/about-content.json')) {
            return $defaults;
        }

        $decoded = json_decode((string) Storage::disk('local')->get('admin/about-content.json'), true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, $decoded);
    }
}
