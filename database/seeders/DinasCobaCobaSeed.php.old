<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Opd;
use App\Models\Bagian;
use App\Models\Jabatan;

class DinasCobaCobaSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat OPD Dinas Coba-coba
        $opd = Opd::create([
            'nama' => 'Dinas Coba-coba'
        ]);

        // Buat bagian-bagian
        $sekretariat = Bagian::create([
            'nama' => 'Sekretariat',
            'opd_id' => $opd->id
        ]);

        $bidangPelayanan = Bagian::create([
            'nama' => 'Bidang Pelayanan Publik',
            'opd_id' => $opd->id
        ]);

        $bidangTeknis = Bagian::create([
            'nama' => 'Bidang Teknis',
            'opd_id' => $opd->id
        ]);

        $bidangKeuangan = Bagian::create([
            'nama' => 'Bidang Keuangan',
            'opd_id' => $opd->id
        ]);

        // Buat jabatan-jabatan
        // Jabatan Pimpinan (tanpa parent_id)
        Jabatan::create([
            'nama' => 'Kepala Dinas',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 10, // IV/a
            'kebutuhan' => 1,
            'bezetting' => 1,
            'parent_id' => null
        ]);

        // Jabatan di Sekretariat
        Jabatan::create([
            'nama' => 'Sekretaris',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9, // III/d
            'kebutuhan' => 1,
            'bezetting' => 1,
            'parent_id' => $sekretariat->id
        ]);

        Jabatan::create([
            'nama' => 'Kepala Sub Bagian Umum',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 8, // III/c
            'kebutuhan' => 1,
            'bezetting' => 0,
            'parent_id' => $sekretariat->id
        ]);

        Jabatan::create([
            'nama' => 'Staf Administrasi',
            'jenis_jabatan' => 'Pelaksana',
            'kelas' => 4, // II/c
            'kebutuhan' => 3,
            'bezetting' => 2,
            'parent_id' => $sekretariat->id
        ]);

        // Jabatan di Bidang Pelayanan Publik
        Jabatan::create([
            'nama' => 'Kepala Bidang Pelayanan Publik',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9, // III/d
            'kebutuhan' => 1,
            'bezetting' => 1,
            'parent_id' => $bidangPelayanan->id
        ]);

        Jabatan::create([
            'nama' => 'Analis Pelayanan Publik',
            'jenis_jabatan' => 'Fungsional',
            'kelas' => 6, // III/a
            'kebutuhan' => 2,
            'bezetting' => 1,
            'parent_id' => $bidangPelayanan->id
        ]);

        // Jabatan di Bidang Teknis
        Jabatan::create([
            'nama' => 'Kepala Bidang Teknis',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9, // III/d
            'kebutuhan' => 1,
            'bezetting' => 0,
            'parent_id' => $bidangTeknis->id
        ]);

        Jabatan::create([
            'nama' => 'Teknisi Ahli',
            'jenis_jabatan' => 'Fungsional',
            'kelas' => 7, // III/b
            'kebutuhan' => 4,
            'bezetting' => 3,
            'parent_id' => $bidangTeknis->id
        ]);

        Jabatan::create([
            'nama' => 'Teknisi Pelaksana',
            'jenis_jabatan' => 'Pelaksana',
            'kelas' => 5, // II/d
            'kebutuhan' => 6,
            'bezetting' => 4,
            'parent_id' => $bidangTeknis->id
        ]);

        // Jabatan di Bidang Keuangan
        Jabatan::create([
            'nama' => 'Kepala Bidang Keuangan',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9, // III/d
            'kebutuhan' => 1,
            'bezetting' => 1,
            'parent_id' => $bidangKeuangan->id
        ]);

        Jabatan::create([
            'nama' => 'Analis Anggaran',
            'jenis_jabatan' => 'Fungsional',
            'kelas' => 6, // III/a
            'kebutuhan' => 2,
            'bezetting' => 2,
            'parent_id' => $bidangKeuangan->id
        ]);

        Jabatan::create([
            'nama' => 'Bendahara',
            'jenis_jabatan' => 'Fungsional',
            'kelas' => 7, // III/b
            'kebutuhan' => 1,
            'bezetting' => 1,
            'parent_id' => $bidangKeuangan->id
        ]);

        echo "OPD 'Dinas Coba-coba' berhasil dibuat dengan 4 bagian dan 12 jabatan.\n";
    }
}