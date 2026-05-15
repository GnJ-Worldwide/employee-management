<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('doc_aadhaar_mode')->default('file')->after('doc_aadhaar');
            $table->text('doc_aadhaar_text')->nullable()->after('doc_aadhaar_mode');

            $table->string('doc_pan_mode')->default('file')->after('doc_pan');
            $table->text('doc_pan_text')->nullable()->after('doc_pan_mode');

            $table->string('doc_bank_passbook_mode')->default('file')->after('doc_bank_passbook');
            $table->text('doc_bank_passbook_text')->nullable()->after('doc_bank_passbook_mode');

            $table->string('doc_education_certificate_mode')->default('file')->after('doc_education_certificate');
            $table->text('doc_education_certificate_text')->nullable()->after('doc_education_certificate_mode');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'doc_aadhaar_mode', 'doc_aadhaar_text',
                'doc_pan_mode', 'doc_pan_text',
                'doc_bank_passbook_mode', 'doc_bank_passbook_text',
                'doc_education_certificate_mode', 'doc_education_certificate_text',
            ]);
        });
    }
};