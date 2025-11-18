<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        Admin::create([
            'name' => 'Administrator',
            'email' => 'admin@sipeta.com',
            'password' => Hash::make('password'),
        ]);

        // Create sample OPD
        $opd1 = Opd::create(['nama' => 'Dinas Pendidikan']);
        $opd2 = Opd::create(['nama' => 'Dinas Kesehatan']);
        $opd3 = Opd::create(['nama' => 'Dinas Perhubungan']);

        // Create jabatan hierarchy for Dinas Pendidikan
        $kepalaDisdik = Jabatan::create([
            'nama' => 'Kepala Dinas Pendidikan',
            'opd_id' => $opd1->id,
            'parent_id' => null,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 14,
            'kebutuhan' => 1,
        ]);

        $kasubagUmum = Jabatan::create([
            'nama' => 'Kepala Sub Bagian Umum',
            'opd_id' => null,
            'parent_id' => $kepalaDisdik->id,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
        ]);

        $staffUmum = Jabatan::create([
            'nama' => 'Staf Sub Bagian Umum',
            'opd_id' => null,
            'parent_id' => $kasubagUmum->id,
            'jenis_jabatan' => 'Pelaksana',
            'kelas' => 5,
            'kebutuhan' => 3,
        ]);

        $kabidPendidikan = Jabatan::create([
            'nama' => 'Kepala Bidang Pendidikan Dasar',
            'opd_id' => null,
            'parent_id' => $kepalaDisdik->id,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 11,
            'kebutuhan' => 1,
        ]);

        $kaseksiPendidikan = Jabatan::create([
            'nama' => 'Kepala Seksi Kurikulum',
            'opd_id' => null,
            'parent_id' => $kabidPendidikan->id,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
        ]);

        // Create jabatan hierarchy for Dinas Kesehatan
        $kepalaDinkes = Jabatan::create([
            'nama' => 'Kepala Dinas Kesehatan',
            'opd_id' => $opd2->id,
            'parent_id' => null,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 14,
            'kebutuhan' => 1,
        ]);

        $kabidPelayanan = Jabatan::create([
            'nama' => 'Kepala Bidang Pelayanan Kesehatan',
            'opd_id' => null,
            'parent_id' => $kepalaDinkes->id,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 11,
            'kebutuhan' => 1,
        ]);

        // Create jabatan for Dinas Perhubungan
        $kepalaDishub = Jabatan::create([
            'nama' => 'Kepala Dinas Perhubungan',
            'opd_id' => $opd3->id,
            'parent_id' => null,
            'jenis_jabatan' => 'Struktural',
            'kelas' => 14,
            'kebutuhan' => 1,
        ]);

        // Create sample ASN
        Asn::create([
            'nama' => 'Dr. Ahmad Suryanto, M.Pd',
            'nip' => '196801011990031001',
            'jabatan_id' => $kepalaDisdik->id,
            'opd_id' => $opd1->id,
        ]);

        Asn::create([
            'nama' => 'Siti Nurhaliza, S.Pd',
            'nip' => '198505152009032002',
            'jabatan_id' => $kasubagUmum->id,
            'opd_id' => $opd1->id,
        ]);

        Asn::create([
            'nama' => 'Budi Santoso',
            'nip' => '199003102015031003',
            'jabatan_id' => $staffUmum->id,
            'opd_id' => $opd1->id,
        ]);

        Asn::create([
            'nama' => 'Dr. Siti Rahayu, Sp.PD',
            'nip' => '197505101998032001',
            'jabatan_id' => $kepalaDinkes->id,
            'opd_id' => $opd2->id,
        ]);

        Asn::create([
            'nama' => 'Ir. Bambang Susilo, M.T',
            'nip' => '197802152003121001',
            'jabatan_id' => $kepalaDishub->id,
            'opd_id' => $opd3->id,
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
