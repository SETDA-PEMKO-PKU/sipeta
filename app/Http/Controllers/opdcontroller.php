<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    /**
     * Menampilkan daftar semua OPD
     */
    public function index()
    {
        $opds = Opd::orderBy('nama')->get();
        return view('opds.index', compact('opds'));
    }

    /**
     * Menampilkan detail OPD beserta bagian dan jabatan
     */
    public function show($id)
    {
        $opd = Opd::with([
            'bagians.jabatans.bagian', 
            'bagians.children',
            'jabatans.bagian',
            'jabatans.asns'
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
        $opd = Opd::with(['bagians' => function($query) {
            $query->whereNull('parent_id')->with('children.jabatans');
        }])->findOrFail($id);

        return response()->json($opd);
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
            'bagian_id' => 'nullable|exists:bagians,id'
        ]);

        // Pastikan bagian_id milik OPD ini (jika ada)
        if ($request->bagian_id) {
            $bagian = Bagian::where('id', $request->bagian_id)
                           ->where('opd_id', $opdId)
                           ->firstOrFail();
        }

        $jabatanData = [
            'nama' => $request->nama,
            'jenis_jabatan' => $request->jenis_jabatan,
            'kelas' => $request->kelas,
            'kebutuhan' => $request->kebutuhan,
            'parent_id' => $request->bagian_id
        ];
        
        // Jika tidak ada bagian_id, maka ini adalah jabatan kepala OPD
        if (!$request->bagian_id) {
            $jabatanData['opd_id'] = $opdId;
        }

        Jabatan::create($jabatanData);

        return redirect()->route('opds.show', $opdId)
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
            'bagian_id' => 'nullable|exists:bagians,id'
        ]);

        // Cari jabatan berdasarkan ID dan OPD (bisa jabatan kepala OPD atau jabatan dengan bagian)
        $jabatan = Jabatan::where('id', $jabatanId)
                          ->where(function($query) use ($opdId) {
                              $query->where('opd_id', $opdId) // Jabatan kepala OPD
                                    ->orWhereHas('parentBagian', function($subQuery) use ($opdId) {
                                        $subQuery->where('opd_id', $opdId); // Jabatan dengan bagian
                                    });
                          })
                          ->firstOrFail();

        // Pastikan bagian_id milik OPD ini (jika ada)
        if ($request->bagian_id) {
            $bagian = Bagian::where('id', $request->bagian_id)
                           ->where('opd_id', $opdId)
                           ->firstOrFail();
        }

        $updateData = [
            'nama' => $request->nama,
            'jenis_jabatan' => $request->jenis_jabatan,
            'kelas' => $request->kelas,
            'kebutuhan' => $request->kebutuhan,
            'parent_id' => $request->bagian_id
        ];
        
        // Jika tidak ada bagian_id, maka ini adalah jabatan kepala OPD
        if (!$request->bagian_id) {
            $updateData['opd_id'] = $opdId;
            $updateData['parent_id'] = null;
        } else {
            $updateData['opd_id'] = null;
        }

        $jabatan->update($updateData);

        return redirect()->route('opds.show', $opdId)
                        ->with('success', 'Jabatan berhasil diperbarui!');
    }

    /**
     * Menghapus jabatan
     */
    public function destroyJabatan($opdId, $jabatanId)
    {
        // Cari jabatan berdasarkan ID dan OPD (bisa jabatan kepala OPD atau jabatan dengan bagian)
        $jabatan = Jabatan::where('id', $jabatanId)
                          ->where(function($query) use ($opdId) {
                              $query->where('opd_id', $opdId) // Jabatan kepala OPD
                                    ->orWhereHas('parentBagian', function($subQuery) use ($opdId) {
                                        $subQuery->where('opd_id', $opdId); // Jabatan dengan bagian
                                    });
                          })
                          ->firstOrFail();

        // Cek apakah jabatan memiliki jabatan terkait dalam bagian yang sama
        if ($jabatan->siblings()->count() > 0) {
            return redirect()->route('opds.show', $opdId)
                            ->with('error', 'Tidak dapat menghapus jabatan yang memiliki jabatan terkait dalam bagian yang sama!');
        }

        // Cek apakah jabatan memiliki ASN
        if ($jabatan->asns()->count() > 0) {
            return redirect()->route('opds.show', $opdId)
                            ->with('error', 'Tidak dapat menghapus jabatan yang memiliki ASN!');
        }

        $jabatan->delete();

        return redirect()->route('opds.show', $opdId)
                        ->with('success', 'Jabatan berhasil dihapus!');
    }

    /**
     * Update nama OPD
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:opds,nama,' . $id
        ]);

        $opd = Opd::findOrFail($id);
        $opd->update([
            'nama' => $request->nama
        ]);

        return redirect()->route('opds.show', $id)
                        ->with('success', 'Nama OPD berhasil diperbarui!');
    }

    /**
     * Hapus OPD beserta semua bagian dan jabatan di dalamnya
     */
    public function destroy($id)
    {
        $opd = Opd::findOrFail($id);
        
        // Hapus semua jabatan yang terkait dengan OPD ini
        // (akan otomatis menghapus jabatan di semua bagian karena cascade)
        $opd->jabatans()->delete();
        
        // Hapus semua bagian
        $opd->bagians()->delete();
        
        // Hapus OPD
        $opd->delete();

        return redirect()->route('opds.index')
                        ->with('success', 'OPD "' . $opd->nama . '" beserta semua bagian dan jabatan berhasil dihapus!');
    }

    /**
     * Menyimpan ASN baru
     */
    public function storeAsn(Request $request, $opdId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:asns,nip',
            'jabatan_id' => 'required|exists:jabatans,id',
            'bagian_id' => 'nullable|exists:bagians,id'
        ]);

        // Validasi bahwa jabatan dan bagian (jika ada) milik OPD yang benar
        $jabatan = Jabatan::findOrFail($request->jabatan_id);
        
        // Cek apakah jabatan adalah kepala OPD atau jabatan dengan bagian
        if ($jabatan->opd_id) {
            // Jabatan kepala OPD
            if ($jabatan->opd_id != $opdId) {
                return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk OPD ini.']);
            }
            $bagianId = null;
        } else {
            // Jabatan dengan bagian
            if (!$jabatan->parentBagian || $jabatan->parentBagian->opd_id != $opdId) {
                return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk OPD ini.']);
            }
            $bagianId = $jabatan->parent_id;
        }

        // Validasi bagian_id jika disediakan
        if ($request->bagian_id) {
            $bagian = Bagian::findOrFail($request->bagian_id);
            if ($bagian->opd_id != $opdId) {
                return back()->withErrors(['bagian_id' => 'Bagian tidak valid untuk OPD ini.']);
            }
            $bagianId = $request->bagian_id;
        }

        Asn::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'bagian_id' => $bagianId,
            'opd_id' => $opdId
        ]);

        return redirect()->route('opds.show', $opdId)
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
            'jabatan_id' => 'required|exists:jabatans,id',
            'bagian_id' => 'nullable|exists:bagians,id'
        ]);

        $asn = Asn::where('id', $asnId)
                  ->where('opd_id', $opdId)
                  ->firstOrFail();

        // Simpan jabatan lama jika jabatan berubah
        $jabatanLama = $asn->jabatan;

        // Validasi jabatan dan bagian seperti di storeAsn
        $jabatan = Jabatan::findOrFail($request->jabatan_id);
        
        if ($jabatan->opd_id) {
            if ($jabatan->opd_id != $opdId) {
                return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk OPD ini.']);
            }
            $bagianId = null;
        } else {
            if (!$jabatan->parentBagian || $jabatan->parentBagian->opd_id != $opdId) {
                return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk OPD ini.']);
            }
            $bagianId = $jabatan->parent_id;
        }

        if ($request->bagian_id) {
            $bagian = Bagian::findOrFail($request->bagian_id);
            if ($bagian->opd_id != $opdId) {
                return back()->withErrors(['bagian_id' => 'Bagian tidak valid untuk OPD ini.']);
            }
            $bagianId = $request->bagian_id;
        }

        $asn->update([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan_id' => $request->jabatan_id,
            'bagian_id' => $bagianId,
            'opd_id' => $opdId
        ]);

        return redirect()->route('opds.show', $opdId)
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
        $jabatan = $asn->jabatan;
        
        $asn->delete();

        return redirect()->route('opds.show', $opdId)
                        ->with('success', 'ASN "' . $namaAsn . '" berhasil dihapus!');
    }
}