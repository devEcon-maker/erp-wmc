<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description'); // Description du produit/service
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2); // Prix unitaire HT
            $table->decimal('tax_rate', 5, 2)->default(19.25); // TVA
            $table->decimal('discount_rate', 5, 2)->default(0); // Remise en %
            $table->decimal('total_ht', 15, 2); // Total ligne HT
            $table->decimal('total_ttc', 15, 2); // Total ligne TTC

            $table->timestamps();
        });

        // Supprimer les colonnes de montant direct de subscriptions (on calculera depuis les lignes)
        // On garde amount_ht, tax_rate, amount_ttc comme totaux calculés
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_lines');
    }
};
