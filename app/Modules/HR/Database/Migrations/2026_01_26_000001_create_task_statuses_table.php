<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('order')->default(0);
            $table->string('color')->default('gray');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // Insertion des statuts par defaut
        DB::table('task_statuses')->insert([
            [
                'name' => 'A faire',
                'slug' => 'todo',
                'order' => 1,
                'color' => 'slate',
                'is_default' => true,
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'En cours',
                'slug' => 'in_progress',
                'order' => 2,
                'color' => 'blue',
                'is_default' => false,
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'En revue',
                'slug' => 'review',
                'order' => 3,
                'color' => 'amber',
                'is_default' => false,
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Terminee',
                'slug' => 'completed',
                'order' => 4,
                'color' => 'green',
                'is_default' => false,
                'is_completed' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Annulee',
                'slug' => 'cancelled',
                'order' => 5,
                'color' => 'red',
                'is_default' => false,
                'is_completed' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
