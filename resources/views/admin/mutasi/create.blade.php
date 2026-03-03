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
                        <input type="hidden" name="asn_id" :value="selectedAsn?.id" required>
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
                        <select name="atasan_id" x-model="atasanId" @change="onAtasanChange()" class="input w-full" :disabled="!opdTujuanId || loadingAtasan"
                                x-html="'<option value=\'\'>-- Pilih Atasan Dahulu --</option>' + atasanList.map(a => `<option value='${a.id}'>${a.nama.replace(/</g,'&lt;')}</option>`).join('')">
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
                        <select name="jabatan_tujuan_id" x-model="jabatanTujuanId" class="input w-full" :disabled="!atasanId || loadingJabatan"
                                x-html="'<option value=\'\'>-- Pilih Jabatan (Opsional) --</option>' + jabatanTujuanList.map(j => `<option value='${j.id}'>${j.nama.replace(/</g,'&lt;')} (${j.jenis_jabatan}${j.kelas ? ' - Kelas ' + j.kelas : ''})</option>`).join('')">
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
        selectedAsn: null,
        jenisMutasi: '',
        opdTujuanId: '',
        atasanId: '',
        jabatanTujuanId: '',
        atasanList: [],
        jabatanTujuanList: [],
        loadingAtasan: false,
        loadingJabatan: false,
        _searchAbort: null,

        async init() {
            @if(isset($selectedAsn) && $selectedAsn)
            this.selectedAsn = {{ Illuminate\Support\Js::from($selectedAsn) }};
            this.searchQuery = this.selectedAsn.nama;
            @endif

            @if(old('jenis_mutasi'))
            this.jenisMutasi = '{{ old('jenis_mutasi') }}';
            @endif

            @if(old('opd_tujuan_id'))
            this.opdTujuanId = '{{ old('opd_tujuan_id') }}';
            await this.fetchAtasanList(
                '{{ old('atasan_id') }}',
                '{{ old('jabatan_tujuan_id') }}'
            );
            @endif
        },

        async searchAsn() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }
            if (this._searchAbort) this._searchAbort.abort();
            this._searchAbort = new AbortController();
            try {
                const res = await fetch(
                    `{{ route('admin.mutasi.search-asn') }}?q=${encodeURIComponent(this.searchQuery)}`,
                    { signal: this._searchAbort.signal }
                );
                if (!res.ok) throw new Error('Search failed');
                this.searchResults = await res.json();
                this.showResults = true;
            } catch (e) {
                if (e.name !== 'AbortError') console.error('searchAsn error:', e);
            }
        },

        async selectAsn(asn) {
            this.selectedAsn = asn;
            this.searchQuery = asn.nama;
            this.showResults = false;
            this.searchResults = [];
            if (this.jenisMutasi === 'internal_opd') {
                this.opdTujuanId = String(asn.opd_id);
                await this.fetchAtasanList();
            }
        },

        clearAsn() {
            this.selectedAsn = null;
            this.searchQuery = '';
            this.searchResults = [];
            this.showResults = false;
            if (this.jenisMutasi === 'internal_opd') {
                this.opdTujuanId = '';
                this.atasanId = '';
                this.jabatanTujuanId = '';
                this.atasanList = [];
                this.jabatanTujuanList = [];
            }
        },

        async onJenisMutasiChange() {
            this.atasanId = '';
            this.jabatanTujuanId = '';
            this.atasanList = [];
            this.jabatanTujuanList = [];
            if (this.jenisMutasi === 'internal_opd' && this.selectedAsn) {
                this.opdTujuanId = String(this.selectedAsn.opd_id);
                await this.fetchAtasanList();
            }
        },

        async onOpdTujuanChange() {
            this.atasanId = '';
            this.jabatanTujuanId = '';
            this.atasanList = [];
            this.jabatanTujuanList = [];
            await this.fetchAtasanList();
        },

        async fetchAtasanList(restoreAtasanId = null, restoreJabatanId = null) {
            if (!this.opdTujuanId) return;

            this.loadingAtasan = true;
            try {
                const res = await fetch(`{{ url('/admin/mutasi/jabatan-struktural') }}/${this.opdTujuanId}`);
                if (!res.ok) throw new Error('Failed to load atasan list');
                this.atasanList = await res.json();

                if (restoreAtasanId) {
                    this.atasanId = restoreAtasanId;
                    await this.fetchJabatanByAtasan(restoreJabatanId);
                }
            } catch (e) {
                console.error('fetchAtasanList error:', e);
            } finally {
                this.loadingAtasan = false;
            }
        },

        async onAtasanChange() {
            this.jabatanTujuanId = '';
            this.jabatanTujuanList = [];
            await this.fetchJabatanByAtasan();
        },

        async fetchJabatanByAtasan(restoreJabatanId = null) {
            if (!this.opdTujuanId || !this.atasanId) return;

            this.loadingJabatan = true;
            try {
                const res = await fetch(`{{ url('/admin/mutasi/jabatan-by-atasan') }}/${this.opdTujuanId}/${this.atasanId}`);
                if (!res.ok) throw new Error('Failed to load jabatan list');
                this.jabatanTujuanList = await res.json();

                if (restoreJabatanId) {
                    this.jabatanTujuanId = restoreJabatanId;
                }
            } catch (e) {
                console.error('fetchJabatanByAtasan error:', e);
            } finally {
                this.loadingJabatan = false;
            }
        },
    };
}
</script>
@endpush
@endsection
