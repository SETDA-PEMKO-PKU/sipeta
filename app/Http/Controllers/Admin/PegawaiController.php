<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asn;
use App\Models\Opd;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Menampilkan daftar semua pegawai
     */
    public function index(Request $request)
    {
        $query = Asn::with(['jabatan.parent', 'opd']);

        // Filter berdasarkan OPD
        if ($request->filled('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }

        // Filter berdasarkan Jabatan
        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        // Filter berdasarkan Jenis Jabatan
        if ($request->filled('jenis_jabatan')) {
            $query->whereHas('jabatan', function($q) use ($request) {
                $q->where('jenis_jabatan', $request->jenis_jabatan);
            });
        }

        // Filter berdasarkan Kelas Jabatan
        if ($request->filled('kelas')) {
            $query->whereHas('jabatan', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Per page options
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $pegawais = $query->orderBy('nama')->paginate($perPage)->withQueryString();

        // Data untuk filter dropdown
        $opds = Opd::orderBy('nama')->get();
        $jabatans = Jabatan::orderBy('nama')->get();

        // Daftar jenis jabatan yang unik
        $jenisJabatans = Jabatan::select('jenis_jabatan')
                                ->distinct()
                                ->whereNotNull('jenis_jabatan')
                                ->pluck('jenis_jabatan');

        // Daftar kelas jabatan yang unik
        $kelasJabatans = Jabatan::select('kelas')
                                ->distinct()
                                ->whereNotNull('kelas')
                                ->orderBy('kelas', 'desc')
                                ->pluck('kelas');

        // Statistik
        $totalPegawai = $pegawais->count();
        $totalOpd = $pegawais->pluck('opd_id')->unique()->count();

        return view('admin.pegawai.index', compact(
            'pegawais',
            'totalPegawai',
            'totalOpd',
            'opds',
            'jabatans',
            'jenisJabatans',
            'kelasJabatans'
        ));
    }

    /**
     * Menampilkan form tambah pegawai
     */
    public function create()
    {
        $opds = Opd::with(['jabatanKepala.children.children'])->orderBy('nama')->get();

        return view('admin.pegawai.create', compact('opds'));
    }

    /**
     * Menyimpan pegawai baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:asns,nip',
            'jabatan_id' => 'required|exists:jabatans,id',
            'opd_id' => 'required|exists:opds,id'
        ]);

        Asn::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'opd_id' => $request->opd_id
        ]);

        return redirect()->route('admin.pegawai.index')
                        ->with('success', 'Pegawai berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit pegawai
     */
    public function edit($id)
    {
        $pegawai = Asn::with(['jabatan', 'opd'])->findOrFail($id);
        $opds = Opd::with(['jabatanKepala.children.children'])->orderBy('nama')->get();

        return view('admin.pegawai.edit', compact('pegawai', 'opds'));
    }

    /**
     * Memperbarui data pegawai
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:asns,nip,' . $id,
            'jabatan_id' => 'required|exists:jabatans,id',
            'opd_id' => 'required|exists:opds,id'
        ]);

        $pegawai = Asn::findOrFail($id);

        $pegawai->update([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'opd_id' => $request->opd_id
        ]);

        return redirect()->route('admin.pegawai.index')
                        ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    /**
     * Menghapus pegawai
     */
    public function destroy($id)
    {
        $pegawai = Asn::findOrFail($id);
        $namaPegawai = $pegawai->nama;

        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')
                        ->with('success', 'Pegawai "' . $namaPegawai . '" berhasil dihapus!');
    }

    /**
     * API endpoint untuk mendapatkan jabatan berdasarkan OPD
     */
    public function getJabatanByOpd($opdId)
    {
        $opd = Opd::with(['jabatanKepala.children.children.children'])->findOrFail($opdId);

        $jabatans = [];

        // Recursive function untuk mendapatkan semua jabatan
        $addJabatan = function($jabatan, $level = 0) use (&$jabatans, &$addJabatan) {
            $prefix = str_repeat('— ', $level);

            $jabatans[] = [
                'id' => $jabatan->id,
                'nama' => $prefix . $jabatan->nama,
                'type' => $jabatan->isRoot() ? 'kepala' : 'sub',
                'parent_id' => $jabatan->parent_id,
                'level' => $level
            ];

            // Rekursif untuk children
            foreach ($jabatan->children as $child) {
                $addJabatan($child, $level + 1);
            }
        };

        // Proses semua jabatan kepala
        foreach ($opd->jabatanKepala as $jabatan) {
            $addJabatan($jabatan);
        }

        return response()->json($jabatans);
    }
}
