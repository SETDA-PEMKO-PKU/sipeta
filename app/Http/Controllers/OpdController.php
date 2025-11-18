<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    /**
     * Menampilkan daftar semua OPD
     */
    public function index(Request $request)
    {
        // Per page options
        $perPage = $request->get('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $opds = Opd::with(['jabatanKepala.asns', 'asns'])
                   ->orderBy('nama')
                   ->paginate($perPage)
                   ->withQueryString();
        return view('opds.index', compact('opds'));
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

        return redirect()->route('admin.opds.index')
                        ->with('success', 'OPD "' . $opd->nama . '" berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail OPD beserta hierarki jabatan
     */
    public function show($id)
    {
        $opd = Opd::with([
            'jabatanKepala.children.children.children',
            'jabatanKepala.asns',
            'asns.jabatan'
        ])->findOrFail($id);

        // Tambahkan semua jabatan (termasuk jabatan kepala OPD)
        $opd->allJabatans = $opd->getAllJabatans();

        return view('opds.show', compact('opd'));
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
            'jenis_jabatan' => 'required|in:Staf Ahli,Struktural,Fungsional,Pelaksana',
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

        Jabatan::create($jabatanData);

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
            'jenis_jabatan' => 'required|in:Staf Ahli,Struktural,Fungsional,Pelaksana',
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

        $jabatan->update($updateData);

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
        $opd->update([
            'nama' => $request->nama
        ]);

        return redirect()->route('admin.opds.index')
                        ->with('success', 'OPD "' . $oldNama . '" berhasil diubah menjadi "' . $opd->nama . '"');
    }

    /**
     * Hapus OPD beserta semua jabatan di dalamnya
     */
    public function destroy($id)
    {
        $opd = Opd::findOrFail($id);

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

        Asn::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'opd_id' => $opdId
        ]);

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

        $asn->update([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'opd_id' => $opdId
        ]);

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
        $asn->delete();

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'ASN "' . $namaAsn . '" berhasil dihapus!');
    }

    /**
     * Menampilkan peta jabatan (organizational chart)
     */
    public function petaJabatan($id)
    {
        $opd = Opd::with([
            'jabatanKepala.children.children.children.asns',
            'jabatanKepala.asns'
        ])->findOrFail($id);

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

        return response()->stream($callback, 200, $headers);
    }
}
