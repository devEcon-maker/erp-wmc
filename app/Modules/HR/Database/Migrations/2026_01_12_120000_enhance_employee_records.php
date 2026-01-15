<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrichir la table employees avec informations supplementaires
        Schema::table('employees', function (Blueprint $table) {
            // Informations personnelles supplementaires
            $table->string('gender')->nullable()->after('last_name'); // M, F
            $table->string('nationality')->nullable()->after('gender');
            $table->string('marital_status')->nullable()->after('nationality'); // single, married, divorced, widowed
            $table->integer('dependents_count')->default(0)->after('marital_status');

            // Informations d'identite
            $table->string('cni_number')->nullable()->after('phone'); // Numero CNI
            $table->date('cni_expiry_date')->nullable()->after('cni_number');
            $table->string('social_security_number')->nullable()->after('cni_expiry_date'); // Numero CNPS

            // Adresse
            $table->text('address')->nullable()->after('social_security_number');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->default('Cameroun')->after('city');

            // Contact d'urgence
            $table->string('emergency_contact_name')->nullable()->after('country');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');

            // Informations bancaires
            $table->string('bank_name')->nullable()->after('emergency_contact_relationship');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_rib')->nullable()->after('bank_account_number');

            // Contrat - enrichissement
            $table->date('contract_start_date')->nullable()->after('contract_type');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->date('probation_end_date')->nullable()->after('contract_end_date');
            $table->boolean('probation_completed')->default(false)->after('probation_end_date');

            // Photo
            $table->string('photo_path')->nullable()->after('probation_completed');
        });

        // Table pour les documents des employes
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // cni, passport, diploma, cv, contract, certificate, other
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, jpg, png
            $table->bigInteger('file_size')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'type']);
        });

        // Table pour l'historique de carriere
        Schema::create('employee_career_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // hire, promotion, mutation, salary_change, contract_renewal, sanction, award, departure
            $table->date('event_date');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('old_department')->nullable();
            $table->string('new_department')->nullable();
            $table->string('old_job_title')->nullable();
            $table->string('new_job_title')->nullable();
            $table->decimal('old_salary', 12, 2)->nullable();
            $table->decimal('new_salary', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable(); // Document justificatif
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'event_type']);
            $table->index(['employee_id', 'event_date']);
        });

        // Table pour les contrats (historique et avenants)
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique(); // CTR-2024-0001
            $table->string('type'); // initial, renewal, amendment
            $table->string('contract_type'); // cdi, cdd, interim, stage, alternance
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->integer('probation_duration_days')->nullable();
            $table->decimal('salary', 12, 2);
            $table->text('terms')->nullable(); // Conditions particulieres
            $table->string('document_path')->nullable();
            $table->string('status')->default('active'); // draft, active, expired, terminated
            $table->date('signed_date')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
        });

        // Table pour les alertes RH
        Schema::create('hr_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type'); // contract_expiry, probation_end, document_expiry, birthday, anniversary
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('alert_date');
            $table->date('due_date')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('pending'); // pending, acknowledged, resolved, dismissed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable(); // daily, weekly, monthly, yearly
            $table->timestamps();

            $table->index(['status', 'alert_date']);
            $table->index(['employee_id', 'type']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_alerts');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employee_career_history');
        Schema::dropIfExists('employee_documents');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'nationality', 'marital_status', 'dependents_count',
                'cni_number', 'cni_expiry_date', 'social_security_number',
                'address', 'city', 'country',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'bank_name', 'bank_account_number', 'bank_rib',
                'contract_start_date', 'contract_end_date', 'probation_end_date', 'probation_completed',
                'photo_path',
            ]);
        });
    }
};
