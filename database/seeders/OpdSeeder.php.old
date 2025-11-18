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
                'jenis_jabatan' => 'Struktural',
                'kelas' => 'IV/b',
                'kebutuhan' => 1,
                'bezetting' => 1,
                'parent_id' => null // Jabatan pimpinan tanpa parent bagian
            ]);

            $jabatanSekretaris = Jabatan::create([
                'nama' => 'Sekretaris',
                'jenis_jabatan' => 'Struktural',
                'kelas' => 'IV/a',
                'kebutuhan' => 1,
                'bezetting' => 1,
                'parent_id' => $bagianSekretariat->id // Parent bagian adalah Sekretariat
            ]);

            $jabatanKasubag = Jabatan::create([
                'nama' => 'Kepala Sub Bagian Umum',
                'jenis_jabatan' => 'Struktural',
                'kelas' => 'III/d',
                'kebutuhan' => 1,
                'bezetting' => 0,
                'parent_id' => $subBagianUmum->id // Parent bagian adalah Sub Bagian Umum
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
