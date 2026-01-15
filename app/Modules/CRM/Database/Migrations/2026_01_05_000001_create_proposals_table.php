<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('opportunity_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference')->unique(); // e.g., PROP-202401-0001
            $table->enum('status', ['draft', 'sent', 'accepted', 'refused'])->default('draft');
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->text('notes')->nullable();
            $table->text('terms')->nullable(); // Conditions spécifiques

            // Financials (denormalized for performance and history)
            $table->decimal('total_amount', 15, 2)->default(0); // HT
            $table->decimal('tax_amount', 15, 2)->default(0);   // TVA
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount_ttc', 15, 2)->default(0); // TTC

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('proposal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('description'); // Copied from product or custom
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00); // 18% default usually, or customizable
            $table->decimal('discount_rate', 5, 2)->default(0);

            $table->decimal('total_amount', 15, 2); // Calculated: qty * price * (1-discount)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_lines');
        Schema::dropIfExists('proposals');
    }
};
