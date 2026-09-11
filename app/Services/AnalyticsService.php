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
        // First get all OPDs with their ASN count
        $query = Opd::withCount('asns');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('id', $accessibleOpdIds);
        }

        $opds = $query->orderBy('asns_count', 'desc')
            ->limit($limit)
            ->get();

        // Get all jabatans for these OPDs in a SINGLE query with eager loading
        $opdIds = $opds->pluck('id');
        $allJabatans = Jabatan::whereIn('opd_id', $opdIds)->get();

        // Group by opd_id for efficient lookup
        $kebutuhanByOpd = $allJabatans->groupBy('opd_id')
            ->map(fn($jabatans) => $jabatans->sum('kebutuhan'));

        return $opds->map(function ($opd) use ($kebutuhanByOpd) {
            $kebutuhan = $kebutuhanByOpd->get($opd->id, 0);
            return [
                'nama' => $opd->nama,
                'bezetting' => $opd->asns_count,
                'kebutuhan' => $kebutuhan,
                'selisih' => $opd->asns_count - $kebutuhan,
            ];
        });
    }

    /**
     * Get kebutuhan by OPD - optimized version
     */
    public function getKebutuhanByOpd($opdId)
    {
        return Jabatan::where('opd_id', $opdId)->sum('kebutuhan');
    }

    /**
     * Get understaffed positions (top positions with biggest gap)
     */
    public function getUnderstaffedPositions($limit = 10, $accessibleOpdIds = null)
    {
        // Get jabatan kebutuhan - bezetting gap using subquery
        $query = Jabatan::select('jabatans.*', DB::raw('kebutuhan - (SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id) as gap'))
            ->with(['parent', 'opdLangsung']);

        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }

        $results = $query->havingRaw('gap > 0')
            ->orderBy('gap', 'desc')
            ->limit($limit)
            ->get();

        // Get related OPDs in a single query for jabatans without direct opd_id
        $jabatanIdsNeedingOpdLookup = $results->filter(fn($j) => !$j->opd_id)->pluck('id');

        $opdMap = collect();
        if ($jabatanIdsNeedingOpdLookup->isNotEmpty()) {
            // Build a map from jabatan_id to opd_id using parent relationships
            $allParentIds = $results->whereNotNull('parent_id')->pluck('parent_id')->unique();
            $parentJabatans = Jabatan::with('opdLangsung')
                ->whereIn('id', $allParentIds)
                ->get()
                ->keyBy('id');

            // Map jabatan -> parent -> opd
            foreach ($jabatanIdsNeedingOpdLookup as $jabatanId) {
                $jabatan = $results->first(fn($j) => $j->id === $jabatanId);
                if ($jabatan && $jabatan->parent_id) {
                    $parent = $parentJabatans->get($jabatan->parent_id);
                    if ($parent && $parent->opd_id) {
                        $opdMap->put($jabatanId, $parent->opd_id);
                    }
                }
            }
        }

        // Get all OPDs needed
        $neededOpdIds = $results->pluck('opd_id')->filter()->merge($opdMap->values())->unique();
        $opds = $neededOpdIds->isNotEmpty()
            ? Opd::whereIn('id', $neededOpdIds)->get()->keyBy('id')
            : collect();

        return $results->map(function ($jabatan) use ($opdMap, $opds) {
            // Calculate bezetting from gap (gap = kebutuhan - bezetting, so bezetting = kebutuhan - gap)
            $bezetting = $jabatan->kebutuhan - $jabatan->gap;

            // Get OPD - try direct opd_id first, then lookup via map
            $opdId = $jabatan->opd_id ?? $opdMap->get($jabatan->id);
            $opd = $opdId ? $opds->get($opdId) : null;

            return [
                'id' => $jabatan->id,
                'nama_jabatan' => $jabatan->nama,
                'jenis_jabatan' => $jabatan->jenis_jabatan,
                'kelas' => $jabatan->kelas,
                'opd' => $opd ? $opd->nama : '-',
                'parent_jabatan' => $jabatan->parent ? $jabatan->parent->nama : '-',
                'kebutuhan' => $jabatan->kebutuhan,
                'bezetting' => $bezetting,
                'gap' => $jabatan->gap,
            ];
        });
    }

    /**
     * Get overstaffed positions - optimized
     */
    public function getOverstaffedPositions($limit = 10, $accessibleOpdIds = null)
    {
        // Get jabatan where bezetting > kebutuhan
        $query = Jabatan::select('jabatans.*', DB::raw('(SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id) - kebutuhan as gap'))
            ->with(['parent', 'opdLangsung']);

        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }

        $results = $query->havingRaw('gap > 0')
            ->orderBy('gap', 'desc')
            ->limit($limit)
            ->get();

        // Get related OPDs in a single query for jabatans without direct opd_id
        $jabatanIdsNeedingOpdLookup = $results->filter(fn($j) => !$j->opd_id)->pluck('id');

        $opdMap = collect();
        if ($jabatanIdsNeedingOpdLookup->isNotEmpty()) {
            // Build a map from jabatan_id to opd_id using parent relationships
            $allParentIds = $results->whereNotNull('parent_id')->pluck('parent_id')->unique();
            $parentJabatans = Jabatan::with('opdLangsung')
                ->whereIn('id', $allParentIds)
                ->get()
                ->keyBy('id');

            // Map jabatan -> parent -> opd
            foreach ($jabatanIdsNeedingOpdLookup as $jabatanId) {
                $jabatan = $results->first(fn($j) => $j->id === $jabatanId);
                if ($jabatan && $jabatan->parent_id) {
                    $parent = $parentJabatans->get($jabatan->parent_id);
                    if ($parent && $parent->opd_id) {
                        $opdMap->put($jabatanId, $parent->opd_id);
                    }
                }
            }
        }

        // Get all OPDs needed
        $neededOpdIds = $results->pluck('opd_id')->filter()->merge($opdMap->values())->unique();
        $opds = $neededOpdIds->isNotEmpty()
            ? Opd::whereIn('id', $neededOpdIds)->get()->keyBy('id')
            : collect();

        return $results->map(function ($jabatan) use ($opdMap, $opds) {
            // Calculate bezetting from gap (gap = bezetting - kebutuhan, so bezetting = gap + kebutuhan)
            $bezetting = $jabatan->gap + $jabatan->kebutuhan;

            // Get OPD - try direct opd_id first, then lookup via map
            $opdId = $jabatan->opd_id ?? $opdMap->get($jabatan->id);
            $opd = $opdId ? $opds->get($opdId) : null;

            return [
                'id' => $jabatan->id,
                'nama_jabatan' => $jabatan->nama,
                'jenis_jabatan' => $jabatan->jenis_jabatan,
                'kelas' => $jabatan->kelas,
                'opd' => $opd ? $opd->nama : '-',
                'parent_jabatan' => $jabatan->parent ? $jabatan->parent->nama : '-',
                'kebutuhan' => $jabatan->kebutuhan,
                'bezetting' => $bezetting,
                'gap' => $jabatan->gap,
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
     * Get bezetting dan kebutuhan per jenis jabatan untuk sidebar
     */
    public function getSidebarBezettingData($accessibleOpdIds = null)
    {
        $query = Jabatan::select(
                'jenis_jabatan',
                DB::raw('SUM(kebutuhan) as total_kebutuhan'),
                DB::raw('COUNT(asns.id) as total_bezetting')
            )
            ->leftJoin('asns', 'jabatans.id', '=', 'asns.jabatan_id')
            ->groupBy('jenis_jabatan');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('jabatans.opd_id', $accessibleOpdIds);
        }

        $rows = $query->get();

        $totalKebutuhan = $rows->sum('total_kebutuhan');
        $totalBezetting = $rows->sum('total_bezetting');

        $perJenis = $rows->map(function ($item) {
            $persen = $item->total_kebutuhan > 0
                ? round(($item->total_bezetting / $item->total_kebutuhan) * 100)
                : 0;
            return [
                'jenis'          => $item->jenis_jabatan ?? 'Lainnya',
                'kebutuhan'      => (int) $item->total_kebutuhan,
                'bezetting'      => (int) $item->total_bezetting,
                'selisih'        => (int) $item->total_kebutuhan - (int) $item->total_bezetting,
                'persen'         => $persen,
            ];
        })->values()->toArray();

        return [
            'total_kebutuhan' => (int) $totalKebutuhan,
            'total_bezetting' => (int) $totalBezetting,
            'total_selisih'   => (int) $totalKebutuhan - (int) $totalBezetting,
            'persen_global'   => $totalKebutuhan > 0
                ? round(($totalBezetting / $totalKebutuhan) * 100)
                : 0,
            'per_jenis'       => $perJenis,
        ];
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
     * Get jabatan kosong vs terisi - optimized using single query
     */
    public function getJabatanKosongVsTerisi($accessibleOpdIds = null)
    {
        // Use LEFT JOIN with COUNT to get filled positions in one query
        $query = Jabatan::select('jabatans.id', DB::raw('COUNT(asns.id) as asn_count'))
            ->leftJoin('asns', 'jabatans.id', '=', 'asns.jabatan_id')
            ->where('jabatans.kebutuhan', '>', 0)
            ->groupBy('jabatans.id');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('jabatans.opd_id', $accessibleOpdIds);
        }

        $results = $query->get();

        $kosong = $results->where('asn_count', 0)->count();
        $terisi = $results->where('asn_count', '>', 0)->count();

        return [
            'Kosong' => $kosong,
            'Terisi' => $terisi,
        ];
    }

    /**
     * Get average bezetting per jenis jabatan - optimized
     */
    public function getAverageBezettingPerJenis($accessibleOpdIds = null)
    {
        // Single query to get count and bezetting per jenis
        $query = Jabatan::select('jenis_jabatan', DB::raw('COUNT(*) as jabatan_count'), DB::raw('SUM((SELECT COUNT(*) FROM asns WHERE asns.jabatan_id = jabatans.id)) as total_bezetting'))
            ->where('kebutuhan', '>', 0)
            ->groupBy('jenis_jabatan');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $accessibleOpdIds);
        }

        $results = $query->get();

        return $results->mapWithKeys(function ($item) {
            return [
                $item->jenis_jabatan => $item->jabatan_count > 0
                    ? round($item->total_bezetting / $item->jabatan_count, 2)
                    : 0
            ];
        })->toArray();
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
     * Get gap heat map data (selisih per OPD) - optimized
     */
    public function getGapHeatMapData($accessibleOpdIds = null)
    {
        // Get OPDs with their ASN count
        $query = Opd::withCount('asns');

        if ($accessibleOpdIds !== null) {
            $query->whereIn('id', $accessibleOpdIds);
        }

        $opds = $query->get();

        // Get all jabatans for these OPDs in a SINGLE query
        $opdIds = $opds->pluck('id');
        $kebutuhanByOpd = Jabatan::select('opd_id', DB::raw('SUM(kebutuhan) as total_kebutuhan'))
            ->whereIn('opd_id', $opdIds)
            ->groupBy('opd_id')
            ->pluck('total_kebutuhan', 'opd_id');

        return $opds->map(function ($opd) use ($kebutuhanByOpd) {
            $kebutuhan = $kebutuhanByOpd->get($opd->id, 0);
            $bezetting = $opd->asns_count;
            $selisih = $bezetting - $kebutuhan;

            return [
                'opd' => $opd->nama,
                'kebutuhan' => $kebutuhan,
                'bezetting' => $bezetting,
                'selisih' => $selisih,
                'persentase' => $kebutuhan > 0 ? round(($bezetting / $kebutuhan) * 100, 2) : 0,
            ];
        })->sortBy('selisih')->values();
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
