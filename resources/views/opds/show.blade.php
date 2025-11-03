@extends('layouts.app')

@section('title', $opd->nama . ' - Detail OPD')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="opdShow()">
    <!-- Compact Header -->
    <header class="bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <!-- Breadcrumb -->
            <nav class="text-xs text-primary-100 mb-2">
                <a href="{{ route('opds.index') }}" class="hover:text-white inline-flex items-center gap-1">
                    <span class="iconify" data-icon="mdi:home" data-width="14" data-height="14"></span>
                    <span>Daftar OPD</span>
                </a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $opd->nama }}</span>
            </nav>

            <!-- Header Content -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-lg">
                        <span class="iconify" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold" x-show="!editingNama">{{ $opd->nama }}</h1>
                        <form action="{{ route('opds.update', $opd->id) }}" method="POST" x-show="editingNama" @submit="editingNama = false">
                            @csrf
                            @method('PUT')
                            <input type="text" name="nama" value="{{ $opd->nama }}" class="input text-sm text-gray-900 w-64" required>
                        </form>
                        <p class="text-xs text-primary-100">ID: {{ $opd->id }}</p>
                    </div>
                    <div class="flex gap-1">
                        <button @click="editingNama = !editingNama" class="p-1.5 hover:bg-white/10 rounded text-xs">
                            <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                        </button>
                        <button @click="$dispatch('open-modal', 'delete-opd')" class="p-1.5 hover:bg-red-500/20 rounded text-xs">
                            <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                        </button>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('opds.index') }}" class="btn btn-sm bg-white/10 hover:bg-white/20 text-white border-0">
                        <span class="iconify" data-icon="mdi:arrow-left" data-width="14" data-height="14"></span>
                        <span class="ml-1">Kembali</span>
                    </a>
                    <a href="{{ route('opds.export', $opd->id) }}" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white border-0">
                        <span class="iconify" data-icon="mdi:download" data-width="14" data-height="14"></span>
                        <span class="ml-1">Export</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4 flex items-center gap-2 animate-fade-in">
                <span class="iconify" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4 flex items-center gap-2 animate-fade-in">
                <span class="iconify" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

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

        <!-- Compact Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            @php
                $allJabatans = $opd->jabatanKepala->concat($opd->bagians->flatMap->jabatans);
                $totalBezetting = $allJabatans->sum(function($jabatan) { return $jabatan->asns->count(); });
                $totalKebutuhan = $allJabatans->sum('kebutuhan');
                $totalSelisih = $totalBezetting - $totalKebutuhan;
            @endphp

            <div class="stat-card">
                <div class="flex items-center justify-between mb-1">
                    <span class="stat-label">Bagian</span>
                    <span class="iconify text-primary-600" data-icon="mdi:folder" data-width="18" data-height="18"></span>
                </div>
                <div class="stat-value text-primary-600">{{ $opd->bagians->count() }}</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-1">
                    <span class="stat-label">Jabatan</span>
                    <span class="iconify text-green-600" data-icon="mdi:account-tie" data-width="18" data-height="18"></span>
                </div>
                <div class="stat-value text-green-600">{{ $opd->jabatanKepala->count() + $opd->bagians->sum(function($bagian) { return $bagian->jabatans->count(); }) }}</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-1">
                    <span class="stat-label">Bezetting</span>
                    <span class="iconify text-blue-600" data-icon="mdi:account-check" data-width="18" data-height="18"></span>
                </div>
                <div class="stat-value text-blue-600">{{ $totalBezetting }}</div>
                <div class="text-xs text-gray-500">Kebutuhan: {{ $totalKebutuhan }}</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-1">
                    <span class="stat-label">Selisih</span>
                    <span class="iconify {{ $totalSelisih > 0 ? 'text-green-600' : ($totalSelisih < 0 ? 'text-red-600' : 'text-gray-400') }}" data-icon="mdi:scale-balance" data-width="18" data-height="18"></span>
                </div>
                <div class="stat-value {{ $totalSelisih > 0 ? 'text-green-600' : ($totalSelisih < 0 ? 'text-red-600' : 'text-gray-400') }}">
                    {{ $totalSelisih > 0 ? '+' : '' }}{{ $totalSelisih }}
                </div>
            </div>
        </div>

        <!-- Compact Tree Explorer -->
        <div class="card animate-slide-up">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">
                    <span class="iconify" data-icon="mdi:file-tree" data-width="16" data-height="16"></span>
                    <span class="ml-2">Struktur Organisasi</span>
                </h2>
                <div class="flex gap-2">
                    <button @click="expandAll()" class="btn btn-sm btn-outline">
                        <span class="iconify" data-icon="mdi:arrow-expand-all" data-width="12" data-height="12"></span>
                        <span class="ml-1">Expand</span>
                    </button>
                    <button @click="collapseAll()" class="btn btn-sm btn-outline">
                        <span class="iconify" data-icon="mdi:arrow-collapse-all" data-width="12" data-height="12"></span>
                        <span class="ml-1">Collapse</span>
                    </button>
                    <button @click="$dispatch('open-modal', 'add-asn')" class="btn btn-sm bg-purple-600 hover:bg-purple-700 text-white border-0">
                        <span class="iconify" data-icon="mdi:account-plus" data-width="12" data-height="12"></span>
                        <span class="ml-1">Pegawai</span>
                    </button>
                    <button @click="$dispatch('open-modal', 'add-jabatan')" class="btn btn-sm btn-primary">
                        <span class="iconify" data-icon="mdi:plus" data-width="12" data-height="12"></span>
                        <span class="ml-1">Jabatan</span>
                    </button>
                    <button @click="$dispatch('open-modal', 'add-bagian')" class="btn btn-sm btn-success">
                        <span class="iconify" data-icon="mdi:folder-plus" data-width="12" data-height="12"></span>
                        <span class="ml-1">Bagian</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-4" role="tree" aria-label="Struktur Organisasi {{ $opd->nama }}">
                <div class="space-y-2">
                    <!-- Kepala OPD -->
                    @foreach($opd->jabatanKepala as $jabatan)
                        @include('opds.partials.tree-jabatan', [
                            'jabatan' => $jabatan,
                            'type' => 'kepala',
                            'level' => 0
                        ])
                    @endforeach

                    <!-- Bagian -->
                    @foreach($opd->bagians->where('parent_id', null) as $bagian)
                        @include('opds.partials.tree-bagian', [
                            'bagian' => $bagian,
                            'level' => 0
                        ])
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <!-- Compact Footer -->
    <footer class="bg-gray-800 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                <span>&copy; {{ date('Y') }} Sistem Peta Jabatan</span>
                <span class="text-gray-500">Laravel {{ app()->version() }}</span>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <x-modal name="delete-opd" title="Hapus OPD" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus OPD?</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus <strong>{{ $opd->nama }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
            <form action="{{ route('opds.destroy', $opd->id) }}" method="POST" class="flex gap-2 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('close-modal', 'delete-opd')" class="btn btn-outline">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                    <span class="ml-1">Hapus</span>
                </button>
            </form>
        </div>
    </x-modal>

    <x-modal name="add-bagian" title="Tambah Bagian" maxWidth="lg">
        <form action="{{ route('opds.bagian.store', $opd->id) }}" method="POST">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bagian</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Bagian Umum">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Bagian (Opsional)</label>
                    <select name="parent_id" class="input w-full">
                        <option value="">-- Tidak Ada Parent --</option>
                        @foreach($opd->bagians as $bagian)
                            <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'add-bagian')" class="btn btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <span class="iconify" data-icon="mdi:plus" data-width="14" data-height="14"></span>
                        <span class="ml-1">Tambah</span>
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <x-modal name="add-jabatan" title="Tambah Jabatan" maxWidth="lg">
        <form action="{{ route('opds.jabatan.store', $opd->id) }}" method="POST">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Kepala Bagian">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bagian</label>
                    <select name="bagian_id" class="input w-full">
                        <option value="">-- Jabatan Kepala (Tanpa Bagian) --</option>
                        @foreach($opd->bagians as $bagian)
                            <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                        <input type="text" name="jenis_jabatan" class="input w-full" placeholder="Contoh: Struktural">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <input type="number" name="kelas" class="input w-full" placeholder="Contoh: 9">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan ASN</label>
                    <input type="number" name="kebutuhan" required class="input w-full" placeholder="Contoh: 1" value="1">
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'add-jabatan')" class="btn btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:plus" data-width="14" data-height="14"></span>
                        <span class="ml-1">Tambah</span>
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <x-modal name="add-asn" title="Tambah Pegawai/ASN" maxWidth="lg">
        <form action="{{ route('opds.asn.store', $opd->id) }}" method="POST">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Dr. Budi Santoso, S.Kom, M.T">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <input type="text" name="nip" required class="input w-full" placeholder="Contoh: 199001012015031001">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <select name="jabatan_id" id="jabatan_asn_select" required class="input w-full">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($opd->jabatanKepala as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} (Kepala)</option>
                        @endforeach
                        @foreach($opd->bagians as $bagian)
                            <optgroup label="{{ $bagian->nama }}">
                                @foreach($bagian->jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (Opsional)</label>
                    <input type="email" name="email" class="input w-full" placeholder="Contoh: budi.santoso@pemda.go.id">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon (Opsional)</label>
                    <input type="text" name="phone" class="input w-full" placeholder="Contoh: 08123456789">
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'add-asn')" class="btn btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <span class="iconify" data-icon="mdi:account-plus" data-width="14" data-height="14"></span>
                        <span class="ml-1">Tambah Pegawai</span>
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-asn" title="Edit Pegawai/ASN" maxWidth="lg">
        <form id="editAsnForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_asn_nama" required class="input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <input type="text" name="nip" id="edit_asn_nip" required class="input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <select name="jabatan_id" id="edit_asn_jabatan" required class="input w-full">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($opd->jabatanKepala as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} (Kepala)</option>
                        @endforeach
                        @foreach($opd->bagians as $bagian)
                            <optgroup label="{{ $bagian->nama }}">
                                @foreach($bagian->jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'edit-asn')" class="btn btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:content-save" data-width="14" data-height="14"></span>
                        <span class="ml-1">Simpan</span>
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-asn" title="Hapus Pegawai/ASN" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus Pegawai?</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus <strong id="delete_asn_nama"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deleteAsnForm" method="POST" class="flex gap-2 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('close-modal', 'delete-asn')" class="btn btn-outline">
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
function opdShow() {
    return {
        editingNama: false,
        expandedNodes: new Set(),

        toggleNode(id) {
            if (this.expandedNodes.has(id)) {
                this.expandedNodes.delete(id);
            } else {
                this.expandedNodes.add(id);
            }
        },

        isExpanded(id) {
            return this.expandedNodes.has(id);
        },

        expandAll() {
            document.querySelectorAll('[data-tree-node]').forEach(node => {
                this.expandedNodes.add(node.dataset.treeNode);
            });
        },

        collapseAll() {
            this.expandedNodes.clear();
        }
    }
}

// Handle ASN events from tree
window.addEventListener('edit-asn', function(e) {
    const { id, nama, nip, jabatanId } = e.detail;

    // Populate form
    document.getElementById('edit_asn_nama').value = nama;
    document.getElementById('edit_asn_nip').value = nip;
    document.getElementById('edit_asn_jabatan').value = jabatanId;

    // Set form action
    document.getElementById('editAsnForm').action = `/opds/{{ $opd->id }}/asn/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-asn' }));
});

window.addEventListener('delete-asn', function(e) {
    const { id, nama } = e.detail;

    // Set nama
    document.getElementById('delete_asn_nama').textContent = nama;

    // Set form action
    document.getElementById('deleteAsnForm').action = `/opds/{{ $opd->id }}/asn/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-asn' }));
});

window.addEventListener('add-asn', function(e) {
    const { jabatanId } = e.detail;

    // Pre-select jabatan if provided
    if (jabatanId) {
        document.getElementById('jabatan_asn_select').value = jabatanId;
    }

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-asn' }));
});
</script>
@endpush
@endsection
