<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Nouveaux types de contrats
            $table->string('contract_category')->default('client')->after('type');
            // client, fournisseur, prestataire

            // Sous-type de contrat (prestation, géolocalisation, maintenance, etc.)
            $table->string('contract_subtype')->nullable()->after('contract_category');

            // Document uploadé
            $table->string('document_path')->nullable()->after('terms');
            $table->string('document_name')->nullable()->after('document_path');
            $table->string('document_type')->nullable()->after('document_name'); // pdf, docx
            $table->bigInteger('document_size')->nullable()->after('document_type'); // en bytes
            $table->timestamp('document_uploaded_at')->nullable()->after('document_size');

            // Informations complémentaires
            $table->string('signatory_name')->nullable()->after('document_uploaded_at'); // Nom du signataire
            $table->date('signature_date')->nullable()->after('signatory_name');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'contract_category',
                'contract_subtype',
                'document_path',
                'document_name',
                'document_type',
                'document_size',
                'document_uploaded_at',
                'signatory_name',
                'signature_date',
            ]);
        });
    }
};
