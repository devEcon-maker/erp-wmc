<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('max_amount', 10, 2)->nullable(); // Plafond par ligne
            $table->boolean('requires_receipt')->default(true);
            $table->timestamps();
        });

        // Expense Reports
        Schema::create('expense_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique(); // NDF-YYYYMM-XXXX
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'paid'])->default('draft');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Expense Lines
        Schema::create('expense_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_lines');
        Schema::dropIfExists('expense_reports');
        Schema::dropIfExists('expense_categories');
    }
};
