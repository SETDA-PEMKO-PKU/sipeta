<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HasOpdScope;
use App\Services\BezettingService;
use Illuminate\Http\Request;

class BezettingController extends Controller
{
    use HasOpdScope;

    public function __construct(protected BezettingService $bezettingService) {}

    /**
     * Halaman utama bezetting per jabatan
     */
    public function index()
    {
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $overview = $this->bezettingService->getOverview($accessibleOpdIds);

        return view('admin.bezetting.index', compact('overview'));
    }

    /**
     * API: live search jabatan
     */
    public function search(Request $request)
    {
        $keyword = trim($request->get('q', ''));

        if (strlen($keyword) < 2) {
            return response()->json([]);
        }

        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $results = $this->bezettingService->searchJabatan($keyword, $accessibleOpdIds);

        return response()->json($results);
    }

    /**
     * API: detail sebaran jabatan per OPD
     */
    public function detail(Request $request, string $namaJabatan)
    {
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $data = $this->bezettingService->getDetailPerOpd($namaJabatan, $accessibleOpdIds);

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('admin.bezetting.index', [
            'overview'      => $this->bezettingService->getOverview($accessibleOpdIds),
            'detailData'    => $data,
            'namaJabatan'   => $namaJabatan,
        ]);
    }
}
