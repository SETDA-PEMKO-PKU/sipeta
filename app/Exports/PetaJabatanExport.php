<?php

namespace App\Exports;

use App\Models\Opd;
use App\Models\Jabatan;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PetaJabatanExport implements WithEvents, WithTitle
{
    protected $opd;
    protected $jabatanTree;

    public function __construct(Opd $opd)
    {
        $this->opd = $opd;
        $this->loadJabatanTree();
    }

    /**
     * Load jabatan tree with all children recursively
     */
    private function loadJabatanTree()
    {
        $this->jabatanTree = Jabatan::where('opd_id', $this->opd->id)
            ->whereNull('parent_id')
            ->with(['asns'])
            ->get();

        $this->loadChildrenRecursively($this->jabatanTree);
    }

    /**
     * Recursively load children for jabatan collection
     */
    private function loadChildrenRecursively($jabatans, $depth = 0, $maxDepth = 10)
    {
        if ($depth >= $maxDepth || $jabatans->isEmpty()) {
            return;
        }

        $jabatanIds = $jabatans->pluck('id')->toArray();

        $allChildren = Jabatan::whereIn('parent_id', $jabatanIds)
            ->with(['asns'])
            ->get()
            ->groupBy('parent_id');

        foreach ($jabatans as $jabatan) {
            $children = $allChildren->get($jabatan->id, collect());
            $jabatan->setRelation('children', $children);

            if ($children->isNotEmpty()) {
                $this->loadChildrenRecursively($children, $depth + 1, $maxDepth);
            }
        }
    }

    /**
     * Get worksheet title
     */
    public function title(): string
    {
        return 'Peta Jabatan';
    }

    /**
     * Register events for custom Excel manipulation
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getColumnDimension('B')->setWidth(8);
                $sheet->getColumnDimension('C')->setWidth(8);
                $sheet->getColumnDimension('D')->setWidth(8);
                $sheet->getColumnDimension('E')->setWidth(8);

                $currentRow = 1;

                // Draw the organizational chart
                foreach ($this->jabatanTree as $kepala) {
                    $currentRow = $this->drawJabatanNode($sheet, $kepala, $currentRow, 1);
                    $currentRow += 2; // Gap between root nodes
                }
            },
        ];
    }

    /**
     * Draw a jabatan node (struktural box or table row)
     */
    private function drawJabatanNode($sheet, $jabatan, $startRow, $startCol)
    {
        $bezetting = $jabatan->asns ? $jabatan->asns->count() : 0;
        $selisih = $bezetting - $jabatan->kebutuhan;
        $selisihText = ($selisih >= 0 ? '+' : '') . $selisih;

        $currentRow = $startRow;

        if ($jabatan->jenis_jabatan === 'Struktural') {
            // Draw struktural box (3 rows: header, nama, kelas)
            $colLetter = $this->getColumnLetter($startCol);
            $endColLetter = $this->getColumnLetter($startCol + 3);

            // Row 1: Header "Jabatan Struktural"
            $sheet->mergeCells("{$colLetter}{$currentRow}:{$endColLetter}{$currentRow}");
            $sheet->setCellValue("{$colLetter}{$currentRow}", 'Jabatan Struktural');
            $this->applyHeaderStyle($sheet, "{$colLetter}{$currentRow}:{$endColLetter}{$currentRow}");
            $currentRow++;

            // Row 2: Nama Jabatan
            $sheet->mergeCells("{$colLetter}{$currentRow}:{$endColLetter}{$currentRow}");
            $sheet->setCellValue("{$colLetter}{$currentRow}", $jabatan->nama);
            $this->applyNameStyle($sheet, "{$colLetter}{$currentRow}:{$endColLetter}{$currentRow}");
            $currentRow++;

            // Row 3: Kelas
            $sheet->mergeCells("{$colLetter}{$currentRow}:{$endColLetter}{$currentRow}");
            $sheet->setCellValue("{$colLetter}{$currentRow}", 'Kelas ' . ($jabatan->kelas ?? '-'));
            $this->applyKelasStyle($sheet, "{$colLetter}{$currentRow}:{$endColLetter}{$currentRow}");
            $currentRow++;

            // Box border for struktural
            $this->applyBoxBorder($sheet, "{$colLetter}{$startRow}:{$endColLetter}" . ($currentRow - 1));

            $currentRow++; // Gap after struktural box

            // Get children separated by type
            $children = $jabatan->children ?? collect();
            $strukturalChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Struktural');
            $pelaksanaChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Pelaksana');
            $fungsionalChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Fungsional');

            // Draw Pelaksana table if exists
            if ($pelaksanaChildren->isNotEmpty()) {
                $currentRow = $this->drawTable($sheet, 'Pelaksana', $pelaksanaChildren, $currentRow, $startCol);
                $currentRow++; // Gap after table
            }

            // Draw Fungsional table if exists
            if ($fungsionalChildren->isNotEmpty()) {
                $currentRow = $this->drawTable($sheet, 'Fungsional', $fungsionalChildren, $currentRow, $startCol);
                $currentRow++; // Gap after table
            }

            // Recursively draw struktural children
            foreach ($strukturalChildren as $child) {
                $currentRow = $this->drawJabatanNode($sheet, $child, $currentRow, $startCol);
                $currentRow++; // Gap between siblings
            }
        }

        return $currentRow;
    }

    /**
     * Draw a table for Pelaksana or Fungsional jabatan
     */
    private function drawTable($sheet, $jenis, $items, $startRow, $startCol)
    {
        $colA = $this->getColumnLetter($startCol);
        $colB = $this->getColumnLetter($startCol + 1);
        $colC = $this->getColumnLetter($startCol + 2);
        $colD = $this->getColumnLetter($startCol + 3);
        $colE = $this->getColumnLetter($startCol + 4);

        $currentRow = $startRow;

        // Table header: "Jabatan Pelaksana" or "Jabatan Fungsional"
        $sheet->mergeCells("{$colA}{$currentRow}:{$colE}{$currentRow}");
        $sheet->setCellValue("{$colA}{$currentRow}", "Jabatan {$jenis}");
        $this->applyHeaderStyle($sheet, "{$colA}{$currentRow}:{$colE}{$currentRow}");
        $currentRow++;

        // Column headers: Nama Jabatan | Kls | B | K | S
        $sheet->setCellValue("{$colA}{$currentRow}", 'Nama Jabatan');
        $sheet->setCellValue("{$colB}{$currentRow}", 'Kls');
        $sheet->setCellValue("{$colC}{$currentRow}", 'B');
        $sheet->setCellValue("{$colD}{$currentRow}", 'K');
        $sheet->setCellValue("{$colE}{$currentRow}", 'S');
        $this->applyColumnHeaderStyle($sheet, "{$colA}{$currentRow}:{$colE}{$currentRow}");
        $currentRow++;

        // Data rows
        foreach ($items as $item) {
            $bezetting = $item->asns ? $item->asns->count() : 0;
            $selisih = $bezetting - $item->kebutuhan;
            $selisihText = ($selisih >= 0 ? '+' : '') . $selisih;

            $sheet->setCellValue("{$colA}{$currentRow}", $item->nama);
            $sheet->setCellValue("{$colB}{$currentRow}", $item->kelas ?? '-');
            $sheet->setCellValue("{$colC}{$currentRow}", $bezetting);
            $sheet->setCellValue("{$colD}{$currentRow}", $item->kebutuhan);
            $sheet->setCellValue("{$colE}{$currentRow}", $selisihText);
            $this->applyDataRowStyle($sheet, "{$colA}{$currentRow}:{$colE}{$currentRow}");
            $currentRow++;
        }

        // Table border
        $this->applyBoxBorder($sheet, "{$colA}{$startRow}:{$colE}" . ($currentRow - 1));

        return $currentRow;
    }

    /**
     * Get column letter from number (1 = A, 2 = B, etc.)
     */
    private function getColumnLetter($num)
    {
        $letter = '';
        while ($num > 0) {
            $num--;
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intval($num / 26);
        }
        return $letter;
    }

    /**
     * Apply header style (black background, white text)
     */
    private function applyHeaderStyle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(explode(':', $range)[0][1] ?? substr($range, 1, 1))->setRowHeight(22);
    }

    /**
     * Apply name style (center aligned)
     */
    private function applyNameStyle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
    }

    /**
     * Apply kelas style
     */
    private function applyKelasStyle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * Apply column header style (gray background)
     */
    private function applyColumnHeaderStyle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * Apply data row style
     */
    private function applyDataRowStyle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'size' => 9,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align for columns B-E (Kls, B, K, S)
        $parts = explode(':', $range);
        $row = preg_replace('/[^0-9]/', '', $parts[0]);
        $startCol = preg_replace('/[0-9]/', '', $parts[0]);

        // Get columns after first one
        $colB = chr(ord($startCol) + 1);
        $colE = chr(ord($startCol) + 4);

        $sheet->getStyle("{$colB}{$row}:{$colE}{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Apply box border around a range
     */
    private function applyBoxBorder($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }
}
