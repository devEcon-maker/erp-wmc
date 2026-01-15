<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('color')->default('#E76F51'); // Primary color

            // Type d'événement
            $table->enum('type', ['meeting', 'call', 'task', 'reminder', 'other'])->default('meeting');

            // Récurrence
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable(); // Format RRULE (ex: FREQ=WEEKLY;BYDAY=MO,WE,FR)
            $table->date('recurrence_end')->nullable();
            $table->unsignedBigInteger('parent_event_id')->nullable(); // Pour les occurrences modifiées

            // Rappels
            $table->integer('reminder_minutes')->nullable(); // Minutes avant l'événement
            $table->boolean('reminder_sent')->default(false);

            // Relations
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');

            // Visibilité
            $table->enum('visibility', ['public', 'private', 'team'])->default('team');

            $table->timestamps();

            $table->index(['start_at', 'end_at']);
            $table->index('created_by');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
