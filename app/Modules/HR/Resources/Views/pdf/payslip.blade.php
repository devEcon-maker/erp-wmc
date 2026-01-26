<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bulletin de paie {{ $payslip->reference }}</title>
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
        }

        .logo {
            max-height: 70px;
            max-width: 180px;
        }

        .clearfix {
            clear: both;
        }

        /* Document Title */
        .document-title {
            background-color: #2563eb;
            color: white;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            margin: 15px 0;
        }

        /* Info Boxes */
        .info-section {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        .info-box-title {
            font-size: 10pt;
            font-weight: bold;
            color: #2563eb;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .info-row {
            margin-bottom: 4px;
        }

        .info-label {
            font-size: 8pt;
            color: #666;
        }

        .info-value {
            font-size: 9pt;
            font-weight: bold;
        }

        /* Two columns layout */
        .col-left {
            float: left;
            width: 48%;
        }

        .col-right {
            float: right;
            width: 48%;
        }

        /* Tables */
        .payslip-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }

        .payslip-table th {
            background-color: #2563eb;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #2563eb;
        }

        .payslip-table td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }

        .payslip-table .amount {
            text-align: right;
            width: 25%;
        }

        .payslip-table .label {
            width: 50%;
        }

        .payslip-table .rate {
            text-align: center;
            width: 25%;
        }

        /* Section headers */
        .section-title {
            background-color: #f3f4f6;
            font-weight: bold;
            padding: 6px;
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 9pt;
            color: #333;
        }

        /* Totals */
        .subtotal-row td {
            font-weight: bold;
            background-color: #f9fafb;
        }

        .total-row {
            background-color: #2563eb;
            color: white;
        }

        .total-row td {
            font-weight: bold;
            font-size: 10pt;
            padding: 8px 4px;
            color: white;
            border: 1px solid #2563eb;
        }

        .net-row {
            background-color: #16a34a;
        }

        .net-row td {
            font-weight: bold;
            font-size: 11pt;
            padding: 10px 4px;
            color: white;
            border: 1px solid #16a34a;
        }

        /* Amount colors */
        .positive {
            color: #16a34a;
        }

        .negative {
            color: #dc2626;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #2563eb;
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

        .employer-section {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #ddd;
        }

        .employer-title {
            font-size: 8pt;
            color: #666;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <!-- Header: Logo + Company Info -->
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('logo_wmc_orange.png')))
                <img src="{{ public_path('logo_wmc_orange.png') }}" class="logo" alt="Logo">
            @else
                <div style="font-size: 14pt; font-weight: bold; color: #2563eb;">
                    {{ config('app.name', 'ERP-WMC') }}
                </div>
            @endif
            <div style="margin-top: 5px; font-size: 8pt; color: #666;">
                Cameroun
            </div>
        </div>
        <div class="header-right">
            <div style="font-size: 9pt; font-weight: bold;">
                {{ $payslip->payrollPeriod->name ?? 'Période de paie' }}
            </div>
            <div style="font-size: 8pt; color: #666;">
                Du {{ $payslip->payrollPeriod->start_date->format('d/m/Y') }}
                au {{ $payslip->payrollPeriod->end_date->format('d/m/Y') }}
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        BULLETIN DE PAIE
    </div>

    <!-- Employee Info -->
    <div class="info-section">
        <div class="col-left">
            <div class="info-box">
                <div class="info-box-title">Informations Employé</div>
                <div class="info-row">
                    <span class="info-label">Nom complet :</span>
                    <span class="info-value">{{ $payslip->employee->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Matricule :</span>
                    <span class="info-value">{{ $payslip->employee->employee_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Poste :</span>
                    <span class="info-value">{{ $payslip->employee->job_title ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Département :</span>
                    <span class="info-value">{{ $payslip->employee->department?->name ?? '-' }}</span>
                </div>
            </div>
        </div>
        <div class="col-right">
            <div class="info-box">
                <div class="info-box-title">Informations Contrat</div>
                <div class="info-row">
                    <span class="info-label">Date d'embauche :</span>
                    <span class="info-value">{{ $payslip->employee->hire_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type de contrat :</span>
                    <span class="info-value">{{ $payslip->employee->contract_type_label ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">N° Sécurité Sociale :</span>
                    <span class="info-value">{{ $payslip->employee->social_security_number ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Référence bulletin :</span>
                    <span class="info-value">{{ $payslip->reference }}</span>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Salary Details -->
    <div class="section-title">SALAIRE DE BASE</div>
    <table class="payslip-table">
        <tr>
            <td class="label">Salaire de base mensuel</td>
            <td class="rate">{{ $payslip->paid_days ?? $payslip->worked_days ?? '-' }} jours</td>
            <td class="amount">{{ number_format($payslip->base_salary, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <!-- Bonuses -->
    <div class="section-title">PRIMES ET AVANTAGES</div>
    <table class="payslip-table">
        @forelse($payslip->bonuses as $bonus)
            <tr>
                <td class="label">{{ $bonus->bonusType->name ?? 'Prime' }}</td>
                <td class="rate">-</td>
                <td class="amount positive">+{{ number_format($bonus->amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #666;">Aucune prime ce mois</td>
            </tr>
        @endforelse
        @if($payslip->total_bonuses > 0)
            <tr class="subtotal-row">
                <td class="label">Total primes</td>
                <td class="rate"></td>
                <td class="amount positive">+{{ number_format($payslip->total_bonuses, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif
    </table>

    <!-- Gross Salary -->
    <table class="payslip-table">
        <tr class="total-row">
            <td class="label">SALAIRE BRUT</td>
            <td class="rate"></td>
            <td class="amount">{{ number_format($payslip->gross_salary, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <!-- Deductions -->
    <div class="section-title">DÉDUCTIONS SALARIALES</div>
    <table class="payslip-table">
        @foreach($payslip->deductions as $deduction)
            <tr>
                <td class="label">{{ $deduction->deductionType->name ?? 'Déduction' }}</td>
                <td class="rate">{{ $deduction->deductionType->rate ? $deduction->deductionType->rate . '%' : '-' }}</td>
                <td class="amount negative">-{{ number_format($deduction->amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach

        @if($payslip->income_tax > 0)
            <tr>
                <td class="label">IRPP (Impôt sur le revenu)</td>
                <td class="rate">-</td>
                <td class="amount negative">-{{ number_format($payslip->income_tax, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif

        @if($payslip->advance_deduction > 0)
            <tr>
                <td class="label">Remboursement avance</td>
                <td class="rate">-</td>
                <td class="amount negative">-{{ number_format($payslip->advance_deduction, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif

        @if($payslip->loan_deduction > 0)
            <tr>
                <td class="label">Remboursement prêt</td>
                <td class="rate">-</td>
                <td class="amount negative">-{{ number_format($payslip->loan_deduction, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif

        <tr class="subtotal-row">
            <td class="label">Total déductions</td>
            <td class="rate"></td>
            <td class="amount negative">-{{ number_format($payslip->total_deductions + $payslip->income_tax + $payslip->advance_deduction + $payslip->loan_deduction, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <!-- Net Salary -->
    <table class="payslip-table">
        <tr class="net-row">
            <td class="label">NET À PAYER</td>
            <td class="rate"></td>
            <td class="amount">{{ number_format($payslip->net_payable ?? $payslip->net_salary, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    @if($payslip->payment_date)
        <div style="text-align: right; font-size: 8pt; color: #16a34a; margin-top: 5px;">
            Payé le {{ $payslip->payment_date->format('d/m/Y') }}
        </div>
    @endif

    <!-- Employer Contributions -->
    <div class="employer-section">
        <div class="employer-title">CHARGES PATRONALES (pour information)</div>
        <table class="payslip-table" style="margin-bottom: 0;">
            <tr>
                <td class="label">Cotisations CNPS employeur</td>
                <td class="rate">-</td>
                <td class="amount">{{ number_format($payslip->total_employer_charges ?? 0, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr class="subtotal-row">
                <td class="label">Coût total employeur</td>
                <td class="rate"></td>
                <td class="amount">{{ number_format($payslip->gross_salary + ($payslip->total_employer_charges ?? 0), 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} | {{ config('app.name', 'ERP-WMC') }}<br>
        Ce bulletin de paie est un document confidentiel.
    </div>
</body>

</html>
