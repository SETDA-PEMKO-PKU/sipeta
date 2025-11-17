<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Bagian;
use Illuminate\Http\Request;

class BagianController extends Controller
{
    /**
     * Menyimpan bagian baru untuk OPD tertentu
     */
    public function store(Request $request, $opdId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:bagians,id'
        ]);

        // Pastikan parent_id milik OPD ini (jika ada)
        if ($request->parent_id) {
            $parentBagian = Bagian::where('id', $request->parent_id)
                                  ->where('opd_id', $opdId)
                                  ->firstOrFail();
        }

        // Cek apakah bagian dengan nama yang sama sudah ada di OPD ini
        $existingBagian = Bagian::where('nama', $request->nama)
                                ->where('opd_id', $opdId)
                                ->first();

        if ($existingBagian) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Bagian dengan nama "' . $request->nama . '" sudah ada!');
        }

        Bagian::create([
            'nama' => $request->nama,
            'opd_id' => $opdId,
            'parent_id' => $request->parent_id
        ]);

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Bagian berhasil ditambahkan!');
    }

    /**
     * Mengupdate bagian
     */
    public function update(Request $request, $opdId, $bagianId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:bagians,id'
        ]);

        // Cari bagian berdasarkan ID dan OPD
        $bagian = Bagian::where('id', $bagianId)
                        ->where('opd_id', $opdId)
                        ->firstOrFail();

        // Pastikan parent_id milik OPD ini dan bukan dirinya sendiri (jika ada)
        if ($request->parent_id) {
            if ($request->parent_id == $bagianId) {
                return redirect()->route('admin.opds.show', $opdId)
                                ->with('error', 'Bagian tidak dapat menjadi parent dari dirinya sendiri!');
            }

            $parentBagian = Bagian::where('id', $request->parent_id)
                                  ->where('opd_id', $opdId)
                                  ->firstOrFail();

            // Cek apakah parent yang dipilih adalah child dari bagian ini (mencegah circular reference)
            if ($this->isDescendant($bagianId, $request->parent_id)) {
                return redirect()->route('admin.opds.show', $opdId)
                                ->with('error', 'Tidak dapat memilih sub-bagian sebagai parent!');
            }
        }

        // Cek apakah bagian dengan nama yang sama sudah ada di OPD ini (kecuali dirinya sendiri)
        $existingBagian = Bagian::where('nama', $request->nama)
                                ->where('opd_id', $opdId)
                                ->where('id', '!=', $bagianId)
                                ->first();

        if ($existingBagian) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Bagian dengan nama "' . $request->nama . '" sudah ada!');
        }

        $bagian->update([
            'nama' => $request->nama,
            'parent_id' => $request->parent_id
        ]);

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Bagian berhasil diperbarui!');
    }

    /**
     * Menghapus bagian
     */
    public function destroy($opdId, $bagianId)
    {
        // Cari bagian berdasarkan ID dan OPD
        $bagian = Bagian::where('id', $bagianId)
                        ->where('opd_id', $opdId)
                        ->firstOrFail();

        // Cek apakah bagian memiliki sub-bagian
        if ($bagian->children()->count() > 0) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Tidak dapat menghapus bagian yang memiliki sub-bagian!');
        }

        // Cek apakah bagian memiliki jabatan
        if ($bagian->jabatans()->count() > 0) {
            return redirect()->route('admin.opds.show', $opdId)
                            ->with('error', 'Tidak dapat menghapus bagian yang memiliki jabatan!');
        }

        $bagian->delete();

        return redirect()->route('admin.opds.show', $opdId)
                        ->with('success', 'Bagian berhasil dihapus!');
    }

    /**
     * Helper function untuk mengecek apakah suatu bagian adalah descendant dari bagian lain
     */
    private function isDescendant($ancestorId, $descendantId)
    {
        $descendant = Bagian::find($descendantId);

        while ($descendant && $descendant->parent_id) {
            if ($descendant->parent_id == $ancestorId) {
                return true;
            }
            $descendant = $descendant->parent;
        }

        return false;
    }

    /**
     * API endpoint untuk mendapatkan detail bagian dengan statistik
     */
    public function getDetail($id)
    {
        $bagian = Bagian::with([
            'jabatans.asns',
            'children.jabatans.asns'
        ])->findOrFail($id);

        // Calculate statistics
        $totalAsn = 0;
        $totalKebutuhan = 0;

        // Count from direct jabatans
        foreach ($bagian->jabatans as $jabatan) {
            $totalAsn += $jabatan->asns->count();
            $totalKebutuhan += $jabatan->kebutuhan;
        }

        // Count from sub-bagians
        foreach ($bagian->children as $subBagian) {
            foreach ($subBagian->jabatans as $jabatan) {
                $totalAsn += $jabatan->asns->count();
                $totalKebutuhan += $jabatan->kebutuhan;
            }
        }

        // Build response
        $data = [
            'bagian_id' => $bagian->id,
            'nama' => $bagian->nama,
            'opd_id' => $bagian->opd_id,
            'parent_id' => $bagian->parent_id,
            'total_asn' => $totalAsn,
            'total_kebutuhan' => $totalKebutuhan,
            'jabatans' => $bagian->jabatans->map(function($jabatan) {
                return [
                    'id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'kebutuhan' => $jabatan->kebutuhan,
                    'bezetting' => $jabatan->asns->count(),
                ];
            }),
            'sub_bagians' => $bagian->children->map(function($subBagian) {
                $asnCount = 0;
                foreach ($subBagian->jabatans as $jabatan) {
                    $asnCount += $jabatan->asns->count();
                }
                return [
                    'id' => $subBagian->id,
                    'nama' => $subBagian->nama,
                    'jabatan_count' => $subBagian->jabatans->count(),
                    'asn_count' => $asnCount,
                ];
            })
        ];

        return response()->json($data);
    }
}
