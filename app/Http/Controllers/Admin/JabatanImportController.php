<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HasOpdScope;
use App\Models\Jabatan;
use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JabatanImportController extends Controller
{
    use HasOpdScope;

    /**
     * Apply middleware untuk permission check
     */
    public function __construct()
    {
        $this->middleware('admin.permission:manage_opd_jabatan');
    }

    /**
     * Show import form
     */
    public function showImportForm(Request $request)
    {
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $opds = Opd::whereIn('id', $accessibleOpdIds)->orderBy('nama')->get();
        
        // Pre-select OPD for admin_opd
        $selectedOpdId = null;
        $admin = auth('admin')->user();
        if ($admin->isAdminOpd()) {
            $selectedOpdId = $admin->opd_id;
        } elseif ($request->filled('opd_id')) {
            $selectedOpdId = $request->opd_id;
        }

        return view('admin.jabatan.import', compact('opds', 'selectedOpdId'));
    }

    /**
     * Download template Excel untuk import jabatan
     */
    public function downloadTemplate(Request $request)
    {
        $admin = auth('admin')->user();
        
        // For admin_opd, use their OPD
        if ($admin->isAdminOpd()) {
            $opdId = $admin->opd_id;
        } else {
            $opdId = $request->query('opd_id');
        }

        $opd = null;
        $opdName = 'semua_opd';
        
        if ($opdId) {
            $opd = Opd::find($opdId);
            if ($opd) {
                $opdName = str_replace(' ', '_', strtolower($opd->nama));
            }
        }

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        
        // ============ Sheet 1: Template Import ============
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        // Header
        $headers = ['nama', 'jenis_jabatan', 'kelas', 'kebutuhan', 'parent_nama', 'opd_id'];
        $headerDescriptions = [
            'Nama Jabatan (wajib)',
            'Jenis Jabatan (Struktural/Fungsional/Pelaksana)',
            'Kelas Jabatan (1-17)',
            'Jumlah Kebutuhan Formasi',
            'Nama Jabatan Parent (opsional)',
            'ID OPD (wajib)'
        ];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($headerDescriptions, null, 'A2');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Style description row
        $descStyle = [
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']]
        ];
        $sheet->getStyle('A2:F2')->applyFromArray($descStyle);

        // Example data rows
        $exampleData = [];
        if ($opd) {
            $exampleData = [
                ['Kepala ' . $opd->nama, 'Struktural', 14, 1, '', $opd->id],
                ['Sekretaris', 'Struktural', 12, 1, 'Kepala ' . $opd->nama, $opd->id],
                ['Kepala Sub Bagian Umum', 'Struktural', 9, 1, 'Sekretaris', $opd->id],
                ['Kepala Sub Bagian Keuangan', 'Struktural', 9, 1, 'Sekretaris', $opd->id],
                ['Analis Kebijakan', 'Fungsional', 9, 5, 'Kepala Sub Bagian Umum', $opd->id],
            ];
        } else {
            $exampleData = [
                ['Kepala Dinas', 'Struktural', 14, 1, '', '(isi dengan ID OPD)'],
                ['Sekretaris', 'Struktural', 12, 1, 'Kepala Dinas', '(isi dengan ID OPD)'],
                ['Kepala Bidang', 'Struktural', 11, 1, 'Kepala Dinas', '(isi dengan ID OPD)'],
            ];
        }
        
        $row = 3;
        foreach ($exampleData as $data) {
            $sheet->fromArray($data, null, 'A' . $row);
            $row++;
        }

        // Style example rows
        $sheet->getStyle('A3:F' . ($row - 1))->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFDE7');

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ============ Sheet 2: Daftar OPD (untuk referensi) ============
        $opdSheet = $spreadsheet->createSheet();
        $opdSheet->setTitle('Daftar OPD');
        
        $opdSheet->fromArray(['ID', 'Nama OPD'], null, 'A1');
        $opdSheet->getStyle('A1:B1')->applyFromArray($headerStyle);
        
        $allOpds = Opd::orderBy('nama')->get(['id', 'nama']);
        $opdRow = 2;
        foreach ($allOpds as $opdItem) {
            $opdSheet->setCellValue('A' . $opdRow, $opdItem->id);
            $opdSheet->setCellValue('B' . $opdRow, $opdItem->nama);
            $opdRow++;
        }
        
        $opdSheet->getColumnDimension('A')->setWidth(10);
        $opdSheet->getColumnDimension('B')->setAutoSize(true);

        // ============ Sheet 3: Jabatan yang sudah ada (untuk referensi parent) ============
        if ($opd) {
            $jabatanSheet = $spreadsheet->createSheet();
            $jabatanSheet->setTitle('Jabatan Existing');
            
            $jabatanSheet->fromArray(['ID', 'Nama Jabatan', 'Parent'], null, 'A1');
            $jabatanSheet->getStyle('A1:C1')->applyFromArray($headerStyle);
            
            $existingJabatans = Jabatan::where('opd_id', $opd->id)
                ->with('parent')
                ->orderBy('nama')
                ->get();
            
            $jabatanRow = 2;
            foreach ($existingJabatans as $jabatan) {
                $jabatanSheet->setCellValue('A' . $jabatanRow, $jabatan->id);
                $jabatanSheet->setCellValue('B' . $jabatanRow, $jabatan->nama);
                $jabatanSheet->setCellValue('C' . $jabatanRow, $jabatan->parent ? $jabatan->parent->nama : '-');
                $jabatanRow++;
            }
            
            $jabatanSheet->getColumnDimension('A')->setWidth(10);
            $jabatanSheet->getColumnDimension('B')->setAutoSize(true);
            $jabatanSheet->getColumnDimension('C')->setAutoSize(true);
        }

        // Set active sheet back to template
        $spreadsheet->setActiveSheetIndex(0);

        // Generate filename
        $filename = 'template_import_jabatan_' . $opdName . '_' . date('Y-m-d') . '.xlsx';

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Preview import data
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:xls,xlsx'
        ], [
            'file.required' => 'File harus dipilih',
            'file.mimes' => 'File harus berformat XLS atau XLSX',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        $file = $request->file('file');
        $parsedData = $this->parseExcelFile($file);

        if (empty($parsedData['data'])) {
            return back()->withErrors(['file' => 'File kosong atau format tidak valid']);
        }

        // Validate data
        $validationResult = $this->validateImportData($parsedData['data']);

        // Store data in session for import
        session(['jabatan_import_preview_data' => $validationResult]);

        $accessibleOpdIds = $this->getAccessibleOpdIds();
        $opds = Opd::whereIn('id', $accessibleOpdIds)->orderBy('nama')->get();

        return view('admin.jabatan.import-preview', compact('validationResult', 'opds'));
    }

    /**
     * Process import
     */
    public function processImport(Request $request)
    {
        $validationResult = session('jabatan_import_preview_data');
        
        if (!$validationResult || empty($validationResult['valid'])) {
            return redirect()->route('admin.jabatan.import.form')
                ->with('error', 'Tidak ada data valid untuk diimport. Silakan upload ulang file.');
        }

        $admin = auth('admin')->user();
        $accessibleOpdIds = $this->getAccessibleOpdIds();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            // Sort by parent dependency - process items without parent first
            $validData = collect($validationResult['valid'])->sortBy(function ($item) {
                return empty($item['parent_nama']) ? 0 : 1;
            });

            // Keep track of newly created jabatan names for parent lookup
            $createdJabatans = [];

            foreach ($validData as $index => $row) {
                $opdId = (int) $row['opd_id'];
                
                // Validate OPD access
                if (!in_array($opdId, $accessibleOpdIds)) {
                    $errors[] = "Baris {$row['row_number']}: Anda tidak memiliki akses ke OPD ini";
                    $skipped++;
                    continue;
                }

                // Check for duplicate
                $existing = Jabatan::where('nama', $row['nama'])
                    ->where('opd_id', $opdId)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Find parent_id
                $parentId = null;
                if (!empty($row['parent_nama'])) {
                    // First check in createdJabatans
                    $parentKey = strtolower(trim($row['parent_nama'])) . '_' . $opdId;
                    if (isset($createdJabatans[$parentKey])) {
                        $parentId = $createdJabatans[$parentKey];
                    } else {
                        // Then check in database
                        $parent = Jabatan::where('nama', $row['parent_nama'])
                            ->where('opd_id', $opdId)
                            ->first();
                        if ($parent) {
                            $parentId = $parent->id;
                        }
                    }
                }

                // Create new jabatan
                $jabatan = Jabatan::create([
                    'nama' => $row['nama'],
                    'jenis_jabatan' => $row['jenis_jabatan'] ?: null,
                    'kelas' => !empty($row['kelas']) ? (int) $row['kelas'] : null,
                    'kebutuhan' => !empty($row['kebutuhan']) ? (int) $row['kebutuhan'] : null,
                    'parent_id' => $parentId,
                    'opd_id' => $opdId
                ]);

                // Store for parent lookup
                $jabatanKey = strtolower(trim($row['nama'])) . '_' . $opdId;
                $createdJabatans[$jabatanKey] = $jabatan->id;

                $imported++;
            }

            DB::commit();

            // Clear session
            session()->forget('jabatan_import_preview_data');

            $message = "Import selesai! {$imported} jabatan berhasil diimport.";
            if ($skipped > 0) {
                $message .= " {$skipped} data dilewati (sudah ada/duplikat).";
            }

            return redirect()->route('admin.jabatan.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.jabatan.import.form')
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    /**
     * API: Import single row
     */
    public function importSingleRow(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'opd_id' => 'required|integer',
            'index' => 'required|integer'
        ]);

        $admin = auth('admin')->user();
        $accessibleOpdIds = $this->getAccessibleOpdIds();
        
        $opdId = (int) $request->input('opd_id');
        $nama = $request->input('nama');
        $jenisJabatan = $request->input('jenis_jabatan');
        $kelas = $request->input('kelas');
        $kebutuhan = $request->input('kebutuhan');
        $parentNama = $request->input('parent_nama');
        $index = $request->input('index');

        try {
            // Validate OPD access
            if (!in_array($opdId, $accessibleOpdIds)) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke OPD ini',
                    'index' => $index
                ], 403);
            }

            // Check if already exists
            $existing = Jabatan::where('nama', $nama)
                ->where('opd_id', $opdId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'status' => 'skipped',
                    'message' => 'Jabatan dengan nama ini sudah ada di OPD ini',
                    'index' => $index,
                    'data' => ['nama' => $nama, 'existing_id' => $existing->id]
                ]);
            }

            // Find parent_id
            $parentId = null;
            if (!empty($parentNama)) {
                $parent = Jabatan::where('nama', $parentNama)
                    ->where('opd_id', $opdId)
                    ->first();
                if ($parent) {
                    $parentId = $parent->id;
                }
            }

            // Create new jabatan
            $jabatan = Jabatan::create([
                'nama' => $nama,
                'jenis_jabatan' => $jenisJabatan ?: null,
                'kelas' => !empty($kelas) ? (int) $kelas : null,
                'kebutuhan' => !empty($kebutuhan) ? (int) $kebutuhan : null,
                'parent_id' => $parentId,
                'opd_id' => $opdId
            ]);

            return response()->json([
                'success' => true,
                'status' => 'imported',
                'message' => 'Jabatan berhasil diimport',
                'index' => $index,
                'data' => [
                    'id' => $jabatan->id,
                    'nama' => $nama
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'index' => $index
            ], 500);
        }
    }

    /**
     * Clear import session
     */
    public function clearImportSession()
    {
        session()->forget('jabatan_import_preview_data');
        
        return response()->json([
            'success' => true,
            'message' => 'Session cleared'
        ]);
    }

    /**
     * Parse Excel file
     */
    private function parseExcelFile($file)
    {
        $data = [];
        $header = null;

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $rowNumber = 0;
            $skipDescriptionRow = false;

            foreach ($rows as $row) {
                $rowNumber++;

                // Skip empty rows
                $nonEmptyValues = array_filter($row, function($val) {
                    return $val !== null && $val !== '';
                });
                if (empty($nonEmptyValues)) {
                    continue;
                }

                // First non-empty row is header
                if ($header === null) {
                    $header = array_map(function($val) {
                        return strtolower(trim($val ?? ''));
                    }, $row);
                    $skipDescriptionRow = true;
                    continue;
                }

                // Skip description row (second row with italicized descriptions)
                if ($skipDescriptionRow) {
                    // Check if this looks like a description row
                    $firstVal = $row[0] ?? '';
                    if (stripos($firstVal, 'nama jabatan') !== false || stripos($firstVal, 'wajib') !== false) {
                        $skipDescriptionRow = false;
                        continue;
                    }
                    $skipDescriptionRow = false;
                }

                // Map row to associative array
                $rowData = [];
                foreach ($header as $index => $key) {
                    if (!empty($key)) {
                        $rowData[$key] = isset($row[$index]) ? trim($row[$index] ?? '') : '';
                    }
                }
                $rowData['row_number'] = $rowNumber;
                $data[] = $rowData;
            }
        } catch (\Exception $e) {
            // Return empty if cannot parse
            return ['header' => null, 'data' => []];
        }

        return [
            'header' => $header,
            'data' => $data
        ];
    }

    /**
     * Validate import data
     */
    private function validateImportData($data)
    {
        $valid = [];
        $invalid = [];
        $existing = [];
        $accessibleOpdIds = $this->getAccessibleOpdIds();

        foreach ($data as $index => $row) {
            $errors = [];

            // Get values
            $nama = $row['nama'] ?? '';
            $jenisJabatan = $row['jenis_jabatan'] ?? '';
            $kelas = $row['kelas'] ?? '';
            $kebutuhan = $row['kebutuhan'] ?? '';
            $parentNama = $row['parent_nama'] ?? '';
            $opdId = $row['opd_id'] ?? '';

            // Skip completely empty rows
            if (empty($nama) && empty($opdId)) {
                continue;
            }

            // Validate nama (required)
            if (empty($nama)) {
                $errors[] = 'Nama jabatan wajib diisi';
            }

            // Validate opd_id (required)
            if (empty($opdId)) {
                $errors[] = 'ID OPD wajib diisi';
            } elseif (!is_numeric($opdId)) {
                $errors[] = 'ID OPD harus berupa angka';
            } else {
                $opd = Opd::find($opdId);
                if (!$opd) {
                    $errors[] = 'OPD dengan ID ' . $opdId . ' tidak ditemukan';
                } elseif (!in_array((int)$opdId, $accessibleOpdIds)) {
                    $errors[] = 'Anda tidak memiliki akses ke OPD ini';
                }
            }

            // Validate jenis_jabatan (optional but must be valid if provided)
            if (!empty($jenisJabatan)) {
                $validJenis = ['struktural', 'fungsional', 'pelaksana'];
                if (!in_array(strtolower($jenisJabatan), $validJenis)) {
                    $errors[] = 'Jenis jabatan harus "Struktural", "Fungsional", atau "Pelaksana"';
                }
            }

            // Validate kelas (optional but must be valid if provided)
            if (!empty($kelas)) {
                if (!is_numeric($kelas) || $kelas < 1 || $kelas > 17) {
                    $errors[] = 'Kelas harus angka antara 1-17';
                }
            }

            // Validate kebutuhan (optional but must be valid if provided)
            if (!empty($kebutuhan)) {
                if (!is_numeric($kebutuhan) || $kebutuhan < 0) {
                    $errors[] = 'Kebutuhan harus angka positif';
                }
            }

            // Get OPD name for display
            $opdNama = '';
            if (!empty($opdId) && is_numeric($opdId)) {
                $opd = Opd::find($opdId);
                $opdNama = $opd ? $opd->nama : '-';
            }

            // Check existing jabatan
            $existingJabatan = null;
            if (!empty($nama) && !empty($opdId) && is_numeric($opdId)) {
                $existingJabatan = Jabatan::where('nama', $nama)
                    ->where('opd_id', $opdId)
                    ->first();
            }

            $rowResult = [
                'row_number' => $row['row_number'],
                'nama' => $nama,
                'jenis_jabatan' => $jenisJabatan,
                'kelas' => $kelas,
                'kebutuhan' => $kebutuhan,
                'parent_nama' => $parentNama,
                'opd_id' => $opdId,
                'opd_nama' => $opdNama,
                'existing_info' => $existingJabatan ? ['id' => $existingJabatan->id] : null,
                'errors' => $errors
            ];

            // Categorize
            if (!empty($errors)) {
                $invalid[$index] = $rowResult;
            } elseif ($existingJabatan !== null) {
                $existing[$index] = $rowResult;
            } else {
                $valid[$index] = $rowResult;
            }
        }

        return [
            'valid' => $valid,
            'existing' => $existing,
            'invalid' => $invalid,
            'total' => count($data),
            'valid_count' => count($valid),
            'existing_count' => count($existing),
            'invalid_count' => count($invalid)
        ];
    }
}
