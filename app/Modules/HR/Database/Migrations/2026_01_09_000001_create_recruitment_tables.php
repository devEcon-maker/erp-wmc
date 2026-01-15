<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Job Positions
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->enum('type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->string('location')->nullable();
            $table->decimal('salary_range_min', 10, 2)->nullable();
            $table->decimal('salary_range_max', 10, 2)->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Job Applications
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_position_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['new', 'reviewing', 'interview', 'offer', 'hired', 'rejected'])->default('new');
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('notes')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_positions');
    }
};
