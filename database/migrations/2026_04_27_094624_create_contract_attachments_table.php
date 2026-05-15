<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->nullable();

            // Plain string — no enum, free to add types without migrations
            $table->string('document_type');

            // File upload path (nullable — user may enter text instead)
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();

            // Manual text entry (nullable — user may upload a file instead)
            $table->longText('text_content')->nullable();

            // Tracks which input was used
            $table->enum('input_mode', ['file', 'text'])->default('file');

            $table->enum('status', ['Pending', 'Verified', 'Discrepancy'])->default('Pending');
            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_attachments');
    }
};