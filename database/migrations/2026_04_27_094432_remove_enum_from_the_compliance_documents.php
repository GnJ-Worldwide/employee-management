<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // For MySQL: cast the column to varchar, dropping the enum constraint
        DB::statement('ALTER TABLE compliance_documents MODIFY document_type VARCHAR(100) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE compliance_documents MODIFY document_type ENUM(
            'form_a','form_b','form_c','form_d','ecr_pf',
            'challan_and_copy_of_remittance_pf','ecr_esic',
            'challan_and_copy_of_remittance_esic','bank_statement',
            'annual_return','bonus_register','lwf_challan_and_remittance',
            'challan_and_copy_of_remittance_pt','user_attachment_1',
            'user_attachment_2','user_attachment_3','other'
        ) NOT NULL");
    }
};