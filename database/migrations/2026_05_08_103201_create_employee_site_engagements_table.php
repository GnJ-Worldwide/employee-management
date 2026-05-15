<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_site_engagements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id')
                ->nullable();

            $table->unsignedBigInteger('vendor_id')
                ->nullable();

            $table->unsignedBigInteger('contract_id')
                ->nullable();

            // Engagement window
            $table->date('engaged_date');
            $table->date('released_date')->nullable();

            // Status: only one row per employee can be 'Engaged' at a time
            // (enforced at application layer + unique partial index below)
            $table->enum('status', ['Engaged', 'Released'])->default('Engaged');

            // Audit trail
            $table->text('engagement_remarks')->nullable();
            $table->text('release_remarks')->nullable();
            $table->string('engaged_by')->nullable();  
            $table->string('released_by')->nullable();

            $table->timestamps();

            // Prevent duplicate active engagements at DB level.
            // A unique index on (employee_id) where status = 'Engaged'
            // cannot be expressed in standard SQL here — we enforce it in the
            // model/service layer. The index below at least speeds up the lookup.
            $table->index(['employee_id', 'status']);
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_site_engagements');
    }
};