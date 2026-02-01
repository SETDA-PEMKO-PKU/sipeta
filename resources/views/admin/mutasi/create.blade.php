@extends('admin.layouts.app')

@section('title', 'Buat Mutasi Baru')
@section('page-title', 'Buat Mutasi Baru')

@section('content')
<div class="p-4 lg:p-8" x-data="mutasiForm()">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('admin.mutasi.index') }}" class="hover:text-primary-600">Mutasi Pegawai</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span class="text-gray-900">Buat Mutasi Baru</span>
    </nav>

    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Buat Mutasi Baru</h2>
        <p class="text-gray-600 mt-1">Masukkan data mutasi pegawai</p>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="alert alert-error mb-6">
            <div class="flex items-start gap-2">
                <span class="iconify flex-shrink-0 mt-0.5" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                <div>
                    <p class="font-medium">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.mutasi.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Card: Data Pegawai -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:account" data-width="20" data-height="20"></span>
                        Data Pegawai
                    </h3>
                </div>
                <div class="card-body space-y-4">
                    <!-- Pencarian ASN -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Cari Pegawai <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   x-model="searchQuery"
                                   @input.debounce.300ms="searchAsn()"
                                   @focus="showResults = searchResults.length > 0"
                                   placeholder="Ketik nama atau NIP pegawai..."
                                   class="input w-full"
                                   :disabled="selectedAsn !== null">
                            
                            <!-- Search Results Dropdown -->
                            <div x-show="showResults && searchResults.length > 0" 
                                 x-cloak
                                 @click.away="showResults = false"
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="asn in searchResults" :key="asn.id">
                                    <div @click="selectAsn(asn)" 
                                         class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0">
                                        <div class="font-medium text-gray-900" x-text="asn.nama"></div>
                                        <div class="text-xs text-gray-500">
                                            <span x-text="'NIP: ' + asn.nip"></span> | 
                                            <span x-text="asn.opd_nama"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <input type="hidden" name="asn_id" x-model="selectedAsn?.id" required>
                    </div>

                    <!-- Selected ASN Card -->
                    <div x-show="selectedAsn" x-cloak class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-semibold text-blue-900" x-text="selectedAsn?.nama"></h4>
                                <p class="text-sm text-blue-700" x-text="'NIP: ' + selectedAsn?.nip"></p>
                                <p class="text-sm text-blue-700 mt-2">
                                    <span class="font-medium">OPD Saat Ini:</span> 
                                    <span x-text="selectedAsn?.opd_nama"></span>
                                </p>
                                <p class="text-sm text-blue-700">
                                    <span class="font-medium">Jabatan Saat Ini:</span> 
                                    <span x-text="selectedAsn?.jabatan_nama || '-'"></span>
                                </p>
                            </div>
                            <button type="button" @click="clearAsn()" class="text-blue-600 hover:text-blue-800">
                                <span class="iconify" data-icon="mdi:close" data-width="20" data-height="20"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Data Mutasi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:swap-horizontal" data-width="20" data-height="20"></span>
                        Data Mutasi
                    </h3>
                </div>
                <div class="card-body space-y-4">
                    <!-- Jenis Mutasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Mutasi <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_mutasi" x-model="jenisMutasi" @change="onJenisMutasiChange()" class="input w-full" required>
                            <option value="">-- Pilih Jenis Mutasi --</option>
                            <option value="antar_opd">Mutasi Antar OPD</option>
                            <option value="internal_opd">Mutasi Internal OPD (Pindah Jabatan)</option>
                        </select>
                    </div>

                    <!-- OPD Tujuan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            OPD Tujuan <span class="text-red-500">*</span>
                        </label>
                        <select name="opd_tujuan_id" x-model="opdTujuanId" @change="onOpdTujuanChange()" class="input w-full" required>
                            <option value="">-- Pilih OPD Tujuan --</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <p x-show="jenisMutasi === 'internal_opd'" class="text-xs text-gray-500 mt-1">
                            Untuk mutasi internal, pilih OPD yang sama dengan OPD asal
                        </p>
                    </div>

                    <!-- Pilih Atasan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Pilih Atasan (Jabatan Struktural)
                        </label>
                        <select name="atasan_id" x-model="atasanId" @change="loadJabatanTujuanByAtasan()" class="input w-full" :disabled="!opdTujuanId || loadingAtasan">
                            <option value="">-- Pilih Atasan Dahulu --</option>
                            <template x-for="atasan in atasanList" :key="atasan.id">
                                <option :value="atasan.id" x-text="atasan.nama"></option>
                            </template>
                        </select>
                        <p x-show="loadingAtasan" class="text-xs text-gray-500 mt-1">Memuat daftar atasan...</p>
                        <p x-show="!loadingAtasan && atasanList.length === 0 && opdTujuanId" class="text-xs text-gray-500 mt-1">
                            Tidak ada jabatan struktural di OPD tujuan
                        </p>
                    </div>

                    <!-- Jabatan Tujuan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jabatan Tujuan (di bawah atasan yang dipilih)
                        </label>
                        <select name="jabatan_tujuan_id" x-model="jabatanTujuanId" class="input w-full" :disabled="!atasanId || loadingJabatan">
                            <option value="">-- Pilih Jabatan (Opsional) --</option>
                            <template x-for="jabatan in jabatanTujuanList" :key="jabatan.id">
                                <option :value="jabatan.id" x-text="jabatan.nama + ' (' + jabatan.jenis_jabatan + (jabatan.kelas ? ' - Kelas ' + jabatan.kelas : '') + ')'"></option>
                            </template>
                        </select>
                        <p x-show="loadingJabatan" class="text-xs text-gray-500 mt-1">Memuat daftar jabatan...</p>
                        <p x-show="!loadingJabatan && jabatanTujuanList.length === 0 && atasanId" class="text-xs text-gray-500 mt-1">
                            Tidak ada jabatan di bawah atasan yang dipilih
                        </p>
                    </div>

                    <!-- Tanggal Mutasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Mutasi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mutasi" value="{{ old('tanggal_mutasi', date('Y-m-d')) }}" class="input w-full" required>
                    </div>
                </div>
            </div>

            <!-- Card: Data SK -->
            <div class="card lg:col-span-2">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:file-document" data-width="20" data-height="20"></span>
                        Data Surat Keputusan (Opsional)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Nomor SK -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor SK</label>
                            <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" 
                                   placeholder="Contoh: 800/001/SK/2026"
                                   class="input w-full">
                        </div>

                        <!-- Tanggal SK -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal SK</label>
                            <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk') }}" class="input w-full">
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea name="keterangan" rows="2" 
                                      placeholder="Keterangan tambahan..."
                                      class="input w-full">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.mutasi.index') }}" class="btn btn-secondary">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                <span class="ml-2">Batal</span>
            </a>
            <button type="submit" class="btn btn-primary" :disabled="!selectedAsn || !opdTujuanId || !jenisMutasi">
                <span class="iconify" data-icon="mdi:content-save" data-width="18" data-height="18"></span>
                <span class="ml-2">Simpan Mutasi</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function mutasiForm() {
    return {
        searchQuery: '',
        searchResults: [],
        showResults: false,
        selectedAsn: @json($selectedAsn),
        jenisMutasi: '{{ old('jenis_mutasi', '') }}',
        opdTujuanId: '{{ old('opd_tujuan_id', '') }}',
        atasanId: '{{ old('atasan_id', '') }}',
        jabatanTujuanId: '{{ old('jabatan_tujuan_id', '') }}',
        atasanList: [],
        jabatanTujuanList: [],
        loadingAtasan: false,
        loadingJabatan: false,

        init() {
            if (this.selectedAsn) {
                this.searchQuery = this.selectedAsn.nama;
            }
            if (this.opdTujuanId) {
                this.onOpdTujuanChange();
            }
        },

        async searchAsn() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }

            try {
                const response = await fetch(`{{ route('admin.mutasi.search-asn') }}?q=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = await response.json();
                this.showResults = true;
            } catch (error) {
                console.error('Error searching ASN:', error);
            }
        },

        selectAsn(asn) {
            this.selectedAsn = asn;
            this.searchQuery = asn.nama;
            this.showResults = false;
            this.searchResults = [];

            // Auto-select OPD for internal mutation
            if (this.jenisMutasi === 'internal_opd') {
                this.opdTujuanId = asn.opd_id;
                this.onOpdTujuanChange();
            }
        },

        clearAsn() {
            this.selectedAsn = null;
            this.searchQuery = '';
            this.searchResults = [];
        },

        onJenisMutasiChange() {
            if (this.jenisMutasi === 'internal_opd' && this.selectedAsn) {
                this.opdTujuanId = this.selectedAsn.opd_id;
                this.onOpdTujuanChange();
            }
        },

        async onOpdTujuanChange() {
            // Reset atasan and jabatan selection
            this.atasanId = '';
            this.jabatanTujuanId = '';
            this.atasanList = [];
            this.jabatanTujuanList = [];

            if (!this.opdTujuanId) {
                return;
            }

            // Load atasan list (structural positions only)
            this.loadingAtasan = true;
            try {
                const response = await fetch(`/admin/mutasi/jabatan-struktural/${this.opdTujuanId}`);
                this.atasanList = await response.json();
            } catch (error) {
                console.error('Error loading atasan:', error);
            } finally {
                this.loadingAtasan = false;
            }
        },

        async loadJabatanTujuanByAtasan() {
            // Reset jabatan selection
            this.jabatanTujuanId = '';
            this.jabatanTujuanList = [];

            if (!this.opdTujuanId || !this.atasanId) {
                return;
            }

            // Load jabatan under selected atasan
            this.loadingJabatan = true;
            try {
                const response = await fetch(`/admin/mutasi/jabatan-by-atasan/${this.opdTujuanId}/${this.atasanId}`);
                this.jabatanTujuanList = await response.json();
            } catch (error) {
                console.error('Error loading jabatan:', error);
            } finally {
                this.loadingJabatan = false;
            }
        }
    };
}
</script>
@endpush
@endsection
