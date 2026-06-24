<?php

return [
    'company' => [
        'name' => 'Colldett Trace Limited',
        'tagline' => 'Professional Recovery and Tracing Solutions You Can Trust',
        'description' => 'Colldett Trace Limited delivers disciplined debt recovery, tracing, and investigative support for institutions and businesses that require consistent, enforceable, and confidential outcomes.',
        'phone' => '+254 720 980 569',
        'phone_alt' => '+254 712 342 110',
        'email' => 'info@colldetttrace.com',
        'address' => "St George's House 4th Flr, Suite 404, Nairobi, Kenya",
        'kra_pin' => '',
        'map_embed_url' => 'https://www.google.com/maps?q=Nairobi%20Kenya&output=embed',
        'affiliate_law_firm' => [
            'name' => 'Brigid Achieng and Company Advocates',
            'summary' => 'Our affiliate legal partner supports legal recovery actions, litigation, and enforcement where strategic legal intervention is required.',
        ],
    ],

    /*
    | Branding for printable admin documents (invoices, letters, etc.).
    | Drop a full-page PNG at public/uploads/letterhead-document.png to use as background;
    | otherwise the built-in CSS theme matches official letterhead colours.
    */
    'document_theme' => [
        'website' => 'www.colldett.co.ke',
        'phones' => '0720 980 569 / 0712 342 110',
        'address_lines' => [
            "ST GEORGE'S HOUSE 4TH FLR, SUITE 404",
            'P.O. BOX 5805 - 00100 PARLIAMENT ROAD',
            'NAIROBI',
        ],
        'letterhead_image' => 'uploads/letterhead-document.png',
    ],

    /*
    | Invoice preview/PDF: VAT, currency label, and payment instructions (top-right block).
    */
    'invoice' => [
        'vat_rate' => 0.16,
        'vat_label' => '16.00% Kenyan VAT',
        'currency' => 'Ksh',
        'payment_details' => [
            'title' => 'Payment Details',
            'sections' => [
                [
                    'heading' => 'Bank',
                    'lines' => [
                        'Account Name: Colldett Trace Limited',
                        'Account Number: 1351221760',
                        'Bank: KENYA COMMERCIAL BANK',
                        'Branch: HAILE SELASSIE',
                        'Reference: your invoice number',
                        'Swift Code: KCBLKENX',
                        'Bank Code: 01',
                        'Branch Code: 288',
                    ],
                ],
                [
                    'heading' => 'M-Pesa',
                    'lines' => [
                        'Paybill: 522533',
                        'Account Number: 8080678',
                        'Account Name: Colldett Trace',
                        'Reference: your invoice number',
                    ],
                ],
            ],
            'note' => 'NB: Quote your invoice number on all remittances.',
        ],
    ],

    'services' => [
        [
            'name' => 'Debt Recovery',
            'slug' => 'debt-recovery',
            'description' => 'End-to-end commercial and consumer debt recovery with structured escalation and compliance-focused execution.',
        ],
        [
            'name' => 'Asset Tracing',
            'slug' => 'asset-tracing',
            'description' => 'Professional tracing of movable and immovable assets to support enforcement, negotiations, and legal action.',
        ],
        [
            'name' => 'Insurance Tracing',
            'slug' => 'insurance-tracing',
            'description' => 'Evidence-led tracing services for insurers and legal teams handling claims, fraud signals, and recoveries.',
        ],
        [
            'name' => 'Investigations & Field Services',
            'slug' => 'investigations',
            'description' => 'Professional investigations and field services for verification, intelligence gathering, and enforcement readiness — delivered with structured reporting and trusted partners where needed.',
            'seo_title' => 'Investigations & Field Services in Kenya | Colldett Trace Limited',
            'seo_description' => 'Investigations and field services for verification, intelligence gathering, and recovery support — including partner-led specialist support where required, with disciplined reporting and confidentiality.',
            'seo_keywords' => 'investigations Kenya, field investigations Nairobi, background checks, verification services, field intelligence, asset verification, partner investigators',
            'details' => [
                'Field verification and data validation for decision-ready clarity',
                'Operational intelligence gathering to reduce recovery and litigation risk',
                'Partner-enabled specialist support when the scope requires it',
                'Confidential, structured reporting aligned to institutional standards',
            ],
            'content' => [
                'intro' => "Investigations & Field Services\n\nColldett Trace Limited provides disciplined investigations and field services to support debt recovery, asset tracing, and enforcement readiness. We combine field intelligence with structured workflows to verify critical facts, identify risk signals, and provide actionable findings that support informed decisions.",
                'sections' => [
                    [
                        'title' => 'What we do',
                        'bullets' => [
                            'Field verification of addresses, business operations, and identity signals.',
                            'Background and relationship checks to confirm key facts and reduce exposure.',
                            'Asset and location verification to support negotiation and enforcement planning.',
                            'Incident and fraud signal support for sensitive matters that require controlled handling.',
                        ],
                    ],
                    [
                        'title' => 'Partner-enabled execution',
                        'body' => 'Where a matter requires specialist coverage, additional jurisdictions, or niche expertise, we work with trusted partners to extend field capability — while maintaining a single accountable workflow, quality control, and consistent reporting standards.',
                    ],
                    [
                        'title' => 'How we report',
                        'bullets' => [
                            'Clear findings and evidence-led notes (what we observed, verified, and could not verify).',
                            'Risk flags and recommended next steps aligned to recovery timelines.',
                            'Confidential handling of sensitive information and lawful data practices.',
                            'Structured updates to support internal approvals and client oversight.',
                        ],
                    ],
                    [
                        'title' => 'Value to your team',
                        'body' => 'Our investigations reduce uncertainty and improve recovery decisions by strengthening what your team knows, documenting what can be proven, and enabling compliant escalation when required.',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Skip Tracing',
            'slug' => 'skip-tracing',
            'description' => 'Specialized skip tracing services delivering timely, accurate, actionable intelligence to locate individuals, verify critical data, and support compliant recoveries.',
            'seo_title' => 'Skip Tracing Services in Kenya | Colldett Trace Limited',
            'seo_description' => 'Skip tracing services for debt collection agencies, legal teams, and investigators — locate individuals, verify data, and support compliant recovery decisions with timely, actionable intelligence.',
            'seo_keywords' => 'skip tracing Kenya, skip tracer Nairobi, debtor tracing, locate debtors, asset tracing, debt recovery support, investigations, lawful data verification',
            'details' => [
                'Comprehensive data sources across reliable channels',
                'Advanced tools for precise tracking and analysis',
                'Skilled, experienced skip tracers for complex investigations',
                'Ethical, compliant practices aligned to professional standards',
            ],
            'content' => [
                'intro' => "Skip Tracing.\n\nAt Colldett, we provide specialized skip tracing services designed to support debt collection agencies, law enforcement, and private investigators. Our mission is to deliver timely, accurate, and actionable intelligence that empowers informed decision-making. With meticulous attention to detail and a relentless pursuit of results, we leave no stone unturned in locating individuals, uncovering hidden assets, and verifying critical data.",
                'sections' => [
                    [
                        'title' => 'Key Strengths',
                        'bullets' => [
                            'Comprehensive Data Sources: Access to diverse and reliable information channels.',
                            'Advanced Technology & Tools: Cutting-edge systems for precise tracking and analysis.',
                            'Skilled & Experienced Skip Tracers: Professionals adept at handling complex investigations.',
                            'Tailored Approach: Customized strategies to meet unique client needs.',
                            'Ethical Practices: Strict adherence to legal and professional standards.',
                            'Proactive & Persistent Efforts: Determined pursuit to ensure successful outcomes.',
                            'Timely & Actionable Results: Delivering intelligence that drives effective decisions.',
                        ],
                    ],
                    [
                        'title' => 'Value Proposition',
                        'body' => 'Our skip tracing services enable clients to:',
                        'bullets' => [
                            'Locate debtors efficiently for faster recoveries.',
                            'Enhance asset management through thorough investigations.',
                            'Safeguard relationships by focusing on constructive and compliant resolutions.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Debt Portfolio Management',
            'slug' => 'debt-portfolio-management',
            'description' => 'Debt portfolio management with legal precision and financial discipline — segmentation, risk-based action plans, and reporting for measurable recovery outcomes.',
            'seo_title' => 'Debt Portfolio Management in Kenya | Colldett Trace Limited',
            'seo_description' => 'Structured debt portfolio management for institutions: segmentation by age/value/risk, targeted interventions, compliant escalation, and performance reporting for measurable recoveries.',
            'seo_keywords' => 'debt portfolio management Kenya, portfolio recovery strategy, collections segmentation, risk-based collections, recovery reporting, institutional recovery',
            'details' => [
                'Portfolio segmentation and strategy design by age, value, and risk',
                'Action planning with measured engagement and structured escalation',
                'Legal-aligned execution for higher-risk and non-responsive accounts',
                'Performance tracking and reporting for decision-ready visibility',
            ],
            'content' => [
                'intro' => "Debt Portfolio Management\n\nAt Colldett Trace Limited, our Debt Portfolio Management service combines legal precision with financial discipline to deliver structured and measurable recovery outcomes. We manage debt portfolios as controlled assets, aligning recovery strategies with institutional risk frameworks and cash flow priorities.",
                'sections' => [
                    [
                        'title' => 'Portfolio segmentation and strategy design',
                        'body' => 'Our approach begins with portfolio segmentation and strategy design, categorizing accounts by age, value, and risk profile. This enables targeted interventions, ensuring that each account is handled with the appropriate level of intensity and expertise.',
                    ],
                    [
                        'title' => 'Risk-based action plans and escalation',
                        'body' => 'We implement action plans based on risk and aging buckets, applying firm but measured engagement at early stages, and structured escalation—including legal action—where recovery risk increases. Each step is deliberate, compliant, and geared toward efficient resolution.',
                    ],
                    [
                        'title' => 'Reporting and performance tracking',
                        'body' => 'Through robust reporting and performance tracking, we provide clear visibility on recovery progress, enabling informed decision-making. Our disciplined workflows and accountability structures ensure consistent performance while safeguarding client interests.',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Car Tracking',
            'slug' => 'car-tracking',
            'description' => 'Vehicle tracking device fitting, real-time monitoring, remote engine immobilization, and fleet oversight for stronger asset control.',
            'featured' => true,
        ],
        [
            'name' => 'Colldett Microfinance',
            'slug' => 'colldett-microfinance',
            'description' => 'A future financial services division focused on accessible and structured microfinance solutions for individuals and businesses.',
            'coming_soon' => true,
        ],
    ],
    'industries' => [
        'Banks',
        'Microfinance Institutions',
        'SACCOs',
        'Insurance Companies',
        'Corporates',
        'Law Firms',
        'Pharmaceuticals',
        'Manufacturing',
        'Hotel and Hospitality',
        'Real Estate',
        'Other Debt Related Services',
    ],
    'team' => [
        [
            'slug' => 'evance-odhiambo',
            'name' => 'Evance Odhiambo',
            'role' => 'Chief Executive Officer (CEO) & Managing Director',
            'department' => 'Executive Leadership',
            'image' => 'uploads/team/Evance-Odhiambo.jpg',
            'bio' => 'Evance Odhiambo provides executive leadership for Colldett Trace Limited, overseeing strategy, operations, and institutional client outcomes.',
            'experience_years' => 12,
            'location' => 'Nairobi, Kenya',
            'email' => 'evance@colldetttrace.com',
            'seo_description' => 'Profile of Evance Odhiambo, CEO and Managing Director at Colldett Trace Limited.',
            'specialties' => ['Executive Strategy', 'Institutional Relationships', 'Performance Governance'],
            'credentials' => ['Corporate Leadership', 'Recovery Oversight', 'Strategic Planning'],
            'industries' => ['Banking', 'Microfinance', 'Corporate Services'],
            'principles' => ['Integrity', 'Accountability', 'Results leadership'],
        ],
        [
            'slug' => 'yohana-baraza',
            'name' => 'Yohana Baraza',
            'role' => 'Head of Business Development',
            'department' => 'Senior Management',
            'image' => 'https://randomuser.me/api/portraits/men/83.jpg',
            'bio' => 'Yohana Baraza leads business development, partnerships, and growth strategy for Colldett Trace Limited.',
            'experience_years' => 9,
            'location' => 'Nairobi, Kenya',
            'email' => 'yohana@colldetttrace.com',
            'seo_description' => 'Profile of Yohana Baraza, Head of Business Development at Colldett Trace Limited.',
            'specialties' => ['Partnership Development', 'Client Acquisition', 'Market Expansion'],
            'credentials' => ['Business Development', 'Strategic Accounts', 'Commercial Planning'],
            'industries' => ['Banking', 'Insurance', 'Corporate'],
            'principles' => ['Client value', 'Strategic growth', 'Collaborative leadership'],
        ],
        [
            'slug' => 'brigit-achieng',
            'name' => "Brigit Achieng'",
            'role' => 'Head of Legal Litigation',
            'department' => 'Senior Management',
            'image' => 'https://randomuser.me/api/portraits/women/22.jpg',
            'bio' => "Brigit Achieng' leads legal litigation strategy and legal-recovery alignment for escalated matters.",
            'experience_years' => 11,
            'location' => 'Nairobi, Kenya',
            'email' => 'brigit@colldetttrace.com',
            'seo_description' => "Profile of Brigit Achieng', Head of Legal Litigation at Colldett Trace Limited.",
            'specialties' => ['Legal Recovery Strategy', 'Litigation Oversight', 'Enforcement Coordination'],
            'credentials' => ['Legal Litigation Leadership', 'Recovery-Legal Coordination', 'Dispute Resolution'],
            'industries' => ['Banking', 'Insurance', 'Commercial Recovery'],
            'principles' => ['Legal precision', 'Ethical practice', 'Outcome accountability'],
        ],
        [
            'slug' => 'elisha-gogi',
            'name' => 'Elisha Gogi',
            'role' => 'Head of Insurance & Risk',
            'department' => 'Senior Management',
            'image' => 'https://randomuser.me/api/portraits/men/28.jpg',
            'bio' => 'Elisha Gogi leads insurance tracing and risk-focused recovery interventions across complex files.',
            'experience_years' => 9,
            'location' => 'Nairobi, Kenya',
            'email' => 'elisha@colldetttrace.com',
            'seo_description' => 'Profile of Elisha Gogi, Head of Insurance & Risk at Colldett Trace Limited.',
            'specialties' => ['Insurance Tracing', 'Risk Assessment', 'Claims Recovery Support'],
            'credentials' => ['Insurance Recovery Operations', 'Risk Controls', 'Case Governance'],
            'industries' => ['Insurance', 'Asset Finance', 'Corporate Risk'],
            'principles' => ['Risk awareness', 'Data-led execution', 'Compliance discipline'],
        ],
        [
            'slug' => 'james-mwalo-kodieny-ogw',
            'name' => 'James Mwalo Kodieny, OGW',
            'role' => 'Head of Operations',
            'department' => 'Senior Management',
            'image' => 'https://randomuser.me/api/portraits/men/64.jpg',
            'bio' => 'James Mwalo Kodieny, OGW oversees operational systems, delivery standards, and execution quality across the recovery function.',
            'experience_years' => 12,
            'location' => 'Nairobi, Kenya',
            'email' => 'james.kodieny@colldetttrace.com',
            'seo_description' => 'Profile of James Mwalo Kodieny, OGW, Head of Operations at Colldett Trace Limited.',
            'specialties' => ['Operations Oversight', 'Service Delivery Management', 'Recovery Performance Controls'],
            'credentials' => ['Operational Governance', 'Recovery Systems Leadership', 'Institutional Coordination'],
            'industries' => ['Banking', 'Corporate Debt', 'Commercial Recovery'],
            'principles' => ['Operational excellence', 'Consistency', 'Accountability'],
        ],
        [
            'slug' => 'daglaus-omondi',
            'name' => 'Daglaus Omondi',
            'role' => 'Manager - Debt Recovery',
            'department' => 'Debt Recovery Management Team',
            'image' => 'uploads/team/Daglas.png',
            'bio' => 'Daglaus Omondi manages debt recovery execution and field coordination for assigned portfolio segments.',
            'experience_years' => 8,
            'location' => 'Nairobi, Kenya',
            'email' => 'daglaus@colldetttrace.com',
            'seo_description' => 'Profile of Daglaus Omondi, Manager - Debt Recovery at Colldett Trace Limited.',
            'specialties' => ['Portfolio Recovery', 'Tracing Support', 'Recovery Escalation'],
            'credentials' => ['Collections Management', 'Field Recovery Coordination', 'Case Monitoring'],
            'industries' => ['Asset Finance', 'Insurance', 'Corporate Debt'],
            'principles' => ['Case discipline', 'Timely execution', 'Results focus'],
        ],
        [
            'slug' => 'phoebe-onyango',
            'name' => 'Phoebe Onyango',
            'role' => 'Manager - Debt Recovery',
            'department' => 'Debt Recovery Management Team',
            'image' => 'uploads/team/Phoebe-Onyango.jpg',
            'bio' => 'Phoebe Onyango manages follow-up structures and repayment pipelines for debt recovery accounts.',
            'experience_years' => 7,
            'location' => 'Nairobi, Kenya',
            'email' => 'phoebe@colldetttrace.com',
            'seo_description' => 'Profile of Phoebe Onyango, Manager - Debt Recovery at Colldett Trace Limited.',
            'specialties' => ['Debtor Engagement', 'Recovery Follow-ups', 'Settlement Coordination'],
            'credentials' => ['Collections Performance Management', 'Repayment Monitoring', 'Recovery Reporting'],
            'industries' => ['Microfinance', 'Retail Credit', 'SME Lending'],
            'principles' => ['Transparency', 'Consistency', 'Client focus'],
        ],
        [
            'slug' => 'julius-dondi',
            'name' => 'Julius Dondi',
            'role' => 'Manager - Debt Recovery',
            'department' => 'Debt Recovery Management Team',
            'image' => 'uploads/team/Julius-Dondi.jpg',
            'bio' => 'Julius Dondi manages structured debt recovery workflows for institutional and commercial portfolios.',
            'experience_years' => 9,
            'location' => 'Nairobi, Kenya',
            'email' => 'julius@colldetttrace.com',
            'seo_description' => 'Profile of Julius Dondi, Manager - Debt Recovery at Colldett Trace Limited.',
            'specialties' => ['Portfolio Recovery', 'Case Prioritization', 'Client Reporting'],
            'credentials' => ['Debt Recovery Operations', 'Negotiation Management', 'Regulatory Compliance'],
            'industries' => ['Banking', 'Microfinance', 'Corporate Lending'],
            'principles' => ['Lawful collection practice', 'Timely communication', 'Outcome accountability'],
        ],
        [
            'slug' => 'samwel-mogire',
            'name' => 'Samwel Mogire',
            'role' => 'Manager - Debt Recovery',
            'department' => 'Debt Recovery Management Team',
            'image' => 'uploads/team/Samwel-Mogire.jpg',
            'bio' => 'Samwel Mogire manages debt recovery execution and escalation for field and office case streams.',
            'experience_years' => 11,
            'location' => 'Nairobi, Kenya',
            'email' => 'samwel@colldetttrace.com',
            'seo_description' => 'Profile of Samwel Mogire, Manager - Debt Recovery at Colldett Trace Limited.',
            'specialties' => ['Debt Recovery Management', 'Debtor Engagement', 'Escalation Planning'],
            'credentials' => ['Field Recovery Coordination', 'Collections Workflow Design', 'Case Escalation Handling'],
            'industries' => ['SACCOs', 'Insurance', 'Corporate Services'],
            'principles' => ['Respectful engagement', 'Structured follow-up', 'Evidence-based reporting'],
        ],
        [
            'slug' => 'ann-wambui',
            'name' => 'Ann Wambui',
            'role' => 'Manager - Debt Recovery',
            'department' => 'Debt Recovery Management Team',
            'image' => 'uploads/team/Ann-Wambui.jpg',
            'bio' => 'Ann Wambui manages debt recovery accounts with a focus on follow-up discipline and repayment outcomes.',
            'experience_years' => 6,
            'location' => 'Nairobi, Kenya',
            'email' => 'ann.wambui@colldetttrace.com',
            'seo_description' => 'Profile of Ann Wambui, Manager - Debt Recovery at Colldett Trace Limited.',
            'specialties' => ['Debt Recovery Management', 'Case Monitoring', 'Payment Commitments'],
            'credentials' => ['Collections Operations', 'Recovery Case Administration', 'Client Communication'],
            'industries' => ['Retail Credit', 'Microfinance', 'Corporate Accounts'],
            'principles' => ['Professional engagement', 'Timely updates', 'Confidential handling'],
        ],
    ],

    /*
    | Team photos: these patterns are treated as generic placeholders; the site shows initials avatars instead.
    */
    'team_generic_image_hosts' => [
        'randomuser.me',
        'pravatar.cc',
        'i.pravatar.cc',
        'ui-avatars.com',
    ],
    'team_generic_image_filename_fragments' => [
        'placeholder',
        'generic',
        'silhouette',
        'default-avatar',
    ],

    'insights' => [
        [
            'slug' => 'improving-recovery-outcomes-through-early-case-segmentation',
            'title' => 'Improving Recovery Outcomes Through Early Case Segmentation',
            'excerpt' => 'How operational triage and risk scoring improve debt recovery speed and portfolio performance.',
            'date' => 'April 2026',
            'content' => [
                'Early case segmentation improves recovery performance by assigning each account to the right strategy at the right time. Instead of applying one collection method across all debtors, segmented workflows prioritize accounts by risk, age, value, and legal complexity.',
                'For institutions managing large portfolios, this creates faster action on high-potential accounts, clearer escalation for difficult files, and stronger reporting discipline for management decisions. It also reduces operational waste by matching team effort to expected recovery value.',
                'At Colldett Trace Limited, segmentation is supported by field intelligence, tracing data, and compliance-led execution. The result is better liquidation rates, shorter recovery cycles, and more predictable portfolio outcomes.',
            ],
        ],
        [
            'slug' => 'why-asset-tracing-matters-in-commercial-disputes',
            'title' => 'Why Asset Tracing Matters in Commercial Disputes',
            'excerpt' => 'A practical look at how asset visibility supports strategic legal and recovery decisions.',
            'date' => 'March 2026',
            'content' => [
                'Asset tracing provides practical leverage in commercial disputes by clarifying what can be recovered and where enforcement actions may be effective. Without asset visibility, negotiations and legal processes often become slower, riskier, and less predictable.',
                'A structured tracing process helps legal and recovery teams identify movable and immovable assets, validate ownership signals, and determine enforceability pathways before significant legal spend is committed.',
                'By integrating tracing results into legal strategy, clients make stronger settlement decisions, improve enforcement readiness, and protect value in complex disputes.',
            ],
        ],
        [
            'slug' => 'vehicle-tracking-and-enforcement-readiness',
            'title' => 'Vehicle Tracking and Enforcement Readiness',
            'excerpt' => 'Best practices for real-time monitoring, immobilization controls, and incident response workflows.',
            'date' => 'February 2026',
            'content' => [
                'Vehicle tracking is most effective when it is managed as an operational control system, not just a device installation. Real-time visibility, alert management, and escalation procedures must be aligned for rapid incident response.',
                'For lenders and fleet operators, enforcement readiness depends on accurate location data, disciplined monitoring, and clear authorization rules for immobilization and recovery action.',
                'A mature tracking program combines technology, field execution, and reporting governance to improve asset security and reduce recovery delays.',
            ],
        ],
    ],

    /*
    | Admin panel session key. Panel PIN is stored hashed in storage (see AdminAccess).
    | Legacy ADMIN_ACCESS_SECRET / ADMIN_ACCESS_PIN in .env are only used once to migrate
    | to the stored PIN on first successful login, then ignored.
    */
    'admin' => [
        'access_secret' => env('ADMIN_ACCESS_SECRET', ''),
        'access_pin' => env('ADMIN_ACCESS_PIN', ''),
        'session_key' => 'admin_panel_authenticated',
    ],

    /*
    | Public site SEO defaults (overridable per page in SiteController).
    | SEO_INDEX=false in .env forces noindex for staging.
    */
    'seo' => [
        'locale' => 'en_KE',
        'robots_default' => 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1',
        'twitter_site' => null,
        'twitter_creator' => null,
        'geo_region' => 'KE',
        'geo_placename' => 'Nairobi',
    ],

    /*
    | Progressive Web App (manifests in /public use icon path below; service worker: /sw.js).
    */
    'pwa' => [
        'icon' => 'uploads/favicon.png',
        'site' => [
            'name' => 'Colldett Trace Limited',
            'short_name' => 'Colldett',
            'description' => 'Professional recovery and tracing — add to your home screen for quick access.',
            'theme_color' => '#215e1d',
            'background_color' => '#f7f9f8',
        ],
        'admin' => [
            'name' => 'Colldett Admin',
            'short_name' => 'Admin',
            'description' => 'Colldett administration console — install for faster access.',
            'theme_color' => '#0f4c81',
            'background_color' => '#f4f7fb',
        ],
    ],
];
