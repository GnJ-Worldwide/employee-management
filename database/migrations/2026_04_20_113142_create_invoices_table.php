<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')
                  ->nullable();

            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->decimal('amount_billed', 12, 2);

            // Lifecycle engine
            $table->enum('status', [
                'Submitted',
                'Pending_Compliance',
                'Approved',
                'Paid',
                'Rejected',
            ])->default('Submitted');

            // Audit timestamps per stage
            $table->date('submitted_on')->nullable();
            $table->date('approved_on')->nullable();
            $table->date('paid_on')->nullable();

            $table->string('approved_by')->nullable();
            $table->string('paid_by')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};