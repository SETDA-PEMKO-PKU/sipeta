<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HasOpdScope;
use App\Models\Jabatan;
use App\Models\Opd;
use Illuminate\Http\Request;

class OpdJabatanController extends Controller
{
    use HasOpdScope;

    /**
     * Apply middleware untuk permission check
     */
    public function __construct()
    {
        // Only super_admin, admin_organisasi, and admin_opd can manage jabatan
        $this->middleware('admin.permission:manage_opd_jabatan');
    }

    /**
     * Display a listing of jabatan
     */
    public function index(Request $request)
    {
        $query = Jabatan::with(['parent', 'opdLangsung', 'asns']);

        // Apply OPD scope for admin OPD
        $query = $this->applyOpdScope($query);

        // Filter by OPD
        if ($request->filled('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }

        // Filter by jenis jabatan
        if ($request->filled('jenis_jabatan')) {
            $query->where('jenis_jabatan', $request->jenis_jabatan);
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // Per page options
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $jabatans = $query->orderBy('nama')->paginate($perPage)->withQueryString();

        // Data for filter dropdowns - filter based on accessible OPDs
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $opds = Opd::whereIn('id', $accessibleOpdIds)->orderBy('nama')->get();

        // Get unique jenis jabatan - scoped to accessible OPDs
        $jenisJabatans = Jabatan::whereIn('opd_id', $accessibleOpdIds)
                                ->select('jenis_jabatan')
                                ->distinct()
                                ->whereNotNull('jenis_jabatan')
                                ->pluck('jenis_jabatan');

        // Get unique kelas - scoped to accessible OPDs
        $kelasJabatans = Jabatan::whereIn('opd_id', $accessibleOpdIds)
                                ->select('kelas')
                                ->distinct()
                                ->whereNotNull('kelas')
                                ->orderBy('kelas', 'desc')
                                ->pluck('kelas');

        return view('admin.jabatan.index', compact(
            'jabatans',
            'opds',
            'jenisJabatans',
            'kelasJabatans'
        ));
    }

    /**
     * Show the form for creating a new jabatan
     */
    public function create()
    {
        // Filter OPD dropdown for admin OPD (show only their OPD)
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $opds = Opd::whereIn('id', $accessibleOpdIds)->orderBy('nama')->get();

        // Get parent jabatan options - scoped to accessible OPDs
        $parentJabatans = Jabatan::whereIn('opd_id', $accessibleOpdIds)
                                 ->orderBy('nama')
                                 ->get();

        return view('admin.jabatan.create', compact('opds', 'parentJabatans'));
    }

    /**
     * Store a newly created jabatan
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        // Auto-set opd_id for admin OPD
        if ($admin->isAdminOpd()) {
            $request->merge(['opd_id' => $admin->opd_id]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_jabatan' => 'nullable|string|max:100',
            'kelas' => 'nullable|integer|min:1|max:17',
            'kebutuhan' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:jabatans,id',
            'opd_id' => 'required|exists:opds,id'
        ]);

        // Validate OPD access
        $this->validateOpdAccess($request->opd_id);

        // If parent_id is provided, validate it belongs to the same OPD
        if ($request->filled('parent_id')) {
            $parentJabatan = Jabatan::findOrFail($request->parent_id);
            $parentOpdId = $parentJabatan->getOpdId();

            if ($parentOpdId != $request->opd_id) {
                return back()->withErrors([
                    'parent_id' => 'Jabatan parent harus berada dalam OPD yang sama'
                ])->withInput();
            }
        }

        Jabatan::create([
            'nama' => $request->nama,
            'jenis_jabatan' => $request->jenis_jabatan,
            'kelas' => $request->kelas,
            'kebutuhan' => $request->kebutuhan,
            'parent_id' => $request->parent_id,
            'opd_id' => $request->opd_id
        ]);

        return redirect()->route('admin.jabatan.index')
                        ->with('success', 'Jabatan berhasil ditambahkan!');
    }

    /**
     * Display the specified jabatan
     */
    public function show($id)
    {
        $jabatan = Jabatan::with(['parent', 'children', 'opdLangsung', 'asns'])->findOrFail($id);

        // Validate OPD access
        $opdId = $jabatan->getOpdId();
        $this->validateOpdAccess($opdId);

        return view('admin.jabatan.show', compact('jabatan'));
    }

    /**
     * Show the form for editing the specified jabatan
     */
    public function edit($id)
    {
        $jabatan = Jabatan::with(['parent', 'opdLangsung'])->findOrFail($id);

        // Validate OPD access in edit method
        $opdId = $jabatan->getOpdId();
        $this->validateOpdAccess($opdId);

        // Filter OPD dropdown for admin OPD (show only their OPD)
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $opds = Opd::whereIn('id', $accessibleOpdIds)->orderBy('nama')->get();

        // Get parent jabatan options - scoped to accessible OPDs, exclude current jabatan and its descendants
        $parentJabatans = Jabatan::whereIn('opd_id', $accessibleOpdIds)
                                 ->where('id', '!=', $id)
                                 ->orderBy('nama')
                                 ->get();

        return view('admin.jabatan.edit', compact('jabatan', 'opds', 'parentJabatans'));
    }

    /**
     * Update the specified jabatan
     */
    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        // Validate OPD access in update method
        $opdId = $jabatan->getOpdId();
        $this->validateOpdAccess($opdId);

        $admin = auth('admin')->user();

        // Auto-set opd_id for admin OPD
        if ($admin->isAdminOpd()) {
            $request->merge(['opd_id' => $admin->opd_id]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_jabatan' => 'nullable|string|max:100',
            'kelas' => 'nullable|integer|min:1|max:17',
            'kebutuhan' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:jabatans,id',
            'opd_id' => 'required|exists:opds,id'
        ]);

        // Validate OPD access for the new OPD (in case it changed)
        $this->validateOpdAccess($request->opd_id);

        // If parent_id is provided, validate it belongs to the same OPD
        if ($request->filled('parent_id')) {
            $parentJabatan = Jabatan::findOrFail($request->parent_id);
            $parentOpdId = $parentJabatan->getOpdId();

            if ($parentOpdId != $request->opd_id) {
                return back()->withErrors([
                    'parent_id' => 'Jabatan parent harus berada dalam OPD yang sama'
                ])->withInput();
            }

            // Prevent circular reference - parent cannot be a descendant
            $descendants = $jabatan->getAllDescendants();
            if ($descendants->contains('id', $request->parent_id)) {
                return back()->withErrors([
                    'parent_id' => 'Jabatan parent tidak boleh merupakan sub-jabatan dari jabatan ini'
                ])->withInput();
            }
        }

        $jabatan->update([
            'nama' => $request->nama,
            'jenis_jabatan' => $request->jenis_jabatan,
            'kelas' => $request->kelas,
            'kebutuhan' => $request->kebutuhan,
            'parent_id' => $request->parent_id,
            'opd_id' => $request->opd_id
        ]);

        return redirect()->route('admin.jabatan.index')
                        ->with('success', 'Data jabatan berhasil diperbarui!');
    }

    /**
     * Remove the specified jabatan
     */
    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        // Validate OPD access in destroy method
        $opdId = $jabatan->getOpdId();
        $this->validateOpdAccess($opdId);

        $namaJabatan = $jabatan->nama;

        // Check if jabatan has children
        if ($jabatan->children()->count() > 0) {
            return redirect()->route('admin.jabatan.index')
                            ->with('error', 'Tidak dapat menghapus jabatan "' . $namaJabatan . '" karena masih memiliki sub-jabatan!');
        }

        // Check if jabatan has ASN assigned
        if ($jabatan->asns()->count() > 0) {
            return redirect()->route('admin.jabatan.index')
                            ->with('error', 'Tidak dapat menghapus jabatan "' . $namaJabatan . '" karena masih memiliki ASN yang ditugaskan!');
        }

        $jabatan->delete();

        return redirect()->route('admin.jabatan.index')
                        ->with('success', 'Jabatan "' . $namaJabatan . '" berhasil dihapus!');
    }
}
