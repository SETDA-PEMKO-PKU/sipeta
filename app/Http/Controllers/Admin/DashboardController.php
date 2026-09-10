<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HasOpdScope;
use App\Models\Admin;
use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use HasOpdScope;

    public function index()
    {
        $admin = auth('admin')->user();
        
        // Get accessible OPD IDs for the current admin
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        
        // Calculate statistics with OPD scoping
        $stats = [
            'total_opd' => count($accessibleOpdIds),
            'total_jabatan' => $this->applyOpdScope(Jabatan::query())->count(),
            'total_asn' => $this->applyOpdScope(Asn::query())->count(),
            'total_admin' => Admin::count(),
        ];
        
        // Calculate fill rate (percentage of jabatan that have ASN assigned)
        $totalJabatan = $this->applyOpdScope(Jabatan::query())->count();
        $filledJabatan = $this->applyOpdScope(Jabatan::query())->has('asns')->count();
        $stats['fill_rate'] = $totalJabatan > 0 ? round(($filledJabatan / $totalJabatan) * 100, 1) : 0;
        
        // Get OPD information for admin OPD
        $opdInfo = null;
        if ($admin->isAdminOpd() && $admin->opd) {
            $opdInfo = $admin->opd;
        }

        return view('admin.dashboard', compact('stats', 'opdInfo'));
    }
}
