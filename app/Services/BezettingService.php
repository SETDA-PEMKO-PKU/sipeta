<?php

namespace App\Services;

use App\Models\Jabatan;
use App\Models\Opd;
use Illuminate\Support\Facades\DB;

class BezettingService
{
    /**
     * Overview bezetting per jenis jabatan (Struktural, Fungsional, Pelaksana)
     */
    public function getOverview(?array $accessibleOpdIds = null): array
    {
        $jenisList = ['Struktural', 'Fungsional', 'Pelaksana'];
        $result = [];

        foreach ($jenisList as $jenis) {
            $query = Jabatan::where('jenis_jabatan', $jenis);
            if ($accessibleOpdIds !== null) {
                $query->whereIn('opd_id', $accessibleOpdIds);
            }

            $kebutuhan = (int) $query->sum('kebutuhan');
            $bezetting = (int) $query->withCount('asns')->get()->sum('asns_count');
            $selisih   = $kebutuhan - $bezetting;
            $persen    = $kebutuhan > 0 ? round(($bezetting / $kebutuhan) * 100, 1) : 0;

            $result[] = [
                'jenis'     => $jenis,
                'kebutuhan' => $kebutuhan,
                'bezetting' => $bezetting,
                'selisih'   => $selisih,
                'persen'    => $persen,
            ];
        }

        // Total global
        $totalKebutuhan = array_sum(array_column($result, 'kebutuhan'));
        $totalBezetting = array_sum(array_column($result, 'bezetting'));
        $totalSelisih   = $totalKebutuhan - $totalBezetting;
        $totalPersen    = $totalKebutuhan > 0 ? round(($totalBezetting / $totalKebutuhan) * 100, 1) : 0;

        return [
            'per_jenis'       => $result,
            'total_kebutuhan' => $totalKebutuhan,
            'total_bezetting' => $totalBezetting,
            'total_selisih'   => $totalSelisih,
            'total_persen'    => $totalPersen,
        ];
    }

    /**
     * Search jabatan by nama — return bezetting & kebutuhan aggregate
     */
    public function searchJabatan(string $keyword, ?array $accessibleOpdIds = null): array
    {
        $query = Jabatan::select(
                'nama',
                'jenis_jabatan',
                DB::raw('SUM(kebutuhan) as total_kebutuhan'),
                DB::raw('COUNT(DISTINCT id) as total_jabatan')
            )
            ->where('nama', 'like', '%' . $keyword . '%')
            ->groupBy('nama', 'jenis_jabatan');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }

        $rows = $query->orderBy('nama')->limit(20)->get();

        return $rows->map(function ($row) use ($accessibleOpdIds) {
            // hitung bezetting (jumlah ASN di jabatan dengan nama ini)
            $bezettingQuery = Jabatan::where('nama', $row->nama)
                ->where('jenis_jabatan', $row->jenis_jabatan);
            if ($accessibleOpdIds !== null) {
                $bezettingQuery->whereIn('opd_id', $accessibleOpdIds);
            }
            $bezetting = $bezettingQuery->withCount('asns')->get()->sum('asns_count');
            $kebutuhan = (int) $row->total_kebutuhan;
            $selisih   = $kebutuhan - $bezetting;
            $persen    = $kebutuhan > 0 ? round(($bezetting / $kebutuhan) * 100, 1) : 0;

            return [
                'nama'          => $row->nama,
                'jenis_jabatan' => $row->jenis_jabatan,
                'kebutuhan'     => $kebutuhan,
                'bezetting'     => $bezetting,
                'selisih'       => $selisih,
                'persen'        => $persen,
                'jumlah_opd'    => (int) $row->total_jabatan,
            ];
        })->values()->toArray();
    }

    /**
     * Detail sebaran jabatan (by nama) per OPD
     */
    public function getDetailPerOpd(string $namaJabatan, ?array $accessibleOpdIds = null): array
    {
        $query = Jabatan::with(['opdLangsung'])
            ->where('nama', $namaJabatan)
            ->whereNotNull('opd_id');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }

        $jabatans = $query->withCount('asns')->get();

        $rows = $jabatans->map(function ($jabatan) {
            $opd       = $jabatan->opdLangsung;
            $kebutuhan = (int) $jabatan->kebutuhan;
            $bezetting = (int) $jabatan->asns_count;
            $selisih   = $kebutuhan - $bezetting;
            $persen    = $kebutuhan > 0 ? round(($bezetting / $kebutuhan) * 100, 1) : 0;

            return [
                'jabatan_id'    => $jabatan->id,
                'opd_id'        => $jabatan->opd_id,
                'opd_nama'      => $opd ? $opd->nama : 'Tidak Diketahui',
                'jenis_jabatan' => $jabatan->jenis_jabatan,
                'kelas'         => $jabatan->kelas,
                'kebutuhan'     => $kebutuhan,
                'bezetting'     => $bezetting,
                'selisih'       => $selisih,
                'persen'        => $persen,
            ];
        });

        // sort by opd nama
        $sorted = $rows->sortBy('opd_nama')->values();

        $totalKebutuhan = $sorted->sum('kebutuhan');
        $totalBezetting = $sorted->sum('bezetting');
        $totalSelisih   = $totalKebutuhan - $totalBezetting;
        $totalPersen    = $totalKebutuhan > 0 ? round(($totalBezetting / $totalKebutuhan) * 100, 1) : 0;

        return [
            'nama_jabatan'    => $namaJabatan,
            'jenis_jabatan'   => $jabatans->first()?->jenis_jabatan ?? '-',
            'sebaran'         => $sorted->toArray(),
            'total_kebutuhan' => $totalKebutuhan,
            'total_bezetting' => $totalBezetting,
            'total_selisih'   => $totalSelisih,
            'total_persen'    => $totalPersen,
        ];
    }
}
