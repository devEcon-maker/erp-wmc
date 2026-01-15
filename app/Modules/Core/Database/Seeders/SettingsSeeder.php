<?php

namespace App\Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Core\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'company_name' => 'Mon Entreprise',
            'invoice_prefix' => 'FAC-',
            'order_prefix' => 'CMD-',
            'currency' => 'XOF',
            'default_payment_days' => '30',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'module' => 'Core']
            );
        }
    }
}
