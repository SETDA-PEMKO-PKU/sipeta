@extends('admin.layouts.app')

@section('title', 'Backup Database')
@section('page-title', 'Backup Database')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Backup Database</h2>
                <p class="text-gray-600 mt-1">Buat dan kelola backup database aplikasi (khusus Super Admin)</p>
            </div>
            <!-- Tombol Buat Backup & Import -->
            <div class="flex gap-2">
                <button type="button"
                        onclick="document.getElementById('importModal').classList.remove('hidden')"
                        style="background-color:#f97316;color:#ffffff;"
                        class="inline-flex items-center gap-2 px-5 py-2.5 font-semibold rounded-lg shadow transition-colors hover:opacity-90">
                    <span class="iconify" data-icon="mdi:database-import" data-width="20" data-height="20"></span>
                    Import Database
                </button>
                <form action="{{ route('admin.backup.store') }}" method="POST" id="backupForm">
                    @csrf
                    <input type="hidden" name="download" value="1">
                    <button type="submit"
                            onclick="document.getElementById('backupForm').submit(); this.disabled=true; this.innerHTML='<span class=\'iconify inline\' data-icon=\'mdi:loading\' data-width=\'18\'></span> Memproses...';"
                            style="background-color:#2563eb;color:#ffffff;"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-semibold rounded-lg shadow transition-colors hover:opacity-90">
                        <span class="iconify" data-icon="mdi:database-export" data-width="20" data-height="20"></span>
                        Buat Backup Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="mb-4 flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <span class="iconify flex-shrink-0 mt-0.5" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <span class="iconify flex-shrink-0 mt-0.5" data-icon="mdi:alert-circle" data-width="20" data-height="20"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <span class="iconify flex-shrink-0 mt-0.5" data-icon="mdi:alert-circle" data-width="20" data-height="20"></span>
        <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <!-- Info Card -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <span class="iconify text-blue-600" data-icon="mdi:database" data-width="24" data-height="24"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Backup</p>
                <p class="text-2xl font-bold text-gray-900">{{ count($files) }}</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <span class="iconify text-green-600" data-icon="mdi:harddisk" data-width="24" data-height="24"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Ukuran</p>
                <p class="text-2xl font-bold text-gray-900">
                    @php
                        $total = collect($files)->sum('size');
                        echo $total >= 1048576
                            ? number_format($total / 1048576, 2) . ' MB'
                            : number_format($total / 1024, 2) . ' KB';
                    @endphp
                </p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                <span class="iconify text-purple-600" data-icon="mdi:clock-outline" data-width="24" data-height="24"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Backup Terakhir</p>
                <p class="text-sm font-semibold text-gray-900">
                    @if(count($files) > 0)
                        {{ $files[0]['created_at']->diffForHumans() }}
                    @else
                        Belum Ada
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Backup File Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <span class="iconify text-gray-500" data-icon="mdi:folder-multiple" data-width="20" data-height="20"></span>
                Daftar File Backup
            </h3>
        </div>

        @if(count($files) === 0)
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <span class="iconify text-gray-300 mb-4" data-icon="mdi:database-off" data-width="56" data-height="56"></span>
            <p class="text-gray-500 font-medium">Belum ada file backup</p>
            <p class="text-gray-400 text-sm mt-1">Klik tombol "Buat Backup Sekarang" untuk membuat backup pertama</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama File</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ukuran</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($files as $file)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="iconify text-green-500 flex-shrink-0" data-icon="mdi:file-document" data-width="20" data-height="20"></span>
                                <span class="font-mono text-xs text-gray-800 break-all">{{ $file['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                            @php
                                $size = $file['size'];
                                echo $size >= 1048576
                                    ? number_format($size / 1048576, 2) . ' MB'
                                    : number_format($size / 1024, 2) . ' KB';
                            @endphp
                        </td>
                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                            <div>
                                <span class="font-medium">{{ $file['created_at']->format('d M Y, H:i') }} WIB</span>
                                <span class="block text-xs text-gray-400">{{ $file['created_at']->diffForHumans() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Download -->
                                <a href="{{ route('admin.backup.download', $file['name']) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                   title="Download">
                                    <span class="iconify" data-icon="mdi:download" data-width="16" data-height="16"></span>
                                    Download
                                </a>

                                <!-- Hapus -->
                                <form action="{{ route('admin.backup.destroy', $file['name']) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus backup {{ $file['name'] }}? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                            title="Hapus">
                                        <span class="iconify" data-icon="mdi:delete" data-width="16" data-height="16"></span>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Warning Note -->
    <div class="mt-4 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
        <span class="iconify flex-shrink-0 mt-0.5" data-icon="mdi:information-outline" data-width="18" data-height="18"></span>
        <div>
            <strong>Catatan:</strong> File backup tersimpan di direktori <code class="bg-amber-100 px-1 rounded">storage/app/backups/</code>.
            Pastikan <code class="bg-amber-100 px-1 rounded">mysqldump</code> tersedia di server. Simpan file backup di tempat yang aman dan terjamin.
        </div>
    </div>
</div>

<!-- Modal Import Database -->
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('importModal').classList.add('hidden')"></div>

    <!-- Modal Panel -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 z-10 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                <span class="iconify text-orange-500" data-icon="mdi:database-import" data-width="20" data-height="20"></span>
                Import Database
            </h3>
            <button type="button"
                    onclick="document.getElementById('importModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="iconify" data-icon="mdi:close" data-width="20" data-height="20"></span>
            </button>
        </div>

        <!-- Body -->
        <form action="{{ route('admin.backup.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
            @csrf
            <div class="px-6 py-5 space-y-4 overflow-y-auto">
                <!-- Peringatan -->
                <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    <span class="iconify flex-shrink-0 mt-0.5" data-icon="mdi:alert" data-width="18" data-height="18"></span>
                    <div>
                        <strong>Peringatan:</strong> Import akan menimpa data database yang ada saat ini. Pastikan Anda sudah membuat backup terlebih dahulu sebelum melanjutkan.
                    </div>
                </div>

                <!-- Upload File -->
                <div>
                    <label for="sql_file" class="block text-sm font-medium text-gray-700 mb-1.5">
                        File SQL <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file"
                               id="sql_file"
                               name="sql_file"
                               accept=".sql,.txt"
                               required
                               onchange="updateFileName(this)"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Format: <code class="bg-gray-100 px-1 rounded">.sql</code> atau <code class="bg-gray-100 px-1 rounded">.txt</code>. Maksimal 3 MB.</p>
                </div>

                <!-- Konfirmasi -->
                <div class="flex items-start gap-2">
                    <input type="checkbox"
                           id="confirm_import"
                           required
                           class="mt-0.5 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <label for="confirm_import" class="text-sm text-gray-700">
                        Saya mengerti bahwa proses ini akan <strong>menimpa data yang ada</strong> dan tidak bisa dibatalkan.
                    </label>
                </div>
            </div>

        </form>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl flex-shrink-0">
            <button type="button"
                    onclick="document.getElementById('importModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <button type="submit"
                    form="importForm"
                    id="importSubmitBtn"
                    style="background-color:#f97316;color:#ffffff;"
                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-lg transition-colors hover:opacity-90">
                <span class="iconify" data-icon="mdi:database-import" data-width="16" data-height="16"></span>
                Import Sekarang
            </button>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    // Reset tombol submit saat file berubah
    const btn = document.getElementById('importSubmitBtn');
    btn.disabled = false;
    btn.innerHTML = '<span class="iconify" data-icon="mdi:database-import" data-width="16" data-height="16"></span> Import Sekarang';
}

document.getElementById('importForm').addEventListener('submit', function (e) {
    const btn = document.getElementById('importSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="iconify inline" data-icon="mdi:loading" data-width="16"></span> Memproses...';
});
</script>

@endsection
