<?php

namespace App\Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'CRM' => [
                'contacts.view',
                'contacts.create',
                'contacts.edit',
                'contacts.delete',
                'opportunities.view',
                'opportunities.create',
                'opportunities.edit',
                'orders.view',
                'orders.create',
                'orders.edit',
                'contracts.view',
                'contracts.create',
                'contracts.edit'
            ],
            'Finance' => [
                'invoices.view',
                'invoices.create',
                'invoices.edit',
                'invoices.send',
                'payments.view',
                'payments.create'
            ],
            'HR' => [
                'employees.view',
                'employees.create',
                'employees.edit',
                'leaves.view',
                'leaves.create',
                'leaves.approve',
                'expenses.view',
                'expenses.create',
                'expenses.approve',
                'recruitment.view',
                'recruitment.manage'
            ],
            'Inventory' => [
                'products.view',
                'products.create',
                'products.edit',
                'stock.view',
                'stock.manage',
                'purchases.view',
                'purchases.create'
            ],
            'Productivity' => [
                'projects.view',
                'projects.create',
                'projects.edit',
                'tasks.view',
                'tasks.create',
                'time.view',
                'time.create'
            ],
            'Agenda' => [
                'events.view',
                'events.create',
                'events.edit'
            ],
            'Config' => [
                'settings.manage'
            ],
        ];

        // Create all permissions
        foreach ($permissions as $module => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
        }

        // 2. Create Roles and Assign Permissions

        // Super Admin (all)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin (all except settings.manage)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::where('name', '!=', 'settings.manage')->get());

        // Commercial: CRM complet + Agenda
        $commercial = Role::firstOrCreate(['name' => 'commercial', 'guard_name' => 'web']);
        $crmPerms = Permission::where('name', 'like', 'contacts.%')
            ->orWhere('name', 'like', 'opportunities.%')
            ->orWhere('name', 'like', 'orders.%')
            ->orWhere('name', 'like', 'contracts.%')
            ->orWhere('name', 'like', 'events.%')
            ->get();
        $commercial->givePermissionTo($crmPerms);

        // Comptable: Finance complet + contacts.view
        $comptable = Role::firstOrCreate(['name' => 'comptable', 'guard_name' => 'web']);
        $financePerms = Permission::where('name', 'like', 'invoices.%')
            ->orWhere('name', 'like', 'payments.%')
            ->orWhere('name', 'contacts.view')
            ->get();
        $comptable->givePermissionTo($financePerms);

        // RH: HR complet + Agenda
        $rh = Role::firstOrCreate(['name' => 'rh', 'guard_name' => 'web']);
        $rhPerms = Permission::where('name', 'like', 'employees.%')
            ->orWhere('name', 'like', 'leaves.%')
            ->orWhere('name', 'like', 'expenses.%')
            ->orWhere('name', 'like', 'recruitment.%')
            ->orWhere('name', 'like', 'events.%')
            ->get();
        $rh->givePermissionTo($rhPerms);

        // Manager: Productivity + Approve Leaves/Expenses + View Access
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerPerms = Permission::where('name', 'like', 'projects.%')
            ->orWhere('name', 'like', 'tasks.%')
            ->orWhere('name', 'like', 'time.%')
            ->orWhere('name', 'leaves.approve')
            ->orWhere('name', 'expenses.approve')
            ->orWhere('name', 'employees.view')
            ->orWhere('name', 'events.%')
            ->get();
        $manager->givePermissionTo($managerPerms);

        // Employe: Limited + Create Requests
        $employe = Role::firstOrCreate(['name' => 'employe', 'guard_name' => 'web']);
        $employe->givePermissionTo([
            'leaves.create',
            'expenses.create',
            'time.create',
            'projects.view',
            'tasks.view',
            'events.view',
            'contacts.view' // Usually needs to see contacts? Maybe.
        ]);
    }
}
