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
        Schema::create('mutasi_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asn_id')->constrained('asns')->cascadeOnDelete();
            $table->foreignId('opd_asal_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('opd_tujuan_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('jabatan_asal_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('jabatan_tujuan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->date('tanggal_mutasi');
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('jenis_mutasi', ['antar_opd', 'internal_opd'])->default('antar_opd');
            $table->foreignId('created_by')->constrained('admins')->cascadeOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('tanggal_mutasi');
            $table->index('jenis_mutasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_pegawai');
    }
};
