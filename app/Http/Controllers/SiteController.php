<?php

namespace App\Http\Controllers;

use App\Models\Capability;
use App\Models\Insight;
use App\Support\AdminStoredSettings;
use App\Support\TeamDirectory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('pages.home', $this->viewData('Home'));
    }

    public function about(): View
    {
        return view('pages.about', $this->viewData('About Us'));
    }

    public function services(): View
    {
        $data = $this->viewData('Services');
        $data['services'] = $this->getCapabilities();

        return view('pages.services', $data);
    }

    public function capabilityShow(string $slug): View
    {
        $capability = collect($this->getCapabilities())->firstWhere('slug', $slug);
        abort_unless($capability, 404);

        $data = $this->viewData($capability['name']);
        $data['metaDescription'] = $capability['description'] ?? config('colldett.company.description');
        $data['capability'] = $capability;
        $data['capabilityDetails'] = $capability['details'] ?? $this->capabilityDetails($slug);

        return view('pages.capability-show', $data);
    }

    public function industries(): View
    {
        return view('pages.industries', $this->viewData('Industries'));
    }

    public function insights(): View
    {
        return view('pages.insights', $this->viewData('Insights'));
    }

    public function insightShow(string $slug): View
    {
        $insight = collect($this->getInsights())->firstWhere('slug', $slug);
        abort_unless($insight, 404);

        $data = $this->viewData($insight['title']);
        $data['insight'] = $insight;

        return view('pages.insight-show', $data);
    }

    public function contact(): View
    {
        return view('pages.contact', $this->viewData('Contact'));
    }

    public function teamShow(string $slug): View
    {
        $member = collect(TeamDirectory::all())->firstWhere('slug', $slug);
        abort_unless($member && ($member['is_active'] ?? true), 404);

        $data = $this->viewData($member['name']);
        $data['metaDescription'] = $member['seo_description'] ?? ($member['bio'] ?? config('colldett.company.description'));
        $memberImage = $member['image'] ?? null;
        $data['metaImage'] = $memberImage
            ? (str_starts_with($memberImage, 'http') ? $memberImage : asset($memberImage))
            : null;
        $data['member'] = $member;

        return view('pages.team-show', $data);
    }

    public function privacy(): View
    {
        return view('pages.privacy-policy', $this->viewData('Privacy Policy'));
    }

    public function terms(): View
    {
        return view('pages.terms-and-conditions', $this->viewData('Terms and Conditions'));
    }

    public function compliance(): View
    {
        return view('pages.compliance', $this->viewData('Compliance'));
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

        return [
            'metaTitle' => $title.' | '.$site['company']['name'],
            'metaDescription' => $site['company']['description'],
            'site' => $site,
        ];
    }

    private function readAdminSettings(): array
    {
        return AdminStoredSettings::all();
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
