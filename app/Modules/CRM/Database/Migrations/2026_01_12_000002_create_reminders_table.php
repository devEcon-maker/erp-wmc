<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Type de relance et entité liée
            $table->string('reminder_type'); // invoice, proposal, contract, general
            $table->string('remindable_type')->nullable(); // App\Modules\Finance\Models\Invoice, etc.
            $table->unsignedBigInteger('remindable_id')->nullable();

            // Détails de la relance
            $table->string('subject'); // Objet de la relance
            $table->text('message')->nullable(); // Message envoyé
            $table->string('channel')->default('email'); // email, phone, sms, meeting
            $table->string('status')->default('pending'); // pending, sent, acknowledged, no_response

            // Dates
            $table->timestamp('scheduled_at')->nullable(); // Date prévue pour la relance
            $table->timestamp('sent_at')->nullable(); // Date d'envoi effectif
            $table->timestamp('response_at')->nullable(); // Date de réponse du client
            $table->date('next_reminder_date')->nullable(); // Date de la prochaine relance prévue

            // Suivi
            $table->integer('reminder_count')->default(1); // Numéro de relance (1ère, 2ème, etc.)
            $table->text('response_notes')->nullable(); // Notes sur la réponse du client
            $table->string('priority')->default('normal'); // low, normal, high, urgent

            $table->timestamps();
            $table->softDeletes();

            // Index pour les recherches
            $table->index(['contact_id', 'status']);
            $table->index(['reminder_type', 'status']);
            $table->index('scheduled_at');
            $table->index('next_reminder_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
