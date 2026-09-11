<?php

namespace Database\Seeders;

use App\Models\NamaJabatanReferensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NamaJabatanReferensiSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua nama jabatan unik dari tabel jabatans
        $jabatans = DB::table('jabatans')
            ->select('nama', 'jenis_jabatan')
            ->get();

        $seen = [];
        $toInsert = [];

        foreach ($jabatans as $jabatan) {
            // Normalisasi: trim + collapse spasi ganda jadi tunggal
            $nama = trim(preg_replace('/\s+/', ' ', $jabatan->nama));

            // Normalisasi jenis_jabatan ke pilihan yang valid
            $jenis = $this->normalizeJenis($jabatan->jenis_jabatan);

            // Key untuk deduplikasi: nama + jenis
            $key = strtolower($nama) . '|' . strtolower($jenis ?? '');

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $toInsert[] = [
                'nama'          => $nama,
                'jenis_jabatan' => $jenis,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // Sort by nama
        usort($toInsert, fn($a, $b) => strcmp($a['nama'], $b['nama']));

        // Insert in chunks
        foreach (array_chunk($toInsert, 100) as $chunk) {
            NamaJabatanReferensi::insert($chunk);
        }

        $this->command->info('Seeded ' . count($toInsert) . ' nama jabatan referensi.');
    }

    private function normalizeJenis(?string $jenis): ?string
    {
        if (!$jenis) return null;

        $map = [
            'Staf Ahli'  => 'Struktural',
            'Struktural' => 'Struktural',
            'Fungsional' => 'Fungsional',
            'Pelaksana'  => 'Pelaksana',
            'Kepala OPD' => 'Kepala OPD',
            'Kepala'     => 'Kepala',
        ];

        return $map[$jenis] ?? $jenis;
    }
}
