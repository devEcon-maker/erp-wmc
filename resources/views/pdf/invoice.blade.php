<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .container {
            padding: 30px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #D97706;
            margin-bottom: 8px;
        }

        .company-info {
            font-size: 11px;
            color: #666;
        }

        .document-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .document-info {
            font-size: 11px;
            color: #666;
        }

        .document-info strong {
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-partial { background: #ffedd5; color: #c2410c; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }

        .parties {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .party {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .party-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-right: 15px;
        }

        .party-box.right {
            margin-right: 0;
            margin-left: 15px;
        }

        .party-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .party-name {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .party-details {
            font-size: 11px;
            color: #666;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items thead {
            background: #D97706;
        }

        table.items th {
            padding: 12px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            letter-spacing: 0.5px;
        }

        table.items th.right {
            text-align: right;
        }

        table.items th.center {
            text-align: center;
        }

        table.items tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        table.items tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table.items td {
            padding: 12px;
            font-size: 11px;
        }

        table.items td.right {
            text-align: right;
        }

        table.items td.center {
            text-align: center;
        }

        .totals-section {
            display: table;
            width: 100%;
        }

        .totals-spacer {
            display: table-cell;
            width: 60%;
        }

        .totals-box {
            display: table-cell;
            width: 40%;
        }

        table.totals {
            width: 100%;
            border-collapse: collapse;
        }

        table.totals tr {
            border-bottom: 1px solid #e5e7eb;
        }

        table.totals td {
            padding: 8px 12px;
            font-size: 11px;
        }

        table.totals td.label {
            text-align: right;
            color: #666;
        }

        table.totals td.value {
            text-align: right;
            font-weight: bold;
            width: 120px;
        }

        table.totals tr.discount td {
            color: #15803d;
        }

        table.totals tr.paid td {
            color: #15803d;
        }

        table.totals tr.remaining td {
            color: #dc2626;
        }

        table.totals tr.total {
            background: #D97706;
            border: none;
        }

        table.totals tr.total td {
            color: white;
            font-size: 14px;
            padding: 12px;
        }

        .payment-info {
            margin-top: 30px;
            padding: 15px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
        }

        .payment-title {
            font-size: 11px;
            font-weight: bold;
            color: #0369a1;
            margin-bottom: 8px;
        }

        .payment-content {
            font-size: 11px;
            color: #0c4a6e;
        }

        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 8px;
        }

        .notes-title {
            font-size: 11px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }

        .notes-content {
            font-size: 11px;
            color: #78350f;
        }

        .payments-history {
            margin-top: 20px;
        }

        .payments-history h4 {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        table.payments {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table.payments th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.payments td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.payments td.right {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 30px;
            right: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ config('app.name', 'ERP-WMC') }}</div>
                <div class="company-info">
                    123 Rue de l'Innovation<br>
                    Dakar, Sénégal<br>
                    contact@erp-wmc.com
                </div>
            </div>
            <div class="header-right">
                <div class="document-title">FACTURE</div>
                <div class="document-info">
                    <strong>Référence:</strong> {{ $invoice->reference }}<br>
                    <strong>Date d'émission:</strong> {{ $invoice->order_date->format('d/m/Y') }}<br>
                    <strong>Date d'échéance:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                </div>
                @php
                    $statusLabels = [
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyée',
                        'partial' => 'Paiement Partiel',
                        'paid' => 'Payée',
                        'overdue' => 'En Retard',
                        'cancelled' => 'Annulée',
                    ];
                @endphp
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ $statusLabels[$invoice->status] ?? ucfirst($invoice->status) }}
                </span>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <div class="party-box">
                    <div class="party-title">Facturé à</div>
                    <div class="party-name">{{ $invoice->contact->display_name }}</div>
                    <div class="party-details">
                        @if($invoice->contact->company_name && $invoice->contact->company_name !== $invoice->contact->display_name)
                            {{ $invoice->contact->company_name }}<br>
                        @endif
                        @if($invoice->contact->address){{ $invoice->contact->address }}<br>@endif
                        @if($invoice->contact->postal_code || $invoice->contact->city)
                            {{ $invoice->contact->postal_code }} {{ $invoice->contact->city }}<br>
                        @endif
                        @if($invoice->contact->country){{ $invoice->contact->country }}<br>@endif
                        @if($invoice->contact->email){{ $invoice->contact->email }}<br>@endif
                        @if($invoice->contact->phone)Tél: {{ $invoice->contact->phone }}@endif
                    </div>
                </div>
            </div>
            <div class="party">
                <div class="party-box right">
                    <div class="party-title">Informations de paiement</div>
                    <div class="party-details">
                        <strong>Mode de paiement:</strong> Virement bancaire<br>
                        <strong>IBAN:</strong> SN00 0000 0000 0000 0000 0000<br>
                        <strong>BIC:</strong> BICSENXXXX<br>
                        <strong>Banque:</strong> Banque Nationale
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th class="right" style="width: 15%">Prix Unitaire</th>
                    <th class="center" style="width: 10%">Qté</th>
                    <th class="center" style="width: 10%">TVA</th>
                    <th class="right" style="width: 15%">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
                    <tr>
                        <td>{{ $line->description }}</td>
                        <td class="right">{{ number_format($line->unit_price, 0, ',', ' ') }} FCFA</td>
                        <td class="center">{{ $line->quantity }}</td>
                        <td class="center">{{ number_format($line->tax_rate ?? 0, 0) }}%</td>
                        <td class="right">{{ number_format($line->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-spacer"></div>
            <div class="totals-box">
                <table class="totals">
                    <tr>
                        <td class="label">Total HT</td>
                        <td class="value">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                        <tr class="discount">
                            <td class="label">Remise</td>
                            <td class="value">-{{ number_format($invoice->discount_amount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">TVA</td>
                        <td class="value">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr class="total">
                        <td class="label">Total TTC</td>
                        <td class="value">{{ number_format($invoice->total_amount_ttc, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($invoice->paid_amount > 0)
                        <tr class="paid">
                            <td class="label">Déjà payé</td>
                            <td class="value">-{{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr class="remaining">
                            <td class="label"><strong>Reste à payer</strong></td>
                            <td class="value"><strong>{{ number_format($invoice->remaining_balance, 0, ',', ' ') }} FCFA</strong></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Payments History -->
        @if($invoice->payments->count() > 0)
            <div class="payments-history">
                <h4>Historique des paiements</h4>
                <table class="payments">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th class="right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td class="right">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Payment Info -->
        <div class="payment-info">
            <div class="payment-title">Conditions de paiement</div>
            <div class="payment-content">
                Paiement à réception de facture. Tout retard de paiement entraînera des pénalités de retard
                calculées au taux légal en vigueur. Une indemnité forfaitaire de 40€ pour frais de recouvrement
                sera également due en cas de retard.
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div class="notes-content">{{ $invoice->notes }}</div>
            </div>
        @endif
    </div>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} | {{ config('app.name', 'ERP-WMC') }}
    </div>
</body>

</html>
