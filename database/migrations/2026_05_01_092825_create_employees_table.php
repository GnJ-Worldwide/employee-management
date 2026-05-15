<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {

            $table->id();

            // ── Employment Status ──────────────────────────────────────────
            $table->string('aadhaar_number')->unique();
            $table->date('joined_at');
            $table->date('exited_at')->nullable();
            $table->date('rejoined_at')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->string('emp_status')->default('active');
            // e.g.: active | on_leave | terminated | resigned

            // ── Personal Information ───────────────────────────────────────
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('guardian_name');
            // Father name for unmarried; husband name for married female employees
            $table->string('mobile_number');
            $table->enum('marital_status', ['Married', 'UnMarried']);
            $table->string('blood_group');
            $table->string('nationality')->default('Indian');
            $table->string('identification_mark');

            // ── Address ────────────────────────────────────────────────────
            $table->text('permanent_address');
            $table->text('present_address');
            $table->string('country')->default('India');
            $table->string('state');
            $table->string('district');
            $table->string('taluka');
            $table->string('village_city');
            $table->string('pin_code');

            // ── Professional Details ───────────────────────────────────────
            $table->enum('trade', ['Un Skilled', 'Semi-Skilled', 'Skilled', 'Highly Skilled']);
            $table->string('skill_level')->nullable();
            $table->string('designation');
            $table->string('highest_education')->nullable();
            // e.g. "10th Pass", "Diploma in Civil Engineering"
            $table->string('academics');
            // Stores the selected degree/course or free-text if "Other" chosen

            // ── Statutory Identifiers ──────────────────────────────────────
            $table->string('pan_number')->nullable();
            $table->string('uan_number')->nullable();
            $table->string('esic_number')->nullable();

            // ── Bank Details ───────────────────────────────────────────────
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_ifsc_code');

            // ── Nominee ────────────────────────────────────────────────────
            $table->string('nominee_name');
            $table->string('nominee_relationship');
            $table->date('nominee_date_of_birth');
            $table->string('nominee_mobile_number');

            // ── Document Paths ─────────────────────────────────────────────
            // All stored relative to the `public` disk; photo is PNG, rest PDF
            $table->string('doc_photo')->nullable();
            $table->string('doc_aadhaar')->nullable();
            $table->string('doc_pan')->nullable();
            $table->string('doc_bank_passbook')->nullable();
            $table->string('doc_education_certificate')->nullable();

            // ── Audit ──────────────────────────────────────────────────────
            $table->string('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ────────────────────────────────────────────────────
            $table->index('emp_status');
            $table->index('designation');
            $table->index(['first_name', 'last_name']);
            $table->index('joined_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};