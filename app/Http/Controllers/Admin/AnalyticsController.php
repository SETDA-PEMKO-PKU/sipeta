<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\ChartDataService;
use App\Models\Opd;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalyticsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsController extends Controller
{
    protected $analyticsService;
    protected $chartDataService;

    public function __construct(AnalyticsService $analyticsService, ChartDataService $chartDataService)
    {
        $this->analyticsService = $analyticsService;
        $this->chartDataService = $chartDataService;
    }

    /**
     * Dashboard Overview
     */
    public function overview()
    {
        $stats = $this->analyticsService->getOverviewStats();

        // Chart data
        $jenisJabatanData = $this->analyticsService->getDistribusiJenisJabatan();
        $pieChartData = $this->chartDataService->formatPieChart($jenisJabatanData);

        $topOpdData = $this->analyticsService->getTopOpdByStaffing(10);
        $barChartData = $this->chartDataService->formatStackedBarChart($topOpdData);

        $gaugeData = $this->chartDataService->formatGaugeChart($stats['persentase_pemenuhan']);

        $understaffedPositions = $this->analyticsService->getUnderstaffedPositions(10);

        return view('admin.analytics.overview', compact(
            'stats',
            'pieChartData',
            'barChartData',
            'gaugeData',
            'understaffedPositions'
        ));
    }

    /**
     * Analytics per OPD
     */
    public function opdAnalytics(Request $request)
    {
        $opds = Opd::orderBy('nama')->get();
        $selectedOpdId = $request->get('opd_id');

        $data = null;
        $bagianChartData = null;
        $distribusiPegawaiData = null;

        if ($selectedOpdId) {
            $data = $this->analyticsService->getOpdAnalytics($selectedOpdId);

            // Chart bezetting vs kebutuhan per bagian
            $bagianChartData = $this->chartDataService->formatStackedBarChart($data['bagians_data']);

            // Distribusi pegawai (pie chart jenis jabatan untuk OPD ini)
            $filters = ['opd_id' => $selectedOpdId];
            $jenisData = $this->analyticsService->getDistribusiAsnPerJenis($filters);
            $distribusiPegawaiData = $this->chartDataService->formatPieChart($jenisData);
        }

        return view('admin.analytics.opd', compact(
            'opds',
            'selectedOpdId',
            'data',
            'bagianChartData',
            'distribusiPegawaiData'
        ));
    }

    /**
     * Analytics Kepegawaian
     */
    public function kepegawaianAnalytics(Request $request)
    {
        $filters = [
            'opd_id' => $request->get('opd_id'),
            'jenis_jabatan' => $request->get('jenis_jabatan'),
            'kelas' => $request->get('kelas'),
        ];

        $data = $this->analyticsService->getKepegawaianAnalytics($filters);

        // Charts
        $opdChartData = $this->chartDataService->formatBarChart($data['distribusi_per_opd'], 'ASN per OPD');
        $jenisChartData = $this->chartDataService->formatDonutChart($data['distribusi_per_jenis']);
        $kelasChartData = $this->chartDataService->formatBarChart(
            collect($data['distribusi_per_kelas'])->map(fn($val, $key) => ['nama' => $key, 'total' => $val])->values(),
            'ASN per Kelas',
            'rgba(168, 85, 247, 0.8)'
        );

        $opds = Opd::orderBy('nama')->get();
        $jenisJabatanList = ['Struktural', 'Fungsional', 'Pelaksana', 'Staf Ahli'];
        $kelasList = range(1, 17);

        return view('admin.analytics.kepegawaian', compact(
            'data',
            'opdChartData',
            'jenisChartData',
            'kelasChartData',
            'opds',
            'jenisJabatanList',
            'kelasList',
            'filters'
        ));
    }

    /**
     * Analytics Jabatan
     */
    public function jabatanAnalytics()
    {
        $data = $this->analyticsService->getJabatanAnalytics();

        // Charts
        $jenisChartData = $this->chartDataService->formatPieChart($data['total_per_jenis']);
        $kelasChartData = $this->chartDataService->formatBarChart(
            collect($data['distribusi_per_kelas'])->map(fn($val, $key) => ['nama' => $key, 'total' => $val])->values(),
            'Jabatan per Kelas'
        );
        $kosongTerisiData = $this->chartDataService->formatPieChart($data['jabatan_kosong_vs_terisi'], [
            'rgba(239, 68, 68, 0.8)', // Red for Kosong
            'rgba(34, 197, 94, 0.8)'  // Green for Terisi
        ]);

        return view('admin.analytics.jabatan', compact(
            'data',
            'jenisChartData',
            'kelasChartData',
            'kosongTerisiData'
        ));
    }

    /**
     * Gap Analysis
     */
    public function gapAnalysis()
    {
        $data = $this->analyticsService->getGapAnalysis();

        // Format heat map data
        $heatMapData = $this->chartDataService->formatHeatMapData($data['heat_map_data']);

        return view('admin.analytics.gap', compact(
            'data',
            'heatMapData'
        ));
    }

    /**
     * Laporan & Export
     */
    public function laporan()
    {
        $opds = Opd::orderBy('nama')->get();

        return view('admin.analytics.laporan', compact('opds'));
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $type = $request->get('type', 'overview');
        $opdId = $request->get('opd_id');

        return Excel::download(new AnalyticsExport($type, $opdId), 'analytics-' . $type . '-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $type = $request->get('type', 'overview');
        $opdId = $request->get('opd_id');

        $data = [];

        switch ($type) {
            case 'overview':
                $data = [
                    'stats' => $this->analyticsService->getOverviewStats(),
                    'topOpd' => $this->analyticsService->getTopOpdByStaffing(10),
                    'understaffed' => $this->analyticsService->getUnderstaffedPositions(10),
                ];
                break;

            case 'opd':
                if ($opdId) {
                    $data = $this->analyticsService->getOpdAnalytics($opdId);
                }
                break;

            case 'gap':
                $data = $this->analyticsService->getGapAnalysis();
                break;
        }

        $pdf = Pdf::loadView('admin.analytics.pdf.' . $type, compact('data'));
        return $pdf->download('analytics-' . $type . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * API endpoint for chart data (AJAX)
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type');
        $opdId = $request->get('opd_id');

        switch ($type) {
            case 'jenis_jabatan':
                $data = $this->analyticsService->getDistribusiJenisJabatan();
                return response()->json($this->chartDataService->formatPieChart($data));

            case 'top_opd':
                $data = $this->analyticsService->getTopOpdByStaffing(10);
                return response()->json($this->chartDataService->formatStackedBarChart($data));

            case 'opd_bagian':
                if ($opdId) {
                    $opdData = $this->analyticsService->getOpdAnalytics($opdId);
                    return response()->json($this->chartDataService->formatStackedBarChart($opdData['bagians_data']));
                }
                break;

            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }

        return response()->json(['error' => 'No data'], 404);
    }
}
