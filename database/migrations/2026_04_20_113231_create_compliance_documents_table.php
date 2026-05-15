<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')
                  ->nullable();

            $table->enum('document_type', [
                'PF_Challan',
                'ESI_Challan',
                'GST_Return',
                'Other',
            ]);

            $table->string('file_path');
            $table->string('original_filename')->nullable();

            $table->enum('status', ['Pending', 'Verified', 'Discrepancy'])
                  ->default('Pending');

            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_documents');
    }
};