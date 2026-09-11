<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('nama_jabatan_referensis')
            ->selectRaw('nama, jenis_jabatan, MIN(id) as keep_id')
            ->groupBy('nama', 'jenis_jabatan')
            ->havingRaw('COUNT(*) > 1')
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
        // Duplikat tidak di-restore
    }
};
