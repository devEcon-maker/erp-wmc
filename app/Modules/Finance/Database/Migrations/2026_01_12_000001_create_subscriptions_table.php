<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ex: "Hébergement serveur", "Licence logiciel"
            $table->text('description')->nullable();
            $table->string('reference')->unique(); // ABN-2026-0001

            // Récurrence
            $table->enum('frequency', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->integer('frequency_interval')->default(1); // Tous les X mois/trimestres/etc.

            // Montants
            $table->decimal('amount_ht', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(19.25); // TVA par défaut
            $table->decimal('amount_ttc', 15, 2)->default(0);

            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null = pas de fin
            $table->date('next_billing_date');
            $table->date('last_billed_date')->nullable();

            // Statut
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->boolean('auto_generate_invoice')->default(true);

            // Relation avec les factures générées
            $table->integer('invoices_generated')->default(0);

            // Notes
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'next_billing_date']);
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
