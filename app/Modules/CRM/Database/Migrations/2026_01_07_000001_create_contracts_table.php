<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');

            $table->string('reference')->unique(); // e.g., CTR-202401-0001
            $table->enum('type', ['contract', 'subscription'])->default('contract');
            $table->enum('status', ['draft', 'active', 'suspended', 'terminated'])->default('draft');

            $table->date('start_date');
            $table->date('end_date')->nullable(); // Nullable for indefinite subscriptions

            $table->enum('billing_frequency', ['once', 'monthly', 'quarterly', 'yearly'])->default('once');
            $table->date('next_billing_date')->nullable();

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Financials (Recurring amount)
            $table->decimal('total_amount', 15, 2)->default(0); // HT
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount_ttc', 15, 2)->default(0); // TTC

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('description');
            $table->integer('quantity')->default(1);

            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('discount_rate', 5, 2)->default(0);

            $table->decimal('total_amount', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_lines');
        Schema::dropIfExists('contracts');
    }
};
