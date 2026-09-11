<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'Tenaga Sanitasi Lingkungan Mahir'    => 'JF Tenaga Sanitasi Lingkungan Mahir',
            'Tenaga Sanitasi Lingkungan Terampil' => 'JF Tenaga Sanitasi Lingkungan Terampil',
        ];

        foreach ($renames as $old => $new) {
            DB::table('jabatans')->where('nama', $old)->update(['nama' => $new]);
            DB::table('nama_jabatan_referensis')->where('nama', $old)->update(['nama' => $new]);
        }
    }

    public function down(): void
    {
        $renames = [
            'JF Tenaga Sanitasi Lingkungan Mahir'    => 'Tenaga Sanitasi Lingkungan Mahir',
            'JF Tenaga Sanitasi Lingkungan Terampil' => 'Tenaga Sanitasi Lingkungan Terampil',
        ];

        foreach ($renames as $old => $new) {
            DB::table('jabatans')->where('nama', $old)->update(['nama' => $new]);
            DB::table('nama_jabatan_referensis')->where('nama', $old)->update(['nama' => $new]);
        }
    }
};
