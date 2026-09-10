<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class AdminOpdExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * Get collection of data to export
     */
    public function collection(): Collection
    {
        return $this->data->map(function ($item, $index) {
            return [
                'no' => $index + 1,
                'opd' => $item['opd_nama'],
                'name' => $item['admin_name'],
                'email' => $item['email'],
                'password' => $item['password'],
                'role' => 'Admin OPD',
            ];
        });
    }

    /**
     * Get headings for Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'OPD',
            'Nama Admin',
            'Email',
            'Password',
            'Role',
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 12,
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
            'B' => 45,  // OPD
            'C' => 35,  // Nama Admin
            'D' => 40,  // Email
            'E' => 20,  // Password
            'F' => 15,  // Role
        ];
    }

    /**
     * Get title for worksheet
     */
    public function title(): string
    {
        return 'Admin OPD';
    }
}
