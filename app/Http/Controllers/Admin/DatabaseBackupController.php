<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    private string $backupDisk = 'local';
    private string $backupDir  = 'backups';

    /**
     * Daftar semua file backup yang tersedia.
     */
    public function index(): \Illuminate\View\View
    {
        $files = [];

        if (Storage::disk($this->backupDisk)->exists($this->backupDir)) {
            $rawFiles = Storage::disk($this->backupDisk)->files($this->backupDir);

            foreach ($rawFiles as $file) {
                if (str_ends_with($file, '.sql') || str_ends_with($file, '.sql.gz')) {
                    $files[] = [
                        'name'       => basename($file),
                        'path'       => $file,
                        'size'       => Storage::disk($this->backupDisk)->size($file),
                        'created_at' => Carbon::createFromTimestamp(
                            Storage::disk($this->backupDisk)->lastModified($file)
                        ),
                    ];
                }
            }

            usort($files, fn($a, $b) => $b['created_at']->timestamp - $a['created_at']->timestamp);
        }

        return view('admin.backup.index', compact('files'));
    }

    /**
     * Jalankan mysqldump dan simpan ke storage, lalu download langsung.
     */
    public function store(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $socket   = config('database.connections.mysql.unix_socket', '');

        if (empty($database)) {
            return back()->with('error', 'Konfigurasi database tidak ditemukan.');
        }

        $filename  = 'backup_' . $database . '_' . Carbon::now()->format('Y-m-d_His') . '.sql';
        $storePath = storage_path('app/' . $this->backupDir . '/' . $filename);

        // Pastikan direktori backup ada
        if (!file_exists(storage_path('app/' . $this->backupDir))) {
            mkdir(storage_path('app/' . $this->backupDir), 0755, true);
        }

        // Bangun perintah mysqldump
        $socketOption = '';
        if (!empty($socket)) {
            $socketOption = '--socket=' . escapeshellarg($socket);
        }

        $dumpCommand = sprintf(
            'mysqldump --single-transaction --routines --triggers ' .
            '--host=%s --port=%s --user=%s %s %s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            empty($password) ? '' : '--password=' . escapeshellarg($password),
            $socketOption,
            escapeshellarg($database),
            escapeshellarg($storePath)
        );

        exec($dumpCommand, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($storePath) || filesize($storePath) === 0) {
            $errDetail = implode(' ', $output);
            // Hapus file kosong jika ada
            if (file_exists($storePath)) {
                unlink($storePath);
            }
            return back()->with('error', 'Backup gagal. Pastikan mysqldump terinstall. Detail: ' . $errDetail);
        }

        // Log aktivitas
        AdminActivityLog::log(
            'backup',
            'database_backup',
            "Membuat backup database: {$filename}",
            null,
            null,
            ['filename' => $filename, 'size' => filesize($storePath)]
        );

        // Tawarkan download langsung
        if ($request->boolean('download', true)) {
            $fileContent = file_get_contents($storePath);
            $fileSize    = filesize($storePath);

            return response()->streamDownload(function () use ($fileContent) {
                echo $fileContent;
            }, $filename, [
                'Content-Type'        => 'application/octet-stream',
                'Content-Length'      => $fileSize,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return redirect()->route('admin.backup.index')
            ->with('success', "Backup berhasil dibuat: {$filename}");
    }

    /**
     * Download file backup yang sudah ada.
     */
    public function download(string $filename): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $path = $this->backupDir . '/' . $filename;

        if (!Storage::disk($this->backupDisk)->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        AdminActivityLog::log(
            'backup',
            'database_backup',
            "Download backup database: {$filename}",
            null,
            null,
            ['filename' => $filename]
        );

        return Storage::disk($this->backupDisk)->download($path, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Hapus file backup.
     */
    public function destroy(string $filename): \Illuminate\Http\RedirectResponse
    {
        $path = $this->backupDir . '/' . $filename;

        if (!Storage::disk($this->backupDisk)->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk($this->backupDisk)->delete($path);

        AdminActivityLog::log(
            'backup',
            'database_backup',
            "Menghapus backup database: {$filename}",
            null,
            ['filename' => $filename],
            null
        );

        return redirect()->route('admin.backup.index')
            ->with('success', "Backup {$filename} berhasil dihapus.");
    }
}
