<?php

namespace App\Services;

use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get overview statistics
     */
    public function getOverviewStats($accessibleOpdIds = null)
    {
        $opdQuery = Opd::query();
        $jabatanQuery = Jabatan::query();
        $asnQuery = Asn::query();

        if ($accessibleOpdIds !== null) {
            $opdQuery->whereIn('id', $accessibleOpdIds);
            $jabatanQuery->whereIn('opd_id', $accessibleOpdIds);
            $asnQuery->whereIn('opd_id', $accessibleOpdIds);
        }

        return [
            'total_opd' => $opdQuery->count(),
            'total_jabatan' => $jabatanQuery->count(),
            'total_asn' => $asnQuery->count(),
            'total_kebutuhan' => $jabatanQuery->sum('kebutuhan'),
            'total_bezetting' => $this->getTotalBezetting($accessibleOpdIds),
            'total_selisih' => $this->getTotalBezetting($accessibleOpdIds) - $jabatanQuery->sum('kebutuhan'),
            'persentase_pemenuhan' => $this->getPersentasePemenuhan($accessibleOpdIds),
        ];
    }

    /**
     * Get total bezetting (current staffing)
     */
    public function getTotalBezetting($accessibleOpdIds = null)
    {
        $query = Asn::query();
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        return $query->count();
    }

    /**
     * Get persentase pemenuhan kebutuhan
     */
    public function getPersentasePemenuhan($accessibleOpdIds = null)
    {
        $query = Jabatan::query();
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        $totalKebutuhan = $query->sum('kebutuhan');
        if ($totalKebutuhan == 0) return 0;

        $totalBezetting = $this->getTotalBezetting($accessibleOpdIds);
        return round(($totalBezetting / $totalKebutuhan) * 100, 2);
    }

    /**
     * Get distribusi jabatan berdasarkan jenis
     */
    public function getDistribusiJenisJabatan($accessibleOpdIds = null)
    {
        $query = Jabatan::select('jenis_jabatan', DB::raw('count(*) as total'));
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        return $query->groupBy('jenis_jabatan')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->jenis_jabatan => $item->total];
            })
            ->toArray();
    }

    /**
     * Get top OPD by staffing (bezetting)
     */
    public function getTopOpdByStaffing($limit = 10, $accessibleOpdIds = null)
    {
        $query = Opd::withCount('asns');
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('id', $accessibleOpdIds);
        }
        
        return $query->orderBy('asns_count', 'desc')
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
        $opd = Opd::findOrFail($opdId);
        $allJabatans = $opd->getAllJabatans();
        return $allJabatans->sum('kebutuhan');
    }

    /**
     * Get understaffed positions (top positions with biggest gap)
     */
    public function getUnderstaffedPositions($limit = 10, $accessibleOpdIds = null)
    {
        $query = Jabatan::select('jabatans.*', DB::raw('kebutuhan - (SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id) as gap'))
            ->with(['parent']);
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        return $query->havingRaw('gap > 0')
            ->orderBy('gap', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jabatan) {
                $bezetting = $jabatan->asns()->count();
                $opdId = $jabatan->getOpdId();
                $opd = $opdId ? Opd::find($opdId) : null;

                return [
                    'id' => $jabatan->id,
                    'nama_jabatan' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'opd' => $opd ? $opd->nama : '-',
                    'parent_jabatan' => $jabatan->parent ? $jabatan->parent->nama : '-',
                    'kebutuhan' => $jabatan->kebutuhan,
                    'bezetting' => $bezetting,
                    'gap' => $jabatan->kebutuhan - $bezetting,
                ];
            });
    }

    /**
     * Get overstaffed positions
     */
    public function getOverstaffedPositions($limit = 10, $accessibleOpdIds = null)
    {
        $query = Jabatan::select('jabatans.*', DB::raw('(SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id) - kebutuhan as gap'))
            ->with(['parent']);
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        return $query->havingRaw('gap > 0')
            ->orderBy('gap', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jabatan) {
                $bezetting = $jabatan->asns()->count();
                $opdId = $jabatan->getOpdId();
                $opd = $opdId ? Opd::find($opdId) : null;

                return [
                    'id' => $jabatan->id,
                    'nama_jabatan' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'opd' => $opd ? $opd->nama : '-',
                    'parent_jabatan' => $jabatan->parent ? $jabatan->parent->nama : '-',
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
        $opd = Opd::with(['asns'])->findOrFail($opdId);
        $allJabatans = $opd->getAllJabatans();

        $totalJabatan = $allJabatans->count();
        $totalKebutuhan = $allJabatans->sum('kebutuhan');
        $totalBezetting = $opd->asns->count();

        return [
            'opd' => $opd,
            'total_jabatan' => $totalJabatan,
            'total_kebutuhan' => $totalKebutuhan,
            'total_bezetting' => $totalBezetting,
            'total_selisih' => $totalBezetting - $totalKebutuhan,
            'persentase_pemenuhan' => $totalKebutuhan > 0 ? round(($totalBezetting / $totalKebutuhan) * 100, 2) : 0,
            'bagians_data' => $this->getJabatanRootDataByOpd($opdId),
            'jabatan_kosong' => $this->getJabatanKosongByOpd($opdId),
        ];
    }

    /**
     * Get root jabatan data grouped for OPD (replacement for bagian data)
     */
    public function getJabatanRootDataByOpd($opdId)
    {
        $opd = Opd::findOrFail($opdId);
        $rootJabatans = $opd->jabatanKepala;

        return $rootJabatans->map(function ($jabatan) {
            $allDescendants = $jabatan->getAllDescendants();
            $allJabatans = collect([$jabatan])->merge($allDescendants);

            $bezetting = 0;
            $kebutuhan = 0;

            foreach ($allJabatans as $j) {
                $bezetting += $j->asns()->count();
                $kebutuhan += $j->kebutuhan;
            }

            return [
                'nama' => $jabatan->nama,
                'bezetting' => $bezetting,
                'kebutuhan' => $kebutuhan,
                'selisih' => $bezetting - $kebutuhan,
            ];
        });
    }

    /**
     * Get jabatan kosong (empty positions) by OPD
     */
    public function getJabatanKosongByOpd($opdId)
    {
        $opd = Opd::findOrFail($opdId);
        $allJabatan = $opd->getAllJabatans();

        return $allJabatan
            ->filter(function ($jabatan) {
                return $jabatan->kebutuhan > 0 && $jabatan->asns()->count() == 0;
            })
            ->values()
            ->map(function ($jabatan) {
                return [
                    'nama_jabatan' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'parent_jabatan' => $jabatan->parent ? $jabatan->parent->nama : '-',
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

        // Apply accessible OPD filter first
        if (!empty($filters['accessible_opd_ids'])) {
            $query->whereIn('opd_id', $filters['accessible_opd_ids']);
        }

        // Then apply specific OPD filter if provided
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
        $query = Opd::withCount('asns');
        
        // Apply accessible OPD filter
        if (!empty($filters['accessible_opd_ids'])) {
            $query->whereIn('id', $filters['accessible_opd_ids']);
        }
        
        return $query->orderBy('asns_count', 'desc')
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

        // Apply accessible OPD filter first
        if (!empty($filters['accessible_opd_ids'])) {
            $query->whereIn('asns.opd_id', $filters['accessible_opd_ids']);
        }

        // Then apply specific OPD filter if provided
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

        // Apply accessible OPD filter first
        if (!empty($filters['accessible_opd_ids'])) {
            $query->whereIn('asns.opd_id', $filters['accessible_opd_ids']);
        }

        // Then apply specific OPD filter if provided
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
    public function getJabatanAnalytics($accessibleOpdIds = null)
    {
        return [
            'total_per_jenis' => $this->getDistribusiJenisJabatan($accessibleOpdIds),
            'distribusi_per_kelas' => $this->getDistribusiJabatanPerKelas($accessibleOpdIds),
            'jabatan_kosong_vs_terisi' => $this->getJabatanKosongVsTerisi($accessibleOpdIds),
            'average_bezetting_per_jenis' => $this->getAverageBezettingPerJenis($accessibleOpdIds),
        ];
    }

    /**
     * Get distribusi jabatan per kelas
     */
    public function getDistribusiJabatanPerKelas($accessibleOpdIds = null)
    {
        $query = Jabatan::select('kelas', DB::raw('count(*) as total'));
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        return $query->groupBy('kelas')
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
    public function getJabatanKosongVsTerisi($accessibleOpdIds = null)
    {
        $query = Jabatan::where('kebutuhan', '>', 0);
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        $allJabatan = $query->get();

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
    public function getAverageBezettingPerJenis($accessibleOpdIds = null)
    {
        $query = Jabatan::select('jenis_jabatan');
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }
        
        return $query->get()
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
    public function getGapAnalysis($accessibleOpdIds = null)
    {
        return [
            'heat_map_data' => $this->getGapHeatMapData($accessibleOpdIds),
            'understaffed_positions' => $this->getUnderstaffedPositions(20, $accessibleOpdIds),
            'overstaffed_positions' => $this->getOverstaffedPositions(20, $accessibleOpdIds),
            'priority_recruitment' => $this->getPriorityRecruitment($accessibleOpdIds),
        ];
    }

    /**
     * Get gap heat map data (selisih per OPD)
     */
    public function getGapHeatMapData($accessibleOpdIds = null)
    {
        $query = Opd::query();
        
        if ($accessibleOpdIds !== null) {
            $query->whereIn('id', $accessibleOpdIds);
        }
        
        return $query->get()->map(function ($opd) {
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
    public function getPriorityRecruitment($accessibleOpdIds = null)
    {
        return $this->getUnderstaffedPositions(10, $accessibleOpdIds)->map(function ($item) {
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
            case 'Pelaksana':
                $typeScore = 10;
                break;
        }

        return $gapScore + $typeScore;
    }
}
