<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use App\Models\Bagian;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_opd' => Opd::count(),
            'total_bagian' => Bagian::count(),
            'total_jabatan' => Jabatan::count(),
            'total_asn' => Asn::count(),
            'total_admin' => Admin::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
