<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('engagement_status', ['Available', 'Engaged'])
                ->default('Available')
                ->after('emp_status');

            $table->unsignedBigInteger('current_vendor_id')
                ->nullable()
                ->after('engagement_status');

            $table->unsignedBigInteger('current_contract_id')
                ->nullable()
                ->after('current_vendor_id');

        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'engagement_status',
                'current_vendor_id',
                'current_contract_id',
            ]);
        });
    }
};