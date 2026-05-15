<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('country', 100)->default('India')->after('address');
            $table->string('state')->nullable()->after('country');
            $table->string('district')->nullable()->after('state');
            $table->string('taluka')->nullable()->after('district');
            $table->string('village')->nullable()->after('taluka');
            $table->string('pincode', 12)->nullable()->after('village');
            $table->boolean('location_manual_entry')->default(false)->after('pincode');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'state',
                'district',
                'taluka',
                'village',
                'pincode',
                'location_manual_entry',
            ]);
        });
    }
};
