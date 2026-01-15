<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Devis {{ $proposal->reference }}</title>
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
        .status-accepted { background: #dcfce7; color: #15803d; }
        .status-refused { background: #fee2e2; color: #dc2626; }

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

        .discount-note {
            font-size: 9px;
            color: #15803d;
            margin-top: 2px;
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

        table.totals tr.total {
            background: #D97706;
            border: none;
        }

        table.totals tr.total td {
            color: white;
            font-size: 14px;
            padding: 12px;
        }

        .terms-section {
            margin-top: 30px;
            padding: 15px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
        }

        .terms-title {
            font-size: 11px;
            font-weight: bold;
            color: #0369a1;
            margin-bottom: 8px;
        }

        .terms-content {
            font-size: 11px;
            color: #0c4a6e;
            white-space: pre-line;
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

        .validity-notice {
            margin-top: 20px;
            padding: 10px 15px;
            background: #fef3c7;
            border-left: 4px solid #D97706;
            font-size: 11px;
            color: #92400e;
        }

        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 45%;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .signature-box.right {
            margin-left: 10%;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 50px;
            margin-bottom: 8px;
        }

        .signature-label {
            font-size: 9px;
            color: #999;
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

        .page-number:after {
            content: counter(page);
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
                <div class="document-title">DEVIS</div>
                <div class="document-info">
                    <strong>Référence:</strong> {{ $proposal->reference }}<br>
                    <strong>Date:</strong> {{ $proposal->created_at->format('d/m/Y') }}<br>
                    @if($proposal->valid_until)
                        <strong>Valable jusqu'au:</strong> {{ $proposal->valid_until->format('d/m/Y') }}
                    @endif
                </div>
                @php
                    $statusLabels = [
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyé',
                        'accepted' => 'Accepté',
                        'refused' => 'Refusé',
                    ];
                @endphp
                <span class="status-badge status-{{ $proposal->status }}">
                    {{ $statusLabels[$proposal->status] ?? ucfirst($proposal->status) }}
                </span>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <div class="party-box">
                    <div class="party-title">Client</div>
                    <div class="party-name">{{ $proposal->contact->display_name }}</div>
                    <div class="party-details">
                        @if($proposal->contact->company_name && $proposal->contact->company_name !== $proposal->contact->display_name)
                            {{ $proposal->contact->company_name }}<br>
                        @endif
                        @if($proposal->contact->address){{ $proposal->contact->address }}<br>@endif
                        @if($proposal->contact->postal_code || $proposal->contact->city)
                            {{ $proposal->contact->postal_code }} {{ $proposal->contact->city }}<br>
                        @endif
                        @if($proposal->contact->country){{ $proposal->contact->country }}<br>@endif
                        @if($proposal->contact->email){{ $proposal->contact->email }}<br>@endif
                        @if($proposal->contact->phone)Tél: {{ $proposal->contact->phone }}@endif
                    </div>
                </div>
            </div>
            <div class="party">
                <div class="party-box right">
                    <div class="party-title">Émis par</div>
                    <div class="party-name">{{ config('app.name', 'ERP-WMC') }}</div>
                    <div class="party-details">
                        Créé par: {{ $proposal->creator->name ?? 'Système' }}<br>
                        Le: {{ $proposal->created_at->format('d/m/Y à H:i') }}
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
                @foreach($proposal->lines as $line)
                    <tr>
                        <td>
                            {{ $line->description }}
                            @if($line->discount_rate > 0)
                                <div class="discount-note">Remise {{ $line->discount_rate }}% appliquée</div>
                            @endif
                        </td>
                        <td class="right">{{ number_format($line->unit_price, 0, ',', ' ') }} FCFA</td>
                        <td class="center">{{ $line->quantity }}</td>
                        <td class="center">{{ number_format($line->tax_rate, 0) }}%</td>
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
                        <td class="value">{{ number_format($proposal->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($proposal->discount_amount > 0)
                        <tr class="discount">
                            <td class="label">Remise</td>
                            <td class="value">-{{ number_format($proposal->discount_amount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">TVA</td>
                        <td class="value">{{ number_format($proposal->tax_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr class="total">
                        <td class="label">Total TTC</td>
                        <td class="value">{{ number_format($proposal->total_amount_ttc, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Validity Notice -->
        @if($proposal->valid_until)
            <div class="validity-notice">
                <strong>Attention:</strong> Ce devis est valable jusqu'au {{ $proposal->valid_until->format('d/m/Y') }}.
                Passé cette date, les prix et conditions peuvent être modifiés.
            </div>
        @endif

        <!-- Terms -->
        @if($proposal->terms)
            <div class="terms-section">
                <div class="terms-title">Conditions commerciales</div>
                <div class="terms-content">{{ $proposal->terms }}</div>
            </div>
        @endif

        <!-- Notes (internal - only shown if status is draft) -->
        @if($proposal->notes && $proposal->status === 'draft')
            <div class="notes-section">
                <div class="notes-title">Notes internes (non imprimées sur version finale)</div>
                <div class="notes-content">{{ $proposal->notes }}</div>
            </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Bon pour accord - Le Client</div>
                <div class="signature-line"></div>
                <div class="signature-label">Date et signature précédées de la mention "Bon pour accord"</div>
            </div>
            <div class="signature-box right" style="margin-left: 10%">
                <div class="signature-title">{{ config('app.name', 'ERP-WMC') }}</div>
                <div class="signature-line"></div>
                <div class="signature-label">Cachet et signature</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} | {{ config('app.name', 'ERP-WMC') }} | Devis {{ $proposal->reference }}
    </div>
</body>

</html>
