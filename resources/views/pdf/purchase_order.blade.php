<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Commande {{ $po->reference }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
        }

        .header {
            margin-bottom: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .details {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .totals {
            float: right;
            width: 300px;
        }

        .totals table {
            margin-top: 0;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="float: left">
            <strong>Ma Société</strong><br>
            123 Rue de l'Innovation<br>
            75001 Paris<br>
            France
        </div>
        <div style="float: right">
            <div class="title">COMMANDE D'ACHAT</div>
            <div>Réf: {{ $po->reference }}</div>
            <div>Date: {{ $po->date->format('d/m/Y') }}</div>
        </div>
        <div style="clear: both"></div>
    </div>

    <div class="details">
        <strong>Fournisseur:</strong><br>
        {{ $po->supplier->company_name }}<br>
        {{ $po->supplier->address }}<br>
        {{ $po->supplier->city }} {{ $po->supplier->postal_code }}<br>
        {{ $po->supplier->country }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Description</th>
                <th style="text-align: right">Qté</th>
                <th style="text-align: right">Prix U. HT</th>
                <th style="text-align: right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->lines as $line)
                <tr>
                    <td>{{ $line->product->reference }}</td>
                    <td>{{ $line->description }}</td>
                    <td style="text-align: right">{{ $line->quantity }}</td>
                    <td style="text-align: right">{{ number_format($line->unit_price, 2) }} FCFA</td>
                    <td style="text-align: right">{{ number_format($line->quantity * $line->unit_price, 2) }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td><strong>Total HT</strong></td>
                <td style="text-align: right">{{ number_format($po->total_ht, 2) }} FCFA</td>
            </tr>
            <tr>
                <td><strong>TVA</strong></td>
                <td style="text-align: right">{{ number_format($po->total_tva, 2) }} FCFA</td>
            </tr>
            <tr>
                <td><strong>Total TTC</strong></td>
                <td style="text-align: right"><strong>{{ number_format($po->total_ttc, 2) }} FCFA</strong></td>
            </tr>
        </table>
    </div>

    @if($po->notes)
        <div style="margin-top: 40px; clear: both;">
            <strong>Notes:</strong><br>
            {{ $po->notes }}
        </div>
    @endif

    <div class="footer">
        Document généré le {{ date('d/m/Y H:i') }}
    </div>
</body>

</html>