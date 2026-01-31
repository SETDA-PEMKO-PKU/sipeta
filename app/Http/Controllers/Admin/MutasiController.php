<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Asn;
use App\Models\Jabatan;
use App\Models\MutasiPegawai;
use App\Models\Opd;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutasiController extends Controller
{
    use LogsAdminActivity;

    /**
     * Apply middleware untuk permission check
     */
    public function __construct()
    {
        // Only super_admin and admin_bkpsdm can access mutasi
        $this->middleware('admin.permission:mutasi_asn');
    }

    /**
     * Menampilkan daftar mutasi pegawai
     */
    public function index(Request $request)
    {
        $query = MutasiPegawai::with([
            'asn', 
            'opdAsal', 
            'opdTujuan', 
            'jabatanAsal', 
            'jabatanTujuan', 
            'createdBy'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan OPD asal
        if ($request->filled('opd_asal_id')) {
            $query->byOpdAsal($request->opd_asal_id);
        }

        // Filter berdasarkan OPD tujuan
        if ($request->filled('opd_tujuan_id')) {
            $query->byOpdTujuan($request->opd_tujuan_id);
        }

        // Filter berdasarkan jenis mutasi
        if ($request->filled('jenis_mutasi')) {
            $query->byJenis($request->jenis_mutasi);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Search by ASN name or NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asn', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Per page options
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $mutasis = $query->paginate($perPage)->withQueryString();

        // Data untuk filter dropdown
        $opds = Opd::orderBy('nama')->get();

        // Statistik
        $totalMutasi = MutasiPegawai::count();
        $mutasiAntarOpd = MutasiPegawai::byJenis(MutasiPegawai::JENIS_ANTAR_OPD)->count();
        $mutasiInternalOpd = MutasiPegawai::byJenis(MutasiPegawai::JENIS_INTERNAL_OPD)->count();
        $mutasiBulanIni = MutasiPegawai::whereMonth('tanggal_mutasi', now()->month)
                                        ->whereYear('tanggal_mutasi', now()->year)
                                        ->count();

        return view('admin.mutasi.index', compact(
            'mutasis',
            'opds',
            'totalMutasi',
            'mutasiAntarOpd',
            'mutasiInternalOpd',
            'mutasiBulanIni'
        ));
    }

    /**
     * Menampilkan form tambah mutasi
     */
    public function create(Request $request)
    {
        $opds = Opd::with(['jabatanKepala.children.children'])->orderBy('nama')->get();
        
        // Pre-select ASN if provided
        $selectedAsn = null;
        if ($request->filled('asn_id')) {
            $selectedAsn = Asn::with(['opd', 'jabatan'])->find($request->asn_id);
        }

        return view('admin.mutasi.create', compact('opds', 'selectedAsn'));
    }

    /**
     * Menyimpan mutasi baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'asn_id' => 'required|exists:asns,id',
            'opd_tujuan_id' => 'required|exists:opds,id',
            'jabatan_tujuan_id' => 'nullable|exists:jabatans,id',
            'tanggal_mutasi' => 'required|date',
            'nomor_sk' => 'nullable|string|max:100',
            'tanggal_sk' => 'nullable|date',
            'keterangan' => 'nullable|string|max:500',
            'jenis_mutasi' => 'required|in:antar_opd,internal_opd',
        ]);

        $asn = Asn::with(['opd', 'jabatan'])->findOrFail($request->asn_id);
        
        // Validate jenis mutasi
        if ($request->jenis_mutasi === MutasiPegawai::JENIS_ANTAR_OPD && $asn->opd_id == $request->opd_tujuan_id) {
            return back()->withErrors([
                'opd_tujuan_id' => 'Untuk mutasi antar OPD, OPD tujuan harus berbeda dengan OPD asal'
            ])->withInput();
        }

        if ($request->jenis_mutasi === MutasiPegawai::JENIS_INTERNAL_OPD && $asn->opd_id != $request->opd_tujuan_id) {
            return back()->withErrors([
                'opd_tujuan_id' => 'Untuk mutasi internal OPD, OPD tujuan harus sama dengan OPD asal'
            ])->withInput();
        }

        // Validate jabatan tujuan belongs to opd tujuan
        if ($request->filled('jabatan_tujuan_id')) {
            $jabatanTujuan = Jabatan::findOrFail($request->jabatan_tujuan_id);
            if ($jabatanTujuan->opd_id != $request->opd_tujuan_id) {
                return back()->withErrors([
                    'jabatan_tujuan_id' => 'Jabatan tujuan harus berada dalam OPD tujuan'
                ])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Simpan data mutasi
            $mutasi = MutasiPegawai::create([
                'asn_id' => $asn->id,
                'opd_asal_id' => $asn->opd_id,
                'opd_tujuan_id' => $request->opd_tujuan_id,
                'jabatan_asal_id' => $asn->jabatan_id,
                'jabatan_tujuan_id' => $request->jabatan_tujuan_id,
                'tanggal_mutasi' => $request->tanggal_mutasi,
                'nomor_sk' => $request->nomor_sk,
                'tanggal_sk' => $request->tanggal_sk,
                'keterangan' => $request->keterangan,
                'jenis_mutasi' => $request->jenis_mutasi,
                'created_by' => auth('admin')->id(),
            ]);

            // Update ASN data
            $oldAsnData = $asn->toArray();
            $asn->update([
                'opd_id' => $request->opd_tujuan_id,
                'jabatan_id' => $request->jabatan_tujuan_id,
            ]);

            // Log activity
            $this->logMutasi($mutasi, $asn, $oldAsnData);

            DB::commit();

            return redirect()->route('admin.mutasi.index')
                            ->with('success', 'Mutasi pegawai "' . $asn->nama . '" berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menampilkan detail mutasi
     */
    public function show(MutasiPegawai $mutasi)
    {
        $mutasi->load([
            'asn', 
            'opdAsal', 
            'opdTujuan', 
            'jabatanAsal', 
            'jabatanTujuan', 
            'createdBy'
        ]);

        return view('admin.mutasi.show', compact('mutasi'));
    }

    /**
     * API endpoint untuk mendapatkan ASN berdasarkan pencarian
     */
    public function searchAsn(Request $request)
    {
        $search = $request->get('q', '');
        
        $asns = Asn::with(['opd', 'jabatan'])
            ->where(function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->limit(20)
            ->get()
            ->map(function ($asn) {
                return [
                    'id' => $asn->id,
                    'nama' => $asn->nama,
                    'nip' => $asn->nip,
                    'opd_id' => $asn->opd_id,
                    'opd_nama' => $asn->opd->nama ?? '-',
                    'jabatan_id' => $asn->jabatan_id,
                    'jabatan_nama' => $asn->jabatan->nama ?? '-',
                ];
            });

        return response()->json($asns);
    }

    /**
     * API endpoint untuk mendapatkan jabatan berdasarkan OPD
     */
    public function getJabatanByOpd($opdId)
    {
        $opd = Opd::with(['jabatanKepala.children.children.children'])->findOrFail($opdId);

        $jabatans = [];

        // Recursive function untuk mendapatkan semua jabatan
        $addJabatan = function($jabatan, $level = 0) use (&$jabatans, &$addJabatan) {
            $prefix = str_repeat('— ', $level);

            $jabatans[] = [
                'id' => $jabatan->id,
                'nama' => $prefix . $jabatan->nama,
                'type' => $jabatan->isRoot() ? 'kepala' : 'sub',
                'parent_id' => $jabatan->parent_id,
                'level' => $level
            ];

            // Rekursif untuk children
            foreach ($jabatan->children as $child) {
                $addJabatan($child, $level + 1);
            }
        };

        // Proses semua jabatan kepala
        foreach ($opd->jabatanKepala as $jabatan) {
            $addJabatan($jabatan);
        }

        return response()->json($jabatans);
    }

    /**
     * Mendapatkan riwayat mutasi ASN
     */
    public function riwayatAsn($asnId)
    {
        $asn = Asn::with(['opd', 'jabatan'])->findOrFail($asnId);
        
        $riwayat = MutasiPegawai::with([
            'opdAsal', 
            'opdTujuan', 
            'jabatanAsal', 
            'jabatanTujuan', 
            'createdBy'
        ])
            ->byAsn($asnId)
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();

        return view('admin.mutasi.riwayat', compact('asn', 'riwayat'));
    }

    /**
     * Log mutasi activity
     */
    protected function logMutasi(MutasiPegawai $mutasi, Asn $asn, array $oldAsnData): void
    {
        $opdAsal = Opd::find($mutasi->opd_asal_id);
        $opdTujuan = Opd::find($mutasi->opd_tujuan_id);
        
        $description = sprintf(
            "Mutasi %s dari %s ke %s (Tanggal: %s)",
            $asn->nama,
            $opdAsal->nama ?? '-',
            $opdTujuan->nama ?? '-',
            $mutasi->tanggal_mutasi->format('d/m/Y')
        );

        AdminActivityLog::log(
            AdminActivityLog::ACTION_MUTASI,
            AdminActivityLog::MODULE_MUTASI,
            $description,
            $mutasi,
            $oldAsnData,
            $asn->fresh()->toArray()
        );
    }

    /**
     * Export data mutasi ke CSV
     */
    public function export(Request $request)
    {
        $query = MutasiPegawai::with([
            'asn', 
            'opdAsal', 
            'opdTujuan', 
            'jabatanAsal', 
            'jabatanTujuan', 
            'createdBy'
        ])->orderBy('tanggal_mutasi', 'desc');

        // Apply same filters as index
        if ($request->filled('opd_asal_id')) {
            $query->byOpdAsal($request->opd_asal_id);
        }
        if ($request->filled('opd_tujuan_id')) {
            $query->byOpdTujuan($request->opd_tujuan_id);
        }
        if ($request->filled('jenis_mutasi')) {
            $query->byJenis($request->jenis_mutasi);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $mutasis = $query->get();

        // Log the export activity
        AdminActivityLog::log(
            AdminActivityLog::ACTION_EXPORT,
            AdminActivityLog::MODULE_MUTASI,
            'Export data mutasi pegawai'
        );

        $filename = 'mutasi_pegawai_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($mutasis) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'No',
                'Tanggal Mutasi',
                'NIP',
                'Nama Pegawai',
                'OPD Asal',
                'Jabatan Asal',
                'OPD Tujuan',
                'Jabatan Tujuan',
                'Jenis Mutasi',
                'Nomor SK',
                'Tanggal SK',
                'Keterangan',
                'Dibuat Oleh',
                'Tanggal Input',
            ]);

            $no = 1;
            foreach ($mutasis as $mutasi) {
                fputcsv($file, [
                    $no++,
                    $mutasi->tanggal_mutasi->format('d/m/Y'),
                    $mutasi->asn->nip ?? '-',
                    $mutasi->asn->nama ?? '-',
                    $mutasi->opdAsal->nama ?? '-',
                    $mutasi->jabatanAsal->nama ?? '-',
                    $mutasi->opdTujuan->nama ?? '-',
                    $mutasi->jabatanTujuan->nama ?? '-',
                    $mutasi->jenis_mutasi_label,
                    $mutasi->nomor_sk ?? '-',
                    $mutasi->tanggal_sk ? $mutasi->tanggal_sk->format('d/m/Y') : '-',
                    $mutasi->keterangan ?? '-',
                    $mutasi->createdBy->name ?? '-',
                    $mutasi->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
