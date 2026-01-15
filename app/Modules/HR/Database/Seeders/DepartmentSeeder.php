<?php

namespace App\Modules\HR\Database\Seeders;

use App\Modules\HR\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Direction'],
            ['name' => 'Commercial'],
            ['name' => 'Technique'],
            ['name' => 'Administratif'],
            ['name' => 'Ressources Humaines'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate($dept);
        }
    }
}
