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

    // Layout constants
    const STRUKTURAL_WIDTH = 4;  // columns for struktural box
    const TABLE_WIDTH = 5;       // columns for table (nama, kls, b, k, s)
    const COL_GAP = 1;           // gap between horizontal siblings

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

                // Set default column width
                $sheet->getDefaultColumnDimension()->setWidth(12);

                // Draw the organizational chart
                foreach ($this->jabatanTree as $kepala) {
                    // Calculate the width needed for this tree
                    $treeWidth = $this->calculateTreeWidth($kepala);
                    $startCol = 1;
                    $centerCol = $startCol + intval($treeWidth / 2) - intval(self::STRUKTURAL_WIDTH / 2);

                    $this->drawJabatanTree($sheet, $kepala, 1, max(1, $centerCol));
                }
            },
        ];
    }

    /**
     * Calculate the total width (in columns) needed for a jabatan tree
     */
    private function calculateTreeWidth($jabatan)
    {
        $children = $jabatan->children ?? collect();
        $strukturalChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Struktural');

        if ($strukturalChildren->isEmpty()) {
            // Leaf node - just need space for this node and its tables
            return max(self::STRUKTURAL_WIDTH, self::TABLE_WIDTH);
        }

        // Sum of all children widths + gaps
        $totalWidth = 0;
        foreach ($strukturalChildren as $index => $child) {
            if ($index > 0) {
                $totalWidth += self::COL_GAP;
            }
            $totalWidth += $this->calculateTreeWidth($child);
        }

        return max($totalWidth, self::STRUKTURAL_WIDTH, self::TABLE_WIDTH);
    }

    /**
     * Draw a complete jabatan tree starting from a node
     * Returns the row after the last drawn element
     */
    private function drawJabatanTree($sheet, $jabatan, $startRow, $startCol)
    {
        $currentRow = $startRow;

        // Draw this struktural node
        $boxEndRow = $this->drawStrukturalBox($sheet, $jabatan, $currentRow, $startCol);
        $currentRow = $boxEndRow + 1; // Gap after box

        // Get children separated by type
        $children = $jabatan->children ?? collect();
        $strukturalChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Struktural');
        $pelaksanaChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Pelaksana');
        $fungsionalChildren = $children->filter(fn($c) => $c->jenis_jabatan === 'Fungsional');

        // Draw Pelaksana table below struktural box
        if ($pelaksanaChildren->isNotEmpty()) {
            $currentRow = $this->drawTable($sheet, 'Pelaksana', $pelaksanaChildren, $currentRow, $startCol);
            $currentRow++; // Gap after table
        }

        // Draw Fungsional table below Pelaksana
        if ($fungsionalChildren->isNotEmpty()) {
            $currentRow = $this->drawTable($sheet, 'Fungsional', $fungsionalChildren, $currentRow, $startCol);
            $currentRow++; // Gap after table
        }

        // Draw struktural children horizontally
        if ($strukturalChildren->isNotEmpty()) {
            $childStartRow = $currentRow;
            $childCol = $startCol;
            $maxEndRow = $currentRow;

            // Calculate total width needed for all children
            $childWidths = [];
            foreach ($strukturalChildren as $child) {
                $childWidths[] = $this->calculateTreeWidth($child);
            }
            $totalChildWidth = array_sum($childWidths) + (count($childWidths) - 1) * self::COL_GAP;

            // Center children under parent
            $parentCenterCol = $startCol + intval(self::STRUKTURAL_WIDTH / 2);
            $childCol = $parentCenterCol - intval($totalChildWidth / 2);
            $childCol = max(1, $childCol);

            foreach ($strukturalChildren as $index => $child) {
                $childWidth = $childWidths[$index];

                // Center this child within its allocated width
                $childCenterCol = $childCol + intval($childWidth / 2) - intval(self::STRUKTURAL_WIDTH / 2);
                $childCenterCol = max(1, $childCenterCol);

                $childEndRow = $this->drawJabatanTree($sheet, $child, $childStartRow, $childCenterCol);
                $maxEndRow = max($maxEndRow, $childEndRow);

                $childCol += $childWidth + self::COL_GAP;
            }

            $currentRow = $maxEndRow;
        }

        return $currentRow;
    }

    /**
     * Draw a struktural box (header, nama, kelas)
     * Returns the last row used
     */
    private function drawStrukturalBox($sheet, $jabatan, $startRow, $startCol)
    {
        $colStart = $this->getColumnLetter($startCol);
        $colEnd = $this->getColumnLetter($startCol + self::STRUKTURAL_WIDTH - 1);

        $currentRow = $startRow;

        // Row 1: Header "Jabatan Struktural"
        $sheet->mergeCells("{$colStart}{$currentRow}:{$colEnd}{$currentRow}");
        $sheet->setCellValue("{$colStart}{$currentRow}", 'Jabatan Struktural');
        $this->applyHeaderStyle($sheet, "{$colStart}{$currentRow}:{$colEnd}{$currentRow}");
        $currentRow++;

        // Row 2: Nama Jabatan
        $sheet->mergeCells("{$colStart}{$currentRow}:{$colEnd}{$currentRow}");
        $sheet->setCellValue("{$colStart}{$currentRow}", $jabatan->nama);
        $this->applyNameStyle($sheet, "{$colStart}{$currentRow}:{$colEnd}{$currentRow}");
        $currentRow++;

        // Row 3: Kelas
        $sheet->mergeCells("{$colStart}{$currentRow}:{$colEnd}{$currentRow}");
        $sheet->setCellValue("{$colStart}{$currentRow}", 'Kelas ' . ($jabatan->kelas ?? '-'));
        $this->applyKelasStyle($sheet, "{$colStart}{$currentRow}:{$colEnd}{$currentRow}");

        // Box border
        $this->applyBoxBorder($sheet, "{$colStart}{$startRow}:{$colEnd}{$currentRow}");

        return $currentRow;
    }

    /**
     * Draw a table for Pelaksana or Fungsional jabatan
     * Returns the row after the table
     */
    private function drawTable($sheet, $jenis, $items, $startRow, $startCol)
    {
        $colA = $this->getColumnLetter($startCol);
        $colB = $this->getColumnLetter($startCol + 1);
        $colC = $this->getColumnLetter($startCol + 2);
        $colD = $this->getColumnLetter($startCol + 3);
        $colE = $this->getColumnLetter($startCol + 4);

        $currentRow = $startRow;

        // Table header
        $sheet->mergeCells("{$colA}{$currentRow}:{$colE}{$currentRow}");
        $sheet->setCellValue("{$colA}{$currentRow}", "Jabatan {$jenis}");
        $this->applyHeaderStyle($sheet, "{$colA}{$currentRow}:{$colE}{$currentRow}");
        $currentRow++;

        // Column headers
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
            $this->applyDataRowStyle($sheet, "{$colA}{$currentRow}:{$colE}{$currentRow}", $startCol);
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
                'size' => 10,
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
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
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
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
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
                'startColor' => ['rgb' => 'E5E7EB'],
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
    private function applyDataRowStyle($sheet, $range, $startCol)
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

        $colB = $this->getColumnLetter($startCol + 1);
        $colE = $this->getColumnLetter($startCol + 4);

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
