<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Opd;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Asn;

class OpdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya tambahkan data jika belum ada
        if (Opd::count() == 0) {
            // Buat OPD contoh
            $opd1 = Opd::create([
                'nama' => 'Dinas Pendidikan'
            ]);

            $opd2 = Opd::create([
                'nama' => 'Dinas Kesehatan'
            ]);

            $opd3 = Opd::create([
                'nama' => 'Badan Kepegawaian Daerah'
            ]);

            // Buat bagian untuk Dinas Pendidikan
            $bagianSekretariat = Bagian::create([
                'nama' => 'Sekretariat',
                'opd_id' => $opd1->id,
                'parent_id' => null
            ]);

            $bagianPendidikanDasar = Bagian::create([
                'nama' => 'Bidang Pendidikan Dasar',
                'opd_id' => $opd1->id,
                'parent_id' => null
            ]);

            // Sub bagian
            $subBagianUmum = Bagian::create([
                'nama' => 'Sub Bagian Umum dan Kepegawaian',
                'opd_id' => $opd1->id,
                'parent_id' => $bagianSekretariat->id
            ]);

            // Buat jabatan
            $jabatanKepala = Jabatan::create([
                'nama' => 'Kepala Dinas',
                'kelas' => 'IV/a',
                'kebutuhan' => 1,
                'bezetting' => 1,
                'bagian_id' => $bagianSekretariat->id,
                'parent_id' => null
            ]);

            $jabatanSekretaris = Jabatan::create([
                'nama' => 'Sekretaris',
                'kelas' => 'IV/a',
                'kebutuhan' => 1,
                'bezetting' => 1,
                'bagian_id' => $bagianSekretariat->id,
                'parent_id' => $jabatanKepala->id
            ]);

            $jabatanKasubag = Jabatan::create([
                'nama' => 'Kepala Sub Bagian Umum',
                'kelas' => 'III/d',
                'kebutuhan' => 1,
                'bezetting' => 0,
                'bagian_id' => $subBagianUmum->id,
                'parent_id' => $jabatanSekretaris->id
            ]);

            // Buat ASN contoh
            Asn::create([
                'nama' => 'Dr. Ahmad Suryadi, M.Pd',
                'nip' => '196501011990031001',
                'jabatan_id' => $jabatanKepala->id
            ]);

            Asn::create([
                'nama' => 'Dra. Siti Nurhaliza, M.M',
                'nip' => '197203151995032002',
                'jabatan_id' => $jabatanSekretaris->id
            ]);
        }
    }
}
