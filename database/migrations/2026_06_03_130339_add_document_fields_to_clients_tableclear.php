<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // PAN Document
            $table->string('pan_document_mode')->default('file')->after('email');
            $table->string('pan_document_path')->nullable()->after('pan_document_mode');
            $table->text('pan_document_text')->nullable()->after('pan_document_path');

            // GST Certificate
            $table->string('gst_certificate_mode')->default('file')->after('pan_document_text');
            $table->string('gst_certificate_path')->nullable()->after('gst_certificate_mode');
            $table->text('gst_certificate_text')->nullable()->after('gst_certificate_path');

            // User Attachment 1
            $table->string('doc_user_attachment_1_mode')->default('file')->after('gst_certificate_text');
            $table->string('doc_user_attachment_1_path')->nullable()->after('doc_user_attachment_1_mode');
            $table->text('doc_user_attachment_1_text')->nullable()->after('doc_user_attachment_1_path');

            // User Attachment 2
            $table->string('doc_user_attachment_2_mode')->default('file')->after('doc_user_attachment_1_text');
            $table->string('doc_user_attachment_2_path')->nullable()->after('doc_user_attachment_2_mode');
            $table->text('doc_user_attachment_2_text')->nullable()->after('doc_user_attachment_2_path');

            // User Attachment 3
            $table->string('doc_user_attachment_3_mode')->default('file')->after('doc_user_attachment_2_text');
            $table->string('doc_user_attachment_3_path')->nullable()->after('doc_user_attachment_3_mode');
            $table->text('doc_user_attachment_3_text')->nullable()->after('doc_user_attachment_3_path');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'pan_document_mode', 'pan_document_path', 'pan_document_text',
                'gst_certificate_mode', 'gst_certificate_path', 'gst_certificate_text',
                'doc_user_attachment_1_mode', 'doc_user_attachment_1_path', 'doc_user_attachment_1_text',
                'doc_user_attachment_2_mode', 'doc_user_attachment_2_path', 'doc_user_attachment_2_text',
                'doc_user_attachment_3_mode', 'doc_user_attachment_3_path', 'doc_user_attachment_3_text',
            ]);
        });
    }
};