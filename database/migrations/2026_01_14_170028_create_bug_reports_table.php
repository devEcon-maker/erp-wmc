<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['bug', 'improvement', 'feature', 'question'])->default('bug');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'wont_fix'])->default('open');
            $table->string('title');
            $table->text('description');
            $table->string('page_url')->nullable();
            $table->string('browser')->nullable();
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_behavior')->nullable();
            $table->text('actual_behavior')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
