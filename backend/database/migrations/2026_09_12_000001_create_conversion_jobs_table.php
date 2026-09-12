<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversion_jobs', function (Blueprint $table) {
            $table->string('job_id')->primary();
            $table->string('status')->default('processing');
            $table->string('format')->default('png');
            $table->integer('dpi')->default(300);
            $table->string('original_name')->nullable();
            $table->string('source_path')->nullable();
            $table->text('error')->nullable();
            $table->string('error_type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->boolean('is_zip')->default(false);
            $table->integer('page_count')->nullable();
            $table->decimal('elapsed_sec', 8, 2)->nullable();
            $table->text('quota_data')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversion_jobs');
    }
};
