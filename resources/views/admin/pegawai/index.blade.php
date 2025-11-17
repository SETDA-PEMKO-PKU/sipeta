@extends('admin.layouts.app')

@section('title', 'Daftar Pegawai')
@section('page-title', 'Data Pegawai')

@section('content')
<div class="p-4 lg:p-8" x-data="pegawaiIndex()">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Daftar Pegawai</h2>
            <p class="text-gray-600 mt-1">Kelola Data Pegawai/ASN</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.pegawai.create') }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:account-plus" data-width="18" data-height="18"></span>
                <span class="ml-2">Tambah Pegawai</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Pegawai -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Total Pegawai</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalPegawai }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-blue-600" data-icon="mdi:account-group" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total OPD -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Total OPD</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalOpd }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-green-600" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pegawai Struktural -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Struktural</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $pegawais->filter(function($p) { return $p->jabatan && $p->jabatan->jenis_jabatan === 'Struktural'; })->count() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-purple-600" data-icon="mdi:account-tie" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pegawai Fungsional -->
        <div class="card">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Fungsional</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $pegawais->filter(function($p) { return $p->jabatan && $p->jabatan->jenis_jabatan === 'Fungsional'; })->count() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-yellow-600" data-icon="mdi:briefcase" data-width="20" data-height="20"></span>
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

        @if($pegawais->count() > 0 || request()->hasAny(['opd_id', 'bagian_id', 'jabatan_id', 'jenis_jabatan', 'kelas']))
            <!-- Filter Panel -->
            <div class="card mb-3 animate-slide-up">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('admin.pegawai.index') }}" class="space-y-4">
                        <!-- Search Bar -->
                        <div class="flex items-center gap-3">
                            <div class="flex-1 relative">
                                <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" data-icon="mdi:magnify" data-width="16" data-height="16"></span>
                                <input
                                    type="text"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="performSearch()"
                                    placeholder="Cari nama, NIP, atau OPD..."
                                    class="input pl-9 pr-9 w-full text-sm"
                                >
                                <button
                                    type="button"
                                    x-show="searchQuery"
                                    @click="clearSearch()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <span class="iconify" data-icon="mdi:close" data-width="16" data-height="16"></span>
                                </button>
                            </div>
                            <button type="button"
                                    @click="showFilters = !showFilters"
                                    class="btn btn-outline flex items-center gap-2">
                                <span class="iconify" data-icon="mdi:filter-variant" data-width="16" data-height="16"></span>
                                <span>Filter</span>
                                @if(request()->hasAny(['opd_id', 'bagian_id', 'jabatan_id', 'jenis_jabatan', 'kelas']))
                                    <span class="badge badge-primary badge-sm">{{ collect(['opd_id', 'bagian_id', 'jabatan_id', 'jenis_jabatan', 'kelas'])->filter(fn($key) => request()->filled($key))->count() }}</span>
                                @endif
                            </button>
                            <div class="text-xs text-gray-500" x-text="searchResults + ' Pegawai'"></div>
                        </div>

                        <!-- Filter Options (Collapsible) -->
                        <div x-show="showFilters"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 pt-3 border-t border-gray-200">

                            <!-- Filter OPD -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">OPD</label>
                                <select name="opd_id" class="input text-sm w-full">
                                    <option value="">Semua OPD</option>
                                    @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}" {{ request('opd_id') == $opd->id ? 'selected' : '' }}>
                                            {{ $opd->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Bagian -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Bagian</label>
                                <select name="bagian_id" class="input text-sm w-full">
                                    <option value="">Semua Bagian</option>
                                    @foreach($bagians as $bagian)
                                        <option value="{{ $bagian->id }}" {{ request('bagian_id') == $bagian->id ? 'selected' : '' }}>
                                            {{ $bagian->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Jabatan -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Jabatan</label>
                                <select name="jabatan_id" class="input text-sm w-full">
                                    <option value="">Semua Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" {{ request('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                            {{ $jabatan->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Jenis Jabatan -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                                <select name="jenis_jabatan" class="input text-sm w-full">
                                    <option value="">Semua Jenis</option>
                                    @foreach($jenisJabatans as $jenis)
                                        <option value="{{ $jenis }}" {{ request('jenis_jabatan') == $jenis ? 'selected' : '' }}>
                                            {{ $jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Kelas -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Kelas Jabatan</label>
                                <select name="kelas" class="input text-sm w-full">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelasJabatans as $kelas)
                                        <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                            Kelas {{ $kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Actions -->
                            <div class="md:col-span-3 lg:col-span-5 flex items-center gap-2 justify-end">
                                <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline btn-sm">
                                    <span class="iconify" data-icon="mdi:refresh" data-width="14" data-height="14"></span>
                                    <span class="ml-1">Reset</span>
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="iconify" data-icon="mdi:filter" data-width="14" data-height="14"></span>
                                    <span class="ml-1">Terapkan Filter</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Active Filters Display -->
                    @if(request()->hasAny(['opd_id', 'bagian_id', 'jabatan_id', 'jenis_jabatan', 'kelas']))
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-medium text-gray-600">Filter Aktif:</span>

                                @if(request('opd_id'))
                                    <span class="badge badge-primary">
                                        OPD: {{ $opds->find(request('opd_id'))->nama ?? '' }}
                                        <a href="{{ route('admin.pegawai.index', array_filter(request()->except('opd_id'))) }}" class="ml-1 hover:text-white">×</a>
                                    </span>
                                @endif

                                @if(request('bagian_id'))
                                    <span class="badge badge-success">
                                        Bagian: {{ $bagians->find(request('bagian_id'))->nama ?? '' }}
                                        <a href="{{ route('admin.pegawai.index', array_filter(request()->except('bagian_id'))) }}" class="ml-1 hover:text-white">×</a>
                                    </span>
                                @endif

                                @if(request('jabatan_id'))
                                    <span class="badge badge-purple">
                                        Jabatan: {{ $jabatans->find(request('jabatan_id'))->nama ?? '' }}
                                        <a href="{{ route('admin.pegawai.index', array_filter(request()->except('jabatan_id'))) }}" class="ml-1 hover:text-white">×</a>
                                    </span>
                                @endif

                                @if(request('jenis_jabatan'))
                                    <span class="badge badge-info">
                                        Jenis: {{ request('jenis_jabatan') }}
                                        <a href="{{ route('admin.pegawai.index', array_filter(request()->except('jenis_jabatan'))) }}" class="ml-1 hover:text-white">×</a>
                                    </span>
                                @endif

                                @if(request('kelas'))
                                    <span class="badge badge-warning">
                                        Kelas: {{ request('kelas') }}
                                        <a href="{{ route('admin.pegawai.index', array_filter(request()->except('kelas'))) }}" class="ml-1 hover:text-white">×</a>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pegawai Table List -->
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Pegawai</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Jabatan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OPD</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pegawais as $pegawai)
                                <tr class="hover:bg-gray-50 transition-colors pegawai-row"
                                    data-pegawai-id="{{ $pegawai->id }}"
                                    data-pegawai-nama="{{ strtolower($pegawai->nama) }}"
                                    data-pegawai-nip="{{ strtolower($pegawai->nip) }}"
                                    data-pegawai-opd="{{ strtolower($pegawai->opd ? $pegawai->opd->nama : '') }}"
                                    data-pegawai-jabatan="{{ strtolower($pegawai->jabatan ? $pegawai->jabatan->nama : '') }}"
                                    data-pegawai-bagian="{{ strtolower($pegawai->bagian ? $pegawai->bagian->nama : '') }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                                                {{ strtoupper(substr($pegawai->nama, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900">{{ $pegawai->nama }}</div>
                                                @if($pegawai->bagian)
                                                    <div class="text-xs text-gray-500">{{ $pegawai->bagian->nama }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 font-mono">{{ $pegawai->nip }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $pegawai->jabatan ? $pegawai->jabatan->nama : '-' }}</div>
                                        <div class="flex items-center gap-1 mt-1">
                                            @if($pegawai->jabatan && $pegawai->jabatan->jenis_jabatan)
                                                <span class="badge badge-sm
                                                    @if($pegawai->jabatan->jenis_jabatan === 'Struktural') badge-primary
                                                    @elseif($pegawai->jabatan->jenis_jabatan === 'Fungsional') badge-success
                                                    @else badge-gray
                                                    @endif">
                                                    {{ $pegawai->jabatan->jenis_jabatan }}
                                                </span>
                                            @endif
                                            @if($pegawai->jabatan && $pegawai->jabatan->kelas)
                                                <span class="badge badge-sm badge-gray">
                                                    Kelas {{ $pegawai->jabatan->kelas }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="iconify text-gray-400" data-icon="mdi:office-building" data-width="14" data-height="14"></span>
                                            <span class="text-sm text-gray-900">{{ $pegawai->opd ? $pegawai->opd->nama : '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.pegawai.edit', $pegawai->id) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white rounded hover:bg-primary-700 text-sm whitespace-nowrap">
                                                <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                                                <span class="ml-1">Edit</span>
                                            </a>
                                            <button @click="deletePegawai({{ $pegawai->id }}, '{{ addslashes($pegawai->nama) }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm whitespace-nowrap">
                                                <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                                                <span class="ml-1">Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <span class="iconify text-gray-300" data-icon="mdi:account-off" data-width="48" data-height="48"></span>
                                        <p class="text-sm text-gray-500 mt-2">Tidak ada pegawai yang ditemukan dengan filter yang dipilih</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- No Results from Search -->
                <div x-show="searchResults === 0 && searchQuery && {{ $pegawais->count() }} > 0"
                     class="p-8 text-center border-t border-gray-200">
                    <span class="iconify text-gray-300" data-icon="mdi:magnify" data-width="48" data-height="48"></span>
                    <p class="text-sm text-gray-500 mt-2">Tidak ada hasil untuk "<span x-text="searchQuery"></span>"</p>
                </div>
            </div>

            <!-- Pagination with Per Page Selector -->
            <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Tampilkan:</label>
                    <form method="GET" action="{{ route('admin.pegawai.index') }}" class="inline-block">
                        @foreach(request()->except('per_page', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <select name="per_page"
                                onchange="this.form.submit()"
                                class="input text-sm py-1 px-2 pr-8 w-auto">
                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                    <span class="text-sm text-gray-600">per halaman</span>
                </div>

                <!-- Pagination Links -->
                <div class="flex-1 flex justify-end">
                    {{ $pegawais->links('vendor.pagination.custom') }}
                </div>
            </div>

        @else
            <!-- Empty State -->
            <div class="card p-12 text-center animate-fade-in">
                <span class="iconify text-gray-300" data-icon="mdi:account-group" data-width="64" data-height="64"></span>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Belum Ada Data Pegawai</h3>
                <p class="text-sm text-gray-500 mb-4">Sistem belum memiliki data pegawai/ASN</p>
                <a href="{{ route('admin.pegawai.create') }}" class="btn btn-primary">
                    <span class="iconify" data-icon="mdi:account-plus" data-width="16" data-height="16"></span>
                    <span class="ml-2">Tambah Pegawai Pertama</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Delete Modal -->
    <x-modal name="delete-pegawai" title="Hapus Pegawai" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus Pegawai?</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus pegawai <strong id="delete_pegawai_nama"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deletePegawaiForm" method="POST" class="flex gap-2 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('close-modal', 'delete-pegawai')" class="btn btn-outline">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                    <span class="ml-1">Hapus</span>
                </button>
            </form>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
function pegawaiIndex() {
    return {
        searchQuery: '',
        searchResults: {{ $pegawais->total() }},
        totalPegawais: {{ $pegawais->total() }},
        showFilters: {{ request()->hasAny(['opd_id', 'bagian_id', 'jabatan_id', 'jenis_jabatan', 'kelas']) ? 'true' : 'false' }},

        performSearch() {
            const rows = document.querySelectorAll('.pegawai-row');
            let count = 0;

            rows.forEach(row => {
                const nama = row.dataset.pegawaiNama || '';
                const nip = row.dataset.pegawaiNip || '';
                const opd = row.dataset.pegawaiOpd || '';
                const jabatan = row.dataset.pegawaiJabatan || '';
                const bagian = row.dataset.pegawaiBagian || '';
                const query = this.searchQuery.toLowerCase();

                if (nama.includes(query) || nip.includes(query) || opd.includes(query) || jabatan.includes(query) || bagian.includes(query)) {
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
            this.searchResults = this.totalPegawais;
            document.querySelectorAll('.pegawai-row').forEach(row => {
                row.style.display = '';
            });
        },

        deletePegawai(id, nama) {
            document.getElementById('delete_pegawai_nama').textContent = nama;
            document.getElementById('deletePegawaiForm').action = `/admin/pegawai/${id}`;
            this.$dispatch('open-modal', 'delete-pegawai');
        }
    }
}
</script>
@endpush
@endsection
