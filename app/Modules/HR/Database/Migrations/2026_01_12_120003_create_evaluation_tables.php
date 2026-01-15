<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Periodes d'evaluation
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Evaluation S1 2024
            $table->string('type'); // annual, semi_annual, quarterly, probation
            $table->date('start_date');
            $table->date('end_date');
            $table->date('evaluation_start_date'); // Date debut des evaluations
            $table->date('evaluation_end_date'); // Date limite
            $table->string('status')->default('draft'); // draft, open, closed, archived
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });

        // Modeles/Templates d'evaluation
        Schema::create('evaluation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // performance, probation, 360, skills
            $table->boolean('is_active')->default(true);
            $table->boolean('include_self_evaluation')->default(true);
            $table->boolean('include_manager_evaluation')->default(true);
            $table->boolean('include_peer_evaluation')->default(false);
            $table->timestamps();
        });

        // Criteres d'evaluation
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_template_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // technical, behavioral, goals, competencies
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('weight')->default(1); // Poids dans le calcul
            $table->integer('max_score')->default(5);
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['evaluation_template_id', 'category']);
        });

        // Objectifs individuels
        Schema::create('employee_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_period_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // business, development, team
            $table->string('type')->default('quantitative'); // quantitative, qualitative
            $table->string('metric')->nullable(); // KPI ou metrique
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->string('unit')->nullable(); // %, nombre, euros
            $table->integer('weight')->default(1);
            $table->date('start_date');
            $table->date('due_date');
            $table->string('status')->default('in_progress'); // draft, in_progress, completed, cancelled
            $table->integer('progress_percentage')->default(0);
            $table->integer('achievement_score')->nullable(); // Score final (1-5)
            $table->text('manager_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['evaluation_period_id']);
        });

        // Evaluations
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('employees')->cascadeOnDelete(); // Manager ou pair
            $table->string('evaluator_type'); // self, manager, peer, subordinate
            $table->string('status')->default('draft'); // draft, in_progress, submitted, validated
            $table->decimal('overall_score', 4, 2)->nullable();
            $table->string('overall_rating')->nullable(); // A, B, C, D, E ou excellent, good, average, below_average, poor
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('development_plan')->nullable();
            $table->text('manager_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'evaluation_period_id', 'evaluator_id', 'evaluator_type'], 'eval_unique');
            $table->index(['status']);
        });

        // Scores par critere
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('evaluation_criteria_id');
            $table->foreign('evaluation_criteria_id')->references('id')->on('evaluation_criteria')->cascadeOnDelete();
            $table->integer('score')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_id', 'evaluation_criteria_id']);
        });

        // Feedback 360
        Schema::create('feedback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete(); // Employe evalue
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_from_id')->constrained('employees')->cascadeOnDelete(); // Personne demandee
            $table->string('relationship'); // manager, peer, subordinate, cross_functional
            $table->string('status')->default('pending'); // pending, submitted, declined
            $table->text('feedback')->nullable();
            $table->integer('overall_rating')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->timestamps();

            $table->index(['employee_id', 'evaluation_period_id']);
            $table->index(['requested_from_id', 'status']);
        });

        // Plans de developpement individuel
        Schema::create('development_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('skill_area')->nullable(); // technical, leadership, communication
            $table->string('action_type'); // training, mentoring, project, certification, course
            $table->date('start_date');
            $table->date('target_date');
            $table->string('status')->default('planned'); // planned, in_progress, completed, cancelled
            $table->integer('progress_percentage')->default(0);
            $table->text('resources_needed')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->text('completion_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_plans');
        Schema::dropIfExists('feedback_requests');
        Schema::dropIfExists('evaluation_scores');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('employee_objectives');
        Schema::dropIfExists('evaluation_criteria');
        Schema::dropIfExists('evaluation_templates');
        Schema::dropIfExists('evaluation_periods');
    }
};
