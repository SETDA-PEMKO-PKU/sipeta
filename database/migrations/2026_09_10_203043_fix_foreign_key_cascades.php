<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutasi_pegawai', function (Blueprint $table) {
            // Drop existing foreign keys with cascade
            $table->dropForeign(['asn_id']);
            $table->dropForeign(['opd_asal_id']);
            $table->dropForeign(['opd_tujuan_id']);
            $table->dropForeign(['jabatan_asal_id']);
            $table->dropForeign(['jabatan_tujuan_id']);
            $table->dropForeign(['created_by']);

            // Re-add without cascade
            $table->foreign('asn_id')->references('id')->on('asns')->restrictOnDelete();
            $table->foreign('opd_asal_id')->references('id')->on('opds')->restrictOnDelete();
            $table->foreign('opd_tujuan_id')->references('id')->on('opds')->restrictOnDelete();
            $table->foreign('jabatan_asal_id')->references('id')->on('jabatans')->nullOnDelete();
            $table->foreign('jabatan_tujuan_id')->references('id')->on('jabatans')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('admins')->restrictOnDelete();
        });


    }

    public function down(): void
    {
        Schema::table('mutasi_pegawai', function (Blueprint $table) {
            $table->dropForeign(['asn_id']);
            $table->dropForeign(['opd_asal_id']);
            $table->dropForeign(['opd_tujuan_id']);
            $table->dropForeign(['jabatan_asal_id']);
            $table->dropForeign(['jabatan_tujuan_id']);
            $table->dropForeign(['created_by']);

            $table->foreignId('asn_id')->constrained('asns')->cascadeOnDelete();
            $table->foreignId('opd_asal_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('opd_tujuan_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('jabatan_asal_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('jabatan_tujuan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('created_by')->constrained('admins')->cascadeOnDelete();
        });
    }
};
