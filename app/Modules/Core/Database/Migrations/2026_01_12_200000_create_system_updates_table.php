<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('build')->nullable();
            $table->timestamp('applied_at');
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_updates');
    }
};
