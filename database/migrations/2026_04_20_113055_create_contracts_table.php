<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_code', 100)->unique();
            $table->string('contract_title');
            $table->text('nature_of_work')->nullable();

            // Vendor
            $table->string('vendor_name');
            $table->text('vendor_address')->nullable();
            $table->string('vendor_gst_no', 15)->nullable();

            // Client / Principal Employer
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_gst_no', 15)->nullable();

            // Period
            $table->date('start_date');
            $table->date('end_date');

            // Status
            $table->enum('overall_status', ['Active', 'Expired', 'Terminated'])
                  ->default('Active');

            // Foundational attachments (one-time, per contract)
            $table->string('po_document_path')->nullable();
            $table->string('vendor_pan_path')->nullable();
            $table->string('vendor_gst_certificate_path')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};