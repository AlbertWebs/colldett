<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactDetail;
use App\Support\AdminStoredSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const STORAGE_PATH = 'admin/settings.json';

    public function index(): View
    {
        $saved = $this->readSettings();
        $company = config('colldett.company', []);
        $siteEmail = $saved['company_email'] ?? ($company['email'] ?? null);
        $siteDomain = $siteEmail && str_contains($siteEmail, '@')
            ? substr(strrchr($siteEmail, '@'), 1)
            : request()->getHost();

        $settings = [
            'company_name' => $saved['company_name'] ?? ($company['name'] ?? ''),
            'company_tagline' => $saved['company_tagline'] ?? ($company['tagline'] ?? ''),
            'company_email' => $siteEmail ?? '',
            'company_phone' => $saved['company_phone'] ?? ($company['phone'] ?? ''),
            'company_address' => $saved['company_address'] ?? ($company['address'] ?? ''),
            'company_domain' => $saved['company_domain'] ?? $siteDomain,
            'company_description' => $saved['company_description'] ?? ($company['description'] ?? ''),
            'company_logo' => $this->resolveMediaUrl($saved['company_logo'] ?? 'uploads/logo.png'),
            'footer_logo' => $this->resolveMediaUrl($saved['footer_logo'] ?? 'uploads/logo-white.png'),
            'favicon' => $this->resolveMediaUrl($saved['favicon'] ?? 'uploads/favicon.png'),
            'social_facebook' => $saved['social_facebook'] ?? '',
            'social_twitter' => $saved['social_twitter'] ?? '',
            'social_linkedin' => $saved['social_linkedin'] ?? '',
            'social_instagram' => $saved['social_instagram'] ?? '',
            'social_youtube' => $saved['social_youtube'] ?? '',
            'smtp_host' => $saved['smtp_host'] ?? env('MAIL_HOST', ''),
            'smtp_credentials' => $saved['smtp_credentials'] ?? '',
            'document_prefixes' => $saved['document_prefixes'] ?? '',
            'currency_tax' => $saved['currency_tax'] ?? '',
            'regional_preferences' => $saved['regional_preferences'] ?? (config('app.timezone').' / Y-m-d / en / 15'),
            'document_website' => $saved['document_website'] ?? config('colldett.document_theme.website', ''),
            'document_phones' => $saved['document_phones'] ?? config('colldett.document_theme.phones', ''),
            'document_address_lines' => $saved['document_address_lines'] ?? implode("\n", config('colldett.document_theme.address_lines', [])),
            'document_letterhead_path' => $saved['document_letterhead_path'] ?? config('colldett.document_theme.letterhead_image', ''),
            'invoice_vat_rate' => $saved['invoice_vat_rate'] ?? (string) ((float) config('colldett.invoice.vat_rate', 0.16) * 100),
            'invoice_vat_label' => $saved['invoice_vat_label'] ?? config('colldett.invoice.vat_label', ''),
            'invoice_currency' => $saved['invoice_currency'] ?? config('colldett.invoice.currency', 'Ksh'),
            'invoice_payment_title' => $saved['invoice_payment_title'] ?? config('colldett.invoice.payment_details.title', 'Payment Details'),
            'invoice_bank_heading' => $saved['invoice_bank_heading'] ?? 'Bank',
            'invoice_payment_bank_lines' => $saved['invoice_payment_bank_lines'] ?? $this->defaultBankLinesFromConfig(),
            'invoice_other_heading' => $saved['invoice_other_heading'] ?? 'Other',
            'invoice_payment_other_lines' => $saved['invoice_payment_other_lines'] ?? '',
            'invoice_payment_note' => $saved['invoice_payment_note'] ?? config('colldett.invoice.payment_details.note', ''),
            'show_reports_nav' => filter_var($saved['show_reports_nav'] ?? false, FILTER_VALIDATE_BOOL),
            'company_map_embed_url' => old(
                'company_map_embed_url',
                Schema::hasTable('contact_details')
                    ? (ContactDetail::query()->value('map_embed_url')
                        ?? (string) (config('colldett.company.map_embed_url') ?? ''))
                    : (string) (config('colldett.company.map_embed_url') ?? '')
            ),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'document_website' => ['nullable', 'string', 'max:255'],
            'document_phones' => ['nullable', 'string', 'max:500'],
            'document_address_lines' => ['nullable', 'string', 'max:2000'],
            'document_letterhead_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'invoice_vat_rate' => ['nullable', 'string', 'max:32'],
            'invoice_vat_label' => ['nullable', 'string', 'max:255'],
            'invoice_currency' => ['nullable', 'string', 'max:32'],
            'invoice_payment_title' => ['nullable', 'string', 'max:255'],
            'invoice_bank_heading' => ['nullable', 'string', 'max:128'],
            'invoice_payment_bank_lines' => ['nullable', 'string', 'max:5000'],
            'invoice_other_heading' => ['nullable', 'string', 'max:128'],
            'invoice_payment_other_lines' => ['nullable', 'string', 'max:2000'],
            'invoice_payment_note' => ['nullable', 'string', 'max:2000'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:2000'],
            'company_map_embed_url' => ['nullable', 'string', 'max:4000'],
            'company_domain' => ['nullable', 'string', 'max:255'],
            'company_description' => ['nullable', 'string', 'max:2000'],
            'company_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'footer_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'favicon_file' => ['nullable', 'image', 'mimes:png,ico,webp', 'max:2048'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_twitter' => ['nullable', 'url', 'max:500'],
            'social_linkedin' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_credentials' => ['nullable', 'string', 'max:1000'],
            'document_prefixes' => ['nullable', 'string', 'max:500'],
            'currency_tax' => ['nullable', 'string', 'max:255'],
            'regional_preferences' => ['nullable', 'string', 'max:255'],
            'show_reports_nav' => ['nullable', 'boolean'],
        ]);

        unset($data['company_logo_file'], $data['footer_logo_file'], $data['favicon_file'], $data['document_letterhead_file']);

        $saved = $this->readSettings();
        $settings = array_merge($saved, $data);
        $settings['show_reports_nav'] = $request->boolean('show_reports_nav');

        $mapEmbedUrl = $settings['company_map_embed_url'] ?? null;
        unset($settings['company_map_embed_url']);

        if ($request->hasFile('company_logo_file')) {
            $settings['company_logo'] = $this->storeUploadedImage($request->file('company_logo_file'), 'company-logo');
        }

        if ($request->hasFile('footer_logo_file')) {
            $settings['footer_logo'] = $this->storeUploadedImage($request->file('footer_logo_file'), 'footer-logo');
        }

        if ($request->hasFile('favicon_file')) {
            $settings['favicon'] = $this->storeUploadedImage($request->file('favicon_file'), 'favicon');
        }

        if ($request->hasFile('document_letterhead_file')) {
            $settings['document_letterhead_path'] = $this->storeUploadedImage($request->file('document_letterhead_file'), 'letterhead-document');
        }

        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        AdminStoredSettings::flushCache();

        if (Schema::hasTable('contact_details')) {
            ContactDetail::syncFromAdminSettings([
                'phone' => $settings['company_phone'] ?? null,
                'email' => $settings['company_email'] ?? null,
                'address' => $settings['company_address'] ?? null,
                'map_embed_url' => $mapEmbedUrl,
            ]);
        }

        return redirect()
            ->route('admin.settings')
            ->with('status', 'Settings saved successfully.');
    }

    private function readSettings(): array
    {
        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return [];
        }

        $json = Storage::disk('local')->get(self::STORAGE_PATH);
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function storeUploadedImage(UploadedFile $file, string $prefix): string
    {
        $uploadDir = public_path('uploads');
        if (! File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = $prefix.'-'.now()->format('YmdHis').'-'.Str::random(6).'.'.$extension;
        $file->move($uploadDir, $filename);

        return 'uploads/'.$filename;
    }

    private function defaultBankLinesFromConfig(): string
    {
        $lines = config('colldett.invoice.payment_details.sections.0.lines', []);

        return is_array($lines) ? implode("\n", $lines) : '';
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
}
