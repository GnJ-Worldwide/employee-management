<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('doc_moa_mode')->default('file')->after('doc_moa');
            $table->text('doc_moa_text')->nullable()->after('doc_moa_mode');

            $table->string('doc_incorporation_mode')->default('file')->after('doc_incorporation');
            $table->text('doc_incorporation_text')->nullable()->after('doc_incorporation_mode');

            $table->string('doc_pan_card_mode')->default('file')->after('doc_pan_card');
            $table->text('doc_pan_card_text')->nullable()->after('doc_pan_card_mode');

            $table->string('doc_pf_registration_mode')->default('file')->after('doc_pf_registration');
            $table->text('doc_pf_registration_text')->nullable()->after('doc_pf_registration_mode');

            $table->string('doc_esic_registration_mode')->default('file')->after('doc_esic_registration');
            $table->text('doc_esic_registration_text')->nullable()->after('doc_esic_registration_mode');

            $table->string('doc_gst_registration_mode')->default('file')->after('doc_gst_registration');
            $table->text('doc_gst_registration_text')->nullable()->after('doc_gst_registration_mode');

            $table->string('doc_msme_registration_mode')->default('file')->after('doc_msme_registration');
            $table->text('doc_msme_registration_text')->nullable()->after('doc_msme_registration_mode');

            $table->string('doc_iso_registration_mode')->default('file')->after('doc_iso_registration');
            $table->text('doc_iso_registration_text')->nullable()->after('doc_iso_registration_mode');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'doc_moa_mode', 'doc_moa_text',
                'doc_incorporation_mode', 'doc_incorporation_text',
                'doc_pan_card_mode', 'doc_pan_card_text',
                'doc_pf_registration_mode', 'doc_pf_registration_text',
                'doc_esic_registration_mode', 'doc_esic_registration_text',
                'doc_gst_registration_mode', 'doc_gst_registration_text',
                'doc_msme_registration_mode', 'doc_msme_registration_text',
                'doc_iso_registration_mode', 'doc_iso_registration_text',
            ]);
        });
    }
};