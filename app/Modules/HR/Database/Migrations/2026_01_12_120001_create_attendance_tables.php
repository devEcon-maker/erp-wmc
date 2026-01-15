<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des pointages journaliers
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->decimal('worked_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->string('status')->default('present'); // present, absent, late, half_day, holiday, weekend
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->boolean('left_early')->default(false);
            $table->integer('early_departure_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->string('check_in_location')->nullable(); // GPS ou IP
            $table->string('check_out_location')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });

        // Table des absences
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // unexcused, excused, sick, family_emergency, other
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 5, 2);
            $table->text('reason')->nullable();
            $table->string('justification_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('deduct_from_salary')->default(false);
            $table->boolean('deduct_from_leave')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'start_date']);
            $table->index(['status']);
        });

        // Table des permissions/autorisations
        Schema::create('permissions_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // personal, medical, administrative, family, other
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('hours', 4, 2);
            $table->text('reason');
            $table->string('justification_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('deduct_from_salary')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['status']);
        });

        // Table des retards
        Schema::create('late_arrivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('expected_time');
            $table->time('actual_time');
            $table->integer('minutes_late');
            $table->text('reason')->nullable();
            $table->string('justification_path')->nullable();
            $table->string('status')->default('pending'); // pending, excused, unexcused
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->boolean('deduct_from_salary')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['status']);
        });

        // Configuration des horaires de travail
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->time('monday_start')->nullable();
            $table->time('monday_end')->nullable();
            $table->time('tuesday_start')->nullable();
            $table->time('tuesday_end')->nullable();
            $table->time('wednesday_start')->nullable();
            $table->time('wednesday_end')->nullable();
            $table->time('thursday_start')->nullable();
            $table->time('thursday_end')->nullable();
            $table->time('friday_start')->nullable();
            $table->time('friday_end')->nullable();
            $table->time('saturday_start')->nullable();
            $table->time('saturday_end')->nullable();
            $table->time('sunday_start')->nullable();
            $table->time('sunday_end')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->integer('late_tolerance_minutes')->default(15);
            $table->decimal('daily_hours', 4, 2)->default(8);
            $table->decimal('weekly_hours', 5, 2)->default(40);
            $table->timestamps();
        });

        // Association employe - horaire
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('work_schedule_id')->nullable()->after('status')
                ->constrained('work_schedules')->nullOnDelete();
        });

        // Table des jours feries
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->integer('year');
            $table->boolean('is_recurring')->default(false); // Meme date chaque annee
            $table->boolean('is_paid')->default(true);
            $table->timestamps();

            $table->unique(['date']);
            $table->index(['year']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['work_schedule_id']);
            $table->dropColumn('work_schedule_id');
        });

        Schema::dropIfExists('public_holidays');
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('late_arrivals');
        Schema::dropIfExists('permissions_requests');
        Schema::dropIfExists('absences');
        Schema::dropIfExists('attendances');
    }
};
