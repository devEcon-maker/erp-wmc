<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('set null');

            $table->string('reference')->unique(); // e.g., FAC-2024-00001
            $table->enum('type', ['invoice', 'credit_note'])->default('invoice');
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');

            $table->date('order_date'); // Date of issue
            $table->date('due_date');   // Date due
            $table->date('paid_at')->nullable();

            // Financials
            $table->decimal('total_amount', 15, 2)->default(0); // HT
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount_ttc', 15, 2)->default(0); // TTC
            $table->decimal('paid_amount', 15, 2)->default(0);      // Amount paid so far

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('description');
            $table->integer('quantity')->default(1);

            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('discount_rate', 5, 2)->default(0);

            $table->decimal('total_amount', 15, 2);

            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bank_transfer', 'check', 'cash', 'card', 'other'])->default('bank_transfer');
            $table->string('reference')->nullable(); // Transaction ID, Check #
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
