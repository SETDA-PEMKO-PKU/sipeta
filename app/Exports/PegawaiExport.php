<?php

namespace App\Exports;

use App\Models\Asn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    protected $accessibleOpdIds;

    protected $filters;

    public function __construct($accessibleOpdIds = null, $filters = [])
    {
        $this->accessibleOpdIds = $accessibleOpdIds;
        $this->filters = $filters;
    }

    /**
     * Get collection of data to export
     */
    public function collection()
    {
        $query = Asn::with(['jabatan.parent', 'opd']);

        // Apply OPD scope
        if ($this->accessibleOpdIds !== null) {
            $query->whereIn('opd_id', $this->accessibleOpdIds);
        }

        // Apply filters
        if (! empty($this->filters['search'])) {
            $searchTerm = strtolower($this->filters['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(nip) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereHas('opd', function ($q) use ($searchTerm) {
                        $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchTerm}%"]);
                    })
                    ->orWhereHas('jabatan', function ($q) use ($searchTerm) {
                        $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchTerm}%"]);
                    });
            });
        }

        if (! empty($this->filters['opd_id'])) {
            $query->where('opd_id', $this->filters['opd_id']);
        }

        if (! empty($this->filters['jabatan_id'])) {
            $query->where('jabatan_id', $this->filters['jabatan_id']);
        }

        if (! empty($this->filters['jenis_jabatan'])) {
            $query->whereHas('jabatan', function ($q) {
                $q->where('jenis_jabatan', $this->filters['jenis_jabatan']);
            });
        }

        if (! empty($this->filters['kelas'])) {
            $query->whereHas('jabatan', function ($q) {
                $q->where('kelas', $this->filters['kelas']);
            });
        }

        $pegawais = $query->orderBy('nama')->get();

        $no = 1;

        return $pegawais->map(function ($pegawai) use (&$no) {
            return [
                'no' => $no++,
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jabatan' => $pegawai->jabatan ? $pegawai->jabatan->nama : '-',
                'jenis_jabatan' => $pegawai->jabatan ? $pegawai->jabatan->jenis_jabatan : '-',
                'kelas_jabatan' => $pegawai->jabatan ? $pegawai->jabatan->kelas : '-',
                'opd' => $pegawai->opd ? $pegawai->opd->nama : '-',
            ];
        });
    }

    /**
     * Get headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Pegawai',
            'NIP',
            'Jabatan',
            'Jenis Jabatan',
            'Kelas Jabatan',
            'OPD',
        ];
    }

    /**
     * Apply styles to worksheet
     */
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

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 35,  // Nama Pegawai
            'C' => 25,  // NIP
            'D' => 40,  // Jabatan
            'E' => 15,  // Jenis Jabatan
            'F' => 15,  // Kelas Jabatan
            'G' => 45,  // OPD
        ];
    }

    /**
     * Get title for worksheet
     */
    public function title(): string
    {
        return 'Data Pegawai';
    }
}
