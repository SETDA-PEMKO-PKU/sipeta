<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use Illuminate\Http\Request;

class BagianController extends Controller
{
    /**
     * Mendapatkan daftar bagian berdasarkan OPD untuk auto-complete
     */
    public function search(Request $request, $opdId)
    {
        $query = $request->get('q', '');
        
        $bagians = Bagian::where('opd_id', $opdId)
                         ->where('nama', 'LIKE', '%' . $query . '%')
                         ->select('id', 'nama')
                         ->limit(10)
                         ->get();
        
        return response()->json($bagians);
    }
}
