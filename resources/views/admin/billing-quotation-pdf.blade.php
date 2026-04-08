<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $values['number'] ?? 'Quotation' }}</title>
    <style>
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
    <x-colldett-document
        document-title="Quotation"
        :reference="null"
        :show-doc-title="false"
        :for-pdf="true"
        :logo-url="$logoUrl"
    >
        @include('admin.partials.quotation-document-content', ['values' => $values, 'pdfMode' => true])
    </x-colldett-document>
</body>
</html>
