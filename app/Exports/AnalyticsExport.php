<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $type;
    protected $opdId;
    protected $analyticsService;

    public function __construct($type = 'overview', $opdId = null)
    {
        $this->type = $type;
        $this->opdId = $opdId;
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Get collection of data to export
     */
    public function collection()
    {
        switch ($this->type) {
            case 'overview':
                return $this->getOverviewData();

            case 'opd':
                return $this->getOpdData();

            case 'kepegawaian':
                return $this->getKepegawaianData();

            case 'jabatan':
                return $this->getJabatanData();

            case 'gap':
                return $this->getGapData();

            default:
                return collect([]);
        }
    }

    /**
     * Get headings based on type
     */
    public function headings(): array
    {
        switch ($this->type) {
            case 'overview':
                return ['OPD', 'Kebutuhan', 'Bezetting', 'Selisih', 'Persentase Pemenuhan'];

            case 'opd':
                return ['Bagian', 'Kebutuhan', 'Bezetting', 'Selisih'];

            case 'kepegawaian':
                return ['OPD', 'Total ASN'];

            case 'jabatan':
                return ['Jenis Jabatan', 'Total Jabatan', 'Rata-rata Bezetting'];

            case 'gap':
                return ['Jabatan', 'Jenis', 'Kelas', 'OPD', 'Bagian', 'Kebutuhan', 'Bezetting', 'Gap'];

            default:
                return [];
        }
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    /**
     * Get title for worksheet
     */
    public function title(): string
    {
        return ucfirst($this->type) . ' Analytics';
    }

    /**
     * Get overview data
     */
    private function getOverviewData()
    {
        $topOpd = $this->analyticsService->getTopOpdByStaffing(999);

        return $topOpd->map(function ($item) {
            $item = (array) $item;
            $kebutuhan = $item['kebutuhan'] ?? 0;
            $bezetting = $item['bezetting'] ?? 0;
            $persentase = $kebutuhan > 0 ? round(($bezetting / $kebutuhan) * 100, 2) : 0;

            return [
                'opd' => $item['nama'] ?? '-',
                'kebutuhan' => $kebutuhan,
                'bezetting' => $bezetting,
                'selisih' => $item['selisih'] ?? 0,
                'persentase' => $persentase . '%',
            ];
        });
    }

    /**
     * Get OPD specific data
     */
    private function getOpdData()
    {
        if (!$this->opdId) {
            return collect([]);
        }

        $data = $this->analyticsService->getOpdAnalytics($this->opdId);

        return collect($data['bagians_data'])->map(function ($item) {
            $item = (array) $item;
            return [
                'bagian' => $item['nama'] ?? '-',
                'kebutuhan' => $item['kebutuhan'] ?? 0,
                'bezetting' => $item['bezetting'] ?? 0,
                'selisih' => $item['selisih'] ?? 0,
            ];
        });
    }

    /**
     * Get kepegawaian data
     */
    private function getKepegawaianData()
    {
        $data = $this->analyticsService->getDistribusiAsnPerOpd([]);

        return $data->map(function ($item) {
            $item = (array) $item;
            return [
                'opd' => $item['nama'] ?? '-',
                'total' => $item['total'] ?? 0,
            ];
        });
    }

    /**
     * Get jabatan data
     */
    private function getJabatanData()
    {
        $data = $this->analyticsService->getJabatanAnalytics();
        $result = [];

        foreach ($data['total_per_jenis'] as $jenis => $total) {
            $avgBezetting = $data['average_bezetting_per_jenis'][$jenis] ?? 0;
            $result[] = [
                'jenis' => $jenis,
                'total' => $total,
                'avg_bezetting' => $avgBezetting,
            ];
        }

        return collect($result);
    }

    /**
     * Get gap analysis data
     */
    private function getGapData()
    {
        $understaffed = $this->analyticsService->getUnderstaffedPositions(999);

        return $understaffed->map(function ($item) {
            return [
                'jabatan' => $item['nama_jabatan'] ?? '-',
                'jenis' => $item['jenis_jabatan'] ?? '-',
                'kelas' => $item['kelas'] ?? '-',
                'opd' => $item['opd'] ?? '-',
                'bagian' => $item['bagian'] ?? '-',
                'kebutuhan' => $item['kebutuhan'] ?? 0,
                'bezetting' => $item['bezetting'] ?? 0,
                'gap' => $item['gap'] ?? 0,
            ];
        });
    }
}
