<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core seeders
            \App\Modules\Core\Database\Seeders\RolePermissionSeeder::class,
            \App\Modules\Core\Database\Seeders\CompanySeeder::class,
            \App\Modules\Core\Database\Seeders\SettingsSeeder::class,

            // HR seeders
            \App\Modules\HR\Database\Seeders\DepartmentSeeder::class,
            \App\Modules\HR\Database\Seeders\LeaveTypeSeeder::class,
            \App\Modules\HR\Database\Seeders\ExpenseCategorySeeder::class,

            // CRM seeders
            \App\Modules\CRM\Database\Seeders\OpportunityStageSeeder::class,

            // Inventory seeders
            \App\Modules\Inventory\Database\Seeders\WarehouseSeeder::class,

            // Demo data (optional - comment out for production)
            DemoDataSeeder::class,
        ]);
    }
}
