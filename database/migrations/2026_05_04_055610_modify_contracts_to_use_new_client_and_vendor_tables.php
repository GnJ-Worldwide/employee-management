<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Add FK columns (nullable so existing rows don't break immediately)
            $table->unsignedBigInteger('vendor_id')
                ->nullable()
                ->after('nature_of_work');

            $table->unsignedBigInteger('client_id')
                ->nullable()
                ->after('vendor_id');

            // Drop the old inlined columns (now live on vendors/clients tables)
            $table->dropColumn([
                'vendor_name',
                'vendor_address',
                'vendor_gst_no',
                'client_name',
                'client_address',
                'client_gst_no',
                'vendor_pan_path',           // moved to vendors.pan_document_path
                'vendor_gst_certificate_path', // moved to vendors.gst_certificate_path
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['vendor_id', 'client_id']);

            // Restore dropped columns
            $table->string('vendor_name')->nullable();
            $table->text('vendor_address')->nullable();
            $table->string('vendor_gst_no', 15)->nullable();
            $table->string('client_name')->nullable();
            $table->text('client_address')->nullable();
            $table->string('client_gst_no', 15)->nullable();
            $table->string('vendor_pan_path')->nullable();
            $table->string('vendor_gst_certificate_path')->nullable();
        });
    }
};