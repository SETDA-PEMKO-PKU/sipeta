<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mapping dari format lama ke angka
        $kelasMapping = [
            'IV/e' => 14,  // Pimpinan tertinggi
            'IV/d' => 13,
            'IV/c' => 12,
            'IV/b' => 11,
            'IV/a' => 10,
            'III/d' => 9,
            'III/c' => 8,
            'III/b' => 7,
            'III/a' => 6,
            'II/d' => 5,
            'II/c' => 4,
            'II/b' => 3,
            'II/a' => 2,
            'I/d' => 1,
            'I/c' => 1,
            'I/b' => 1,
            'I/a' => 1
        ];

        // Update data yang ada berdasarkan mapping
        foreach ($kelasMapping as $oldKelas => $newKelas) {
            DB::table('jabatans')
                ->where('kelas', $oldKelas)
                ->update(['kelas' => $newKelas]);
        }

        // Ubah tipe kolom dari string ke integer
        Schema::table('jabatans', function (Blueprint $table) {
            $table->integer('kelas')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mapping dari angka kembali ke format lama
        $kelasMapping = [
            14 => 'IV/e',
            13 => 'IV/d',
            12 => 'IV/c',
            11 => 'IV/b',
            10 => 'IV/a',
            9 => 'III/d',
            8 => 'III/c',
            7 => 'III/b',
            6 => 'III/a',
            5 => 'II/d',
            4 => 'II/c',
            3 => 'II/b',
            2 => 'II/a',
            1 => 'I/a'
        ];

        // Ubah tipe kolom kembali ke string
        Schema::table('jabatans', function (Blueprint $table) {
            $table->string('kelas', 50)->default('I/a')->change();
        });

        // Update data kembali ke format lama
        foreach ($kelasMapping as $newKelas => $oldKelas) {
            DB::table('jabatans')
                ->where('kelas', $newKelas)
                ->update(['kelas' => $oldKelas]);
        }
    }
};
