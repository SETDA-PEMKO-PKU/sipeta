@extends('admin.layouts.app')

@section('title', 'Mutasi Pegawai')
@section('page-title', 'Mutasi Pegawai')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Mutasi Pegawai</h2>
            <p class="text-gray-600 mt-1">Kelola mutasi/perpindahan pegawai antar OPD</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.mutasi.export', request()->query()) }}" class="btn btn-secondary">
                <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                <span class="ml-2">Export CSV</span>
            </a>
            <a href="{{ route('admin.mutasi.create') }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:swap-horizontal" data-width="18" data-height="18"></span>
                <span class="ml-2">Buat Mutasi Baru</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Mutasi -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Total Mutasi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalMutasi }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-blue-600" data-icon="mdi:swap-horizontal" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mutasi Antar OPD -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Antar OPD</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $mutasiAntarOpd }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-green-600" data-icon="mdi:office-building-marker" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mutasi Internal -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Internal OPD</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $mutasiInternalOpd }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-purple-600" data-icon="mdi:account-switch" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mutasi Bulan Ini -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $mutasiBulanIni }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-yellow-600" data-icon="mdi:calendar-month" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-3 flex items-center gap-2 animate-fade-in">
            <span class="iconify" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-3 flex items-center gap-2 animate-fade-in">
            <span class="iconify" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Panel -->
    <div class="card mb-6">
        <div class="card-body">
            <form action="{{ route('admin.mutasi.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nama atau NIP..."
                               class="input w-full">
                    </div>

                    <!-- Filter OPD Asal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OPD Asal</label>
                        <select name="opd_asal_id" class="input w-full">
                            <option value="">Semua OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ request('opd_asal_id') == $opd->id ? 'selected' : '' }}>
                                    {{ $opd->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter OPD Tujuan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OPD Tujuan</label>
                        <select name="opd_tujuan_id" class="input w-full">
                            <option value="">Semua OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ request('opd_tujuan_id') == $opd->id ? 'selected' : '' }}>
                                    {{ $opd->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Jenis Mutasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Mutasi</label>
                        <select name="jenis_mutasi" class="input w-full">
                            <option value="">Semua Jenis</option>
                            <option value="antar_opd" {{ request('jenis_mutasi') == 'antar_opd' ? 'selected' : '' }}>Antar OPD</option>
                            <option value="internal_opd" {{ request('jenis_mutasi') == 'internal_opd' ? 'selected' : '' }}>Internal OPD</option>
                        </select>
                    </div>

                    <!-- Filter Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mutasi</label>
                        <div class="flex gap-2">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                   class="input w-full text-sm">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                   class="input w-full text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary inline-flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:magnify" data-width="18" data-height="18"></span>
                        Filter
                    </button>
                    <a href="{{ route('admin.mutasi.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">OPD Asal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">OPD Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($mutasis as $mutasi)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $mutasi->tanggal_mutasi->format('d/m/Y') }}</div>
                                @if($mutasi->nomor_sk)
                                    <div class="text-xs text-gray-500">SK: {{ $mutasi->nomor_sk }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $mutasi->asn->nama ?? '-' }}</div>
                                <div class="text-xs text-gray-500">NIP: {{ $mutasi->asn->nip ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $mutasi->opdAsal->nama ?? '-' }}</div>
                                @if($mutasi->jabatanAsal)
                                    <div class="text-xs text-gray-500">{{ $mutasi->jabatanAsal->nama }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $mutasi->opdTujuan->nama ?? '-' }}</div>
                                @if($mutasi->jabatanTujuan)
                                    <div class="text-xs text-gray-500">{{ $mutasi->jabatanTujuan->nama }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($mutasi->jenis_mutasi === 'antar_opd')
                                    <span class="badge badge-success badge-sm">Antar OPD</span>
                                @else
                                    <span class="badge badge-info badge-sm">Internal OPD</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $mutasi->createdBy->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $mutasi->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('admin.mutasi.show', $mutasi) }}" 
                                   class="btn btn-sm btn-outline" title="Lihat Detail">
                                    <span class="iconify" data-icon="mdi:eye" data-width="16" data-height="16"></span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <span class="iconify mx-auto mb-2" data-icon="mdi:swap-horizontal" data-width="48" data-height="48"></span>
                                <p class="text-lg font-medium">Belum ada data mutasi</p>
                                <p class="text-sm">Klik tombol "Buat Mutasi Baru" untuk menambah data</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($mutasis->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $mutasis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
