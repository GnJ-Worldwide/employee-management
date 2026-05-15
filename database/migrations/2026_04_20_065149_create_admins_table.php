<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            // ── General Information ──────────────────────────────────────────
            $table->string('admin_name');
            $table->string('register_office')->nullable();
            $table->string('office')->nullable();
            $table->string('contact_no', 10);
            $table->string('email')->unique();
            $table->string('gst_registration_no')->nullable();
            $table->string('gst_state')->nullable();   // auto-fetched from GST No.

            // ── Permanent Address ────────────────────────────────────────────
            $table->string('country')->default('India');
            $table->string('address_state')->nullable();
            $table->string('district')->nullable();
            $table->string('taluka')->nullable();
            $table->string('village_city')->nullable();
            $table->string('pin_code')->nullable();

            // ── Bank & Other Information ─────────────────────────────────────
            $table->string('account_number')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_micr')->nullable();

            // ── Statutory & Regulatory Attachments (PDF paths) ───────────────
            $table->string('doc_moa')->nullable();                  // Memorandum of Association
            $table->string('doc_incorporation')->nullable();        // Certificate of Incorporation
            $table->string('doc_pan_card')->nullable();             // PAN Card
            $table->string('doc_pf_registration')->nullable();      // PF Registration
            $table->string('doc_esic_registration')->nullable();    // ESIC Registration
            $table->string('doc_gst_registration')->nullable();     // GST Registration
            $table->string('doc_msme_registration')->nullable();    // MSME Registration
            $table->string('doc_iso_registration')->nullable();     // ISO Registration

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};