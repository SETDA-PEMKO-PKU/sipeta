<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename jabatan di tabel jabatans
        DB::table('jabatans')
            ->where('nama', 'JF Sanitarian Penyelia')
            ->update(['nama' => 'JF Tenaga Sanitasi Lingkungan Penyelia']);

        // 2. Rename di tabel nama_jabatan_referensis
        DB::table('nama_jabatan_referensis')
            ->where('nama', 'JF Sanitarian Penyelia')
            ->update(['nama' => 'JF Tenaga Sanitasi Lingkungan Penyelia']);

        // 3. Hapus duplikat di nama_jabatan_referensis — keep id terkecil per (nama, jenis_jabatan)
        $duplicates = DB::table('nama_jabatan_referensis')
            ->selectRaw('nama, jenis_jabatan, MIN(id) as keep_id')
            ->groupBy('nama', 'jenis_jabatan')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('nama_jabatan_referensis')
                ->where('nama', $dup->nama)
                ->where('jenis_jabatan', $dup->jenis_jabatan)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }
    }

    public function down(): void
    {
        // Kembalikan nama jabatan
        DB::table('jabatans')
            ->where('nama', 'JF Tenaga Sanitasi Lingkungan Penyelia')
            ->where('id', 3481)
            ->update(['nama' => 'JF Sanitarian Penyelia']);

        DB::table('nama_jabatan_referensis')
            ->where('nama', 'JF Tenaga Sanitasi Lingkungan Penyelia')
            ->where('id', 498)
            ->update(['nama' => 'JF Sanitarian Penyelia']);

        // Duplikat tidak di-restore karena data sudah tidak ada
    }
};
