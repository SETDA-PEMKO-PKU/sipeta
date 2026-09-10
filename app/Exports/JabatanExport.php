<?php

namespace App\Exports;

use App\Models\Jabatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JabatanExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    protected $accessibleOpdIds;

    protected $filters;

    public function __construct($accessibleOpdIds = null, $filters = [])
    {
        $this->accessibleOpdIds = $accessibleOpdIds;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Jabatan::with(['parent', 'opdLangsung', 'asns']);

        // Apply OPD scope
        if ($this->accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $this->accessibleOpdIds);
        }

        // Search by nama jabatan
        if (! empty($this->filters['search'])) {
            $searchTerm = strtolower($this->filters['search']);
            $query->whereRaw('LOWER(nama) LIKE ?', ["%{$searchTerm}%"]);
        }

        // Filter by OPD
        if (! empty($this->filters['opd_id'])) {
            $query->where('opd_id', $this->filters['opd_id']);
        }

        // Filter by jenis jabatan
        if (! empty($this->filters['jenis_jabatan'])) {
            $query->where('jenis_jabatan', $this->filters['jenis_jabatan']);
        }

        // Filter by kelas
        if (! empty($this->filters['kelas'])) {
            $query->where('kelas', $this->filters['kelas']);
        }

        $jabatans = $query->orderBy('nama')->get();

        $no = 1;

        return $jabatans->map(function ($jabatan) use (&$no) {
            return [
                'no' => $no++,
                'nama' => $jabatan->nama,
                'opd' => $jabatan->opdLangsung ? $jabatan->opdLangsung->nama : ($jabatan->parent ? $jabatan->parent->opdLangsung->nama ?? '-' : '-'),
                'bagian_bidang' => $jabatan->parent ? $jabatan->parent->nama : '-',
                'jenis_jabatan' => $jabatan->jenis_jabatan ?? '-',
                'kelas' => $jabatan->kelas ?? '-',
                'kebutuhan' => $jabatan->kebutuhan ?? 0,
                'bezetting' => $jabatan->asns->count(),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Jabatan',
            'OPD',
            'Bagian/Bidang',
            'Jenis Jabatan',
            'Kelas',
            'Kebutuhan',
            'Bezetting',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 40,  // Nama Jabatan
            'C' => 45,  // OPD
            'D' => 35,  // Bagian/Bidang
            'E' => 18,  // Jenis Jabatan
            'F' => 10,  // Kelas
            'G' => 12,  // Kebutuhan
            'H' => 12,  // Bezetting
        ];
    }

    public function title(): string
    {
        return 'Data Jabatan';
    }
}
