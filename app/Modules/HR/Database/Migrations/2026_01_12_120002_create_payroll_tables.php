<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Configuration de la paie
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->string('category')->default('general'); // general, taxes, social, overtime
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Types de primes
        Schema::create('bonus_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('calculation_type'); // fixed, percentage, formula
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable(); // Si calculation_type = percentage
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_recurring')->default(false);
            $table->string('frequency')->nullable(); // monthly, quarterly, yearly, one_time
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Types de deductions
        Schema::create('deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('calculation_type'); // fixed, percentage, formula
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('base')->nullable(); // gross_salary, base_salary, net_salary
            $table->decimal('employee_rate', 5, 2)->nullable(); // Taux part salariale
            $table->decimal('employer_rate', 5, 2)->nullable(); // Taux part patronale
            $table->decimal('ceiling', 12, 2)->nullable(); // Plafond
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Periodes de paie
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Janvier 2024, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payment_date')->nullable();
            $table->string('status')->default('draft'); // draft, processing, validated, paid, closed
            $table->integer('working_days');
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->decimal('total_employer_charges', 15, 2)->default(0);
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['start_date', 'end_date']);
            $table->index(['status']);
        });

        // Fiches de paie
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique(); // BP-2024-01-0001

            // Salaire de base
            $table->decimal('base_salary', 12, 2);
            $table->integer('worked_days');
            $table->integer('paid_days');
            $table->decimal('hourly_rate', 10, 4)->nullable();

            // Heures
            $table->decimal('regular_hours', 6, 2)->default(0);
            $table->decimal('overtime_hours_25', 6, 2)->default(0); // HS 25%
            $table->decimal('overtime_hours_50', 6, 2)->default(0); // HS 50%
            $table->decimal('overtime_hours_100', 6, 2)->default(0); // HS 100%
            $table->decimal('night_hours', 6, 2)->default(0);
            $table->decimal('holiday_hours', 6, 2)->default(0);

            // Totaux
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('total_bonuses', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('total_employee_charges', 12, 2)->default(0);
            $table->decimal('total_employer_charges', 12, 2)->default(0);
            $table->decimal('taxable_income', 12, 2)->default(0);
            $table->decimal('income_tax', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->decimal('net_payable', 12, 2); // Net a payer

            // Avances et prets
            $table->decimal('advance_deduction', 12, 2)->default(0);
            $table->decimal('loan_deduction', 12, 2)->default(0);

            // Statut
            $table->string('status')->default('draft'); // draft, validated, paid
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable(); // bank_transfer, cash, check
            $table->string('payment_reference')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'payroll_period_id']);
            $table->index(['status']);
        });

        // Lignes de primes sur bulletin
        Schema::create('payslip_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bonus_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();
        });

        // Lignes de deductions sur bulletin
        Schema::create('payslip_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->decimal('base', 12, 2)->nullable(); // Base de calcul
            $table->decimal('rate', 5, 2)->nullable(); // Taux applique
            $table->decimal('employee_amount', 12, 2)->default(0);
            $table->decimal('employer_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        // Avances sur salaire
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique(); // AV-2024-0001
            $table->decimal('amount', 12, 2);
            $table->date('request_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, paid, deducted
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignId('payslip_id')->nullable()->constrained()->nullOnDelete(); // Bulletin de deduction
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });

        // Prets au personnel
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique(); // PRET-2024-0001
            $table->decimal('amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->integer('duration_months');
            $table->decimal('monthly_payment', 12, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, active, completed, cancelled
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
        });

        // Echeances de prets
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payslip_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('payment_number');
            $table->date('due_date');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('pending'); // pending, paid, overdue
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->index(['employee_loan_id', 'status']);
        });

        // Primes recurrentes assignees aux employes
        Schema::create('employee_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bonus_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['employee_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_bonuses');
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('salary_advances');
        Schema::dropIfExists('payslip_deductions');
        Schema::dropIfExists('payslip_bonuses');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('deduction_types');
        Schema::dropIfExists('bonus_types');
        Schema::dropIfExists('payroll_settings');
    }
};
