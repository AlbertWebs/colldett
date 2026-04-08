<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $values['number'] ?? $meta['singular'] }}</title>
    @php
        use App\Support\AdminStoredSettings;
        $theme = AdminStoredSettings::documentTheme();
        $addressLines = $theme['address_lines'] ?? [];
        $website = $theme['website'] ?? '';
        $phones = $theme['phones'] ?? '';
        $email = AdminStoredSettings::companyEmail();
    @endphp
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 9mm 16mm 9mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #334155;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: none;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .header-left { display: table-cell; vertical-align: top; width: 55%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; width: 45%; }
        .logo { height: 48px; width: auto; max-width: 200px; }
        .addr {
            font-size: 7pt;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            line-height: 1.45;
        }
        .rule {
            height: 3px;
            background: linear-gradient(90deg, #e8d48b, #c9a227, #e8d48b);
            margin: 10px 0 16px;
        }
        h1 {
            font-size: 12pt;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #0f3326;
            margin: 0 0 14px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        th {
            text-align: left;
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            padding: 8px 10px;
            background: #f8faf9;
            border: 1px solid #e2e8f0;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            color: #0f172a;
        }
        /* Letterhead-style footer: accent + thin gold rule + 3-column contacts */
        .footer-wrap { margin-top: 28px; }
        .colldett-footer-accent { padding: 0 0 8px 0; }
        .colldett-footer-accent__svg {
            display: block;
            height: 7px;
            width: auto;
        }
        .footer-rule-thin {
            height: 2px;
            background: linear-gradient(90deg, #e8d48b, #c9a227, #e8d48b);
            margin: 0;
        }
        .footer {
            padding: 10px 0 0;
            font-size: 8pt;
            font-weight: 600;
            color: #1a4d3a;
        }
        .footer-row { display: table; width: 100%; table-layout: fixed; }
        .footer-cell {
            display: table-cell;
            width: 33%;
            padding: 4px 6px 0 0;
            vertical-align: top;
        }
        .footer-cell-center { text-align: center; }
        .footer-cell-end { text-align: right; padding-right: 0; }
        /* Demand letter layout (matches web preview) */
        .colldett-demand-letter { font-size: 10pt; line-height: 1.55; color: #0f172a; }
        .colldett-demand-letter__meta { margin-bottom: 14pt; padding-bottom: 10pt; border-bottom: 1px solid #e2e8f0; }
        .colldett-demand-letter__meta-row { margin-bottom: 6pt; }
        .colldett-demand-letter__meta-row--grid { display: table; width: 100%; table-layout: fixed; margin-bottom: 0; }
        .colldett-demand-letter__meta-row--grid > div { display: table-cell; width: 33%; padding-right: 8pt; vertical-align: top; }
        .colldett-demand-letter__meta-label { display: block; font-size: 7pt; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b; margin-bottom: 2pt; }
        .colldett-demand-letter__meta-value { font-weight: 600; color: #0f172a; }
        .colldett-demand-letter__subject-block { margin-bottom: 12pt; padding: 8pt 10pt; background: #f8fafc; border: 1px solid #e2e8f0; }
        .colldett-demand-letter__subject-label { font-size: 7pt; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b; margin: 0 0 4pt; }
        .colldett-demand-letter__subject-text { font-size: 10.5pt; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.45; }
        .colldett-demand-letter__body { font-size: 10pt; line-height: 1.65; color: #1e293b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if(!empty($logoDataUri))
                <img class="logo" src="{{ $logoDataUri }}" alt="">
            @endif
        </div>
        <div class="header-right">
            <div class="addr">
                @foreach($addressLines as $line)
                    {{ $line }}<br>
                @endforeach
            </div>
        </div>
    </div>
    <div class="rule"></div>

    <h1>{{ $docTitle }} @if($docRef)<span style="font-weight:600;color:#475569;">{{ $docRef }}</span>@endif</h1>

    @if(($module ?? '') === 'demand')
        @include('admin.partials.demand-letter-preview', ['values' => $values])
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:32%;">Field</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meta['fields'] as $field)
                    @php $value = $values[$field['name']] ?? '—'; @endphp
                    <tr>
                        <td>{{ $field['label'] }}</td>
                        <td>{!! nl2br(e((string) $value)) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer-wrap">
        <x-colldett-footer-accent />
        <div class="footer-rule-thin"></div>
        <div class="footer">
            <div class="footer-row">
                <div class="footer-cell">{{ $website ?: '—' }}</div>
                <div class="footer-cell footer-cell-center">{{ $phones }}</div>
                <div class="footer-cell footer-cell-end">{{ $email ?: '—' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
