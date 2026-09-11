@extends('admin.layouts.app')

@section('title', 'Edit Pegawai')
@section('page-title', 'Edit Pegawai')

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
        <span>Edit Pegawai</span>
    </nav>
    @endif

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.pegawai.index') }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Edit Pegawai</h2>
                <p class="text-gray-600 mt-1">Perbarui data pegawai {{ $pegawai->nama }}</p>
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
    <div class="card animate-slide-up" x-data="pegawaiEditForm()">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Informasi Pegawai</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Data Pribadi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="nama"
                               value="{{ old('nama', $pegawai->nama) }}"
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
                               value="{{ old('nip', $pegawai->nip) }}"
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
                                <option value="{{ $opd->id }}" {{ old('opd_id', $pegawai->opd_id) == $opd->id ? 'selected' : '' }}>
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
                        <div class="space-y-1">
                            <!-- Jabatan Kepala -->
                            <template x-for="jabatan in jabatans.filter(j => j.type === 'kepala')" :key="jabatan.id">
                                <div>
                                    <!-- Kepala Header -->
                                    <div class="flex items-center gap-2 p-2 bg-gray-50 rounded mb-1">
                                        <span class="iconify text-purple-600" data-icon="mdi:account-star" data-width="14" data-height="14"></span>
                                        <span class="text-xs font-semibold text-gray-600">Kepala OPD</span>
                                    </div>
                                    <!-- Jabatan Item -->
                                    <div @click="selectJabatan(jabatan.id, jabatan.nama, 'Kepala OPD')"
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
                                </div>
                            </template>

                            <!-- Bagian dan Jabatan Tree -->
                            <template x-for="bagian in bagianTree" :key="bagian.id">
                                <div x-html="renderBagianTree(bagian, 0)"></div>
                            </template>
                        </div>
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
                        <span class="ml-2">Perbarui Pegawai</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pegawaiEditForm() {
    return {
        selectedOpdId: '{{ old('opd_id', $pegawai->opd_id) }}',
        selectedJabatanId: '{{ old('jabatan_id', $pegawai->jabatan_id) }}',
        selectedJabatanName: '{{ $pegawai->jabatan ? $pegawai->jabatan->nama : '' }}',
        selectedBagianName: '{{ $pegawai->bagian ? $pegawai->bagian->nama : ($pegawai->jabatan && !$pegawai->jabatan->parent_id ? 'Kepala OPD' : '') }}',
        jabatans: [],
        bagians: [],
        bagianTree: [],
        loadingJabatans: false,

        async loadJabatans() {
            if (!this.selectedOpdId) {
                this.jabatans = [];
                this.bagians = [];
                this.bagianTree = [];
                return;
            }

            this.loadingJabatans = true;
            const currentJabatanId = this.selectedJabatanId;

            try {
                const response = await fetch(`/admin/api/opds/${this.selectedOpdId}/jabatans`);
                const data = await response.json();
                this.jabatans = data;

                // Build bagian structure
                this.buildBagianTree();

                // Restore selected jabatan if same OPD
                if (currentJabatanId && this.selectedOpdId === '{{ $pegawai->opd_id }}') {
                    this.selectedJabatanId = currentJabatanId;
                } else {
                    this.selectedJabatanId = '';
                    this.selectedJabatanName = '';
                    this.selectedBagianName = '';
                }
            } catch (error) {
                console.error('Error loading jabatans:', error);
            } finally {
                this.loadingJabatans = false;
            }
        },

        buildBagianTree() {
            // Extract unique bagians from jabatans
            const bagianMap = new Map();

            this.jabatans.forEach(jabatan => {
                if (jabatan.type === 'bagian' && jabatan.bagian_id) {
                    if (!bagianMap.has(jabatan.bagian_id)) {
                        bagianMap.set(jabatan.bagian_id, {
                            id: jabatan.bagian_id,
                            nama: jabatan.bagian_nama,
                            parent_id: jabatan.bagian_parent_id,
                            jabatans: [],
                            children: []
                        });
                    }
                    bagianMap.get(jabatan.bagian_id).jabatans.push(jabatan);
                }
            });

            this.bagians = Array.from(bagianMap.values());

            // Build tree structure
            const buildTree = (parentId = null) => {
                return this.bagians
                    .filter(b => b.parent_id === parentId)
                    .map(bagian => ({
                        ...bagian,
                        children: buildTree(bagian.id)
                    }));
            };

            this.bagianTree = buildTree(null);
        },

        renderBagianTree(bagian, level) {
            const indent = '─'.repeat(level * 2);
            const marginLeft = level * 16; // 4 * level for ml-{level*4}

            let html = '<div class="space-y-1">';

            // Bagian Header
            html += `<div class="flex items-center gap-2 p-2 bg-gray-50 rounded ${level > 0 ? 'mt-2' : ''}">`;
            if (level > 0) {
                html += `<span class="text-gray-400 text-xs" style="margin-left: ${marginLeft}px">${indent}</span>`;
            }
            html += `<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="14" data-height="14"></span>`;
            html += `<span class="text-xs font-semibold text-gray-600">${this.escapeHtml(bagian.nama)}</span>`;
            html += '</div>';

            // Jabatan Items
            bagian.jabatans.forEach(jabatan => {
                const indentJabatan = '─'.repeat((level + 1) * 2);
                const marginLeftJabatan = (level + 1) * 16;
                const isSelected = this.selectedJabatanId == jabatan.id;

                html += `<div @click="selectJabatan(${jabatan.id}, '${this.escapeHtml(jabatan.nama)}', '${this.escapeHtml(bagian.nama)}')"
                         class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors ${isSelected ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'}"
                         style="margin-left: ${marginLeftJabatan}px">`;
                html += `<span class="text-gray-400 text-xs">${indentJabatan}</span>`;
                html += `<span class="iconify text-green-600" data-icon="mdi:account-tie" data-width="14" data-height="14"></span>`;
                html += `<span class="text-sm text-gray-700">${this.escapeHtml(jabatan.nama)}</span>`;
                if (isSelected) {
                    html += `<span class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>`;
                }
                html += '</div>';
            });

            // Sub-bagian (Recursive)
            if (bagian.children && bagian.children.length > 0) {
                bagian.children.forEach(child => {
                    html += this.renderBagianTree(child, level + 1);
                });
            }

            html += '</div>';
            return html;
        },

        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
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
