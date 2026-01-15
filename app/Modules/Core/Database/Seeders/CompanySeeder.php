<?php

namespace App\Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Core\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Company::count() === 0) {
            Company::create([
                'name' => 'Mon Entreprise',
                'address' => '123 Rue de la Paix, 75000 Paris',
                'email' => 'contact@monentreprise.com',
                'phone' => '0123456789',
                'siret' => '12345678900010',
                'tva_number' => 'FR12345678901',
            ]);
        }
    }
}
