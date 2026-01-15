<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_periods', function (Blueprint $table) {
            $table->foreignId('evaluation_template_id')
                ->nullable()
                ->after('description')
                ->constrained('evaluation_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_periods', function (Blueprint $table) {
            $table->dropForeign(['evaluation_template_id']);
            $table->dropColumn('evaluation_template_id');
        });
    }
};
