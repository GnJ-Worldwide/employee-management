<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('gst_no', 15)->nullable();
            $table->string('pan_no', 10)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('email')->nullable();

            $table->string('pan_document_path')->nullable();
            $table->string('gst_certificate_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};