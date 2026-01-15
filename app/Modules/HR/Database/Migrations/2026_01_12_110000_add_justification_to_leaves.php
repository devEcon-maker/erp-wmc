<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le flag requires_justification aux types de conges
        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('requires_justification')->default(false)->after('requires_approval');
        });

        // Ajouter les champs justificatif aux demandes de conges
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('justification_path')->nullable()->after('reason');
            $table->string('justification_name')->nullable()->after('justification_path');
            $table->string('justification_type')->nullable()->after('justification_name');
            $table->bigInteger('justification_size')->nullable()->after('justification_type');
            $table->timestamp('justification_uploaded_at')->nullable()->after('justification_size');
        });

        // Mettre a jour le type "Maladie" pour exiger un justificatif
        DB::table('leave_types')
            ->where('name', 'like', '%maladie%')
            ->orWhere('name', 'like', '%Maladie%')
            ->update(['requires_justification' => true]);
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn([
                'justification_path',
                'justification_name',
                'justification_type',
                'justification_size',
                'justification_uploaded_at',
            ]);
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('requires_justification');
        });
    }
};
