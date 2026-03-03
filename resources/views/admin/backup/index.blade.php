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
            <!-- Tombol Buat Backup -->
            <div class="flex gap-2">
                <form action="{{ route('admin.backup.store') }}" method="POST" id="backupForm">
                    @csrf
                    <input type="hidden" name="download" value="1">
                    <button type="submit"
                            onclick="document.getElementById('backupForm').submit(); this.disabled=true; this.innerHTML='<span class=\'iconify inline\' data-icon=\'mdi:loading\' data-width=\'18\'></span> Memproses...';"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow transition-colors">
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
@endsection
