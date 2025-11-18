@extends('admin.layouts.app')

@section('title', 'Detail OPD - ' . $opd->nama)
@section('page-title', 'Detail OPD')

@section('content')
<div class="p-4 lg:p-8" x-data="opdShow()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.opds.index') }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900" x-show="!editingNama">{{ $opd->nama }}</h2>
                <form action="{{ route('admin.opds.update', $opd->id) }}" method="POST" x-show="editingNama" @submit="editingNama = false" class="flex items-center gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="nama" value="{{ $opd->nama }}" class="input text-gray-900" required>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    <button type="button" @click="editingNama = false" class="btn btn-outline btn-sm">Batal</button>
                </form>
            </div>
            <div class="flex gap-2">
                <button @click="editingNama = !editingNama" class="btn btn-outline" x-show="!editingNama">
                    <span class="iconify" data-icon="mdi:pencil" data-width="18" data-height="18"></span>
                    <span class="ml-2">Edit Nama</span>
                </button>
                <a href="{{ route('admin.opds.peta-jabatan', $opd->id) }}" class="btn" style="background-color: #8b5cf6; border-color: #8b5cf6; color: white;">
                    <span class="iconify" data-icon="mdi:file-tree" data-width="18" data-height="18"></span>
                    <span class="ml-2">Peta Jabatan</span>
                </a>
                <a href="{{ route('admin.opds.export', $opd->id) }}" class="btn btn-primary">
                    <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                    <span class="ml-2">Export</span>
                </a>
            </div>
        </div>
    </div>

    <div>
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

        @if ($errors->any())
            <div class="alert alert-error mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card bg-white shadow-sm">
                <div class="card-body">
                    <div class="stat">
                        <div class="stat-title flex items-center gap-2">
                            <span class="iconify text-blue-500" data-icon="mdi:briefcase" data-width="20" data-height="20"></span>
                            <span class="stat-label">Total Jabatan</span>
                        </div>
                        <div class="stat-value text-primary-600">{{ $opd->getAllJabatans()->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="card bg-white shadow-sm">
                <div class="card-body">
                    <div class="stat">
                        <div class="stat-title flex items-center gap-2">
                            <span class="iconify text-green-500" data-icon="mdi:account-group" data-width="20" data-height="20"></span>
                            <span class="stat-label">Total ASN</span>
                        </div>
                        <div class="stat-value text-green-600">{{ $opd->asns->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="card bg-white shadow-sm">
                <div class="card-body">
                    <div class="stat">
                        <div class="stat-title flex items-center gap-2">
                            <span class="iconify text-purple-500" data-icon="mdi:chart-line" data-width="20" data-height="20"></span>
                            <span class="stat-label">Pemenuhan</span>
                        </div>
                        @php
                            $totalKebutuhan = $opd->getAllJabatans()->sum('kebutuhan');
                            $persentase = $totalKebutuhan > 0 ? round(($opd->asns->count() / $totalKebutuhan) * 100, 1) : 0;
                        @endphp
                        <div class="stat-value text-purple-600">{{ $persentase }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Struktur Organisasi -->
        <div class="card bg-white shadow-sm">
            <div class="card-header flex justify-between items-center">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <span class="iconify text-blue-500" data-icon="mdi:file-tree" data-width="20" data-height="20"></span>
                    Struktur Organisasi
                </h3>
                <button @click="$dispatch('open-modal', 'add-jabatan')" class="btn btn-sm btn-primary">
                    <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                    <span class="ml-1">Jabatan</span>
                </button>
            </div>
            <div class="card-body">
                @if($opd->jabatanKepala->count() > 0)
                    <div class="tree">
                        <!-- Root Jabatan -->
                        @foreach($opd->jabatanKepala as $jabatan)
                            @include('opds.partials.tree-jabatan', [
                                'jabatan' => $jabatan,
                                'opd' => $opd,
                                'level' => 0
                            ])
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <span class="iconify text-gray-300" data-icon="mdi:file-tree" data-width="64" data-height="64"></span>
                        <p class="mt-4 text-gray-500">
                            Mulai membangun struktur organisasi {{ $opd->nama }} dengan menambahkan jabatan
                        </p>
                        <div class="mt-6">
                            <button @click="$dispatch('open-modal', 'add-jabatan')" class="btn btn-primary">
                                <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                                <span class="ml-2">Tambah Jabatan</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal: Add Jabatan -->
    <x-modal name="add-jabatan" title="Tambah Jabatan" maxWidth="lg">
        <form action="{{ route('admin.opds.jabatan.store', $opd->id) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Kepala Bidang Umum">
                </div>

                <div x-data="{ selectedParentId: '', selectedParentName: 'Tidak ada (Jabatan Root)' }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan ini berada di bawah</label>
                    <input type="hidden" name="parent_jabatan_id" x-model="selectedParentId">

                    <div class="border border-gray-300 rounded-md max-h-60 overflow-y-auto">
                        <div @click="selectedParentId = ''; selectedParentName = 'Tidak ada (Jabatan Root)'"
                             :class="selectedParentId == '' ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'"
                             class="p-3 cursor-pointer border-b border-gray-100 flex items-center">
                            <span class="iconify text-gray-400 mr-2" data-icon="mdi:domain" data-width="16" data-height="16"></span>
                            <span class="text-sm font-medium text-gray-700">Tidak ada (Jabatan Root/Kepala)</span>
                            <span x-show="selectedParentId == ''" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                        </div>

                        @if($opd->getAllJabatans()->count() > 0)
                            @php
                                function renderJabatanTree($jabatans, $parentId = null, $level = 0) {
                                    $filtered = $jabatans->where('parent_id', $parentId);
                                    foreach ($filtered as $jabatan) {
                                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                        echo '<div @click="selectedParentId = \'' . $jabatan->id . '\'; selectedParentName = \'' . addslashes($jabatan->nama) . '\'"
                                                   :class="selectedParentId == \'' . $jabatan->id . '\' ? \'bg-blue-50 border-l-4 border-blue-500\' : \'hover:bg-gray-50\'"
                                                   class="p-3 cursor-pointer border-b border-gray-100 flex items-center">';

                                        if ($level > 0) {
                                            echo '<span class="text-gray-300 mr-2">' . $indent . '└─</span>';
                                        }

                                        echo '<span class="iconify text-blue-400 mr-2" data-icon="mdi:briefcase-outline" data-width="16" data-height="16"></span>';
                                        echo '<span class="text-sm text-gray-700">' . e($jabatan->nama) . '</span>';
                                        echo '<span x-show="selectedParentId == \'' . $jabatan->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                        echo '</div>';

                                        renderJabatanTree($jabatans, $jabatan->id, $level + 1);
                                    }
                                }
                                renderJabatanTree($opd->getAllJabatans());
                            @endphp
                        @endif
                    </div>

                    <div class="mt-2 text-sm text-gray-600">
                        Dipilih: <span class="font-medium" x-text="selectedParentName"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                    <select name="jenis_jabatan" required class="input w-full">
                        <option value="">Pilih Jenis Jabatan</option>
                        <option value="Struktural">Struktural</option>
                        <option value="Fungsional">Fungsional</option>
                        <option value="Pelaksana">Pelaksana</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Jabatan</label>
                    <input type="number" name="kelas" class="input w-full" placeholder="Contoh: 9" min="1" max="17">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan</label>
                    <input type="number" name="kebutuhan" required class="input w-full" placeholder="Jumlah kebutuhan" min="0" value="1">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'add-jabatan')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Jabatan</button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Edit Jabatan -->
    <x-modal name="edit-jabatan" title="Edit Jabatan" maxWidth="lg">
        <template x-if="editJabatan">
            <form :action="`{{ route('admin.opds.show', $opd->id) }}/jabatan/${editJabatan.id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                        <input type="text" name="nama" x-model="editJabatan.nama" required class="input w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Jabatan</label>
                        <select name="parent_jabatan_id" x-model="editJabatan.parent_id" class="input w-full">
                            <option value="">Tidak ada (Jabatan Root/Kepala)</option>
                            @foreach($opd->getAllJabatans() as $j)
                                <option value="{{ $j->id }}" x-bind:disabled="editJabatan && editJabatan.id == {{ $j->id }}">
                                    {{ str_repeat('—', $j->getPath()->count() - 1) }} {{ $j->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                        <select name="jenis_jabatan" x-model="editJabatan.jenis_jabatan" required class="input w-full">
                            <option value="Struktural">Struktural</option>
                            <option value="Fungsional">Fungsional</option>
                            <option value="Pelaksana">Pelaksana</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Jabatan</label>
                        <input type="number" name="kelas" x-model="editJabatan.kelas" class="input w-full" min="1" max="17">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan</label>
                        <input type="number" name="kebutuhan" x-model="editJabatan.kebutuhan" required class="input w-full" min="0">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'edit-jabatan')" class="btn btn-outline">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </template>
    </x-modal>

    <!-- Modal: Add ASN -->
    <x-modal name="add-asn" title="Tambah ASN" maxWidth="lg">
        <form action="{{ route('admin.opds.asn.store', $opd->id) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Nama lengkap ASN">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <input type="text" name="nip" required class="input w-full" placeholder="NIP">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <select name="jabatan_id" required class="input w-full">
                        <option value="">Pilih Jabatan</option>
                        @foreach($opd->getAllJabatans() as $jabatan)
                            <option value="{{ $jabatan->id }}">
                                {{ str_repeat('—', $jabatan->getPath()->count() - 1) }} {{ $jabatan->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'add-asn')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah ASN</button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Edit ASN -->
    <x-modal name="edit-asn" title="Edit ASN" maxWidth="lg">
        <template x-if="editAsn">
            <form :action="`{{ route('admin.opds.show', $opd->id) }}/asn/${editAsn.id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" name="nama" x-model="editAsn.nama" required class="input w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <input type="text" name="nip" x-model="editAsn.nip" required class="input w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <select name="jabatan_id" x-model="editAsn.jabatan_id" required class="input w-full">
                            <option value="">Pilih Jabatan</option>
                            @foreach($opd->getAllJabatans() as $jabatan)
                                <option value="{{ $jabatan->id }}">
                                    {{ str_repeat('—', $jabatan->getPath()->count() - 1) }} {{ $jabatan->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'edit-asn')" class="btn btn-outline">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </template>
    </x-modal>
</div>

@push('scripts')
<script>
function opdShow() {
    return {
        editingNama: false,
        editJabatan: null,
        editAsn: null,

        init() {
            // Listen for edit jabatan event
            this.$el.addEventListener('edit-jabatan', (e) => {
                this.editJabatan = e.detail;
                this.$dispatch('open-modal', 'edit-jabatan');
            });

            // Listen for edit asn event
            this.$el.addEventListener('edit-asn', (e) => {
                this.editAsn = e.detail;
                this.$dispatch('open-modal', 'edit-asn');
            });
        }
    }
}
</script>
@endpush
@endsection
