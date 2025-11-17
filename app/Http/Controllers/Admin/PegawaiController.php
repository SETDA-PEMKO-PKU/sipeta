<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asn;
use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Bagian;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Menampilkan daftar semua pegawai
     */
    public function index(Request $request)
    {
        $query = Asn::with(['jabatan', 'bagian', 'opd']);

        // Filter berdasarkan OPD
        if ($request->filled('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }

        // Filter berdasarkan Bagian
        if ($request->filled('bagian_id')) {
            $query->where('bagian_id', $request->bagian_id);
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
        $bagians = Bagian::orderBy('nama')->get();
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
            'bagians',
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
        $opds = Opd::with(['bagians.jabatans', 'jabatanKepala'])->orderBy('nama')->get();

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

        // Ambil data jabatan untuk mendapatkan bagian_id
        $jabatan = Jabatan::findOrFail($request->jabatan_id);

        // Tentukan bagian_id
        $bagianId = null;
        if ($jabatan->parent_id) {
            // Jika jabatan memiliki parent_id, berarti itu adalah bagian
            $bagianId = $jabatan->parent_id;
        }

        Asn::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'bagian_id' => $bagianId,
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
        $pegawai = Asn::with(['jabatan', 'bagian', 'opd'])->findOrFail($id);
        $opds = Opd::with(['bagians.jabatans', 'jabatanKepala'])->orderBy('nama')->get();

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

        // Ambil data jabatan untuk mendapatkan bagian_id
        $jabatan = Jabatan::findOrFail($request->jabatan_id);

        // Tentukan bagian_id
        $bagianId = null;
        if ($jabatan->parent_id) {
            $bagianId = $jabatan->parent_id;
        }

        $pegawai->update([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'bagian_id' => $bagianId,
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
        $opd = Opd::with(['bagians.jabatans', 'jabatanKepala'])->findOrFail($opdId);

        $jabatans = [];

        // Jabatan Kepala
        foreach ($opd->jabatanKepala as $jabatan) {
            $jabatans[] = [
                'id' => $jabatan->id,
                'nama' => $jabatan->nama,
                'type' => 'kepala',
                'bagian_nama' => 'Kepala OPD'
            ];
        }

        // Jabatan per Bagian
        foreach ($opd->bagians as $bagian) {
            foreach ($bagian->jabatans as $jabatan) {
                $jabatans[] = [
                    'id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'type' => 'bagian',
                    'bagian_id' => $bagian->id,
                    'bagian_nama' => $bagian->nama,
                    'bagian_parent_id' => $bagian->parent_id
                ];
            }
        }

        return response()->json($jabatans);
    }
}
