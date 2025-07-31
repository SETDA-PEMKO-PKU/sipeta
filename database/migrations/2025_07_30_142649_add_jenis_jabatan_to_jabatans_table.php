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
            // Tambah kolom jenis_jabatan dengan enum
            $table->enum('jenis_jabatan', ['Staf Ahli', 'Struktural', 'Fungsional', 'Pelaksana'])->after('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Hapus kolom jenis_jabatan
            $table->dropColumn('jenis_jabatan');
        });
    }
};
