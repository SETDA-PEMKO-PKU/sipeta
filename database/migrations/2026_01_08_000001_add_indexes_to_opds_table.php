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
        Schema::table('opds', function (Blueprint $table) {
            // Add index untuk kolom nama (untuk search)
            $table->index('nama', 'opds_nama_index');
            
            // Add unique constraint untuk nama (prevent duplicate)
            $table->unique('nama', 'opds_nama_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->dropIndex('opds_nama_index');
            $table->dropUnique('opds_nama_unique');
        });
    }
};
