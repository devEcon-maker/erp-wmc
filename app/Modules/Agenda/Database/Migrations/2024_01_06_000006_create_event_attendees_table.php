<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'tentative'])->default('pending');
            $table->boolean('is_organizer')->default(false);
            $table->timestamps();

            $table->unique(['event_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
    }
};
