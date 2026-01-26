<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de paie</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .info-box {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #6b7280;
            font-size: 14px;
        }
        .value {
            font-weight: bold;
            color: #111827;
        }
        .highlight {
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        .highlight .amount {
            font-size: 24px;
            font-weight: bold;
            color: #059669;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;">Bulletin de Paie</h1>
        <p style="margin: 5px 0 0 0; opacity: 0.9;">{{ $period->name }}</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $employee->first_name }}</strong>,</p>

        <p>Veuillez trouver ci-joint votre bulletin de paie pour la période du <strong>{{ $period->start_date->format('d/m/Y') }}</strong> au <strong>{{ $period->end_date->format('d/m/Y') }}</strong>.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Référence</span>
                <span class="value">{{ $payslip->reference }}</span>
            </div>
            <div class="info-row">
                <span class="label">Salaire de base</span>
                <span class="value">{{ number_format($payslip->base_salary, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="info-row">
                <span class="label">Salaire brut</span>
                <span class="value">{{ number_format($payslip->gross_salary, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="info-row">
                <span class="label">Total déductions</span>
                <span class="value" style="color: #dc2626;">-{{ number_format($payslip->total_deductions + $payslip->income_tax, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <div class="highlight">
            <p style="margin: 0 0 5px 0; color: #6b7280;">Net à payer</p>
            <span class="amount">{{ number_format($payslip->net_payable ?? $payslip->net_salary, 0, ',', ' ') }} FCFA</span>
        </div>

        <p style="font-size: 14px; color: #6b7280;">
            Le bulletin de paie détaillé est disponible en pièce jointe au format PDF.
        </p>
    </div>

    <div class="footer">
        <p style="margin: 0;">{{ config('app.name') }}</p>
        <p style="margin: 5px 0 0 0;">Ce document est confidentiel et destiné uniquement à son destinataire.</p>
    </div>
</body>
</html>
