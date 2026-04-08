<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $values['number'] ?? 'Invoice' }}</title>
    <style>
        /* A4 portrait — page box matches ISO 210×297mm; content fills area inside margins */
        @page {
            size: A4 portrait;
            margin: 12mm 9mm 16mm 9mm;
        }
        * {
            box-sizing: border-box;
        }
        html {
            font-size: 16px;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: none;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            background: #fff;
            color: #4a5c54;
        }
        {!! $documentChromeCss !!}
        {!! $invoiceBodyCss !!}
        /* Use full printable width; drop “card” chrome that wastes horizontal space */
        .colldett-document {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>
    {{-- Same shell + invoice body as admin preview (x-colldett-document + partial) --}}
    <x-colldett-document
        document-title="Invoice"
        :reference="null"
        :show-doc-title="false"
        :for-pdf="true"
        :logo-url="$logoUrl"
    >
        @include('admin.partials.invoice-document-content', ['values' => $values, 'pdfMode' => true])
    </x-colldett-document>
</body>
</html>
