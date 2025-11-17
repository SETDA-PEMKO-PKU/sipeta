@extends('admin.layouts.app')

@section('title', 'Daftar OPD')
@section('page-title', 'Data OPD')

@section('content')
<div class="p-4 lg:p-8" x-data="opdIndex()">
    <!-- Header Actions -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Daftar OPD</h2>
            <p class="text-gray-600 mt-1">Kelola Organisasi Perangkat Daerah</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="btn btn-outline">
                <span class="iconify" data-icon="mdi:download" data-width="16" data-height="16"></span>
                <span class="ml-2">Export Semua</span>
            </button>
            <button class="btn btn-primary">
                <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                <span class="ml-2">Tambah OPD</span>
            </button>
        </div>
    </div>

    <div>
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

        @if($opds->count() > 0)
            <!-- Compact Search & Filter Bar -->
            <div class="card mb-3 animate-slide-up">
                <div class="p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 relative">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" data-icon="mdi:magnify" data-width="16" data-height="16"></span>
                            <input
                                type="text"
                                x-model="searchQuery"
                                @input.debounce.300ms="performSearch()"
                                placeholder="Cari nama OPD atau ID..."
                                class="input pl-9 pr-9 w-full text-sm"
                            >
                            <button
                                x-show="searchQuery"
                                @click="clearSearch()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="iconify" data-icon="mdi:close" data-width="16" data-height="16"></span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-gray-500">Urut:</span>
                            <button @click="sortBy = 'name'; performSort()"
                                    :class="sortBy === 'name' ? 'text-primary-600 font-semibold' : 'text-gray-600'"
                                    class="hover:text-primary-600">
                                <span class="iconify" data-icon="mdi:sort-alphabetical-ascending" data-width="16" data-height="16"></span>
                            </button>
                            <button @click="sortBy = 'id'; performSort()"
                                    :class="sortBy === 'id' ? 'text-primary-600 font-semibold' : 'text-gray-600'"
                                    class="hover:text-primary-600">
                                <span class="iconify" data-icon="mdi:sort-numeric-ascending" data-width="16" data-height="16"></span>
                            </button>
                        </div>
                        <div class="text-xs text-gray-500" x-text="searchResults + ' OPD'"></div>
                    </div>
                </div>
            </div>

            <!-- Compact Table List -->
            <div class="card animate-fade-in overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-16">ID</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama OPD</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">Bagian</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">Jabatan</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">ASN</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($opds->sortBy('nama') as $opd)
                            <tr class="hover:bg-gray-50 transition-colors duration-150 opd-row"
                                data-opd-id="{{ $opd->id }}"
                                data-opd-name="{{ strtolower($opd->nama) }}">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="badge badge-gray text-xs">{{ $opd->id }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded flex items-center justify-center text-white">
                                            <span class="iconify" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">{{ $opd->nama }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-6 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                        {{ $opd->bagians->count() }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-6 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                        {{ $opd->jabatanKepala->count() + $opd->bagians->sum(function($bagian) { return $bagian->jabatans->count(); }) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-6 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold">
                                        {{ $opd->bagians->sum(function($bagian) { return $bagian->jabatans->sum(function($jabatan) { return $jabatan->asns->count(); }); }) + $opd->jabatanKepala->sum(function($jabatan) { return $jabatan->asns->count(); }) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.opds.show', $opd->id) }}"
                                           class="inline-flex items-center px-2 py-1 bg-primary-600 text-white rounded hover:bg-primary-700 text-xs"
                                           title="Lihat Detail">
                                            <span class="iconify" data-icon="mdi:eye" data-width="14" data-height="14"></span>
                                        </a>
                                        <a href="{{ route('admin.api.opds.tree', $opd->id) }}"
                                           class="inline-flex items-center px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs"
                                           target="_blank"
                                           title="Export JSON">
                                            <span class="iconify" data-icon="mdi:download" data-width="14" data-height="14"></span>
                                        </a>
                                        <button class="inline-flex items-center px-2 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-xs"
                                                title="Edit">
                                            <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                                        </button>
                                        <button class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs"
                                                title="Hapus">
                                            <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- No Results -->
                <div x-show="searchResults === 0 && searchQuery"
                     class="p-8 text-center">
                    <span class="iconify text-gray-300" data-icon="mdi:magnify" data-width="48" data-height="48"></span>
                    <p class="text-sm text-gray-500 mt-2">Tidak ada hasil untuk "<span x-text="searchQuery"></span>"</p>
                </div>
            </div>

            <!-- Compact Summary Footer -->
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-4">
                    <span>Total: <strong class="text-gray-900">{{ $opds->count() }}</strong> OPD</span>
                    <span>•</span>
                    <span><strong class="text-gray-900">{{ $opds->sum(function($opd) { return $opd->bagians->count(); }) }}</strong> Bagian</span>
                    <span>•</span>
                    <span><strong class="text-gray-900">{{ $opds->sum(function($opd) { return $opd->jabatanKepala->count() + $opd->bagians->sum(function($bagian) { return $bagian->jabatans->count(); }); }) }}</strong> Jabatan</span>
                </div>
            </div>

        @else
            <!-- Empty State -->
            <div class="card p-12 text-center animate-fade-in">
                <span class="iconify text-gray-300" data-icon="mdi:office-building" data-width="64" data-height="64"></span>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Belum Ada Data OPD</h3>
                <p class="text-sm text-gray-500 mb-4">Sistem belum memiliki data Organisasi Perangkat Daerah</p>
                <button class="btn btn-primary">
                    <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                    <span class="ml-2">Tambah OPD Pertama</span>
                </button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function opdIndex() {
    return {
        searchQuery: '',
        searchResults: {{ $opds->count() }},
        totalOpds: {{ $opds->count() }},
        sortBy: 'name',

        performSearch() {
            const rows = document.querySelectorAll('.opd-row');
            let count = 0;

            rows.forEach(row => {
                const name = row.dataset.opdName;
                const id = row.dataset.opdId;
                const query = this.searchQuery.toLowerCase();

                if (name.includes(query) || id.includes(query)) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });

            this.searchResults = count;
        },

        clearSearch() {
            this.searchQuery = '';
            this.searchResults = this.totalOpds;
            document.querySelectorAll('.opd-row').forEach(row => {
                row.style.display = '';
            });
        },

        performSort() {
            const tbody = document.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('.opd-row'));

            rows.sort((a, b) => {
                if (this.sortBy === 'name') {
                    return a.dataset.opdName.localeCompare(b.dataset.opdName);
                } else {
                    return parseInt(a.dataset.opdId) - parseInt(b.dataset.opdId);
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        }
    }
}
</script>
@endpush
@endsection
