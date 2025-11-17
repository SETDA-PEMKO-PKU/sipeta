<?php

namespace App\Services;

use App\Models\Opd;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get overview statistics
     */
    public function getOverviewStats()
    {
        return [
            'total_opd' => Opd::count(),
            'total_bagian' => Bagian::count(),
            'total_jabatan' => Jabatan::count(),
            'total_asn' => Asn::count(),
            'total_kebutuhan' => Jabatan::sum('kebutuhan'),
            'total_bezetting' => $this->getTotalBezetting(),
            'total_selisih' => $this->getTotalBezetting() - Jabatan::sum('kebutuhan'),
            'persentase_pemenuhan' => $this->getPersentasePemenuhan(),
        ];
    }

    /**
     * Get total bezetting (current staffing)
     */
    public function getTotalBezetting()
    {
        return Asn::count();
    }

    /**
     * Get persentase pemenuhan kebutuhan
     */
    public function getPersentasePemenuhan()
    {
        $totalKebutuhan = Jabatan::sum('kebutuhan');
        if ($totalKebutuhan == 0) return 0;

        $totalBezetting = $this->getTotalBezetting();
        return round(($totalBezetting / $totalKebutuhan) * 100, 2);
    }

    /**
     * Get distribusi jabatan berdasarkan jenis
     */
    public function getDistribusiJenisJabatan()
    {
        return Jabatan::select('jenis_jabatan', DB::raw('count(*) as total'))
            ->groupBy('jenis_jabatan')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->jenis_jabatan => $item->total];
            })
            ->toArray();
    }

    /**
     * Get top OPD by staffing (bezetting)
     */
    public function getTopOpdByStaffing($limit = 10)
    {
        return Opd::withCount('asns')
            ->orderBy('asns_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($opd) {
                return [
                    'nama' => $opd->nama,
                    'bezetting' => $opd->asns_count,
                    'kebutuhan' => $this->getKebutuhanByOpd($opd->id),
                    'selisih' => $opd->asns_count - $this->getKebutuhanByOpd($opd->id),
                ];
            });
    }

    /**
     * Get kebutuhan by OPD
     */
    public function getKebutuhanByOpd($opdId)
    {
        // Jabatan bisa punya opd_id langsung (kepala) atau via bagian (parent_id)
        $kebutuhanLangsung = Jabatan::where('opd_id', $opdId)->sum('kebutuhan');

        $kebutuhanViaBagian = Jabatan::whereHas('bagian', function($query) use ($opdId) {
            $query->where('opd_id', $opdId);
        })->sum('kebutuhan');

        return $kebutuhanLangsung + $kebutuhanViaBagian;
    }

    /**
     * Get understaffed positions (top positions with biggest gap)
     */
    public function getUnderstaffedPositions($limit = 10)
    {
        return Jabatan::select('jabatans.*', DB::raw('kebutuhan - (SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id) as gap'))
            ->with(['opd', 'bagian'])
            ->havingRaw('gap > 0')
            ->orderBy('gap', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jabatan) {
                $bezetting = $jabatan->asns()->count();
                return [
                    'id' => $jabatan->id,
                    'nama_jabatan' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'opd' => $jabatan->opd->nama ?? '-',
                    'bagian' => $jabatan->bagian->nama ?? '-',
                    'kebutuhan' => $jabatan->kebutuhan,
                    'bezetting' => $bezetting,
                    'gap' => $jabatan->kebutuhan - $bezetting,
                ];
            });
    }

    /**
     * Get overstaffed positions
     */
    public function getOverstaffedPositions($limit = 10)
    {
        return Jabatan::select('jabatans.*', DB::raw('(SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id) - kebutuhan as gap'))
            ->with(['opd', 'bagian'])
            ->havingRaw('gap > 0')
            ->orderBy('gap', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jabatan) {
                $bezetting = $jabatan->asns()->count();
                return [
                    'id' => $jabatan->id,
                    'nama_jabatan' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'opd' => $jabatan->opd->nama ?? '-',
                    'bagian' => $jabatan->bagian->nama ?? '-',
                    'kebutuhan' => $jabatan->kebutuhan,
                    'bezetting' => $bezetting,
                    'gap' => $bezetting - $jabatan->kebutuhan,
                ];
            });
    }

    /**
     * Get analytics per OPD
     */
    public function getOpdAnalytics($opdId)
    {
        $opd = Opd::with(['bagians', 'asns'])->findOrFail($opdId);

        // Get total jabatan (langsung + via bagian)
        $totalJabatanLangsung = Jabatan::where('opd_id', $opdId)->count();
        $totalJabatanViaBagian = Jabatan::whereHas('bagian', function($query) use ($opdId) {
            $query->where('opd_id', $opdId);
        })->count();
        $totalJabatan = $totalJabatanLangsung + $totalJabatanViaBagian;

        $totalKebutuhan = $this->getKebutuhanByOpd($opdId);
        $totalBezetting = $opd->asns->count();

        return [
            'opd' => $opd,
            'total_bagian' => $opd->bagians->count(),
            'total_jabatan' => $totalJabatan,
            'total_kebutuhan' => $totalKebutuhan,
            'total_bezetting' => $totalBezetting,
            'total_selisih' => $totalBezetting - $totalKebutuhan,
            'persentase_pemenuhan' => $totalKebutuhan > 0 ? round(($totalBezetting / $totalKebutuhan) * 100, 2) : 0,
            'bagians_data' => $this->getBagianDataByOpd($opdId),
            'jabatan_kosong' => $this->getJabatanKosongByOpd($opdId),
        ];
    }

    /**
     * Get bagian data grouped by bagian for OPD
     */
    public function getBagianDataByOpd($opdId)
    {
        return Bagian::where('opd_id', $opdId)
            ->withCount('asns')
            ->get()
            ->map(function ($bagian) {
                $kebutuhan = Jabatan::where('parent_id', $bagian->id)->sum('kebutuhan');
                return [
                    'nama' => $bagian->nama,
                    'bezetting' => $bagian->asns_count,
                    'kebutuhan' => $kebutuhan,
                    'selisih' => $bagian->asns_count - $kebutuhan,
                ];
            });
    }

    /**
     * Get jabatan kosong (empty positions) by OPD
     */
    public function getJabatanKosongByOpd($opdId)
    {
        // Get jabatan langsung dan via bagian
        $jabatanLangsung = Jabatan::where('opd_id', $opdId)
            ->where('kebutuhan', '>', 0)
            ->with('bagian')
            ->get();

        $jabatanViaBagian = Jabatan::whereHas('bagian', function($query) use ($opdId) {
                $query->where('opd_id', $opdId);
            })
            ->where('kebutuhan', '>', 0)
            ->with('bagian')
            ->get();

        $allJabatan = $jabatanLangsung->merge($jabatanViaBagian);

        return $allJabatan
            ->filter(function ($jabatan) {
                return $jabatan->asns()->count() == 0;
            })
            ->values()
            ->map(function ($jabatan) {
                return [
                    'nama_jabatan' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'bagian' => $jabatan->bagian->nama ?? '-',
                    'kebutuhan' => $jabatan->kebutuhan,
                ];
            });
    }

    /**
     * Get kepegawaian analytics
     */
    public function getKepegawaianAnalytics($filters = [])
    {
        $query = Asn::query();

        if (!empty($filters['opd_id'])) {
            $query->where('opd_id', $filters['opd_id']);
        }

        return [
            'distribusi_per_opd' => $this->getDistribusiAsnPerOpd($filters),
            'distribusi_per_jenis' => $this->getDistribusiAsnPerJenis($filters),
            'distribusi_per_kelas' => $this->getDistribusiAsnPerKelas($filters),
            'total_asn' => $query->count(),
        ];
    }

    /**
     * Get distribusi ASN per OPD
     */
    public function getDistribusiAsnPerOpd($filters = [])
    {
        return Opd::withCount('asns')
            ->orderBy('asns_count', 'desc')
            ->get()
            ->map(function ($opd) {
                return [
                    'nama' => $opd->nama,
                    'total' => $opd->asns_count,
                ];
            });
    }

    /**
     * Get distribusi ASN per jenis jabatan
     */
    public function getDistribusiAsnPerJenis($filters = [])
    {
        $query = Asn::join('jabatans', 'asns.jabatan_id', '=', 'jabatans.id')
            ->select('jabatans.jenis_jabatan', DB::raw('count(*) as total'))
            ->groupBy('jabatans.jenis_jabatan');

        if (!empty($filters['opd_id'])) {
            $query->where('asns.opd_id', $filters['opd_id']);
        }

        return $query->get()->mapWithKeys(function ($item) {
            return [$item->jenis_jabatan => $item->total];
        })->toArray();
    }

    /**
     * Get distribusi ASN per kelas jabatan
     */
    public function getDistribusiAsnPerKelas($filters = [])
    {
        $query = Asn::join('jabatans', 'asns.jabatan_id', '=', 'jabatans.id')
            ->select('jabatans.kelas', DB::raw('count(*) as total'))
            ->groupBy('jabatans.kelas')
            ->orderBy('jabatans.kelas');

        if (!empty($filters['opd_id'])) {
            $query->where('asns.opd_id', $filters['opd_id']);
        }

        return $query->get()->mapWithKeys(function ($item) {
            return ['Kelas ' . $item->kelas => $item->total];
        })->toArray();
    }

    /**
     * Get jabatan analytics
     */
    public function getJabatanAnalytics()
    {
        return [
            'total_per_jenis' => $this->getDistribusiJenisJabatan(),
            'distribusi_per_kelas' => $this->getDistribusiJabatanPerKelas(),
            'jabatan_kosong_vs_terisi' => $this->getJabatanKosongVsTerisi(),
            'average_bezetting_per_jenis' => $this->getAverageBezettingPerJenis(),
        ];
    }

    /**
     * Get distribusi jabatan per kelas
     */
    public function getDistribusiJabatanPerKelas()
    {
        return Jabatan::select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->orderBy('kelas')
            ->get()
            ->mapWithKeys(function ($item) {
                return ['Kelas ' . $item->kelas => $item->total];
            })
            ->toArray();
    }

    /**
     * Get jabatan kosong vs terisi
     */
    public function getJabatanKosongVsTerisi()
    {
        $allJabatan = Jabatan::where('kebutuhan', '>', 0)->get();

        $kosong = 0;
        $terisi = 0;

        foreach ($allJabatan as $jabatan) {
            if ($jabatan->asns()->count() == 0) {
                $kosong++;
            } else {
                $terisi++;
            }
        }

        return [
            'Kosong' => $kosong,
            'Terisi' => $terisi,
        ];
    }

    /**
     * Get average bezetting per jenis jabatan
     */
    public function getAverageBezettingPerJenis()
    {
        return Jabatan::select('jenis_jabatan')
            ->get()
            ->groupBy('jenis_jabatan')
            ->map(function ($jabatans, $jenis) {
                $totalBezetting = 0;
                foreach ($jabatans as $jabatan) {
                    $totalBezetting += $jabatan->asns()->count();
                }
                return round($totalBezetting / $jabatans->count(), 2);
            })
            ->toArray();
    }

    /**
     * Get gap analysis data
     */
    public function getGapAnalysis()
    {
        return [
            'heat_map_data' => $this->getGapHeatMapData(),
            'understaffed_positions' => $this->getUnderstaffedPositions(20),
            'overstaffed_positions' => $this->getOverstaffedPositions(20),
            'priority_recruitment' => $this->getPriorityRecruitment(),
        ];
    }

    /**
     * Get gap heat map data (selisih per OPD)
     */
    public function getGapHeatMapData()
    {
        return Opd::all()->map(function ($opd) {
            $kebutuhan = $this->getKebutuhanByOpd($opd->id);
            $bezetting = $opd->asns()->count();
            $selisih = $bezetting - $kebutuhan;

            return [
                'opd' => $opd->nama,
                'kebutuhan' => $kebutuhan,
                'bezetting' => $bezetting,
                'selisih' => $selisih,
                'persentase' => $kebutuhan > 0 ? round(($bezetting / $kebutuhan) * 100, 2) : 0,
            ];
        })->sortBy('selisih');
    }

    /**
     * Get priority recruitment list
     */
    public function getPriorityRecruitment()
    {
        return $this->getUnderstaffedPositions(10)->map(function ($item) {
            return [
                'jabatan' => $item['nama_jabatan'],
                'jenis' => $item['jenis_jabatan'],
                'kelas' => $item['kelas'],
                'opd' => $item['opd'],
                'gap' => $item['gap'],
                'prioritas' => $this->calculatePriority($item),
            ];
        })->sortByDesc('prioritas');
    }

    /**
     * Calculate recruitment priority score
     */
    private function calculatePriority($item)
    {
        // Priority based on gap size and position type
        $gapScore = $item['gap'] * 10;

        $typeScore = 0;
        switch ($item['jenis_jabatan']) {
            case 'Struktural':
                $typeScore = 30;
                break;
            case 'Fungsional':
                $typeScore = 20;
                break;
            case 'Staf Ahli':
                $typeScore = 25;
                break;
            case 'Pelaksana':
                $typeScore = 10;
                break;
        }

        return $gapScore + $typeScore;
    }
}
