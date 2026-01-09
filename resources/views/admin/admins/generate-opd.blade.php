@extends('admin.layouts.app')

@section('title', 'Generate Admin OPD')
@section('page-title', 'Generate Admin OPD')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ route('admin.admins.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="24" data-height="24"></span>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Generate Admin OPD</h2>
        </div>
        <p class="text-gray-600">Generate akun admin untuk setiap OPD yang belum memiliki admin. Setiap OPD akan mendapatkan 1 akun admin.</p>
    </div>

    <!-- Info Card -->
    <div class="card mb-6">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:information" data-width="24" data-height="24"></span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Informasi</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Setiap OPD akan mendapatkan 1 akun admin dengan role <strong>Admin OPD</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Email akan digenerate dari nama OPD (contoh: <strong>Dinas Kesehatan</strong> → <strong>dinaskesehatan@pku.go.id</strong>)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Password akan digenerate secara otomatis (8 karakter random)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Hasil akhir akan berupa file Excel berisi email dan password masing-masing admin</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-orange-500 mt-0.5" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                            <span class="text-orange-600">OPD yang sudah memiliki admin akan dilewati</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total OPD</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalOpd }}</p>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-green-600" data-icon="mdi:account-check" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">OPD Sudah Punya Admin</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $opdWithAdmin }}</p>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-orange-600" data-icon="mdi:account-plus" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">OPD Belum Punya Admin</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $opdWithoutAdmin }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Table -->
    @if($opdWithoutAdmin > 0)
    <div class="card mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Preview Admin yang Akan Digenerate</h3>
            <p class="text-sm text-gray-500 mt-1">Berikut daftar OPD yang belum memiliki admin dan email yang akan digenerate</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OPD</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($previewData as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item['opd_nama'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['admin_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <code class="px-2 py-1 bg-gray-100 text-gray-800 rounded">{{ $item['email'] }}</code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-4">
        <form action="{{ route('admin.admins.generate-opd.process') }}" method="POST" 
              onsubmit="return confirm('Apakah Anda yakin ingin generate {{ $opdWithoutAdmin }} akun admin OPD? Proses ini akan membuat akun baru dan mengunduh file Excel.')">
            @csrf
            <button type="submit" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                <span class="ml-2">Generate & Download Excel</span>
            </button>
        </form>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline">
            <span class="iconify" data-icon="mdi:close" data-width="18" data-height="18"></span>
            <span class="ml-2">Batal</span>
        </a>
    </div>
    @else
    <div class="card">
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="iconify text-green-600" data-icon="mdi:check-all" data-width="32" data-height="32"></span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Semua OPD Sudah Memiliki Admin</h3>
            <p class="text-gray-600 mb-6">Tidak ada OPD yang perlu digenerate akun admin baru.</p>
            <a href="{{ route('admin.admins.index') }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                <span class="ml-2">Kembali ke Daftar Admin</span>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
