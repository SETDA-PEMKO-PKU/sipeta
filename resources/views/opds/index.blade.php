@extends('admin.layouts.app')

@section('title', 'Daftar OPD')
@section('page-title', 'Data OPD')

@section('content')
<div class="p-4 lg:p-8" x-data="opdIndex()">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Daftar OPD</h2>
            <p class="text-gray-600 mt-1">Kelola Organisasi Perangkat Daerah</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth('admin')->user()->canManageOpdJabatan())
            <button @click="$dispatch('open-modal', 'add-opd')" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                <span class="ml-2">Tambah OPD</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <!-- Total OPD -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Total OPD</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $opds->count() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Bagian -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Total Jabatan</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $opds->sum(function($opd) { return $opd->getAllJabatans()->count(); }) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-green-600" data-icon="mdi:folder-multiple" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Jabatan -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Total ASN</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $opds->sum(function($opd) { return $opd->asns->count(); }) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-purple-600" data-icon="mdi:briefcase" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selisih -->
        @php
            $totalAsn = $opds->sum(function($opd) {
                return $opd->asns->count();
            });
            $totalKebutuhan = $opds->sum(function($opd) {
                return $opd->getAllJabatans()->sum('kebutuhan');
            });
            $selisih = $totalAsn - $totalKebutuhan;
        @endphp
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Selisih</p>
                        <p class="text-2xl font-bold {{ $selisih >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $selisih >= 0 ? '+' : '' }}{{ $selisih }}</p>
                    </div>
                    <div class="w-10 h-10 {{ $selisih >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                        <span class="iconify {{ $selisih >= 0 ? 'text-green-600' : 'text-red-600' }}" data-icon="{{ $selisih >= 0 ? 'mdi:trending-up' : 'mdi:trending-down' }}" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
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

            <!-- OPD Table List -->
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama OPD</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Jabatan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ASN</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($opds->sortBy('nama') as $opd)
                                <tr class="hover:bg-gray-50 transition-colors opd-row"
                                    data-opd-id="{{ $opd->id }}"
                                    data-opd-name="{{ strtolower($opd->nama) }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 max-w-md">
                                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white flex-shrink-0">
                                                <span class="iconify" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ $opd->nama }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-primary">{{ $opd->getAllJabatans()->count() }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-gray">{{ $opd->asns->count() }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.opds.show', $opd->id) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white rounded hover:bg-primary-700 text-sm whitespace-nowrap">
                                                <span class="iconify" data-icon="mdi:eye" data-width="14" data-height="14"></span>
                                                <span class="ml-1">Detail</span>
                                            </a>
                                            @if(auth('admin')->user()->canManageOpdJabatan())
                                            <button @click="openEditModal({{ $opd->id }}, '{{ addslashes($opd->nama) }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm whitespace-nowrap">
                                                <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                                                <span class="ml-1">Edit</span>
                                            </button>
                                            <button @click="openDeleteModal({{ $opd->id }}, '{{ addslashes($opd->nama) }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm whitespace-nowrap">
                                                <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                                                <span class="ml-1">Hapus</span>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- No Results -->
                <div x-show="searchResults === 0 && searchQuery"
                     class="p-8 text-center">
                    <div class="flex justify-center mb-3">
                        <span class="iconify text-gray-300" data-icon="mdi:magnify" data-width="48" data-height="48"></span>
                    </div>
                    <p class="text-sm text-gray-500">Tidak ada hasil untuk "<span x-text="searchQuery"></span>"</p>
                </div>
            </div>

            <!-- Pagination with Per Page Selector -->
            <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Tampilkan:</label>
                    <form method="GET" action="{{ route('admin.opds.index') }}" class="inline-block">
                        <select name="per_page"
                                onchange="this.form.submit()"
                                class="input text-sm py-1 px-2 pr-8 w-auto">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', 10) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                    <span class="text-sm text-gray-600">per halaman</span>
                </div>

                <!-- Pagination Links -->
                <div class="flex-1 flex justify-end">
                    {{ $opds->links('vendor.pagination.custom') }}
                </div>
            </div>

        @else
            <!-- Empty State -->
            <div class="card p-12 text-center animate-fade-in">
                <span class="iconify text-gray-300" data-icon="mdi:office-building" data-width="64" data-height="64"></span>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Belum Ada Data OPD</h3>
                <p class="text-sm text-gray-500 mb-4">Sistem belum memiliki data Organisasi Perangkat Daerah</p>
                <button @click="$dispatch('open-modal', 'add-opd')" class="btn btn-primary">
                    <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                    <span class="ml-2">Tambah OPD Pertama</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Modals -->
    <!-- Modal Tambah OPD -->
    <x-modal name="add-opd" title="Tambah OPD Baru" maxWidth="md">
        <form action="{{ route('admin.opds.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="nama_opd" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama OPD <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="nama_opd"
                        name="nama"
                        required
                        class="input w-full"
                        placeholder="Contoh: Dinas Pendidikan"
                        autofocus
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Masukkan nama lengkap Organisasi Perangkat Daerah
                    </p>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'add-opd')" class="btn btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                        <span class="ml-2">Tambah OPD</span>
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <!-- Modal Edit OPD -->
    <x-modal name="edit-opd" title="Edit OPD" maxWidth="md">
        <form id="editOpdForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit_nama_opd" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama OPD <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_nama_opd"
                        name="nama"
                        required
                        class="input w-full"
                        placeholder="Contoh: Dinas Pendidikan"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Masukkan nama lengkap Organisasi Perangkat Daerah
                    </p>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'edit-opd')" class="btn btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:content-save" data-width="16" data-height="16"></span>
                        <span class="ml-2">Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <!-- Modal Delete OPD -->
    <x-modal name="delete-opd" title="Hapus OPD" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus OPD?</h3>
            <p class="text-sm text-gray-600 mb-4">
                Apakah Anda yakin ingin menghapus <strong id="delete_opd_nama"></strong>?<br>
                Semua data jabatan dan ASN di dalamnya akan ikut terhapus.<br>
                <span class="text-red-600 font-semibold">Tindakan ini tidak dapat dibatalkan!</span>
            </p>
            <form id="deleteOpdForm" method="POST" class="flex gap-2 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('close-modal', 'delete-opd')" class="btn btn-outline">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                    <span class="ml-1">Ya, Hapus</span>
                </button>
            </form>
        </div>
    </x-modal>
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
        },

        openEditModal(opdId, opdNama) {
            document.getElementById('edit_nama_opd').value = opdNama;
            document.getElementById('editOpdForm').action = '{{ route("admin.opds.update", ":id") }}'.replace(':id', opdId);
            this.$dispatch('open-modal', 'edit-opd');
        },

        openDeleteModal(opdId, opdNama) {
            document.getElementById('delete_opd_nama').textContent = opdNama;
            document.getElementById('deleteOpdForm').action = '{{ route("admin.opds.destroy", ":id") }}'.replace(':id', opdId);
            this.$dispatch('open-modal', 'delete-opd');
        }
    }
}
</script>
@endpush
@endsection
