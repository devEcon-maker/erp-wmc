<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la configuration (ex: "Gmail", "OVH", "Serveur Principal")
            $table->string('host');
            $table->integer('port')->default(587);
            $table->string('encryption')->default('tls'); // tls, ssl, null
            $table->string('username');
            $table->text('password'); // Encrypted
            $table->string('from_address');
            $table->string('from_name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('last_test_successful')->nullable();
            $table->text('last_test_error')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_default');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_configurations');
    }
};
