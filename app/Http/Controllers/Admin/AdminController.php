<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
}
