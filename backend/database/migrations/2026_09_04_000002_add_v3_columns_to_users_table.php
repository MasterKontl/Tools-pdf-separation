<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('USER')->after('password');
            $table->foreignId('plan_id')->nullable()->after('role')->constrained('plans')->nullOnDelete();
            $table->unsignedInteger('daily_limit')->nullable()->after('plan_id');
            $table->boolean('unlimited')->default(false)->after('daily_limit');
            $table->boolean('is_active')->default(true)->after('unlimited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['role', 'plan_id', 'daily_limit', 'unlimited', 'is_active']);
        });
    }
};
