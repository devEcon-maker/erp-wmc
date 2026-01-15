<?php

namespace App\Modules\HR\Database\Seeders;

use App\Modules\HR\Models\BonusType;
use App\Modules\HR\Models\DeductionType;
use App\Modules\HR\Models\WorkSchedule;
use App\Modules\HR\Models\PublicHoliday;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDeductionTypes();
        $this->seedBonusTypes();
        $this->seedWorkSchedules();
        $this->seedPublicHolidays();
    }

    private function seedDeductionTypes(): void
    {
        $deductions = [
            // Cotisations CNPS Cameroun
            [
                'name' => 'CNPS - Pension Vieillesse',
                'code' => 'CNPS_PV',
                'description' => 'Cotisation pension vieillesse - Part salariale',
                'calculation_type' => 'percentage',
                'base' => 'gross_salary',
                'employee_rate' => 2.80,
                'employer_rate' => 4.20,
                'ceiling' => 750000, // Plafond mensuel CNPS
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'CNPS - Allocations Familiales',
                'code' => 'CNPS_AF',
                'description' => 'Cotisation allocations familiales - Part patronale uniquement',
                'calculation_type' => 'percentage',
                'base' => 'gross_salary',
                'employee_rate' => 0,
                'employer_rate' => 7.00,
                'ceiling' => 750000,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'CNPS - Accidents du Travail',
                'code' => 'CNPS_AT',
                'description' => 'Cotisation accidents du travail - Part patronale uniquement',
                'calculation_type' => 'percentage',
                'base' => 'gross_salary',
                'employee_rate' => 0,
                'employer_rate' => 1.75,
                'ceiling' => 750000,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 3,
            ],
            // Impots
            [
                'name' => 'IRPP - Impot sur le Revenu',
                'code' => 'IRPP',
                'description' => 'Impot sur le revenu des personnes physiques',
                'calculation_type' => 'formula', // Calcul progressif
                'base' => 'taxable_income',
                'employee_rate' => 0,
                'employer_rate' => 0,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 10,
            ],
            [
                'name' => 'CAC - Centimes Additionnels',
                'code' => 'CAC',
                'description' => 'Centimes additionnels communaux (10% de IRPP)',
                'calculation_type' => 'percentage',
                'base' => 'taxable_income',
                'employee_rate' => 0,
                'employer_rate' => 0,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 11,
            ],
            [
                'name' => 'Credit Foncier',
                'code' => 'CF',
                'description' => 'Contribution au credit foncier',
                'calculation_type' => 'percentage',
                'base' => 'gross_salary',
                'employee_rate' => 1.00,
                'employer_rate' => 1.50,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Taxe Communale',
                'code' => 'TC',
                'description' => 'Taxe communale sur les salaires',
                'calculation_type' => 'percentage',
                'base' => 'gross_salary',
                'employee_rate' => 0,
                'employer_rate' => 2.00,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 6,
            ],
            // Optionnelles
            [
                'name' => 'Mutuelle Sante',
                'code' => 'MUTUELLE',
                'description' => 'Cotisation mutuelle sante',
                'calculation_type' => 'fixed',
                'default_amount' => 15000,
                'employee_rate' => 50,
                'employer_rate' => 50,
                'is_mandatory' => false,
                'is_active' => true,
                'order' => 20,
            ],
            [
                'name' => 'Syndicat',
                'code' => 'SYNDICAT',
                'description' => 'Cotisation syndicale',
                'calculation_type' => 'percentage',
                'base' => 'base_salary',
                'employee_rate' => 1.00,
                'employer_rate' => 0,
                'is_mandatory' => false,
                'is_active' => true,
                'order' => 21,
            ],
        ];

        foreach ($deductions as $deduction) {
            DeductionType::updateOrCreate(
                ['code' => $deduction['code']],
                $deduction
            );
        }
    }

    private function seedBonusTypes(): void
    {
        $bonuses = [
            [
                'name' => 'Prime de transport',
                'code' => 'TRANSPORT',
                'description' => 'Indemnite de transport mensuelle',
                'calculation_type' => 'fixed',
                'default_amount' => 25000,
                'is_taxable' => false,
                'is_recurring' => true,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Prime de logement',
                'code' => 'LOGEMENT',
                'description' => 'Indemnite de logement',
                'calculation_type' => 'percentage',
                'percentage' => 20,
                'is_taxable' => true,
                'is_recurring' => true,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Prime de responsabilite',
                'code' => 'RESPONSABILITE',
                'description' => 'Prime liee aux responsabilites du poste',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_recurring' => true,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Prime d\'anciennete',
                'code' => 'ANCIENNETE',
                'description' => 'Prime basee sur l\'anciennete (2% par an)',
                'calculation_type' => 'percentage',
                'percentage' => 2,
                'is_taxable' => true,
                'is_recurring' => true,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Prime de rendement',
                'code' => 'RENDEMENT',
                'description' => 'Prime de performance/objectifs',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_recurring' => false,
                'frequency' => 'one_time',
                'is_active' => true,
            ],
            [
                'name' => 'Prime de fin d\'annee / 13e mois',
                'code' => '13EME_MOIS',
                'description' => 'Gratification annuelle',
                'calculation_type' => 'percentage',
                'percentage' => 100, // 100% du salaire de base
                'is_taxable' => true,
                'is_recurring' => true,
                'frequency' => 'yearly',
                'is_active' => true,
            ],
            [
                'name' => 'Heures supplementaires 25%',
                'code' => 'HS_25',
                'description' => 'Heures supplementaires majorees a 25%',
                'calculation_type' => 'formula',
                'is_taxable' => true,
                'is_recurring' => false,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Heures supplementaires 50%',
                'code' => 'HS_50',
                'description' => 'Heures supplementaires majorees a 50%',
                'calculation_type' => 'formula',
                'is_taxable' => true,
                'is_recurring' => false,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Heures supplementaires 100%',
                'code' => 'HS_100',
                'description' => 'Heures supplementaires majorees a 100% (dimanche/ferie)',
                'calculation_type' => 'formula',
                'is_taxable' => true,
                'is_recurring' => false,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Indemnite de conges payes',
                'code' => 'CONGES_PAYES',
                'description' => 'Indemnite de conges payes',
                'calculation_type' => 'formula',
                'is_taxable' => true,
                'is_recurring' => false,
                'frequency' => 'one_time',
                'is_active' => true,
            ],
            [
                'name' => 'Prime de panier / Restauration',
                'code' => 'PANIER',
                'description' => 'Indemnite de restauration',
                'calculation_type' => 'fixed',
                'default_amount' => 2500, // Par jour travaille
                'is_taxable' => false,
                'is_recurring' => true,
                'frequency' => 'monthly',
                'is_active' => true,
            ],
        ];

        foreach ($bonuses as $bonus) {
            BonusType::updateOrCreate(
                ['code' => $bonus['code']],
                $bonus
            );
        }
    }

    private function seedWorkSchedules(): void
    {
        WorkSchedule::updateOrCreate(
            ['name' => 'Horaire standard'],
            [
                'is_default' => true,
                'monday_start' => '08:00',
                'monday_end' => '17:00',
                'tuesday_start' => '08:00',
                'tuesday_end' => '17:00',
                'wednesday_start' => '08:00',
                'wednesday_end' => '17:00',
                'thursday_start' => '08:00',
                'thursday_end' => '17:00',
                'friday_start' => '08:00',
                'friday_end' => '17:00',
                'saturday_start' => null,
                'saturday_end' => null,
                'sunday_start' => null,
                'sunday_end' => null,
                'break_start' => '12:00',
                'break_end' => '13:00',
                'late_tolerance_minutes' => 15,
                'daily_hours' => 8,
                'weekly_hours' => 40,
            ]
        );

        WorkSchedule::updateOrCreate(
            ['name' => 'Horaire avec samedi'],
            [
                'is_default' => false,
                'monday_start' => '08:00',
                'monday_end' => '17:00',
                'tuesday_start' => '08:00',
                'tuesday_end' => '17:00',
                'wednesday_start' => '08:00',
                'wednesday_end' => '17:00',
                'thursday_start' => '08:00',
                'thursday_end' => '17:00',
                'friday_start' => '08:00',
                'friday_end' => '17:00',
                'saturday_start' => '08:00',
                'saturday_end' => '13:00',
                'sunday_start' => null,
                'sunday_end' => null,
                'break_start' => '12:00',
                'break_end' => '13:00',
                'late_tolerance_minutes' => 15,
                'daily_hours' => 8,
                'weekly_hours' => 45,
            ]
        );
    }

    private function seedPublicHolidays(): void
    {
        $year = now()->year;

        $holidays = [
            ['name' => 'Jour de l\'An', 'date' => "{$year}-01-01", 'is_recurring' => true],
            ['name' => 'Fete de la Jeunesse', 'date' => "{$year}-02-11", 'is_recurring' => true],
            ['name' => 'Fete du Travail', 'date' => "{$year}-05-01", 'is_recurring' => true],
            ['name' => 'Fete Nationale', 'date' => "{$year}-05-20", 'is_recurring' => true],
            ['name' => 'Assomption', 'date' => "{$year}-08-15", 'is_recurring' => true],
            ['name' => 'Noel', 'date' => "{$year}-12-25", 'is_recurring' => true],
            // Fetes mobiles (a ajuster chaque annee)
            ['name' => 'Vendredi Saint', 'date' => "{$year}-03-29", 'is_recurring' => false],
            ['name' => 'Lundi de Paques', 'date' => "{$year}-04-01", 'is_recurring' => false],
            ['name' => 'Ascension', 'date' => "{$year}-05-09", 'is_recurring' => false],
            ['name' => 'Lundi de Pentecote', 'date' => "{$year}-05-20", 'is_recurring' => false],
            // Fetes religieuses musulmanes (dates variables)
            ['name' => 'Eid al-Fitr', 'date' => "{$year}-04-10", 'is_recurring' => false],
            ['name' => 'Eid al-Adha', 'date' => "{$year}-06-17", 'is_recurring' => false],
        ];

        foreach ($holidays as $holiday) {
            PublicHoliday::updateOrCreate(
                ['date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'year' => $year,
                    'is_recurring' => $holiday['is_recurring'],
                    'is_paid' => true,
                ]
            );
        }
    }
}
