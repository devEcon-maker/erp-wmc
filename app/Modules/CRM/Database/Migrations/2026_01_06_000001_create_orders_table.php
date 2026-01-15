<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('set null');

            $table->string('reference')->unique(); // e.g., CMD-202401-0001
            $table->enum('status', ['draft', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('draft');

            $table->date('order_date')->default(now());
            $table->date('delivery_date')->nullable();
            $table->date('shipped_at')->nullable();

            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();

            // Financials
            $table->decimal('total_amount', 15, 2)->default(0); // HT
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount_ttc', 15, 2)->default(0); // TTC

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->integer('delivered_quantity')->default(0); // Suivi des reliquats

            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('discount_rate', 5, 2)->default(0);

            $table->decimal('total_amount', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lines');
        Schema::dropIfExists('orders');
    }
};
