<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm 2cm 1.5cm;
            size: A4;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 9pt;
            line-height: 1.3;
        }

        /* Header */
        .header {
            width: 100%;
            margin-bottom: 15px;
        }

        .header-left {
            float: left;
            width: 50%;
        }

        .header-right {
            float: right;
            width: 50%;
            text-align: right;
            padding-top: 30px;
        }

        .logo {
            max-height: 70px;
            max-width: 180px;
        }

        .date-location {
            font-size: 10pt;
            color: #333;
        }

        .clearfix {
            clear: both;
        }

        /* Document Title */
        .document-title {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            margin: 20px 0 15px 0;
        }

        /* Client Section */
        .client-section {
            margin-bottom: 15px;
            text-align: right;
        }

        .client-label {
            font-size: 10pt;
            color: #333;
            text-decoration: underline;
        }

        .client-name {
            font-size: 10pt;
            font-weight: bold;
            color: #ea580c;
        }

        /* Main Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }

        .invoice-table th {
            background-color: #ea580c;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #ea580c;
        }

        .invoice-table td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }

        .invoice-table td:first-child {
            text-align: center;
            width: 5%;
        }

        .invoice-table td:nth-child(2) {
            width: 40%;
        }

        .invoice-table td:nth-child(3) {
            text-align: center;
            width: 8%;
        }

        .invoice-table td:nth-child(4) {
            text-align: center;
            width: 8%;
        }

        .invoice-table td:nth-child(5),
        .invoice-table td:last-child {
            text-align: right;
            width: 17%;
        }

        /* Section Headers in Table */
        .section-header td {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center !important;
            font-size: 8pt;
            padding: 4px;
        }

        /* Subtotals and Totals */
        .subtotal-row td {
            font-weight: bold;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .discount-row td {
            color: #ea580c;
        }

        .total-row td {
            background-color: #ea580c;
            color: white !important;
            font-weight: bold;
            font-size: 9pt;
            padding: 8px 4px;
            border: 1px solid #ea580c;
        }

        /* Amount in Words */
        .amount-words {
            margin: 12px 0;
            font-size: 9pt;
            text-align: justify;
        }

        /* Notes Section */
        .notes-section {
            margin: 10px 0;
            font-size: 8pt;
            color: #555;
        }

        .notes-section p {
            margin: 3px 0;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 20px;
            text-align: right;
        }

        .signature-title {
            font-size: 11pt;
            font-weight: bold;
            color: #ea580c;
        }

        /* Payment Conditions */
        .payment-conditions {
            margin-top: 15px;
            font-size: 8pt;
        }

        .payment-conditions strong {
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #ea580c;
            padding: 8px 0;
            text-align: center;
            font-size: 7pt;
            color: #555;
            line-height: 1.4;
        }

        /* Utilities */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Header: Logo + Date -->
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('logo_wmc_orange.png')))
                <img src="{{ public_path('logo_wmc_orange.png') }}" class="logo" alt="Logo">
            @else
                <div style="font-size: 18pt; font-weight: bold; color: #ea580c;">
                    {{ $company->name ?? config('app.name', 'ERP-WMC') }}
                </div>
            @endif
        </div>
        <div class="header-right">
            <div class="date-location">
                {{ $company->city ?? 'Abidjan' }}, {{ ($invoice->order_date ?? now())->translatedFormat('d F Y') }}
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        @if($invoice->type === 'proforma')
            FACTURE PROFORMA {{ $invoice->reference }}
        @else
            FACTURE {{ $invoice->reference }}
        @endif
    </div>

    <!-- Client -->
    <div class="client-section">
        <span class="client-label">Client</span> : <span class="client-name">{{ $invoice->contact->company_name ?? $invoice->contact->display_name }}</span>
    </div>

    <!-- Invoice Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Désignation</th>
                <th>Unité</th>
                <th>Qté</th>
                <th>Prix Unitaire</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody>
            @php
                $lineNumber = 0;
                $currentSection = null;
            @endphp

            @foreach($invoice->lines as $line)
                @php $lineNumber++; @endphp

                {{-- Section Header if category changed --}}
                @if(isset($line->category) && $line->category !== $currentSection)
                    @php $currentSection = $line->category; @endphp
                    <tr class="section-header">
                        <td colspan="6">{{ $line->category }}</td>
                    </tr>
                @endif

                <tr>
                    <td>{{ $lineNumber }}</td>
                    <td>{{ $line->product->name ?? $line->description ?? 'Article' }}</td>
                    <td>{{ $line->unit ?? 'PCS' }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>{{ number_format($line->unit_price, 0, ',', ' ') }}</td>
                    <td>{{ number_format($line->total_amount, 0, ',', ' ') }}</td>
                </tr>
            @endforeach

            {{-- Subtotal Row --}}
            <tr class="subtotal-row">
                <td colspan="5" class="text-right">Montant</td>
                <td class="text-right">{{ number_format($invoice->subtotal ?? $invoice->total_amount, 0, ',', ' ') }}</td>
            </tr>

            {{-- Discount Row (if applicable) --}}
            @if(($invoice->discount_percentage ?? 0) > 0 || ($invoice->discount_amount ?? 0) > 0)
                <tr class="discount-row">
                    <td colspan="5" class="text-right">
                        Remise {{ $invoice->discount_percentage ?? '' }}{{ $invoice->discount_percentage ? '%' : '' }}
                    </td>
                    <td class="text-right">{{ number_format($invoice->discount_amount ?? 0, 0, ',', ' ') }}</td>
                </tr>
                <tr class="subtotal-row">
                    <td colspan="5" class="text-right">Montant Total</td>
                    <td class="text-right">{{ number_format(($invoice->subtotal ?? $invoice->total_amount) - ($invoice->discount_amount ?? 0), 0, ',', ' ') }}</td>
                </tr>
            @endif

            {{-- Tax Row (if applicable) --}}
            @if(($invoice->tax_amount ?? 0) > 0)
                <tr>
                    <td colspan="5" class="text-right">TVA ({{ $invoice->tax_rate ?? 18 }}%)</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount, 0, ',', ' ') }}</td>
                </tr>
            @endif

            {{-- Grand Total --}}
            <tr class="total-row">
                <td colspan="5" class="text-right">Montant Total A Payer</td>
                <td class="text-right">{{ number_format($invoice->total_amount_ttc ?? $invoice->total_amount, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Amount in Words -->
    <div class="amount-words">
        Arrêtée la présente facture {{ $invoice->type === 'proforma' ? 'pro-forma ' : '' }}à la somme de :
        <strong>{{ ucfirst($amountInWords ?? 'zéro') }} ({{ number_format($invoice->total_amount_ttc ?? $invoice->total_amount, 0, ',', ' ') }}) FCFA.</strong>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
        <div class="notes-section">
            <strong>NB :</strong>
            @foreach(explode("\n", $invoice->notes) as $note)
                @if(trim($note))
                    <p>- {{ trim($note) }}</p>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-title">DIRECTION DES OPERATIONS</div>
    </div>

    <!-- Payment Conditions -->
    @if($invoice->payment_terms ?? $invoice->terms)
        <div class="payment-conditions">
            <p><strong>Conditions de Paiement :</strong></p>
            <p>{{ $invoice->payment_terms ?? $invoice->terms }}</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        {{ $company->legal_form ?? 'SARL' }} Au Capital de : {{ number_format($company->capital ?? 1000000, 0, ',', '.') }} FCFA. {{ $company->address ?? '' }}<br>
        Tel : {{ $company->phone ?? '' }}<br>
        RCCM n° {{ $company->rccm ?? '' }}. Compte bancaire : {{ $company->bank_name ?? '' }} {{ $company->bank_account ?? '' }}<br>
        E-mail : {{ $company->email ?? '' }}
    </div>
</body>

</html>
