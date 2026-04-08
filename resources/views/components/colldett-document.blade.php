@props([
    'documentTitle' => 'Document',
    'reference' => null,
    'showDocTitle' => true,
    'logoUrl' => null,
    'forPdf' => false,
])

@php
    use App\Support\AdminStoredSettings;
    $theme = AdminStoredSettings::documentTheme();
    $letterhead = $theme['letterhead_image'] ?? null;
    $letterheadPath = $letterhead ? public_path($letterhead) : null;
    $hasLetterhead = ! $forPdf && $letterheadPath && is_file($letterheadPath);
    $addressLines = $theme['address_lines'] ?? [];
    $website = $theme['website'] ?? '';
    $phones = $theme['phones'] ?? '';
    $email = AdminStoredSettings::companyEmail();
    $websiteUrl = $website ? (str_starts_with($website, 'http') ? $website : 'https://' . ltrim($website, '/')) : '';
    $documentLogo = AdminStoredSettings::companyLogoRelativePath();
@endphp

<div
    class="colldett-document {{ $hasLetterhead ? 'colldett-document--letterhead-bg' : '' }}"
    @if($hasLetterhead) style="background-image: url('{{ asset($letterhead) }}');" @endif
>
    <header class="colldett-document__header">
        <div class="colldett-document__corner" aria-hidden="true"></div>
        <div class="colldett-document__brand">
            <img class="colldett-document__logo" src="{{ $logoUrl ?? asset($documentLogo) }}" alt="Colldett Trace Limited">
        </div>
        <address class="colldett-document__address">
            @foreach($addressLines as $line)
                <div>{{ $line }}</div>
            @endforeach
        </address>
    </header>

    <div class="colldett-document__rule" aria-hidden="true"></div>

    <main class="colldett-document__body {{ $showDocTitle ? '' : 'colldett-document__body--no-doc-title' }}">
        @if($showDocTitle)
            <h2 class="colldett-document__doc-title">
                {{ $documentTitle }}
                @if($reference)
                    <span class="font-semibold text-slate-600"> — {{ $reference }}</span>
                @endif
            </h2>
        @endif
        {{ $slot }}
    </main>

    <div class="colldett-document__footer-wrap">
        <x-colldett-footer-accent wrapper-class="colldett-document__footer-accent" />
        <div class="colldett-document__footer-rule-thin" aria-hidden="true"></div>

        <footer class="colldett-document__footer">
            <div class="colldett-document__footer-grid">
                <div class="colldett-document__footer-item">
                    <span class="colldett-document__footer-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"></path>
                        </svg>
                    </span>
                    <span class="colldett-document__footer-text">
                        @if($websiteUrl)
                            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener">{{ $website }}</a>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="colldett-document__footer-item colldett-document__footer-item--center">
                    <span class="colldett-document__footer-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </span>
                    <span class="colldett-document__footer-text">{{ $phones }}</span>
                </div>
                <div class="colldett-document__footer-item colldett-document__footer-item--end">
                    <span class="colldett-document__footer-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <span class="colldett-document__footer-text">
                        @if($email)
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
        </footer>
    </div>
</div>
