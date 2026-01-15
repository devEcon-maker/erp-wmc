<?php

namespace App\Modules\CRM\Database\Seeders;

use App\Modules\CRM\Models\OpportunityStage;
use Illuminate\Database\Seeder;

class OpportunityStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'name' => 'Qualification',
                'order' => 1,
                'probability' => 10,
                'color' => 'gray',
            ],
            [
                'name' => 'Proposition',
                'order' => 2,
                'probability' => 30,
                'color' => 'blue',
            ],
            [
                'name' => 'Négociation',
                'order' => 3,
                'probability' => 60,
                'color' => 'yellow',
            ],
            [
                'name' => 'Gagnée',
                'order' => 4,
                'probability' => 100,
                'color' => 'green',
            ],
            [
                'name' => 'Perdue',
                'order' => 5,
                'probability' => 0,
                'color' => 'red',
            ],
        ];

        foreach ($stages as $stage) {
            OpportunityStage::firstOrCreate(
                ['name' => $stage['name']],
                $stage
            );
        }
    }
}
