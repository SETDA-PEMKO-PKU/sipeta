@extends('admin.layouts.app')

@section('title', 'Manajemen Jabatan')
@section('page-title', 'Manajemen Jabatan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    @if(auth('admin')->user()->isAdminOpd() && auth('admin')->user()->opd)
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <span class="iconify" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
        <span class="font-medium text-gray-900">{{ auth('admin')->user()->opd->nama }}</span>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span>Daftar Jabatan</span>
    </nav>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Jabatan</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.jabatan.import.form') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center gap-2">
                <span class="iconify" data-icon="mdi:file-upload" data-width="18" data-height="18"></span>
                Import Excel
            </a>
            <a href="{{ route('admin.jabatan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Tambah Jabatan
            </a>
        </div>
    </div>

    <!-- Search & Filters (single form) -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.jabatan.index') }}">
            {{-- Search row --}}
            <div class="flex gap-2 mb-4">
                <div class="relative flex-1 min-w-0">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <span class="iconify text-gray-400" data-icon="mdi:magnify" data-width="20" data-height="20"></span>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama jabatan..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <button type="submit" class="shrink-0 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg">Cari</button>
                @if(request()->hasAny(['search', 'opd_id', 'jenis_jabatan', 'kelas']))
                <a href="{{ route('admin.jabatan.index') }}" class="shrink-0 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">Reset</a>
                @endif
            </div>

            {{-- Filter row --}}
            <div class="grid grid-cols-1 md:grid-cols-{{ auth('admin')->user()->isAdminOpd() ? '3' : '4' }} gap-4">
                @if(!auth('admin')->user()->isAdminOpd())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">OPD</label>
                    <select name="opd_id" class="w-full rounded border-gray-300">
                        <option value="">Semua OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ request('opd_id') == $opd->id ? 'selected' : '' }}>
                                {{ $opd->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <!-- Admin OPD: Show OPD name as info -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">OPD Anda</label>
                    <div class="w-full rounded border border-blue-200 bg-blue-50 px-3 py-2 flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
                        <span class="text-sm font-medium text-blue-900">{{ auth('admin')->user()->opd->nama }}</span>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Jabatan</label>
                    <select name="jenis_jabatan" class="w-full rounded border-gray-300">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisJabatans as $jenis)
                            <option value="{{ $jenis }}" {{ request('jenis_jabatan') == $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                    <select name="kelas" class="w-full rounded border-gray-300">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasJabatans as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                Kelas {{ $kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded w-full">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Jabatan Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Jabatan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bagian/Bidang</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kelas</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kebutuhan</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Bezetting</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($jabatans as $jabatan)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $jabatan->nama }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate" title="{{ $jabatan->opdLangsung ? $jabatan->opdLangsung->nama : ($jabatan->parent ? $jabatan->parent->opdLangsung->nama ?? '-' : '-') }}">
                            {{ $jabatan->opdLangsung ? $jabatan->opdLangsung->nama : ($jabatan->parent ? $jabatan->parent->opdLangsung->nama ?? '-' : '-') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate" title="{{ $jabatan->parent ? $jabatan->parent->nama : '-' }}">
                            {{ $jabatan->parent ? $jabatan->parent->nama : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $jabatan->jenis_jabatan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">
                            {{ $jabatan->kelas ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">
                            {{ $jabatan->kebutuhan ?? 0 }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">
                            {{ $jabatan->asns->count() }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                            <a href="{{ route('admin.jabatan.show', $jabatan->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                            Tidak ada data jabatan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $jabatans->links() }}
    </div>
</div>
@endsection
