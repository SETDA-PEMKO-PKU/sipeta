<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use Faker\Factory as Faker;

class OpdLengkapSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Buat OPD
        $opd = Opd::create([
            'nama' => 'Dinas Komunikasi dan Informatika'
        ]);

        echo "✓ OPD dibuat: {$opd->nama}\n";

        // 2. Buat Hierarki Jabatan

        // LEVEL 1: Kepala Dinas
        $kepalaDinas = Jabatan::create([
            'nama' => 'Kepala Dinas Komunikasi dan Informatika',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 14,
            'kebutuhan' => 1,
            'parent_id' => null,
            'opd_id' => $opd->id
        ]);

        // LEVEL 2: Sekretariat
        $sekretariat = Jabatan::create([
            'nama' => 'Sekretaris',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 12,
            'kebutuhan' => 1,
            'parent_id' => $kepalaDinas->id,
            'opd_id' => null
        ]);

        // LEVEL 3: Sub Bagian di bawah Sekretariat
        $subBagUmum = Jabatan::create([
            'nama' => 'Kepala Sub Bagian Umum dan Kepegawaian',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $sekretariat->id,
            'opd_id' => null
        ]);

        $subBagKeuangan = Jabatan::create([
            'nama' => 'Kepala Sub Bagian Keuangan',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $sekretariat->id,
            'opd_id' => null
        ]);

        $subBagPerencanaan = Jabatan::create([
            'nama' => 'Kepala Sub Bagian Perencanaan dan Pelaporan',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $sekretariat->id,
            'opd_id' => null
        ]);

        // LEVEL 4: Staff di Sub Bagian
        $staffJabatansSekretariat = [
            // Staff Sub Bag Umum
            ['nama' => 'Pengelola Kepegawaian', 'parent' => $subBagUmum->id, 'kelas' => 7, 'kebutuhan' => 2],
            ['nama' => 'Pengelola Barang Milik Daerah', 'parent' => $subBagUmum->id, 'kelas' => 7, 'kebutuhan' => 2],
            ['nama' => 'Pengadministrasi Umum', 'parent' => $subBagUmum->id, 'kelas' => 5, 'kebutuhan' => 3],
            ['nama' => 'Operator Komputer', 'parent' => $subBagUmum->id, 'kelas' => 5, 'kebutuhan' => 2],
            ['nama' => 'Caraka', 'parent' => $subBagUmum->id, 'kelas' => 3, 'kebutuhan' => 2],

            // Staff Sub Bag Keuangan
            ['nama' => 'Pengelola Keuangan', 'parent' => $subBagKeuangan->id, 'kelas' => 7, 'kebutuhan' => 2],
            ['nama' => 'Verifikator Keuangan', 'parent' => $subBagKeuangan->id, 'kelas' => 7, 'kebutuhan' => 2],
            ['nama' => 'Bendahara Pengeluaran', 'parent' => $subBagKeuangan->id, 'kelas' => 7, 'kebutuhan' => 1],
            ['nama' => 'Bendahara Penerimaan', 'parent' => $subBagKeuangan->id, 'kelas' => 7, 'kebutuhan' => 1],
            ['nama' => 'Pengadministrasi Keuangan', 'parent' => $subBagKeuangan->id, 'kelas' => 5, 'kebutuhan' => 3],

            // Staff Sub Bag Perencanaan
            ['nama' => 'Analis Perencanaan', 'parent' => $subBagPerencanaan->id, 'kelas' => 9, 'kebutuhan' => 2],
            ['nama' => 'Analis Pelaporan', 'parent' => $subBagPerencanaan->id, 'kelas' => 7, 'kebutuhan' => 2],
            ['nama' => 'Pengadministrasi Perencanaan', 'parent' => $subBagPerencanaan->id, 'kelas' => 5, 'kebutuhan' => 2],
        ];

        foreach ($staffJabatansSekretariat as $staffData) {
            Jabatan::create([
                'nama' => $staffData['nama'],
                'jenis_jabatan' => 'Fungsional',
                'kelas' => $staffData['kelas'],
                'kebutuhan' => $staffData['kebutuhan'],
                'parent_id' => $staffData['parent'],
                'opd_id' => null
            ]);
        }

        // LEVEL 2: Bidang Infrastruktur TIK
        $bidangInfrastruktur = Jabatan::create([
            'nama' => 'Kepala Bidang Infrastruktur Teknologi Informasi dan Komunikasi',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 12,
            'kebutuhan' => 1,
            'parent_id' => $kepalaDinas->id,
            'opd_id' => null
        ]);

        // LEVEL 3: Sub Bidang di Bidang Infrastruktur
        $subBidJaringan = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Jaringan dan Infrastruktur',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangInfrastruktur->id,
            'opd_id' => null
        ]);

        $subBidDataCenter = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Data Center dan Disaster Recovery',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangInfrastruktur->id,
            'opd_id' => null
        ]);

        // LEVEL 4: Staff di Bidang Infrastruktur
        $staffJabatansInfrastruktur = [
            // Staff Sub Bid Jaringan
            ['nama' => 'Pranata Komputer Ahli Muda', 'parent' => $subBidJaringan->id, 'kelas' => 11, 'kebutuhan' => 2],
            ['nama' => 'Pranata Komputer Ahli Pertama', 'parent' => $subBidJaringan->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Pranata Komputer Terampil', 'parent' => $subBidJaringan->id, 'kelas' => 7, 'kebutuhan' => 4],
            ['nama' => 'Teknisi Jaringan', 'parent' => $subBidJaringan->id, 'kelas' => 5, 'kebutuhan' => 5],

            // Staff Sub Bid Data Center
            ['nama' => 'Administrator Database', 'parent' => $subBidDataCenter->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Administrator Sistem', 'parent' => $subBidDataCenter->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Teknisi Server', 'parent' => $subBidDataCenter->id, 'kelas' => 7, 'kebutuhan' => 4],
            ['nama' => 'Security Operations Analyst', 'parent' => $subBidDataCenter->id, 'kelas' => 9, 'kebutuhan' => 2],
        ];

        foreach ($staffJabatansInfrastruktur as $staffData) {
            Jabatan::create([
                'nama' => $staffData['nama'],
                'jenis_jabatan' => 'Fungsional',
                'kelas' => $staffData['kelas'],
                'kebutuhan' => $staffData['kebutuhan'],
                'parent_id' => $staffData['parent'],
                'opd_id' => null
            ]);
        }

        // LEVEL 2: Bidang Aplikasi dan Layanan Informasi
        $bidangAplikasi = Jabatan::create([
            'nama' => 'Kepala Bidang Aplikasi dan Layanan Informasi',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 12,
            'kebutuhan' => 1,
            'parent_id' => $kepalaDinas->id,
            'opd_id' => null
        ]);

        // LEVEL 3: Sub Bidang di Bidang Aplikasi
        $subBidAplikasi = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Pengembangan Aplikasi',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangAplikasi->id,
            'opd_id' => null
        ]);

        $subBidLayananData = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Layanan Data dan Informasi',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangAplikasi->id,
            'opd_id' => null
        ]);

        // LEVEL 4: Staff di Bidang Aplikasi
        $staffJabatansAplikasi = [
            // Staff Sub Bid Pengembangan Aplikasi
            ['nama' => 'Analis Sistem', 'parent' => $subBidAplikasi->id, 'kelas' => 11, 'kebutuhan' => 3],
            ['nama' => 'Programmer', 'parent' => $subBidAplikasi->id, 'kelas' => 9, 'kebutuhan' => 5],
            ['nama' => 'Web Developer', 'parent' => $subBidAplikasi->id, 'kelas' => 7, 'kebutuhan' => 4],
            ['nama' => 'Mobile Developer', 'parent' => $subBidAplikasi->id, 'kelas' => 7, 'kebutuhan' => 3],
            ['nama' => 'UI/UX Designer', 'parent' => $subBidAplikasi->id, 'kelas' => 7, 'kebutuhan' => 2],
            ['nama' => 'Quality Assurance', 'parent' => $subBidAplikasi->id, 'kelas' => 7, 'kebutuhan' => 2],

            // Staff Sub Bid Layanan Data
            ['nama' => 'Data Scientist', 'parent' => $subBidLayananData->id, 'kelas' => 11, 'kebutuhan' => 2],
            ['nama' => 'Data Analyst', 'parent' => $subBidLayananData->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Content Manager', 'parent' => $subBidLayananData->id, 'kelas' => 7, 'kebutuhan' => 3],
            ['nama' => 'Information Officer', 'parent' => $subBidLayananData->id, 'kelas' => 7, 'kebutuhan' => 2],
        ];

        foreach ($staffJabatansAplikasi as $staffData) {
            Jabatan::create([
                'nama' => $staffData['nama'],
                'jenis_jabatan' => 'Fungsional',
                'kelas' => $staffData['kelas'],
                'kebutuhan' => $staffData['kebutuhan'],
                'parent_id' => $staffData['parent'],
                'opd_id' => null
            ]);
        }

        // LEVEL 2: Bidang E-Government
        $bidangEgov = Jabatan::create([
            'nama' => 'Kepala Bidang E-Government',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 12,
            'kebutuhan' => 1,
            'parent_id' => $kepalaDinas->id,
            'opd_id' => null
        ]);

        // LEVEL 3: Sub Bidang di Bidang E-Government
        $subBidSmartCity = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Smart City dan Inovasi Digital',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangEgov->id,
            'opd_id' => null
        ]);

        $subBidPelayananPublik = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Pelayanan Publik Berbasis Elektronik',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangEgov->id,
            'opd_id' => null
        ]);

        // LEVEL 4: Staff di Bidang E-Government
        $staffJabatansEgov = [
            // Staff Sub Bid Smart City
            ['nama' => 'Koordinator Smart City', 'parent' => $subBidSmartCity->id, 'kelas' => 11, 'kebutuhan' => 2],
            ['nama' => 'IoT Specialist', 'parent' => $subBidSmartCity->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Digital Innovation Officer', 'parent' => $subBidSmartCity->id, 'kelas' => 9, 'kebutuhan' => 2],
            ['nama' => 'Smart City Analyst', 'parent' => $subBidSmartCity->id, 'kelas' => 7, 'kebutuhan' => 3],

            // Staff Sub Bid Pelayanan Publik
            ['nama' => 'Pengelola Layanan Digital', 'parent' => $subBidPelayananPublik->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Customer Service Officer', 'parent' => $subBidPelayananPublik->id, 'kelas' => 7, 'kebutuhan' => 5],
            ['nama' => 'Digital Literacy Trainer', 'parent' => $subBidPelayananPublik->id, 'kelas' => 7, 'kebutuhan' => 3],
        ];

        foreach ($staffJabatansEgov as $staffData) {
            Jabatan::create([
                'nama' => $staffData['nama'],
                'jenis_jabatan' => 'Fungsional',
                'kelas' => $staffData['kelas'],
                'kebutuhan' => $staffData['kebutuhan'],
                'parent_id' => $staffData['parent'],
                'opd_id' => null
            ]);
        }

        // LEVEL 2: Bidang Komunikasi Publik
        $bidangKomunikasi = Jabatan::create([
            'nama' => 'Kepala Bidang Komunikasi Publik',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 12,
            'kebutuhan' => 1,
            'parent_id' => $kepalaDinas->id,
            'opd_id' => null
        ]);

        // LEVEL 3: Sub Bidang di Bidang Komunikasi
        $subBidHumas = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Hubungan Masyarakat dan Protocol',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangKomunikasi->id,
            'opd_id' => null
        ]);

        $subBidMedia = Jabatan::create([
            'nama' => 'Kepala Sub Bidang Media dan Publikasi',
            'jenis_jabatan' => 'Struktural',
            'kelas' => 9,
            'kebutuhan' => 1,
            'parent_id' => $bidangKomunikasi->id,
            'opd_id' => null
        ]);

        // LEVEL 4: Staff di Bidang Komunikasi
        $staffJabatansKomunikasi = [
            // Staff Sub Bid Humas
            ['nama' => 'Pranata Humas Ahli Muda', 'parent' => $subBidHumas->id, 'kelas' => 11, 'kebutuhan' => 2],
            ['nama' => 'Pranata Humas Ahli Pertama', 'parent' => $subBidHumas->id, 'kelas' => 9, 'kebutuhan' => 3],
            ['nama' => 'Public Relations Officer', 'parent' => $subBidHumas->id, 'kelas' => 7, 'kebutuhan' => 3],
            ['nama' => 'Protocol Officer', 'parent' => $subBidHumas->id, 'kelas' => 7, 'kebutuhan' => 2],

            // Staff Sub Bid Media
            ['nama' => 'Videographer', 'parent' => $subBidMedia->id, 'kelas' => 7, 'kebutuhan' => 3],
            ['nama' => 'Photographer', 'parent' => $subBidMedia->id, 'kelas' => 7, 'kebutuhan' => 3],
            ['nama' => 'Graphic Designer', 'parent' => $subBidMedia->id, 'kelas' => 7, 'kebutuhan' => 4],
            ['nama' => 'Content Creator', 'parent' => $subBidMedia->id, 'kelas' => 7, 'kebutuhan' => 4],
            ['nama' => 'Social Media Specialist', 'parent' => $subBidMedia->id, 'kelas' => 7, 'kebutuhan' => 3],
            ['nama' => 'Video Editor', 'parent' => $subBidMedia->id, 'kelas' => 5, 'kebutuhan' => 2],
        ];

        foreach ($staffJabatansKomunikasi as $staffData) {
            Jabatan::create([
                'nama' => $staffData['nama'],
                'jenis_jabatan' => 'Fungsional',
                'kelas' => $staffData['kelas'],
                'kebutuhan' => $staffData['kebutuhan'],
                'parent_id' => $staffData['parent'],
                'opd_id' => null
            ]);
        }

        echo "✓ Hierarki jabatan dibuat\n";

        // 3. Buat ASN untuk mengisi jabatan
        $allJabatans = Jabatan::where('opd_id', $opd->id)
            ->orWhereHas('parent', function($q) use ($opd) {
                $q->where('opd_id', $opd->id);
            })
            ->orWhereHas('parent.parent', function($q) use ($opd) {
                $q->where('opd_id', $opd->id);
            })
            ->orWhereHas('parent.parent.parent', function($q) use ($opd) {
                $q->where('opd_id', $opd->id);
            })
            ->get();

        $totalAsn = 0;
        $tahunMasuk = [2000, 2002, 2005, 2008, 2010, 2012, 2015, 2017, 2018, 2019, 2020, 2021, 2022, 2023];
        $bulan = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        foreach ($allJabatans as $jabatan) {
            // Isi jabatan sesuai kebutuhan
            // Untuk jabatan struktural, isi 100%
            // Untuk fungsional, isi 70-90% dari kebutuhan
            $jumlahAsn = $jabatan->jenis_jabatan === 'Struktural'
                ? $jabatan->kebutuhan
                : max(1, (int)($jabatan->kebutuhan * $faker->numberBetween(70, 90) / 100));

            for ($i = 0; $i < $jumlahAsn; $i++) {
                $tahun = $faker->randomElement($tahunMasuk);
                $bln = $faker->randomElement($bulan);
                $nip = $tahun . $bln . $faker->numberBetween(1, 9) . ' ' .
                       $faker->numberBetween(100000, 999999) . ' ' .
                       $faker->numberBetween(1, 2) . ' ' .
                       $faker->numberBetween(1000, 9999);

                Asn::create([
                    'nama' => $faker->name(),
                    'nip' => $nip,
                    'jabatan_id' => $jabatan->id,
                    'opd_id' => $opd->id
                ]);

                $totalAsn++;
            }
        }

        echo "✓ Total {$totalAsn} ASN dibuat\n";
        echo "\n";
        echo "==========================================\n";
        echo "RINGKASAN SEEDER:\n";
        echo "==========================================\n";
        echo "OPD: {$opd->nama}\n";
        echo "Total Jabatan: " . $allJabatans->count() . "\n";
        echo "Total ASN: {$totalAsn}\n";
        echo "==========================================\n";
        echo "\n";
        echo "Breakdown Jabatan:\n";
        echo "- Kepala Dinas: 1\n";
        echo "- Sekretariat: 1 (+ 3 Sub Bagian + " .
              collect($staffJabatansSekretariat)->sum('kebutuhan') . " Staff)\n";
        echo "- Bidang Infrastruktur TIK: 1 (+ 2 Sub Bidang + " .
              collect($staffJabatansInfrastruktur)->sum('kebutuhan') . " Staff)\n";
        echo "- Bidang Aplikasi: 1 (+ 2 Sub Bidang + " .
              collect($staffJabatansAplikasi)->sum('kebutuhan') . " Staff)\n";
        echo "- Bidang E-Government: 1 (+ 2 Sub Bidang + " .
              collect($staffJabatansEgov)->sum('kebutuhan') . " Staff)\n";
        echo "- Bidang Komunikasi: 1 (+ 2 Sub Bidang + " .
              collect($staffJabatansKomunikasi)->sum('kebutuhan') . " Staff)\n";
        echo "==========================================\n";
    }
}
