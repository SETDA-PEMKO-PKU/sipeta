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
        Schema::table('asns', function (Blueprint $table) {
            $table->unsignedBigInteger('bagian_id')->nullable()->after('jabatan_id');
            $table->unsignedBigInteger('opd_id')->nullable()->after('bagian_id');
            
            $table->foreign('bagian_id')->references('id')->on('bagians')->onDelete('set null');
            $table->foreign('opd_id')->references('id')->on('opds')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asns', function (Blueprint $table) {
            $table->dropForeign(['bagian_id']);
            $table->dropForeign(['opd_id']);
            $table->dropColumn(['bagian_id', 'opd_id']);
        });
    }
};
