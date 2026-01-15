<?php

namespace App\Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\HR\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => 'Congés Payés',
                'days_per_year' => 25,
                'is_paid' => true,
                'requires_approval' => true,
                'color' => 'blue',
            ],
            [
                'name' => 'RTT',
                'days_per_year' => 10,
                'is_paid' => true,
                'requires_approval' => true,
                'color' => 'purple',
            ],
            [
                'name' => 'Maladie',
                'days_per_year' => 0, // No limit
                'is_paid' => true,
                'requires_approval' => false, // Usually just declared
                'color' => 'red',
            ],
            [
                'name' => 'Sans solde',
                'days_per_year' => 0,
                'is_paid' => false,
                'requires_approval' => true,
                'color' => 'gray',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
