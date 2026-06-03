<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_transactions', function (Blueprint $table) {

            $table->string('expense_type')
                ->nullable()
                ->after('category');

            $table->string('vendor_name')
                ->nullable()
                ->after('balance');

            $table->string('bill_number')
                ->nullable()
                ->after('vendor_name');

            $table->string('bill_image')
                ->nullable()
                ->after('bill_number');

            $table->string('reference_no')
                ->nullable()
                ->after('bill_image');

            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('ledger_transactions', function (Blueprint $table) {

            $table->dropColumn([
                'expense_type',
                'vendor_name',
                'bill_number',
                'bill_image',
                'reference_no',
                'created_by',
            ]);
        });
    }
};