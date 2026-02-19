<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Jabatan;
use App\Models\Asn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OpdImportController extends Controller
{
    /**
     * Apply middleware untuk permission check
     */
    public function __construct()
    {
        $this->middleware('admin.permission:import_asn');
    }

    /**
     * Generate dan download template Excel berdasarkan struktur jabatan OPD
     * Template akan berisi baris sesuai dengan kebutuhan setiap jabatan
     */
    public function downloadTemplate($opdId)
    {
        $opd = Opd::findOrFail($opdId);
        $allJabatans = $opd->getAllJabatans();

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import ASN');

        // Header - dengan kolom atasan
        $headers = ['no', 'jabatan_id', 'jabatan', 'atasan', 'kebutuhan_ke', 'nip', 'nama'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        $no = 1;
        foreach ($allJabatans as $jabatan) {
            $kebutuhan = max(1, $jabatan->kebutuhan);
            
            // Get parent jabatan name
            $atasanNama = '-';
            if ($jabatan->parent_id) {
                $parent = Jabatan::find($jabatan->parent_id);
                if ($parent) {
                    $atasanNama = $parent->nama;
                }
            }
            
            for ($i = 1; $i <= $kebutuhan; $i++) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $jabatan->id);
                $sheet->setCellValue('C' . $row, $jabatan->nama);
                $sheet->setCellValue('D' . $row, $atasanNama);
                $sheet->setCellValue('E' . $row, $i . ' dari ' . $kebutuhan);
                $sheet->setCellValue('F' . $row, ''); // NIP - diisi user
                $sheet->setCellValue('G' . $row, ''); // Nama - diisi user
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Protect columns A-E (readonly), allow F-G to be edited
        $sheet->getStyle('A2:E' . ($row - 1))->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);
        $sheet->getStyle('F2:G' . ($row - 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFDE7');

        // Generate filename
        $filename = 'template_import_asn_' . str_replace(' ', '_', strtolower($opd->nama)) . '_' . date('Y-m-d') . '.xlsx';

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generate dan download template Excel gabungan untuk SELURUH OPD
     */
    public function downloadTemplateAll()
    {
        $opds = Opd::orderBy('nama')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import ASN Semua OPD');

        // Header - sama dengan template per-OPD + kolom OPD
        $headers = ['no', 'opd', 'jabatan_id', 'jabatan', 'atasan', 'kebutuhan_ke', 'nip', 'nama'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        $no = 1;
        foreach ($opds as $opd) {
            $allJabatans = $opd->getAllJabatans();

            foreach ($allJabatans as $jabatan) {
                $kebutuhan = max(1, $jabatan->kebutuhan);

                // Get parent jabatan name
                $atasanNama = '-';
                if ($jabatan->parent_id) {
                    $parent = Jabatan::find($jabatan->parent_id);
                    if ($parent) {
                        $atasanNama = $parent->nama;
                    }
                }

                for ($i = 1; $i <= $kebutuhan; $i++) {
                    $sheet->setCellValue('A' . $row, $no++);
                    $sheet->setCellValue('B' . $row, $opd->nama);
                    $sheet->setCellValue('C' . $row, $jabatan->id);
                    $sheet->setCellValue('D' . $row, $jabatan->nama);
                    $sheet->setCellValue('E' . $row, $atasanNama);
                    $sheet->setCellValue('F' . $row, $i . ' dari ' . $kebutuhan);
                    $sheet->setCellValue('G' . $row, ''); // NIP - diisi user
                    $sheet->setCellValue('H' . $row, ''); // Nama - diisi user
                    $row++;
                }
            }
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Protect columns A-F (readonly), allow G-H to be edited
        if ($row > 2) {
            $sheet->getStyle('A2:F' . ($row - 1))->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);
            $sheet->getStyle('G2:H' . ($row - 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFDE7');
        }

        // Generate filename
        $filename = 'template_import_asn_semua_opd_' . date('Y-m-d') . '.xlsx';

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Tampilkan form upload file
     */
    public function showImportForm($opdId)
    {
        $opd = Opd::with([
            'jabatanKepala.children.children.children',
            'jabatanKepala.asns',
            'asns.jabatan'
        ])->findOrFail($opdId);

        $allJabatans = $opd->getAllJabatans();

        // Hitung total kebutuhan
        $totalKebutuhan = $allJabatans->sum('kebutuhan');
        $totalTerisi = $opd->asns->count();

        return view('opds.import', compact('opd', 'allJabatans', 'totalKebutuhan', 'totalTerisi'));
    }

    /**
     * Preview data dari file yang diupload (CSV, XLS, XLSX)
     */
    public function previewImport(Request $request, $opdId)
    {
        $request->validate([
            'csv_file' => 'required|file|max:10240|mimes:csv,txt,xls,xlsx'
        ], [
            'csv_file.required' => 'File harus dipilih',
            'csv_file.mimes' => 'File harus berformat CSV, XLS, atau XLSX',
            'csv_file.max' => 'Ukuran file maksimal 10MB'
        ]);

        $opd = Opd::findOrFail($opdId);
        $file = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Parse file based on extension
        if (in_array($extension, ['xls', 'xlsx'])) {
            $parsedData = $this->parseExcelFile($file);
        } else {
            $parsedData = $this->parseCsvFile($file);
        }

        if (empty($parsedData['data'])) {
            return back()->withErrors(['csv_file' => 'File kosong atau format tidak valid']);
        }

        // Validate data
        $validationResult = $this->validateImportData($parsedData['data'], $opdId);

        // Store data in session for import
        session(['import_preview_data' => $validationResult]);
        session(['import_opd_id' => $opdId]);

        return view('opds.import-preview', compact('opd', 'validationResult'));
    }

    /**
     * Process import dari data yang sudah di-preview (Bulk - deprecated, use API instead)
     */
    public function processImport(Request $request, $opdId)
    {
        // Redirect to show page, actual import is done via API
        return redirect()->route('admin.opds.show', $opdId);
    }

    /**
     * API: Import single row
     */
    public function importSingleRow(Request $request, $opdId)
    {
        $request->validate([
            'nip' => 'required|string',
            'nama' => 'required|string',
            'jabatan_id' => 'required|integer',
            'index' => 'required|integer'
        ]);

        $opd = Opd::findOrFail($opdId);
        
        $nip = $request->input('nip');
        $nama = $request->input('nama');
        $jabatanId = $request->input('jabatan_id');
        $index = $request->input('index');

        try {
            // Check if NIP already exists
            $existingAsn = Asn::where('nip', $nip)->first();
            
            if ($existingAsn) {
                // Skip - NIP already exists
                return response()->json([
                    'success' => true,
                    'status' => 'skipped',
                    'message' => 'NIP sudah ada di database',
                    'index' => $index,
                    'data' => [
                        'nip' => $nip,
                        'nama' => $nama,
                        'existing_nama' => $existingAsn->nama
                    ]
                ]);
            }

            // Create new ASN
            $asn = Asn::create([
                'nama' => $nama,
                'nip' => $nip,
                'jabatan_id' => $jabatanId,
                'opd_id' => $opdId
            ]);

            return response()->json([
                'success' => true,
                'status' => 'imported',
                'message' => 'Data berhasil diimport',
                'index' => $index,
                'data' => [
                    'id' => $asn->id,
                    'nip' => $nip,
                    'nama' => $nama
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'index' => $index,
                'data' => [
                    'nip' => $nip,
                    'nama' => $nama
                ]
            ], 500);
        }
    }

    /**
     * API: Clear import session
     */
    public function clearImportSession($opdId)
    {
        session()->forget(['import_preview_data', 'import_opd_id']);
        
        return response()->json([
            'success' => true,
            'message' => 'Session cleared'
        ]);
    }

    /**
     * Parse CSV file (auto-detect delimiter: comma or semicolon)
     */
    private function parseCsvFile($file)
    {
        $data = [];
        $header = null;
        $delimiter = ','; // Default delimiter

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Skip BOM if exists
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Read first line to detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Skip BOM again after rewind
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Auto-detect delimiter by counting occurrences
            $semicolonCount = substr_count($firstLine, ';');
            $commaCount = substr_count($firstLine, ',');
            $delimiter = ($semicolonCount > $commaCount) ? ';' : ',';

            $rowNumber = 0;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                
                // Skip empty rows
                if (count($row) === 1 && empty($row[0])) {
                    continue;
                }

                // First row is header
                if ($header === null) {
                    $header = array_map('trim', $row);
                    continue;
                }

                // Map row to associative array
                $rowData = [];
                foreach ($header as $index => $key) {
                    $rowData[strtolower($key)] = isset($row[$index]) ? trim($row[$index]) : '';
                }
                $rowData['row_number'] = $rowNumber;
                $data[] = $rowData;
            }
            fclose($handle);
        }

        return [
            'header' => $header,
            'data' => $data
        ];
    }

    /**
     * Parse Excel file (XLS, XLSX)
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
                    continue;
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
     * Categorize into: valid (new), existing (skip), invalid (error)
     */
    private function validateImportData($data, $opdId)
    {
        $valid = [];
        $invalid = [];
        $existing = [];
        $existingNips = [];

        // Get all jabatan IDs for this OPD
        $opd = Opd::findOrFail($opdId);
        $validJabatanIds = $opd->getAllJabatans()->pluck('id')->toArray();

        foreach ($data as $index => $row) {
            $errors = [];

            // Check required fields
            $nip = $row['nip'] ?? '';
            $nama = $row['nama'] ?? '';
            $jabatanId = $row['jabatan_id'] ?? '';

            // Skip completely empty rows
            if (empty($nip) && empty($nama)) {
                continue;
            }

            // Validate NIP
            if (empty($nip)) {
                $errors[] = 'NIP wajib diisi';
            } elseif (strlen($nip) < 10) {
                $errors[] = 'NIP terlalu pendek (minimal 10 karakter)';
            } elseif (in_array($nip, $existingNips)) {
                $errors[] = 'NIP duplikat dalam file';
            } else {
                $existingNips[] = $nip;
            }

            // Validate Nama
            if (empty($nama)) {
                $errors[] = 'Nama wajib diisi';
            }

            // Validate Jabatan ID
            if (empty($jabatanId)) {
                $errors[] = 'Jabatan ID tidak ditemukan';
            } elseif (!in_array((int)$jabatanId, $validJabatanIds)) {
                $errors[] = 'Jabatan tidak valid untuk OPD ini';
            }

            // Get jabatan name
            $jabatan = Jabatan::find($jabatanId);
            $jabatanNama = $jabatan ? $jabatan->nama : '-';

            // Check if NIP already exists in database
            $existingAsn = Asn::where('nip', $nip)->first();
            $existingInfo = null;
            
            if ($existingAsn) {
                $existingInfo = [
                    'nama' => $existingAsn->nama,
                    'jabatan' => $existingAsn->jabatan ? $existingAsn->jabatan->nama : '-',
                    'opd' => $existingAsn->opd ? $existingAsn->opd->nama : '-'
                ];
            }

            $rowResult = [
                'row_number' => $row['row_number'],
                'nip' => $nip,
                'nama' => $nama,
                'jabatan_id' => $jabatanId,
                'jabatan_nama' => $jabatanNama,
                'existing_info' => $existingInfo,
                'errors' => $errors
            ];

            // Categorize: invalid -> existing -> valid
            if (!empty($errors)) {
                $invalid[$index] = $rowResult;
            } elseif ($existingAsn !== null) {
                // NIP already exists in database - will be skipped
                $existing[$index] = $rowResult;
            } else {
                // New data - ready to import
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
            'invalid_count' => count($invalid),
            'skipped_count' => count($existing)
        ];
    }
}
