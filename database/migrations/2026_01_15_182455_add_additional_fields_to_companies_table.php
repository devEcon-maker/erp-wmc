<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('legal_form')->nullable()->after('name'); // SARL, SA, SAS, etc.
            $table->decimal('capital', 15, 2)->nullable()->after('legal_form'); // Capital social
            $table->string('city')->nullable()->after('address');
            $table->string('country')->default('Côte d\'Ivoire')->after('city');
            $table->string('postal_code')->nullable()->after('country');
            $table->string('rccm')->nullable()->after('tva_number'); // Registre du Commerce
            $table->string('bank_name')->nullable()->after('rccm');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('iban')->nullable()->after('bank_account');
            $table->string('swift')->nullable()->after('iban');
            $table->string('website')->nullable()->after('swift');
            $table->text('invoice_footer')->nullable()->after('website');
            $table->text('invoice_terms')->nullable()->after('invoice_footer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'legal_form',
                'capital',
                'city',
                'country',
                'postal_code',
                'rccm',
                'bank_name',
                'bank_account',
                'iban',
                'swift',
                'website',
                'invoice_footer',
                'invoice_terms',
            ]);
        });
    }
};
