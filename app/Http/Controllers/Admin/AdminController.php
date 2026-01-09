<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Opd;
use App\Exports\AdminOpdExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Per page options
        $perPage = $request->get('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $admins = Admin::with('opd')
                       ->orderBy('created_at', 'desc')
                       ->paginate($perPage)
                       ->withQueryString();
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $opds = Opd::orderBy('nama')->get();
        return view('admin.admins.create', compact('opds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin_organisasi,admin_bkpsdm,admin_opd',
            'is_active' => 'boolean',
            'opd_id' => [
                'nullable',
                'exists:opds,id',
                Rule::requiredIf($request->role === 'admin_opd'),
            ],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Ensure opd_id is null for non-admin_opd roles
        if ($validated['role'] !== 'admin_opd') {
            $validated['opd_id'] = null;
        }

        Admin::create($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        $opds = Opd::orderBy('nama')->get();
        return view('admin.admins.edit', compact('admin', 'opds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admins')->ignore($admin->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin_organisasi,admin_bkpsdm,admin_opd',
            'is_active' => 'boolean',
            'opd_id' => [
                'nullable',
                'exists:opds,id',
                Rule::requiredIf($request->role === 'admin_opd'),
            ],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        // Ensure opd_id is null for non-admin_opd roles
        if ($validated['role'] !== 'admin_opd') {
            $validated['opd_id'] = null;
        }

        $admin->update($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        // Prevent deleting self
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil dihapus');
    }

    /**
     * Show the form for generating admin OPD accounts.
     */
    public function showGenerateOpdForm()
    {
        // Get all OPDs
        $allOpds = Opd::orderBy('nama')->get();
        $totalOpd = $allOpds->count();

        // Get OPD IDs that already have admin
        $opdIdsWithAdmin = Admin::where('role', Admin::ROLE_ADMIN_OPD)
            ->whereNotNull('opd_id')
            ->pluck('opd_id')
            ->unique()
            ->toArray();

        $opdWithAdmin = count($opdIdsWithAdmin);

        // Get OPDs without admin
        $opdsWithoutAdmin = $allOpds->filter(function ($opd) use ($opdIdsWithAdmin) {
            return !in_array($opd->id, $opdIdsWithAdmin);
        });

        $opdWithoutAdmin = $opdsWithoutAdmin->count();

        // Generate preview data
        $previewData = $opdsWithoutAdmin->map(function ($opd) {
            return [
                'opd_id' => $opd->id,
                'opd_nama' => $opd->nama,
                'admin_name' => 'Admin ' . $opd->nama,
                'email' => $this->convertToEmail($opd->nama),
            ];
        })->values();

        return view('admin.admins.generate-opd', compact(
            'totalOpd',
            'opdWithAdmin',
            'opdWithoutAdmin',
            'previewData'
        ));
    }

    /**
     * Process generating admin OPD accounts and download Excel.
     */
    public function processGenerateOpd(Request $request)
    {
        // Get OPD IDs that already have admin
        $opdIdsWithAdmin = Admin::where('role', Admin::ROLE_ADMIN_OPD)
            ->whereNotNull('opd_id')
            ->pluck('opd_id')
            ->unique()
            ->toArray();

        // Get OPDs without admin
        $opdsWithoutAdmin = Opd::whereNotIn('id', $opdIdsWithAdmin)
            ->orderBy('nama')
            ->get();

        if ($opdsWithoutAdmin->isEmpty()) {
            return redirect()->route('admin.admins.index')
                ->with('info', 'Semua OPD sudah memiliki admin');
        }

        $generatedAdmins = collect();

        foreach ($opdsWithoutAdmin as $opd) {
            $email = $this->convertToEmail($opd->nama);
            $password = Str::random(8);

            // Check if email already exists
            $existingAdmin = Admin::where('email', $email)->first();
            if ($existingAdmin) {
                // If email exists, add number suffix
                $counter = 1;
                do {
                    $email = $this->convertToEmail($opd->nama) . $counter;
                    $email = str_replace('@pku.go.id', '', $email) . '@pku.go.id';
                    $counter++;
                } while (Admin::where('email', $email)->exists());
            }

            // Create admin
            Admin::create([
                'name' => 'Admin ' . $opd->nama,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => Admin::ROLE_ADMIN_OPD,
                'opd_id' => $opd->id,
                'is_active' => true,
            ]);

            $generatedAdmins->push([
                'opd_id' => $opd->id,
                'opd_nama' => $opd->nama,
                'admin_name' => 'Admin ' . $opd->nama,
                'email' => $email,
                'password' => $password,
            ]);
        }

        // Generate filename with timestamp
        $filename = 'admin_opd_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new AdminOpdExport($generatedAdmins), $filename);
    }

    /**
     * Bulk delete admins.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:admins,id',
        ]);

        $currentAdminId = auth('admin')->id();
        $ids = collect($validated['ids']);

        // Filter out current admin from deletion
        if ($ids->contains($currentAdminId)) {
            $ids = $ids->reject(fn($id) => $id == $currentAdminId);
            
            if ($ids->isEmpty()) {
                return back()->with('error', 'Tidak dapat menghapus akun sendiri');
            }
        }

        $deletedCount = Admin::whereIn('id', $ids)->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', "$deletedCount admin berhasil dihapus");
    }

    /**
     * Convert OPD name to email format.
     * Example: "Dinas Kesehatan" => "dinaskesehatan@pku.go.id"
     */
    private function convertToEmail(string $namaOpd): string
    {
        // Convert to lowercase
        $email = strtolower($namaOpd);
        
        // Remove special characters and keep only alphanumeric
        $email = preg_replace('/[^a-z0-9]/', '', $email);
        
        // Append domain
        return $email . '@pku.go.id';
    }
}
