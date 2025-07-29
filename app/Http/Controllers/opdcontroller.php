<?php

namespace App\Http\Controllers;

use App\Models\Opd;
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
        $opd = Opd::with(['bagians.jabatans', 'bagians.children'])->findOrFail($id);
        
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
}