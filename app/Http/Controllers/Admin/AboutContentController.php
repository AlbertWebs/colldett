<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AboutContentController extends Controller
{
    private const STORAGE_PATH = 'admin/about-content.json';

    public function edit(): View
    {
        return view('admin.about-content', [
            'content' => $this->content(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_intro' => ['required', 'string', 'max:1500'],
            'mission_text' => ['required', 'string', 'max:1500'],
            'vision_text' => ['required', 'string', 'max:1500'],
            'core_values_text' => ['required', 'string', 'max:3000'],
            'story_intro' => ['required', 'string', 'max:1500'],
            'story_paragraph_2' => ['required', 'string', 'max:1500'],
            'story_paragraph_3' => ['required', 'string', 'max:1500'],
            'story_points_text' => ['required', 'string', 'max:3000'],
            'reach_lead' => ['required', 'string', 'max:1500'],
            'reach_chips_text' => ['required', 'string', 'max:2000'],
            'reach_relations_text' => ['required', 'string', 'max:2000'],
            'what_we_do_intro' => ['required', 'string', 'max:1500'],
            'what_we_do_services_text' => ['required', 'string', 'max:4000'],
            'compliance_intro' => ['required', 'string', 'max:1500'],
            'compliance_list_text' => ['required', 'string', 'max:3000'],
            'compliance_points_text' => ['required', 'string', 'max:3000'],
            'experience_intro' => ['required', 'string', 'max:1500'],
            'experience_clients_text' => ['required', 'string', 'max:3000'],
            'experience_summary' => ['required', 'string', 'max:1500'],
            'why_choose_intro' => ['required', 'string', 'max:1500'],
            'why_choose_reasons_text' => ['required', 'string', 'max:4000'],
            'confidence_points_text' => ['required', 'string', 'max:2000'],
            'closing_text' => ['required', 'string', 'max:1500'],
            'cta_title' => ['required', 'string', 'max:255'],
            'cta_text' => ['required', 'string', 'max:1000'],
        ]);

        $normalized = [
            'hero_title' => $data['hero_title'],
            'hero_intro' => $data['hero_intro'],
            'mission_text' => $data['mission_text'],
            'vision_text' => $data['vision_text'],
            'core_values' => $this->splitLines($data['core_values_text']),
            'story_intro' => $data['story_intro'],
            'story_paragraph_2' => $data['story_paragraph_2'],
            'story_paragraph_3' => $data['story_paragraph_3'],
            'story_points' => $this->splitLines($data['story_points_text']),
            'reach_lead' => $data['reach_lead'],
            'reach_chips' => $this->splitLines($data['reach_chips_text']),
            'reach_relations' => $this->splitLines($data['reach_relations_text']),
            'what_we_do_intro' => $data['what_we_do_intro'],
            'what_we_do_services' => $this->splitLines($data['what_we_do_services_text']),
            'compliance_intro' => $data['compliance_intro'],
            'compliance_list' => $this->splitLines($data['compliance_list_text']),
            'compliance_points' => $this->splitLines($data['compliance_points_text']),
            'experience_intro' => $data['experience_intro'],
            'experience_clients' => $this->splitLines($data['experience_clients_text']),
            'experience_summary' => $data['experience_summary'],
            'why_choose_intro' => $data['why_choose_intro'],
            'why_choose_reasons' => $this->splitLines($data['why_choose_reasons_text']),
            'confidence_points' => $this->splitLines($data['confidence_points_text']),
            'closing_text' => $data['closing_text'],
            'cta_title' => $data['cta_title'],
            'cta_text' => $data['cta_text'],
        ];

        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return redirect()->route('admin.about-content.edit')->with('status', 'About page content updated.');
    }

    private function content(): array
    {
        $defaults = $this->defaults();
        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return $defaults;
        }

        $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, $decoded);
    }

    private function defaults(): array
    {
        return [
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
    }

    private function splitLines(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: [])));
    }
}
