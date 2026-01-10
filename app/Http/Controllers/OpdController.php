<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use App\Models\AdminActivityLog;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    use LogsAdminActivity;
    /**
     * Apply middleware untuk permission check
     */
    public function __construct()
    {
        // Methods yang perlu permission manage_opd_jabatan
        $this->middleware('admin.permission:manage_opd_jabatan')
             ->only(['store', 'update', 'destroy', 'storeJabatan', 'updateJabatan', 'destroyJabatan']);

        // Methods yang perlu permission manage_asn
        $this->middleware('admin.permission:manage_asn')
             ->only(['storeAsn', 'updateAsn', 'destroyAsn']);
    }
    /**
     * Menampilkan daftar semua OPD
     */
    public function index(Request $request)
    {
        // Gunakan withCount untuk efisiensi
        $query = Opd::query()->withCount(['asns']);

        // Filter untuk Admin OPD - hanya tampilkan OPD miliknya
        $admin = auth('admin')->user();
        if ($admin && $admin->isAdminOpd() && $admin->opd_id) {
            $query->where('id', $admin->opd_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama', 'like', '%' . $searchTerm . '%')
                  ->orWhere('id', $searchTerm); // Exact match untuk ID
            });
        }

        // Per page options
        $perPage = $request->get('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $opds = $query->orderBy('nama')
                     ->paginate($perPage)
                     ->withQueryString();

        // Calculate total jabatan per OPD using a single efficient query
        // Get all OPD IDs on current page
        $opdIds = $opds->pluck('id')->toArray();
        
        // Get all root jabatan IDs for these OPDs
        $rootJabatanByOpd = Jabatan::whereIn('opd_id', $opdIds)
            ->whereNull('parent_id')
            ->select('id', 'opd_id')
            ->get()
            ->groupBy('opd_id');
        
        // Calculate total jabatan count per OPD (including all descendants)
        $jabatanCountByOpd = [];
        foreach ($opdIds as $opdId) {
            $rootIds = isset($rootJabatanByOpd[$opdId]) 
                ? $rootJabatanByOpd[$opdId]->pluck('id')->toArray() 
                : [];
            
            if (empty($rootIds)) {
                $jabatanCountByOpd[$opdId] = 0;
                continue;
            }

            // Count all jabatan in hierarchy using iterative approach
            $allIds = $rootIds;
            $currentIds = $rootIds;
            $maxDepth = 20;
            $depth = 0;
            
            while (!empty($currentIds) && $depth < $maxDepth) {
                $childIds = Jabatan::whereIn('parent_id', $currentIds)
                    ->pluck('id')
                    ->toArray();
                
                if (empty($childIds)) break;
                
                $allIds = array_merge($allIds, $childIds);
                $currentIds = $childIds;
                $depth++;
            }
            
            $jabatanCountByOpd[$opdId] = count($allIds);
        }

        // Attach jabatan count to each OPD
        $opds->getCollection()->transform(function ($opd) use ($jabatanCountByOpd) {
            $opd->total_jabatan_count = $jabatanCountByOpd[$opd->id] ?? 0;
            return $opd;
        });

        // Calculate stats - filter based on admin's OPD access
        if ($admin && $admin->isAdminOpd() && $admin->opd_id) {
            // Admin OPD hanya lihat stats OPD miliknya
            $opdId = $admin->opd_id;
            
            // Get all jabatan IDs for this OPD
            $rootIds = Jabatan::where('opd_id', $opdId)->whereNull('parent_id')->pluck('id')->toArray();
            $allJabatanIds = [];
            $currentIds = $rootIds;
            $maxDepth = 20;
            $depth = 0;
            $allJabatanIds = $rootIds;
            
            while (!empty($currentIds) && $depth < $maxDepth) {
                $childIds = Jabatan::whereIn('parent_id', $currentIds)->pluck('id')->toArray();
                if (empty($childIds)) break;
                $allJabatanIds = array_merge($allJabatanIds, $childIds);
                $currentIds = $childIds;
                $depth++;
            }
            
            $stats = [
                'total_opd' => 1,
                'total_jabatan' => count($allJabatanIds),
                'total_asn' => Asn::where('opd_id', $opdId)->count(),
            ];
        } else {
            // Super admin / other roles lihat semua
            $stats = [
                'total_opd' => Opd::count(),
                'total_jabatan' => Jabatan::count(),
                'total_asn' => Asn::count(),
            ];
        }
        
        return view('opds.index', compact('opds', 'stats'));
    }

    /**
     * Menyimpan OPD baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:opds,nama'
        ], [
            'nama.required' => 'Nama OPD harus diisi',
            'nama.max' => 'Nama OPD maksimal 255 karakter',
            'nama.unique' => 'Nama OPD sudah terdaftar dalam sistem'
        ]);

        $opd = Opd::create([
            'nama' => $request->nama
        ]);

        // Log activity
        $this->logCreate($opd, "Membuat OPD baru: {$opd->nama}");

        return redirect()->route('admin.opds.index')
                        ->with('success', 'OPD "' . $opd->nama . '" berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail OPD beserta hierarki jabatan
     */
    public function show($id)
    {
        // Load OPD with basic relations only
        $opd = Opd::with(['asns'])->findOrFail($id);

        // Stats - simple counts
        $opd->total_asn_count = $opd->asns->count();
        
        // Get all jabatan IDs for this OPD efficiently
        $rootIds = Jabatan::where('opd_id', $id)->whereNull('parent_id')->pluck('id')->toArray();
        $allJabatanIds = $this->collectAllJabatanIds($rootIds);
        
        $opd->total_jabatan_count = count($allJabatanIds);
        
        // Calculate total kebutuhan from all jabatan
        $opd->total_kebutuhan = Jabatan::whereIn('id', $allJabatanIds)->sum('kebutuhan');

        // Load jabatan tree with deep nesting (6 levels should cover most cases)
        // Using recursive eager loading helper
        $rootJabatans = Jabatan::where('opd_id', $id)
                               ->whereNull('parent_id')
                               ->withCount('asns')
                               ->with(['asns'])
                               ->get();
        
        // Load all children recursively using a helper
        $this->loadAllChildrenRecursively($rootJabatans);
        
        $opd->allJabatans = $this->flattenJabatanTree($rootJabatans);
        $opd->jabatanTree = $rootJabatans;

        return view('opds.show', compact('opd'));
    }

    /**
     * Helper: Load all children recursively for a collection of jabatan
     */
    private function loadAllChildrenRecursively($jabatans, $depth = 0, $maxDepth = 10)
    {
        if ($depth >= $maxDepth || $jabatans->isEmpty()) {
            return;
        }

        // Get all jabatan IDs
        $jabatanIds = $jabatans->pluck('id')->toArray();
        
        // Load all children in one query
        $allChildren = Jabatan::whereIn('parent_id', $jabatanIds)
                              ->withCount('asns')
                              ->with(['asns'])
                              ->get()
                              ->groupBy('parent_id');

        // Assign children to each jabatan
        foreach ($jabatans as $jabatan) {
            $children = $allChildren->get($jabatan->id, collect());
            $jabatan->setRelation('children', $children);
            
            // Recursively load grandchildren
            if ($children->isNotEmpty()) {
                $this->loadAllChildrenRecursively($children, $depth + 1, $maxDepth);
            }
        }
    }

    /**
     * Helper: Flatten jabatan tree to a collection
     */
    private function flattenJabatanTree($jabatans)
    {
        $result = collect();
        
        foreach ($jabatans as $jabatan) {
            $result->push($jabatan);
            if ($jabatan->children && $jabatan->children->isNotEmpty()) {
                $result = $result->merge($this->flattenJabatanTree($jabatan->children));
            }
        }
        
        return $result;
    }

    /**
     * Helper: Collect all jabatan IDs in hierarchy (optimized with batch query)
     */
    private function collectAllJabatanIds(array $rootIds): array
    {
        if (empty($rootIds)) {
            return [];
        }

        $allIds = $rootIds;
        $currentIds = $rootIds;
        $maxDepth = 10;
        $depth = 0;

        while (!empty($currentIds) && $depth < $maxDepth) {
            // Batch query for all children at this level
            $childIds = Jabatan::whereIn('parent_id', $currentIds)->pluck('id')->toArray();
            
            if (empty($childIds)) {
                break;
            }

            $allIds = array_merge($allIds, $childIds);
            $currentIds = $childIds;
            $depth++;
        }

        return $allIds;
    }

    /**
     * API endpoint untuk mendapatkan struktur organisasi OPD dalam format tree
     */
    public function getOpdTree($id)
    {
        $opd = Opd::with(['jabatanKepala' => function($query) {
            $query->with('children.children.children');
        }])->findOrFail($id);

        return response()->json($opd);
    }

    /**
     * API endpoint untuk mendapatkan daftar ASN dalam jabatan tertentu
     */
    public function getJabatanAsns($id)
    {
        $jabatan = Jabatan::with(['asns', 'parent'])->findOrFail($id);

        // Build response data
        $data = [
            'jabatan_id' => $jabatan->id,
            'nama' => $jabatan->nama,
            'jenis_jabatan' => $jabatan->jenis_jabatan,
            'kelas' => $jabatan->kelas,
            'kebutuhan' => $jabatan->kebutuhan,
            'bezetting' => $jabatan->asns->count(),
            'parent_id' => $jabatan->parent_id,
            'parent_nama' => $jabatan->parent ? $jabatan->parent->nama : null,
            'asns' => $jabatan->asns->map(function($asn) {
                return [
                    'id' => $asn->id,
                    'nama' => $asn->nama,
                    'nip' => $asn->nip,
                    'jabatan_id' => $asn->jabatan_id,
                ];
            })
        ];

        return response()->json($data);
    }

    /**
     * Menyimpan jabatan baru untuk OPD tertentu
     */
    public function storeJabatan(Request $request, $opdId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_jabatan' => 'required|in:Struktural,Fungsional,Pelaksana',
            'kelas' => 'nullable|string|max:50',
            'kebutuhan' => 'required|integer|min:0',
            'parent_jabatan_id' => 'nullable|exists:jabatans,id'
        ]);

        // Pastikan parent_jabatan_id milik OPD ini (jika ada)
        if ($request->parent_jabatan_id) {
            $parentJabatan = Jabatan::findOrFail($request->parent_jabatan_id);

            // Validasi parent jabatan milik OPD yang sama
            $parentOpdId = $parentJabatan->getOpdId();
            if ($parentOpdId != $opdId) {
                return back()->withErrors(['parent_jabatan_id' => 'Parent jabatan tidak valid untuk OPD ini.']);
            }
        }

        $jabatanData = [
            'nama' => $request->nama,
            'jenis_jabatan' => $request->jenis_jabatan,
            'kelas' => $request->kelas,
            'kebutuhan' => $request->kebutuhan,
            'parent_id' => $request->parent_jabatan_id
        ];

        // Jika tidak ada parent_jabatan_id, maka ini adalah jabatan kepala OPD
        if (!$request->parent_jabatan_id) {
            $jabatanData['opd_id'] = $opdId;
        }

        $jabatan = Jabatan::create($jabatanData);

        // Log activity
        $this->logCreate($jabatan, "Membuat jabatan baru: {$jabatan->nama} di OPD ID {$opdId}");

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Jabatan berhasil ditambahkan!');
    }

    /**
     * Mengupdate jabatan
     */
    public function updateJabatan(Request $request, $opdId, $jabatanId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_jabatan' => 'required|in:Struktural,Fungsional,Pelaksana',
            'kelas' => 'nullable|string|max:50',
            'kebutuhan' => 'required|integer|min:0',
            'parent_jabatan_id' => 'nullable|exists:jabatans,id'
        ]);

        // Cari jabatan dan validasi
        $jabatan = Jabatan::findOrFail($jabatanId);

        // Validasi jabatan milik OPD ini
        $jabatanOpdId = $jabatan->getOpdId();
        if ($jabatanOpdId != $opdId) {
            return back()->withErrors(['error' => 'Jabatan tidak valid untuk OPD ini.']);
        }

        // Pastikan parent_jabatan_id milik OPD ini (jika ada)
        if ($request->parent_jabatan_id) {
            // Tidak boleh set parent ke diri sendiri
            if ($request->parent_jabatan_id == $jabatanId) {
                return back()->withErrors(['parent_jabatan_id' => 'Jabatan tidak bisa menjadi parent dari dirinya sendiri.']);
            }

            $parentJabatan = Jabatan::findOrFail($request->parent_jabatan_id);
            $parentOpdId = $parentJabatan->getOpdId();
            if ($parentOpdId != $opdId) {
                return back()->withErrors(['parent_jabatan_id' => 'Parent jabatan tidak valid untuk OPD ini.']);
            }

            // Cek circular reference
            if ($this->wouldCreateCircularReference($jabatanId, $request->parent_jabatan_id)) {
                return back()->withErrors(['parent_jabatan_id' => 'Tidak dapat membuat circular reference dalam hierarki jabatan.']);
            }
        }

        $updateData = [
            'nama' => $request->nama,
            'jenis_jabatan' => $request->jenis_jabatan,
            'kelas' => $request->kelas,
            'kebutuhan' => $request->kebutuhan,
            'parent_id' => $request->parent_jabatan_id
        ];

        // Jika tidak ada parent_jabatan_id, maka ini adalah jabatan kepala OPD
        if (!$request->parent_jabatan_id) {
            $updateData['opd_id'] = $opdId;
            $updateData['parent_id'] = null;
        } else {
            $updateData['opd_id'] = null;
        }

        // Simpan data lama untuk log
        $oldData = $jabatan->toArray();

        $jabatan->update($updateData);

        // Log activity
        $this->logUpdate($jabatan, $oldData, "Mengubah jabatan: {$jabatan->nama}");

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Jabatan berhasil diperbarui!');
    }

    /**
     * Check apakah akan membuat circular reference
     */
    private function wouldCreateCircularReference($jabatanId, $proposedParentId)
    {
        $currentParent = Jabatan::find($proposedParentId);

        while ($currentParent) {
            if ($currentParent->id == $jabatanId) {
                return true;
            }
            $currentParent = $currentParent->parent;
        }

        return false;
    }

    /**
     * Menghapus jabatan
     */
    public function destroyJabatan($opdId, $jabatanId)
    {
        $jabatan = Jabatan::findOrFail($jabatanId);

        // Validasi jabatan milik OPD ini
        $jabatanOpdId = $jabatan->getOpdId();
        if ($jabatanOpdId != $opdId) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Jabatan tidak valid untuk OPD ini!');
        }

        // Cek apakah jabatan memiliki child jabatan
        if ($jabatan->children()->count() > 0) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Tidak dapat menghapus jabatan yang memiliki sub-jabatan!');
        }

        // Cek apakah jabatan memiliki ASN
        if ($jabatan->asns()->count() > 0) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Tidak dapat menghapus jabatan yang memiliki ASN!');
        }

        // Log activity sebelum menghapus
        $this->logDelete($jabatan, "Menghapus jabatan: {$jabatan->nama} dari OPD ID {$opdId}");

        $jabatan->delete();

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Jabatan berhasil dihapus!');
    }

    /**
     * Update nama OPD
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:opds,nama,' . $id
        ], [
            'nama.required' => 'Nama OPD harus diisi',
            'nama.max' => 'Nama OPD maksimal 255 karakter',
            'nama.unique' => 'Nama OPD sudah terdaftar dalam sistem'
        ]);

        $opd = Opd::findOrFail($id);
        $oldNama = $opd->nama;
        $oldData = $opd->toArray();
        
        $opd->update([
            'nama' => $request->nama
        ]);

        // Log activity
        $this->logUpdate($opd, $oldData, "Mengubah nama OPD dari '{$oldNama}' menjadi '{$opd->nama}'");

        return redirect()->route('admin.opds.index')
                        ->with('success', 'OPD "' . $oldNama . '" berhasil diubah menjadi "' . $opd->nama . '"');
    }

    /**
     * Hapus OPD beserta semua jabatan di dalamnya
     */
    public function destroy($id)
    {
        $opd = Opd::findOrFail($id);

        // Cek apakah OPD masih memiliki ASN
        if ($opd->asns()->count() > 0) {
            return redirect()->route('admin.opds.index')
                            ->with('error', 'Tidak dapat menghapus OPD "' . $opd->nama . '" karena masih memiliki ' . $opd->asns()->count() . ' ASN. Hapus semua ASN terlebih dahulu!');
        }

        // Cek apakah ada jabatan yang masih memiliki ASN
        $allJabatans = $opd->getAllJabatans();
        foreach ($allJabatans as $jabatan) {
            if ($jabatan->asns()->count() > 0) {
                return redirect()->route('admin.opds.index')
                                ->with('error', 'Tidak dapat menghapus OPD "' . $opd->nama . '" karena jabatan "' . $jabatan->nama . '" masih memiliki ASN!');
            }
        }

        // Log activity sebelum menghapus
        $this->logDelete($opd, "Menghapus OPD: {$opd->nama} beserta semua jabatan");

        // Hapus semua jabatan yang terkait dengan OPD ini
        // (akan otomatis menghapus child jabatan karena cascade)
        $opd->jabatans()->delete();

        // Hapus OPD
        $opd->delete();

        return redirect()->route('admin.opds.index')
                        ->with('success', 'OPD "' . $opd->nama . '" beserta semua jabatan berhasil dihapus!');
    }

    /**
     * Menyimpan ASN baru
     */
    public function storeAsn(Request $request, $opdId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:asns,nip',
            'jabatan_id' => 'required|exists:jabatans,id'
        ]);

        // Validasi bahwa jabatan milik OPD yang benar
        $jabatan = Jabatan::findOrFail($request->jabatan_id);
        $jabatanOpdId = $jabatan->getOpdId();

        if ($jabatanOpdId != $opdId) {
            return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk OPD ini.']);
        }

        $asn = Asn::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'opd_id' => $opdId
        ]);

        // Log activity
        AdminActivityLog::log(
            AdminActivityLog::ACTION_CREATE,
            AdminActivityLog::MODULE_ASN,
            "Menambahkan ASN baru: {$asn->nama} (NIP: {$asn->nip}) ke OPD ID {$opdId}",
            $asn,
            null,
            $asn->toArray()
        );

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'ASN berhasil ditambahkan!');
    }

    /**
     * Memperbarui data ASN
     */
    public function updateAsn(Request $request, $opdId, $asnId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:asns,nip,' . $asnId,
            'jabatan_id' => 'required|exists:jabatans,id'
        ]);

        $asn = Asn::where('id', $asnId)
                  ->where('opd_id', $opdId)
                  ->firstOrFail();

        // Validasi jabatan
        $jabatan = Jabatan::findOrFail($request->jabatan_id);
        $jabatanOpdId = $jabatan->getOpdId();

        if ($jabatanOpdId != $opdId) {
            return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk OPD ini.']);
        }

        // Simpan data lama untuk log
        $oldData = $asn->toArray();

        $asn->update([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'opd_id' => $opdId
        ]);

        // Log activity
        AdminActivityLog::log(
            AdminActivityLog::ACTION_UPDATE,
            AdminActivityLog::MODULE_ASN,
            "Mengubah data ASN: {$asn->nama} (NIP: {$asn->nip})",
            $asn,
            $oldData,
            $asn->toArray()
        );

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Data ASN berhasil diperbarui!');
    }

    /**
     * Menghapus ASN
     */
    public function destroyAsn($opdId, $asnId)
    {
        $asn = Asn::where('id', $asnId)
                  ->where('opd_id', $opdId)
                  ->firstOrFail();

        $namaAsn = $asn->nama;
        
        // Log activity sebelum menghapus
        AdminActivityLog::log(
            AdminActivityLog::ACTION_DELETE,
            AdminActivityLog::MODULE_ASN,
            "Menghapus ASN: {$asn->nama} (NIP: {$asn->nip}) dari OPD ID {$opdId}",
            $asn,
            $asn->toArray(),
            null
        );
        
        $asn->delete();

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'ASN "' . $namaAsn . '" berhasil dihapus!');
    }

    /**
     * Menampilkan peta jabatan (organizational chart)
     */
    public function petaJabatan($id)
    {
        $opd = Opd::findOrFail($id);
        
        // Load jabatan tree with all levels using recursive helper
        $jabatanKepala = Jabatan::where('opd_id', $id)
                                ->whereNull('parent_id')
                                ->withCount('asns')
                                ->with(['asns'])
                                ->get();
        
        // Load all children recursively
        $this->loadAllChildrenRecursively($jabatanKepala);
        
        $opd->setRelation('jabatanKepala', $jabatanKepala);

        return view('opds.peta-jabatan', compact('opd'));
    }

    /**
     * Export data OPD ke format CSV
     */
    public function export($id)
    {
        $opd = Opd::with([
            'jabatanKepala.asns'
        ])->findOrFail($id);

        // Get all jabatan
        $allJabatans = $opd->getAllJabatans();

        // Prepare data untuk export
        $exportData = [];

        // Header CSV
        $exportData[] = [
            'OPD',
            'Hierarki Jabatan',
            'Jabatan',
            'Jenis Jabatan',
            'Kelas',
            'Kebutuhan',
            'Bezetting',
            'Selisih',
            'ASN - Nama',
            'ASN - NIP'
        ];

        // Data Jabatan
        foreach ($allJabatans as $jabatan) {
            $bezetting = $jabatan->asns->count();
            $selisih = $bezetting - $jabatan->kebutuhan;

            // Build hierarki path
            $path = $jabatan->getPath();
            $hierarki = $path->pluck('nama')->implode(' → ');

            if ($jabatan->asns->count() > 0) {
                foreach ($jabatan->asns as $asn) {
                    $exportData[] = [
                        $opd->nama,
                        $hierarki,
                        $jabatan->nama,
                        $jabatan->jenis_jabatan,
                        $jabatan->kelas ?? '-',
                        $jabatan->kebutuhan,
                        $bezetting,
                        $selisih,
                        $asn->nama,
                        $asn->nip
                    ];
                }
            } else {
                $exportData[] = [
                    $opd->nama,
                    $hierarki,
                    $jabatan->nama,
                    $jabatan->jenis_jabatan,
                    $jabatan->kelas ?? '-',
                    $jabatan->kebutuhan,
                    $bezetting,
                    $selisih,
                    '-',
                    '-'
                ];
            }
        }

        // Generate CSV
        $filename = 'data_opd_' . str_replace(' ', '_', strtolower($opd->nama)) . '_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($exportData as $row) {
                fputcsv($file, $row, ';'); // Use semicolon as delimiter for better Excel compatibility
            }
            fclose($file);
        };

        // Log export activity
        $this->logExport(AdminActivityLog::MODULE_OPD, "Export data OPD: {$opd->nama}");

        return response()->stream($callback, 200, $headers);
    }
}
