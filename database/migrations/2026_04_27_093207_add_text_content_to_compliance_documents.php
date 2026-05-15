<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compliance_documents', function (Blueprint $table) {
            // Allow file_path to be null when the user enters text instead
            $table->string('file_path')->nullable()->change();
            $table->string('original_filename')->nullable()->change();

            // New: stores manually entered text/markdown content
            $table->longText('text_content')->nullable()->after('original_filename');

            // Track which input mode was used
            $table->enum('input_mode', ['file', 'text'])->default('file')->after('text_content');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_documents', function (Blueprint $table) {
            $table->dropColumn(['text_content', 'input_mode']);
            $table->string('file_path')->nullable(false)->change();
            $table->string('original_filename')->nullable(false)->change();
        });
    }
};