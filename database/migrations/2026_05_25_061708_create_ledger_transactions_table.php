<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('employee_id')
                ->nullable();

            $table->unsignedBigInteger('contract_id')
                ->nullable();

            $table->date('transaction_date');

            $table->string('type');
            // debit / credit

            $table->string('category')->nullable();

            $table->decimal('amount', 12, 2);

            $table->decimal('balance', 12, 2)->default(0);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
    }
};