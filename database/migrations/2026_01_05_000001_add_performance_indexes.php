<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Contacts indexes
        if (Schema::hasTable('contacts')) {
            $this->addIndexSafe('contacts', 'type');
            $this->addIndexSafe('contacts', 'assigned_to');
            $this->addIndexSafe('contacts', 'status');
            $this->addIndexSafe('contacts', 'email');
        }

        // Invoices indexes
        if (Schema::hasTable('invoices')) {
            $this->addIndexSafe('invoices', 'status');
            $this->addIndexSafe('invoices', 'contact_id');
            $this->addIndexSafe('invoices', 'date');
            $this->addIndexSafe('invoices', 'due_date');
        }

        // Orders indexes
        if (Schema::hasTable('orders')) {
            $this->addIndexSafe('orders', 'status');
            $this->addIndexSafe('orders', 'contact_id');
            $this->addIndexSafe('orders', 'type');
        }

        // Products indexes
        if (Schema::hasTable('products')) {
            $this->addIndexSafe('products', 'reference');
            $this->addIndexSafe('products', 'category_id');
            $this->addIndexSafe('products', 'type');
        }

        // Stock levels indexes
        if (Schema::hasTable('stock_levels')) {
            $this->addIndexSafe('stock_levels', 'product_id');
            $this->addIndexSafe('stock_levels', 'warehouse_id');
        }

        // Employees indexes
        if (Schema::hasTable('employees')) {
            $this->addIndexSafe('employees', 'department_id');
            $this->addIndexSafe('employees', 'manager_id');
            $this->addIndexSafe('employees', 'user_id');
            $this->addIndexSafe('employees', 'status');
        }

        // Leave requests indexes
        if (Schema::hasTable('leave_requests')) {
            $this->addIndexSafe('leave_requests', 'employee_id');
            $this->addIndexSafe('leave_requests', 'status');
        }

        // Tasks indexes
        if (Schema::hasTable('tasks')) {
            $this->addIndexSafe('tasks', 'project_id');
            $this->addIndexSafe('tasks', 'assigned_to');
            $this->addIndexSafe('tasks', 'status');
            $this->addIndexSafe('tasks', 'due_date');
        }

        // Projects indexes
        if (Schema::hasTable('projects')) {
            $this->addIndexSafe('projects', 'client_id');
            $this->addIndexSafe('projects', 'status');
        }

        // Time entries indexes
        if (Schema::hasTable('time_entries')) {
            $this->addIndexSafe('time_entries', 'project_id');
            $this->addIndexSafe('time_entries', 'employee_id');
            $this->addIndexSafe('time_entries', 'date');
        }

        // Events indexes
        if (Schema::hasTable('events')) {
            $this->addIndexSafe('events', 'created_by');
            $this->addIndexSafe('events', 'start_at');
            $this->addIndexSafe('events', 'type');
        }

        // Opportunities indexes
        if (Schema::hasTable('opportunities')) {
            $this->addIndexSafe('opportunities', 'stage_id');
            $this->addIndexSafe('opportunities', 'contact_id');
            $this->addIndexSafe('opportunities', 'assigned_to');
        }

        // Expense reports indexes
        if (Schema::hasTable('expense_reports')) {
            $this->addIndexSafe('expense_reports', 'employee_id');
            $this->addIndexSafe('expense_reports', 'status');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't remove indexes in down() as they don't affect functionality
        // and removing them would slow down queries
    }

    /**
     * Add an index safely (ignoring if it already exists).
     */
    private function addIndexSafe(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";

        try {
            // Check if index already exists using raw SQL
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            if (empty($indexes)) {
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->index($column);
                });
            }
        } catch (\Exception $e) {
            // Silently ignore - index might already exist with different name
        }
    }
};
