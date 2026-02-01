<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing UTC timestamps to WIB (UTC+7)
        // Add 7 hours to all existing timestamps
        DB::statement('UPDATE admin_activity_logs SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)');
        DB::statement('UPDATE admin_activity_logs SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert by subtracting 7 hours
        DB::statement('UPDATE admin_activity_logs SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR)');
        DB::statement('UPDATE admin_activity_logs SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR)');
    }
};
