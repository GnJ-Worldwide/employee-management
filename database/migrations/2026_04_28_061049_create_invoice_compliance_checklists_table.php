<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_compliance_checklists', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('invoice_id')->nullable();

            // Which checklist item this row represents (e.g. 'pf_challan', 'labour_license')
            $table->string('check_item_key');

            // Y / N / NA
            $table->enum('status', ['Yes', 'No', 'NA'])->default('NA');

            // Licence No. / Policy No. / TRRN / Code No.
            $table->string('reference_number')->nullable();

            // "Date of Compliance" for recurring items OR "Valid Upto" for licences/policies
            $table->date('validity_date')->nullable();

            // Free-text remarks from the verifier
            $table->text('remarks')->nullable();

            // Audit: who last touched this row
            $table->string('updated_by')->nullable();

            $table->timestamps();

            // One row per checklist item per invoice
            $table->unique(['invoice_id', 'check_item_key']);

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_compliance_checklists');
    }
};