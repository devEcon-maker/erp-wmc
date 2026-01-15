<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // manager_id and parent_id are self-referencing or referencing employees, but employees table doesn't exist yet.
            // We'll add FKs in a separate migration or use constrained() which works if tables order is correct.
            // Since employees table references departments, we have a circular dependency.
            // Best practice: Create tables first, then add FKs.
            // However, typical Laravel workflow allows nullable FKs.
            // Let's create columns first.
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->foreign('parent_id')->references('id')->on('departments')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
