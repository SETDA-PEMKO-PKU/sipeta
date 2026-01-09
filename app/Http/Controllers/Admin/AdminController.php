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
     * Generate single admin for one OPD (AJAX endpoint).
     */
    public function generateSingleOpdAdmin(Request $request)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
        ]);

        $opd = Opd::findOrFail($validated['opd_id']);

        // Check if OPD already has admin
        $existingOpdAdmin = Admin::where('role', Admin::ROLE_ADMIN_OPD)
            ->where('opd_id', $opd->id)
            ->first();

        if ($existingOpdAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'OPD sudah memiliki admin',
            ], 400);
        }

        $email = $this->convertToEmail($opd->nama);
        $password = Str::random(8);

        // Check if email already exists
        if (Admin::where('email', $email)->exists()) {
            $counter = 1;
            do {
                $newEmail = preg_replace('/@pku\.go\.id$/', '', $email) . $counter . '@pku.go.id';
                $counter++;
            } while (Admin::where('email', $newEmail)->exists());
            $email = $newEmail;
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

        return response()->json([
            'success' => true,
            'data' => [
                'opd_id' => $opd->id,
                'opd_nama' => $opd->nama,
                'admin_name' => 'Admin ' . $opd->nama,
                'email' => $email,
                'password' => $password,
            ],
        ]);
    }

    /**
     * Download Excel from generated admin data.
     */
    public function downloadGeneratedExcel(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array|min:1',
            'data.*.opd_id' => 'required',
            'data.*.opd_nama' => 'required|string',
            'data.*.admin_name' => 'required|string',
            'data.*.email' => 'required|string',
            'data.*.password' => 'required|string',
        ]);

        $generatedAdmins = collect($validated['data']);
        $filename = 'admin_opd_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new AdminOpdExport($generatedAdmins), $filename);
    }

    /**
     * Show form to reset and download all Admin OPD passwords.
     */
    public function showResetPasswordForm()
    {
        // Get all Admin OPD with their OPD
        $adminOpds = Admin::with('opd')
            ->where('role', Admin::ROLE_ADMIN_OPD)
            ->whereNotNull('opd_id')
            ->orderBy('name')
            ->get();

        $totalAdminOpd = $adminOpds->count();

        $previewData = $adminOpds->map(function ($admin) {
            return [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'email' => $admin->email,
                'opd_nama' => $admin->opd->nama ?? '-',
            ];
        });

        return view('admin.admins.reset-password', compact(
            'totalAdminOpd',
            'previewData'
        ));
    }

    /**
     * Reset password for single admin (AJAX endpoint).
     */
    public function resetSingleAdminPassword(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:admins,id',
        ]);

        $admin = Admin::with('opd')->findOrFail($validated['admin_id']);
        $password = Str::random(8);

        $admin->update([
            'password' => Hash::make($password),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'admin_id' => $admin->id,
                'opd_id' => $admin->opd_id,
                'opd_nama' => $admin->opd->nama ?? '-',
                'admin_name' => $admin->name,
                'email' => $admin->email,
                'password' => $password,
            ],
        ]);
    }

    /**
     * Download Excel from reset password data.
     */
    public function downloadResetPasswordExcel(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array|min:1',
            'data.*.admin_id' => 'required',
            'data.*.opd_nama' => 'required|string',
            'data.*.admin_name' => 'required|string',
            'data.*.email' => 'required|string',
            'data.*.password' => 'required|string',
        ]);

        $admins = collect($validated['data'])->map(function ($item) {
            return [
                'opd_id' => $item['admin_id'],
                'opd_nama' => $item['opd_nama'],
                'admin_name' => $item['admin_name'],
                'email' => $item['email'],
                'password' => $item['password'],
            ];
        });

        $filename = 'admin_opd_reset_password_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new AdminOpdExport($admins), $filename);
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
