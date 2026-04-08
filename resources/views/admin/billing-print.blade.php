<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @if($module === 'invoices')
            {{ $values['number'] ?? 'Invoice' }}
        @elseif($module === 'payments')
            {{ $values['payment_id'] ?? 'Payment receipt' }}
        @else
            {{ $meta['singular'] }} — print
        @endif
        | {{ config('colldett.company.name', 'Colldett') }}
    </title>
    @vite(['resources/css/document-theme.css'])
    <style>
        /* Match preview: soft page background on screen only */
        .billing-print-shell {
            margin: 0;
            min-height: 100vh;
            background: #f1f5f9;
        }
        .billing-print-toolbar {
            font-family: ui-sans-serif, system-ui, sans-serif;
            max-width: 52rem;
            margin: 0 auto;
            padding: 1rem 1rem 0.75rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
        }
        .billing-print-toolbar a,
        .billing-print-toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
        }
        .billing-print-toolbar a:hover,
        .billing-print-toolbar button:hover {
            background: #f8fafc;
        }
        .billing-print-toolbar .billing-print-toolbar__primary {
            background: #1a4d3a;
            border-color: #1a4d3a;
            color: #fff;
        }
        .billing-print-toolbar .billing-print-toolbar__primary:hover {
            background: #0f3326;
            border-color: #0f3326;
        }
        .billing-print-doc-wrap {
            padding: 0 1rem 2rem;
        }
        /*
         * Pin the letterhead footer to the bottom of the sheet when content is short
         * (toolbar is .no-print — full height is available to the document).
         */
        @media print {
            html,
            body {
                height: 100%;
                margin: 0 !important;
            }
            .billing-print-shell {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                background: #fff !important;
            }
            .billing-print-doc-wrap {
                flex: 1 1 auto;
                display: flex;
                flex-direction: column;
                padding: 0 !important;
                min-height: 0;
            }
            .billing-print-doc-wrap .colldett-document {
                flex: 1 1 auto;
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
            .billing-print-doc-wrap .colldett-document__header,
            .billing-print-doc-wrap .colldett-document__rule {
                flex: 0 0 auto;
            }
            .billing-print-doc-wrap .colldett-document__body {
                flex: 1 1 auto !important;
                min-height: 1px !important;
                overflow: visible !important;
            }
            .billing-print-doc-wrap .colldett-document__footer-wrap {
                flex: 0 0 auto !important;
                margin-top: auto !important;
            }

            /* Readable on paper: larger type, stronger contrast, crisp borders */
            .billing-print-doc-wrap {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                text-rendering: optimizeLegibility;
            }
            .billing-print-doc-wrap .colldett-document {
                color: #111827 !important;
            }
            .billing-print-doc-wrap .colldett-document__address {
                color: #1f2937 !important;
                font-size: 7.75pt !important;
                font-weight: 700 !important;
                letter-spacing: 0.05em !important;
            }
            .billing-print-doc-wrap .colldett-document__footer-grid,
            .billing-print-doc-wrap .colldett-document__footer-text,
            .billing-print-doc-wrap .colldett-document__footer-text a {
                color: #145032 !important;
                font-size: 10pt !important;
                font-weight: 600 !important;
                line-height: 1.45 !important;
            }
            .billing-print-doc-wrap .colldett-invoice {
                font-size: 11pt !important;
                line-height: 1.55 !important;
                color: #111827 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__number-bar {
                font-size: 13pt !important;
                font-weight: 800 !important;
                color: #0f172a !important;
                background: #f1f5f9 !important;
                border: 1px solid #cbd5e1 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__dates,
            .billing-print-doc-wrap .colldett-invoice__payment {
                font-size: 10.5pt !important;
                color: #111827 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__date-label {
                font-weight: 700 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__to-title,
            .billing-print-doc-wrap .colldett-invoice__payment-title {
                font-size: 11pt !important;
                font-weight: 800 !important;
                color: #0f172a !important;
            }
            .billing-print-doc-wrap .colldett-invoice__to-lines,
            .billing-print-doc-wrap .colldett-invoice__payment-line {
                color: #1e293b !important;
                font-size: 10.5pt !important;
            }
            .billing-print-doc-wrap .colldett-invoice__payment-heading {
                font-size: 10pt !important;
                font-weight: 800 !important;
                color: #0f172a !important;
            }
            .billing-print-doc-wrap .colldett-invoice__payment-note {
                font-size: 9.5pt !important;
                color: #334155 !important;
                font-weight: 600 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__table {
                font-size: 10.5pt !important;
            }
            .billing-print-doc-wrap .colldett-invoice__table th {
                font-size: 8.5pt !important;
                font-weight: 800 !important;
                color: #1e293b !important;
                background: #f1f5f9 !important;
                border: 1px solid #94a3b8 !important;
                letter-spacing: 0.05em !important;
            }
            .billing-print-doc-wrap .colldett-invoice__table td {
                border: 1px solid #cbd5e1 !important;
                color: #111827 !important;
                padding: 0.5rem 0.65rem !important;
            }
            .billing-print-doc-wrap .colldett-invoice__summary-label {
                color: #1e293b !important;
                font-weight: 700 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__summary-row--total .colldett-invoice__summary-label,
            .billing-print-doc-wrap .colldett-invoice__summary-row--total .colldett-invoice__cell-num {
                font-size: 11pt !important;
                color: #0f172a !important;
            }
            .billing-print-doc-wrap .colldett-invoice__notes-label {
                font-size: 8.5pt !important;
                font-weight: 800 !important;
                color: #334155 !important;
            }
            .billing-print-doc-wrap .colldett-invoice__notes-body {
                font-size: 10.5pt !important;
                color: #1e293b !important;
            }
            .billing-print-doc-wrap .colldett-invoice__generated {
                font-size: 9pt !important;
                color: #64748b !important;
                font-weight: 600 !important;
            }
            .billing-print-doc-wrap .colldett-document-table {
                font-size: 10.5pt !important;
            }
            .billing-print-doc-wrap .colldett-document-table th {
                color: #1e293b !important;
                font-size: 8.5pt !important;
                font-weight: 800 !important;
                border: 1px solid #94a3b8 !important;
                background: #f1f5f9 !important;
            }
            .billing-print-doc-wrap .colldett-document-table td {
                color: #111827 !important;
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="billing-print-shell">
    <div class="billing-print-toolbar no-print">
        <button type="button" onclick="window.print()">Print</button>
        <a href="{{ route('admin.billing.module.preview', [$module, $recordId]) }}">Back to preview</a>
    </div>
    <div class="billing-print-doc-wrap">
        @include('admin.partials.billing-preview-document', [
            'meta' => $meta,
            'module' => $module,
            'recordId' => $recordId,
            'values' => $values,
            'showPreviewFooterActions' => false,
        ])
    </div>
    @if(request()->boolean('autoprint'))
        <script>
            window.addEventListener('load', function () {
                window.requestAnimationFrame(function () {
                    window.print();
                });
            });
        </script>
    @endif
</body>
</html>
