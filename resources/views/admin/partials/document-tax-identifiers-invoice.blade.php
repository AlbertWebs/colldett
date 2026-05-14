@php
    use App\Support\AdminStoredSettings;
    use App\Support\ClientDirectory;
    $companyKra = AdminStoredSettings::companyKraPin();
    $clientKra = ClientDirectory::clientTaxPinForDocument($values);
@endphp
<div><span class="colldett-invoice__date-label">Company KRA PIN:</span> {{ $companyKra !== '' ? $companyKra : '—' }}</div>
<div><span class="colldett-invoice__date-label">Client KRA PIN / Tax ID:</span> {{ $clientKra !== '' ? $clientKra : '—' }}</div>
