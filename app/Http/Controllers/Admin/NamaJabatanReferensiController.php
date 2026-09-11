<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NamaJabatanReferensi;
use Illuminate\Http\Request;

class NamaJabatanReferensiController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.permission:super_admin_only');
    }

    public function index(Request $request)
    {
        $query = NamaJabatanReferensi::query();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(nama) LIKE ?', ["%{$search}%"]);
        }

        if ($request->filled('jenis_jabatan')) {
            $query->where('jenis_jabatan', $request->jenis_jabatan);
        }

        $perPage = in_array($request->get('per_page'), [10, 25, 50, 100])
            ? $request->get('per_page')
            : 25;

        $referensis = $query->orderBy('nama')->paginate($perPage)->withQueryString();
        $jenisOptions = NamaJabatanReferensi::jenisOptions();

        return view('admin.nama-jabatan-referensi.index', compact('referensis', 'jenisOptions'));
    }

    public function create()
    {
        $jenisOptions = NamaJabatanReferensi::jenisOptions();
        return view('admin.nama-jabatan-referensi.create', compact('jenisOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'jenis_jabatan' => 'nullable|string|max:100',
        ]);

        // Normalisasi spasi
        $validated['nama'] = trim(preg_replace('/\s+/', ' ', $validated['nama']));

        // Cek duplikat
        $exists = NamaJabatanReferensi::whereRaw('LOWER(nama) = ?', [strtolower($validated['nama'])])
            ->where('jenis_jabatan', $validated['jenis_jabatan'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'nama' => 'Nama jabatan dengan jenis yang sama sudah ada.'
            ]);
        }

        NamaJabatanReferensi::create($validated);

        return redirect()->route('admin.nama-jabatan-referensi.index')
            ->with('success', 'Nama jabatan referensi berhasil ditambahkan.');
    }

    public function edit(NamaJabatanReferensi $namaJabatanReferensi)
    {
        $jenisOptions = NamaJabatanReferensi::jenisOptions();
        return view('admin.nama-jabatan-referensi.edit', compact('namaJabatanReferensi', 'jenisOptions'));
    }

    public function update(Request $request, NamaJabatanReferensi $namaJabatanReferensi)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'jenis_jabatan' => 'nullable|string|max:100',
        ]);

        $validated['nama'] = trim(preg_replace('/\s+/', ' ', $validated['nama']));

        // Cek duplikat (exclude self)
        $exists = NamaJabatanReferensi::whereRaw('LOWER(nama) = ?', [strtolower($validated['nama'])])
            ->where('jenis_jabatan', $validated['jenis_jabatan'])
            ->where('id', '!=', $namaJabatanReferensi->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'nama' => 'Nama jabatan dengan jenis yang sama sudah ada.'
            ]);
        }

        $namaLama = $namaJabatanReferensi->nama;
        $namaJabatanReferensi->update($validated);

        // Sync nama di tabel jabatans jika nama berubah
        if ($namaLama !== $validated['nama']) {
            $updated = \App\Models\Jabatan::where('nama', $namaLama)->update(['nama' => $validated['nama']]);
        }

        return redirect()->route('admin.nama-jabatan-referensi.index')
            ->with('success', 'Nama jabatan referensi berhasil diperbarui.');
    }

    public function destroy(NamaJabatanReferensi $namaJabatanReferensi)
    {
        $namaJabatanReferensi->delete();

        return redirect()->route('admin.nama-jabatan-referensi.index')
            ->with('success', 'Nama jabatan referensi berhasil dihapus.');
    }
}
