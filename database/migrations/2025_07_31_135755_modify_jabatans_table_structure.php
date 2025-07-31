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
        Schema::table('jabatans', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['bagian_id']);
            $table->dropForeign(['parent_id']);
            
            // Drop bagian_id column
            $table->dropColumn('bagian_id');
            
            // Modify parent_id to reference bagians table instead of jabatans
            $table->foreign('parent_id')->references('id')->on('bagians');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['parent_id']);
            
            // Add back bagian_id column
            $table->unsignedBigInteger('bagian_id')->nullable()->after('bezetting');
            
            // Restore original foreign key constraints
            $table->foreign('bagian_id')->references('id')->on('bagians');
            $table->foreign('parent_id')->references('id')->on('jabatans');
        });
    }
};
