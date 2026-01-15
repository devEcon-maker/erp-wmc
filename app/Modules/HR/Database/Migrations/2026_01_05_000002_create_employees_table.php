<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number')->unique(); // EMP-XXXX
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('hire_date');
            $table->date('end_date')->nullable();
            $table->string('job_title');

            // FK to departments created in previous migration
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();

            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();

            $table->decimal('salary', 10, 2)->nullable(); // Visible only to admin/rh
            $table->string('contract_type'); // enum: cdi, cdd, interim, stage, alternance
            $table->string('status')->default('active'); // enum: active, inactive, terminated

            $table->timestamps();
            $table->softDeletes();
        });

        // Now add the FK from departments to employees (manager_id)
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
        Schema::dropIfExists('employees');
    }
};
