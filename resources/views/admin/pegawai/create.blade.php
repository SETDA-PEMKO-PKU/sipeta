@extends('admin.layouts.app')

@section('title', 'Tambah Pegawai')
@section('page-title', 'Tambah Pegawai')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Breadcrumbs -->
    @if(auth('admin')->user()->isAdminOpd() && auth('admin')->user()->opd)
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <span class="iconify" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
        <span class="font-medium text-gray-900">{{ auth('admin')->user()->opd->nama }}</span>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <a href="{{ route('admin.pegawai.index') }}" class="hover:text-gray-900">Daftar Pegawai</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span>Tambah Pegawai</span>
    </nav>
    @endif

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.pegawai.index') }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Tambah Pegawai Baru</h2>
                <p class="text-gray-600 mt-1">Lengkapi formulir di bawah untuk menambahkan pegawai baru</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="alert alert-error mb-4 animate-fade-in">
            <div class="flex items-start gap-2">
                <span class="iconify" data-icon="mdi:alert" data-width="18" data-height="18"></span>
                <div class="flex-1">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mt-1 text-xs space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="card animate-slide-up" x-data="pegawaiForm()">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Informasi Pegawai</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pegawai.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Data Pribadi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="nama"
                               value="{{ old('nama') }}"
                               required
                               class="input w-full"
                               placeholder="Contoh: Dr. Budi Santoso, S.Kom, M.T">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="nip"
                               value="{{ old('nip') }}"
                               required
                               class="input w-full font-mono"
                               placeholder="Contoh: 199001012015031001">
                    </div>
                </div>

                <!-- Pilih OPD -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Organisasi Perangkat Daerah (OPD) <span class="text-red-500">*</span>
                    </label>
                    @if(auth('admin')->user()->isAdminOpd())
                        <!-- Admin OPD: Show OPD name only, hidden input -->
                        <input type="hidden" name="opd_id" x-model="selectedOpdId" value="{{ auth('admin')->user()->opd_id }}">
                        <div class="input w-full bg-gray-50 flex items-center gap-2">
                            <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="18" data-height="18"></span>
                            <span class="text-gray-900 font-medium">{{ auth('admin')->user()->opd->nama }}</span>
                        </div>
                    @else
                        <!-- Other admins: Show dropdown -->
                        <select name="opd_id"
                                x-model="selectedOpdId"
                                @change="loadJabatans()"
                                required
                                class="input w-full">
                            <option value="">-- Pilih OPD --</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>
                                    {{ $opd->nama }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Pilih Jabatan (Tree Structure) -->
                <div x-show="selectedOpdId">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="jabatan_id" x-model="selectedJabatanId" required>

                    <div x-show="loadingJabatans" class="text-center py-8">
                        <span class="iconify text-gray-400 animate-spin" data-icon="mdi:loading" data-width="32" data-height="32"></span>
                        <p class="text-sm text-gray-500 mt-2">Memuat jabatan...</p>
                    </div>

                    <div x-show="!loadingJabatans && jabatans.length > 0" class="border border-gray-300 rounded-lg p-3 bg-white max-h-80 overflow-y-auto">
                        <template x-for="item in groupedJabatans" :key="item.type + '-' + (item.bagian_id || 'kepala')">
                            <div class="mb-3">
                                <!-- Bagian Header -->
                                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded mb-1">
                                    <span class="iconify"
                                          :data-icon="item.type === 'kepala' ? 'mdi:account-star' : 'mdi:folder'"
                                          :class="item.type === 'kepala' ? 'text-purple-600' : 'text-blue-500'"
                                          data-width="14"
                                          data-height="14"></span>
                                    <span class="text-xs font-semibold text-gray-600" x-text="item.bagian_nama"></span>
                                </div>

                                <!-- Jabatan Items -->
                                <template x-for="jabatan in item.jabatans" :key="jabatan.id">
                                    <div @click="selectJabatan(jabatan.id, jabatan.nama, item.bagian_nama)"
                                         :class="selectedJabatanId == jabatan.id ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                                         class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors ml-4">
                                        <span class="iconify text-green-600" data-icon="mdi:account-tie" data-width="14" data-height="14"></span>
                                        <span class="text-sm text-gray-700" x-text="jabatan.nama"></span>
                                        <span x-show="selectedJabatanId == jabatan.id"
                                              class="iconify text-blue-500 ml-auto"
                                              data-icon="mdi:check-circle"
                                              data-width="16"
                                              data-height="16"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="!loadingJabatans && jabatans.length === 0 && selectedOpdId"
                         class="border border-gray-300 rounded-lg p-8 text-center bg-gray-50">
                        <span class="iconify text-gray-300" data-icon="mdi:briefcase-off" data-width="32" data-height="32"></span>
                        <p class="text-sm text-gray-500 mt-2">Belum ada jabatan tersedia di OPD ini</p>
                    </div>

                    <div x-show="selectedJabatanName" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="selectedJabatanName" class="font-semibold"></span>
                            <span x-text="selectedBagianName ? ' (' + selectedBagianName + ')' : ''" class="text-blue-700"></span>
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline">
                        <span class="iconify" data-icon="mdi:close" data-width="16" data-height="16"></span>
                        <span class="ml-2">Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:content-save" data-width="16" data-height="16"></span>
                        <span class="ml-2">Simpan Pegawai</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pegawaiForm() {
    return {
        selectedOpdId: '{{ old('opd_id', auth('admin')->user()->isAdminOpd() ? auth('admin')->user()->opd_id : '') }}',
        selectedJabatanId: '{{ old('jabatan_id') }}',
        selectedJabatanName: '',
        selectedBagianName: '',
        jabatans: [],
        groupedJabatans: [],
        loadingJabatans: false,

        async loadJabatans() {
            if (!this.selectedOpdId) {
                this.jabatans = [];
                this.groupedJabatans = [];
                return;
            }

            this.loadingJabatans = true;
            this.selectedJabatanId = '';
            this.selectedJabatanName = '';
            this.selectedBagianName = '';

            try {
                const response = await fetch(`/admin/api/opds/${this.selectedOpdId}/jabatans`);
                const data = await response.json();
                this.jabatans = data;
                this.groupJabatans();
            } catch (error) {
                console.error('Error loading jabatans:', error);
            } finally {
                this.loadingJabatans = false;
            }
        },

        groupJabatans() {
            const groups = {};

            this.jabatans.forEach(jabatan => {
                const key = jabatan.type === 'kepala' ? 'kepala' : `bagian-${jabatan.bagian_id}`;

                if (!groups[key]) {
                    groups[key] = {
                        type: jabatan.type,
                        bagian_id: jabatan.bagian_id,
                        bagian_nama: jabatan.bagian_nama,
                        jabatans: []
                    };
                }

                groups[key].jabatans.push(jabatan);
            });

            this.groupedJabatans = Object.values(groups);
        },

        selectJabatan(id, nama, bagianNama) {
            this.selectedJabatanId = id;
            this.selectedJabatanName = nama;
            this.selectedBagianName = bagianNama;
        },

        init() {
            if (this.selectedOpdId) {
                this.loadJabatans();
            }
        }
    }
}
</script>
@endpush
@endsection
