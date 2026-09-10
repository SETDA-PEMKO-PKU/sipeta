<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs listing.
     * Only accessible by Super Admin.
     */
    public function index(Request $request)
    {
        // Double check super admin access
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $query = AdminActivityLog::with('admin')
            ->orderBy('created_at', 'desc');

        // Filter by admin
        if ($request->filled('admin_id')) {
            $query->byAdmin($request->admin_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange(
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            );
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('admin', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get all admins for filter dropdown
        $admins = Admin::orderBy('name')->get();

        // Get unique actions for filter
        $actions = [
            AdminActivityLog::ACTION_LOGIN => 'Login',
            AdminActivityLog::ACTION_LOGOUT => 'Logout',
            AdminActivityLog::ACTION_CREATE => 'Tambah Data',
            AdminActivityLog::ACTION_UPDATE => 'Edit Data',
            AdminActivityLog::ACTION_DELETE => 'Hapus Data',
            AdminActivityLog::ACTION_VIEW => 'Lihat Data',
            AdminActivityLog::ACTION_EXPORT => 'Export Data',
            AdminActivityLog::ACTION_IMPORT => 'Import Data',
            AdminActivityLog::ACTION_MUTASI => 'Mutasi Pegawai',
        ];

        // Get unique modules for filter
        $modules = [
            AdminActivityLog::MODULE_AUTH => 'Autentikasi',
            AdminActivityLog::MODULE_OPD => 'OPD',
            AdminActivityLog::MODULE_JABATAN => 'Jabatan',
            AdminActivityLog::MODULE_PEGAWAI => 'Pegawai',
            AdminActivityLog::MODULE_ASN => 'ASN',
            AdminActivityLog::MODULE_ADMIN => 'Admin',
            AdminActivityLog::MODULE_ANALYTICS => 'Analytics',
            AdminActivityLog::MODULE_MUTASI => 'Mutasi Pegawai',
        ];

        return view('admin.activity-logs.index', compact('logs', 'admins', 'actions', 'modules'));
    }

    /**
     * Show detail of a specific activity log.
     */
    public function show(AdminActivityLog $activityLog)
    {
        // Double check super admin access
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $activityLog->load('admin');

        return view('admin.activity-logs.show', compact('activityLog'));
    }

    /**
     * Export activity logs to CSV.
     */
    public function export(Request $request)
    {
        // Double check super admin access
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $query = AdminActivityLog::with('admin')
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('admin_id')) {
            $query->byAdmin($request->admin_id);
        }
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange(
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            );
        }

        $logs = $query->get();

        // Log the export activity
        AdminActivityLog::log(
            AdminActivityLog::ACTION_EXPORT,
            AdminActivityLog::MODULE_ADMIN,
            'Export log aktivitas admin'
        );

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'Waktu',
                'Admin',
                'Email',
                'Role',
                'Aksi',
                'Modul',
                'Deskripsi',
                'IP Address',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->admin->name ?? '-',
                    $log->admin->email ?? '-',
                    $log->admin->role ?? '-',
                    $log->action_label,
                    $log->module_label,
                    $log->description ?? '-',
                    $log->ip_address ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get activity statistics for dashboard widget.
     */
    public function stats()
    {
        // Double check super admin access
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7)->startOfDay();

        return response()->json([
            'today_count' => AdminActivityLog::where('created_at', '>=', $today)->count(),
            'week_count' => AdminActivityLog::where('created_at', '>=', $weekAgo)->count(),
            'login_count_today' => AdminActivityLog::where('created_at', '>=', $today)
                ->where('action', AdminActivityLog::ACTION_LOGIN)
                ->count(),
            'unique_admins_today' => AdminActivityLog::where('created_at', '>=', $today)
                ->distinct('admin_id')
                ->count('admin_id'),
        ]);
    }
}
