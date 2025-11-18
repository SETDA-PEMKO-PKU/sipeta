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
                <h2 class="text-sm font-semibold text-gray-900 flex items-center">
                    <span class="iconify" data-icon="mdi:file-tree" data-width="16" data-height="16"></span>
                    <span class="ml-2">Struktur Organisasi</span>
                </h2>
                <div class="flex flex-wrap gap-2">
                    <button @click="$dispatch('open-modal', 'add-asn')" class="btn btn-sm" style="background-color: #9333ea; border-color: #9333ea; color: white;">
                        <span class="iconify" data-icon="mdi:account-plus" data-width="14" data-height="14"></span>
                        <span class="ml-1">Pegawai</span>
                    </button>
                    <button @click="$dispatch('open-modal', 'add-jabatan')" class="btn btn-primary btn-sm">
                        <span class="iconify" data-icon="mdi:plus" data-width="14" data-height="14"></span>
                        <span class="ml-1">Jabatan</span>
                    </button>
                    <button @click="$dispatch('open-modal', 'add-bagian')" class="btn btn-sm" style="background-color: #10b981; border-color: #10b981; color: white;">
                        <span class="iconify" data-icon="mdi:folder-plus" data-width="14" data-height="14"></span>
                        <span class="ml-1">Bagian</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-4" role="tree" aria-label="Struktur Organisasi {{ $opd->nama }}">
                @if($opd->jabatanKepala->count() > 0 || $opd->bagians->count() > 0)
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
                @else
                    <!-- Empty State -->
                    <div class="py-16 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                            <span class="iconify text-gray-400" data-icon="mdi:file-tree-outline" data-width="32" data-height="32"></span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Struktur Organisasi</h3>
                        <p class="text-sm text-gray-600 mb-6 max-w-md mx-auto">
                            Mulai membangun struktur organisasi {{ $opd->nama }} dengan menambahkan bagian atau jabatan
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <button @click="$dispatch('open-modal', 'add-bagian')" class="btn btn-primary">
                                <span class="iconify" data-icon="mdi:folder-plus" data-width="18" data-height="18"></span>
                                <span class="ml-2">Tambah Bagian</span>
                            </button>
                            <button @click="$dispatch('open-modal', 'add-jabatan')" class="btn btn-outline">
                                <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                                <span class="ml-2">Tambah Jabatan</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    <x-modal name="delete-opd" title="Hapus OPD" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus OPD?</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus <strong>{{ $opd->nama }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
            <form action="{{ route('admin.opds.destroy', $opd->id) }}" method="POST" class="flex gap-2 justify-center">
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
        <form action="{{ route('admin.opds.bagian.store', $opd->id) }}" method="POST">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bagian</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Bagian Umum">
                </div>
                <div x-data="{ selectedParentId: '', selectedParentName: 'Tidak ada (Bagian Utama)' }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bagian ini berada di bawah</label>
                    <input type="hidden" name="parent_id" x-model="selectedParentId">

                    <div class="border border-gray-300 rounded-lg p-3 bg-white max-h-60 overflow-y-auto">
                        <!-- Opsi Tidak Ada Parent -->
                        <div @click="selectedParentId = ''; selectedParentName = 'Tidak ada (Bagian Utama)'"
                             :class="selectedParentId === '' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                             class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">
                            <span class="iconify text-gray-400" data-icon="mdi:home" data-width="16" data-height="16"></span>
                            <span class="text-sm font-medium text-gray-700">Tidak ada (Bagian Utama)</span>
                            <span x-show="selectedParentId === ''" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                        </div>

                        @if($opd->bagians->count() > 0)
                            <div class="mt-2 space-y-1">
                                @php
                                    function renderBagianTree($bagians, $parentId = null, $level = 0) {
                                        $filtered = $bagians->where('parent_id', $parentId);
                                        foreach ($filtered as $bagian) {
                                            $indent = str_repeat('─', $level * 2);
                                            echo '<div @click="selectedParentId = \'' . $bagian->id . '\'; selectedParentName = \'' . addslashes($bagian->nama) . '\'"
                                                       :class="selectedParentId == \'' . $bagian->id . '\' ? \'bg-blue-50 border-blue-500\' : \'hover:bg-gray-50\'"
                                                       class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">';

                                            if ($level > 0) {
                                                echo '<span class="text-gray-400 text-xs ml-' . ($level * 4) . '">' . $indent . '</span>';
                                            }

                                            echo '<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="16" data-height="16"></span>';
                                            echo '<span class="text-sm text-gray-700">' . e($bagian->nama) . '</span>';
                                            echo '<span x-show="selectedParentId == \'' . $bagian->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                            echo '</div>';

                                            renderBagianTree($bagians, $bagian->id, $level + 1);
                                        }
                                    }
                                    renderBagianTree($opd->bagians);
                                @endphp
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="selectedParentName" class="font-semibold"></span>
                        </span>
                    </div>
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

    <x-modal name="edit-bagian" title="Edit Bagian" maxWidth="lg">
        <form id="editBagianForm" method="POST" x-data="{ editParentId: '', editParentName: 'Tidak ada (Bagian Utama)', currentBagianId: '' }">
            @csrf
            @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bagian</label>
                    <input type="text" name="nama" id="edit_bagian_nama" required class="input w-full" placeholder="Contoh: Bagian Umum">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bagian ini berada di bawah</label>
                    <input type="hidden" name="parent_id" x-model="editParentId">

                    <div class="border border-gray-300 rounded-lg p-3 bg-white max-h-60 overflow-y-auto">
                        <!-- Opsi Tidak Ada Parent -->
                        <div @click="editParentId = ''; editParentName = 'Tidak ada (Bagian Utama)'"
                             :class="editParentId === '' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                             class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">
                            <span class="iconify text-gray-400" data-icon="mdi:home" data-width="16" data-height="16"></span>
                            <span class="text-sm font-medium text-gray-700">Tidak ada (Bagian Utama)</span>
                            <span x-show="editParentId === ''" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                        </div>

                        @if($opd->bagians->count() > 0)
                            <div class="mt-2 space-y-1">
                                @php
                                    function renderEditBagianTree($bagians, $parentId = null, $level = 0) {
                                        $filtered = $bagians->where('parent_id', $parentId);
                                        foreach ($filtered as $bagian) {
                                            $indent = str_repeat('─', $level * 2);
                                            echo '<div @click="if (currentBagianId != \'' . $bagian->id . '\') { editParentId = \'' . $bagian->id . '\'; editParentName = \'' . addslashes($bagian->nama) . '\'; }"
                                                       :class="editParentId == \'' . $bagian->id . '\' ? \'bg-blue-50 border-blue-500\' : (currentBagianId == \'' . $bagian->id . '\' ? \'bg-gray-100 cursor-not-allowed opacity-50\' : \'hover:bg-gray-50\')"
                                                       class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">';

                                            if ($level > 0) {
                                                echo '<span class="text-gray-400 text-xs ml-' . ($level * 4) . '">' . $indent . '</span>';
                                            }

                                            echo '<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="16" data-height="16"></span>';
                                            echo '<span class="text-sm text-gray-700">' . e($bagian->nama) . '</span>';
                                            echo '<span x-show="currentBagianId == \'' . $bagian->id . '\'" class="text-xs text-gray-500 ml-auto">(Bagian ini)</span>';
                                            echo '<span x-show="editParentId == \'' . $bagian->id . '\' && currentBagianId != \'' . $bagian->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                            echo '</div>';

                                            renderEditBagianTree($bagians, $bagian->id, $level + 1);
                                        }
                                    }
                                    renderEditBagianTree($opd->bagians);
                                @endphp
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="editParentName" class="font-semibold"></span>
                        </span>
                    </div>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'edit-bagian')" class="btn btn-outline">
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

    <x-modal name="delete-bagian" title="Hapus Bagian" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus Bagian?</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus <strong id="delete_bagian_nama"></strong>? Semua sub bagian dan jabatan di dalamnya juga akan terhapus. Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deleteBagianForm" method="POST" class="flex gap-2 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('close-modal', 'delete-bagian')" class="btn btn-outline">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                    <span class="ml-1">Hapus</span>
                </button>
            </form>
        </div>
    </x-modal>

    <x-modal name="add-jabatan" title="Tambah Jabatan" maxWidth="lg">
        <form action="{{ route('admin.opds.jabatan.store', $opd->id) }}" method="POST" x-data="{ selectedBagianId: '', selectedBagianName: 'Jabatan Kepala (Tanpa Bagian)' }">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Kepala Bagian">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan ini berada di bagian</label>
                    <input type="hidden" name="bagian_id" x-model="selectedBagianId">

                    <div class="border border-gray-300 rounded-lg p-3 bg-white max-h-60 overflow-y-auto">
                        <!-- Opsi Jabatan Kepala -->
                        <div @click="selectedBagianId = ''; selectedBagianName = 'Jabatan Kepala (Tanpa Bagian)'"
                             :class="selectedBagianId === '' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                             class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">
                            <span class="iconify text-purple-600" data-icon="mdi:account-star" data-width="16" data-height="16"></span>
                            <span class="text-sm font-medium text-gray-700">Jabatan Kepala (Tanpa Bagian)</span>
                            <span x-show="selectedBagianId === ''" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                        </div>

                        @if($opd->bagians->count() > 0)
                            <div class="mt-2 space-y-1">
                                @php
                                    function renderJabatanBagianTree($bagians, $parentId = null, $level = 0) {
                                        $filtered = $bagians->where('parent_id', $parentId);
                                        foreach ($filtered as $bagian) {
                                            $indent = str_repeat('─', $level * 2);
                                            echo '<div @click="selectedBagianId = \'' . $bagian->id . '\'; selectedBagianName = \'' . addslashes($bagian->nama) . '\'"
                                                       :class="selectedBagianId == \'' . $bagian->id . '\' ? \'bg-blue-50 border-blue-500\' : \'hover:bg-gray-50\'"
                                                       class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">';

                                            if ($level > 0) {
                                                echo '<span class="text-gray-400 text-xs ml-' . ($level * 4) . '">' . $indent . '</span>';
                                            }

                                            echo '<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="16" data-height="16"></span>';
                                            echo '<span class="text-sm text-gray-700">' . e($bagian->nama) . '</span>';
                                            echo '<span x-show="selectedBagianId == \'' . $bagian->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                            echo '</div>';

                                            renderJabatanBagianTree($bagians, $bagian->id, $level + 1);
                                        }
                                    }
                                    renderJabatanBagianTree($opd->bagians);
                                @endphp
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="selectedBagianName" class="font-semibold"></span>
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                        <select name="jenis_jabatan" class="input w-full">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Struktural">Struktural</option>
                            <option value="Fungsional">Fungsional</option>
                            <option value="Pelaksana">Pelaksana</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Jabatan</label>
                        <select name="kelas" class="input w-full">
                            <option value="">-- Pilih Kelas --</option>
                            <option value="17">17 - Eselon I.a (Pimpinan Tertinggi)</option>
                            <option value="16">16 - Eselon I.b</option>
                            <option value="15">15 - Eselon II.a</option>
                            <option value="14">14 - Eselon II.b</option>
                            <option value="13">13 - Eselon III.a</option>
                            <option value="12">12 - Eselon III.b</option>
                            <option value="11">11 - Eselon IV.a</option>
                            <option value="10">10 - Eselon IV.b</option>
                            <option value="9">9 - Eselon V.a</option>
                            <option value="8">8 - Pelaksana/Staf</option>
                            <option value="7">7 - Pelaksana/Staf</option>
                            <option value="6">6 - Pelaksana/Staf</option>
                            <option value="5">5 - Pelaksana/Staf</option>
                            <option value="4">4 - Pelaksana/Staf</option>
                            <option value="3">3 - Pelaksana/Staf</option>
                            <option value="2">2 - Pelaksana/Staf</option>
                            <option value="1">1 - Pelaksana/Staf</option>
                        </select>
                    </div>
                </div>
                <div x-data="{ kebutuhan: 1 }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan ASN</label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="if(kebutuhan > 1) kebutuhan--" class="btn btn-outline w-10 h-10 flex items-center justify-center">
                            <span class="iconify" data-icon="mdi:minus" data-width="18" data-height="18"></span>
                        </button>
                        <input type="number" name="kebutuhan" x-model="kebutuhan" required min="1" class="input w-full text-center font-semibold text-lg" readonly>
                        <button type="button" @click="kebutuhan++" class="btn btn-outline w-10 h-10 flex items-center justify-center">
                            <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                        </button>
                    </div>
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

    <x-modal name="edit-jabatan" title="Edit Jabatan" maxWidth="lg">
        <form id="editJabatanForm" method="POST" x-data="{ editJabatanBagianId: '', editJabatanBagianName: 'Jabatan Kepala (Tanpa Bagian)' }">
            @csrf
            @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                    <input type="text" name="nama" id="edit_jabatan_nama" required class="input w-full" placeholder="Contoh: Kepala Bagian">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan ini berada di bagian</label>
                    <input type="hidden" name="bagian_id" x-model="editJabatanBagianId">

                    <div class="border border-gray-300 rounded-lg p-3 bg-white max-h-60 overflow-y-auto">
                        <!-- Opsi Jabatan Kepala -->
                        <div @click="editJabatanBagianId = ''; editJabatanBagianName = 'Jabatan Kepala (Tanpa Bagian)'"
                             :class="editJabatanBagianId === '' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                             class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">
                            <span class="iconify text-purple-600" data-icon="mdi:account-star" data-width="16" data-height="16"></span>
                            <span class="text-sm font-medium text-gray-700">Jabatan Kepala (Tanpa Bagian)</span>
                            <span x-show="editJabatanBagianId === ''" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                        </div>

                        @if($opd->bagians->count() > 0)
                            <div class="mt-2 space-y-1">
                                @php
                                    function renderEditJabatanBagianTree($bagians, $parentId = null, $level = 0) {
                                        $filtered = $bagians->where('parent_id', $parentId);
                                        foreach ($filtered as $bagian) {
                                            $indent = str_repeat('─', $level * 2);
                                            echo '<div @click="editJabatanBagianId = \'' . $bagian->id . '\'; editJabatanBagianName = \'' . addslashes($bagian->nama) . '\'"
                                                       :class="editJabatanBagianId == \'' . $bagian->id . '\' ? \'bg-blue-50 border-blue-500\' : \'hover:bg-gray-50\'"
                                                       class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">';

                                            if ($level > 0) {
                                                echo '<span class="text-gray-400 text-xs ml-' . ($level * 4) . '">' . $indent . '</span>';
                                            }

                                            echo '<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="16" data-height="16"></span>';
                                            echo '<span class="text-sm text-gray-700">' . e($bagian->nama) . '</span>';
                                            echo '<span x-show="editJabatanBagianId == \'' . $bagian->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                            echo '</div>';

                                            renderEditJabatanBagianTree($bagians, $bagian->id, $level + 1);
                                        }
                                    }
                                    renderEditJabatanBagianTree($opd->bagians);
                                @endphp
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="editJabatanBagianName" class="font-semibold"></span>
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                        <select name="jenis_jabatan" id="edit_jabatan_jenis" class="input w-full">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Struktural">Struktural</option>
                            <option value="Fungsional">Fungsional</option>
                            <option value="Pelaksana">Pelaksana</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Jabatan</label>
                        <select name="kelas" id="edit_jabatan_kelas" class="input w-full">
                            <option value="">-- Pilih Kelas --</option>
                            <option value="17">17 - Eselon I.a (Pimpinan Tertinggi)</option>
                            <option value="16">16 - Eselon I.b</option>
                            <option value="15">15 - Eselon II.a</option>
                            <option value="14">14 - Eselon II.b</option>
                            <option value="13">13 - Eselon III.a</option>
                            <option value="12">12 - Eselon III.b</option>
                            <option value="11">11 - Eselon IV.a</option>
                            <option value="10">10 - Eselon IV.b</option>
                            <option value="9">9 - Eselon V.a</option>
                            <option value="8">8 - Pelaksana/Staf</option>
                            <option value="7">7 - Pelaksana/Staf</option>
                            <option value="6">6 - Pelaksana/Staf</option>
                            <option value="5">5 - Pelaksana/Staf</option>
                            <option value="4">4 - Pelaksana/Staf</option>
                            <option value="3">3 - Pelaksana/Staf</option>
                            <option value="2">2 - Pelaksana/Staf</option>
                            <option value="1">1 - Pelaksana/Staf</option>
                        </select>
                    </div>
                </div>
                <div x-data="{ editKebutuhan: 1 }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan ASN</label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="if(editKebutuhan > 1) editKebutuhan--" class="btn btn-outline w-10 h-10 flex items-center justify-center">
                            <span class="iconify" data-icon="mdi:minus" data-width="18" data-height="18"></span>
                        </button>
                        <input type="number" name="kebutuhan" id="edit_jabatan_kebutuhan" x-model="editKebutuhan" required min="1" class="input w-full text-center font-semibold text-lg" readonly>
                        <button type="button" @click="editKebutuhan++" class="btn btn-outline w-10 h-10 flex items-center justify-center">
                            <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                        </button>
                    </div>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'edit-jabatan')" class="btn btn-outline">
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

    <x-modal name="delete-jabatan" title="Hapus Jabatan" maxWidth="md">
        <div class="text-center">
            <span class="iconify text-red-500 mx-auto" data-icon="mdi:alert-circle" data-width="48" data-height="48"></span>
            <h3 class="text-lg font-semibold text-gray-900 mt-3 mb-2">Hapus Jabatan?</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus jabatan <strong id="delete_jabatan_nama"></strong>? Semua ASN yang terkait dengan jabatan ini juga akan terhapus. Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deleteJabatanForm" method="POST" class="flex gap-2 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('close-modal', 'delete-jabatan')" class="btn btn-outline">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                    <span class="ml-1">Hapus</span>
                </button>
            </form>
        </div>
    </x-modal>

    <x-modal name="add-asn" title="Tambah Pegawai/ASN" maxWidth="lg">
        <form action="{{ route('admin.opds.asn.store', $opd->id) }}" method="POST" x-data="{ selectedJabatanId: '', selectedJabatanName: '' }">
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
                    <input type="hidden" name="jabatan_id" id="jabatan_asn_select" x-model="selectedJabatanId" required>

                    <div class="border border-gray-300 rounded-lg p-3 bg-white max-h-60 overflow-y-auto">
                        @if($opd->jabatanKepala->count() > 0 || $opd->bagians->count() > 0)
                            <div class="space-y-1">
                                <!-- Jabatan Kepala -->
                                @foreach($opd->jabatanKepala as $jabatan)
                                    <div @click="selectedJabatanId = '{{ $jabatan->id }}'; selectedJabatanName = '{{ addslashes($jabatan->nama) }} (Kepala)'"
                                         :class="selectedJabatanId == '{{ $jabatan->id }}' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                                         class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">
                                        <span class="iconify text-purple-600" data-icon="mdi:account-star" data-width="16" data-height="16"></span>
                                        <span class="text-sm font-medium text-gray-700">{{ $jabatan->nama }}</span>
                                        <span class="text-xs text-purple-600 font-medium">(Kepala)</span>
                                        <span x-show="selectedJabatanId == '{{ $jabatan->id }}'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                                    </div>
                                @endforeach

                                <!-- Jabatan per Bagian -->
                                @php
                                    if (!function_exists('renderAsnJabatanTree')) {
                                        function renderAsnJabatanTree($bagian, $opd, $level = 0) {
                                            $indent = str_repeat('─', $level * 2);

                                            // Show bagian header
                                            echo '<div class="flex items-center gap-2 p-2 bg-gray-50 rounded mt-2">';
                                            if ($level > 0) {
                                                echo '<span class="text-gray-400 text-xs ml-' . ($level * 4) . '">' . $indent . '</span>';
                                            }
                                            echo '<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="14" data-height="14"></span>';
                                            echo '<span class="text-xs font-semibold text-gray-600">' . e($bagian->nama) . '</span>';
                                            echo '</div>';

                                            // Show jabatans in this bagian
                                            foreach ($bagian->jabatans as $jabatan) {
                                                $indentJabatan = str_repeat('─', ($level + 1) * 2);
                                                echo '<div @click="selectedJabatanId = \'' . $jabatan->id . '\'; selectedJabatanName = \'' . addslashes($jabatan->nama) . ' (' . addslashes($bagian->nama) . ')\'"
                                                           :class="selectedJabatanId == \'' . $jabatan->id . '\' ? \'bg-blue-50 border-blue-500\' : \'hover:bg-gray-50\'"
                                                           class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors ml-' . (($level + 1) * 4) . '">';
                                                echo '<span class="text-gray-400 text-xs">' . $indentJabatan . '</span>';
                                                echo '<span class="iconify text-green-600" data-icon="mdi:account-tie" data-width="14" data-height="14"></span>';
                                                echo '<span class="text-sm text-gray-700">' . e($jabatan->nama) . '</span>';
                                                echo '<span x-show="selectedJabatanId == \'' . $jabatan->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                                echo '</div>';
                                            }

                                            // Recursively show sub bagians
                                            $subBagians = $opd->bagians->where('parent_id', $bagian->id);
                                            foreach ($subBagians as $subBagian) {
                                                renderAsnJabatanTree($subBagian, $opd, $level + 1);
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($opd->bagians->where('parent_id', null) as $bagian)
                                    @php renderAsnJabatanTree($bagian, $opd); @endphp
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Belum ada jabatan tersedia</p>
                        @endif
                    </div>
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2" x-show="selectedJabatanName">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="selectedJabatanName" class="font-semibold"></span>
                        </span>
                    </div>
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
        <form id="editAsnForm" method="POST" x-data="{ editAsnJabatanId: '', editAsnJabatanName: '' }">
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
                    <input type="hidden" name="jabatan_id" id="edit_asn_jabatan" x-model="editAsnJabatanId" required>

                    <div class="border border-gray-300 rounded-lg p-3 bg-white max-h-60 overflow-y-auto">
                        @if($opd->jabatanKepala->count() > 0 || $opd->bagians->count() > 0)
                            <div class="space-y-1">
                                <!-- Jabatan Kepala -->
                                @foreach($opd->jabatanKepala as $jabatan)
                                    <div @click="editAsnJabatanId = '{{ $jabatan->id }}'; editAsnJabatanName = '{{ addslashes($jabatan->nama) }} (Kepala)'"
                                         :class="editAsnJabatanId == '{{ $jabatan->id }}' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'"
                                         class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors">
                                        <span class="iconify text-purple-600" data-icon="mdi:account-star" data-width="16" data-height="16"></span>
                                        <span class="text-sm font-medium text-gray-700">{{ $jabatan->nama }}</span>
                                        <span class="text-xs text-purple-600 font-medium">(Kepala)</span>
                                        <span x-show="editAsnJabatanId == '{{ $jabatan->id }}'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                                    </div>
                                @endforeach

                                <!-- Jabatan per Bagian -->
                                @php
                                    if (!function_exists('renderEditAsnJabatanTree')) {
                                        function renderEditAsnJabatanTree($bagian, $opd, $level = 0) {
                                            $indent = str_repeat('─', $level * 2);

                                            // Show bagian header
                                            echo '<div class="flex items-center gap-2 p-2 bg-gray-50 rounded mt-2">';
                                            if ($level > 0) {
                                                echo '<span class="text-gray-400 text-xs ml-' . ($level * 4) . '">' . $indent . '</span>';
                                            }
                                            echo '<span class="iconify text-blue-500" data-icon="mdi:folder" data-width="14" data-height="14"></span>';
                                            echo '<span class="text-xs font-semibold text-gray-600">' . e($bagian->nama) . '</span>';
                                            echo '</div>';

                                            // Show jabatans in this bagian
                                            foreach ($bagian->jabatans as $jabatan) {
                                                $indentJabatan = str_repeat('─', ($level + 1) * 2);
                                                echo '<div @click="editAsnJabatanId = \'' . $jabatan->id . '\'; editAsnJabatanName = \'' . addslashes($jabatan->nama) . ' (' . addslashes($bagian->nama) . ')\'"
                                                           :class="editAsnJabatanId == \'' . $jabatan->id . '\' ? \'bg-blue-50 border-blue-500\' : \'hover:bg-gray-50\'"
                                                           class="flex items-center gap-2 p-2 rounded cursor-pointer border-2 border-transparent transition-colors ml-' . (($level + 1) * 4) . '">';
                                                echo '<span class="text-gray-400 text-xs">' . $indentJabatan . '</span>';
                                                echo '<span class="iconify text-green-600" data-icon="mdi:account-tie" data-width="14" data-height="14"></span>';
                                                echo '<span class="text-sm text-gray-700">' . e($jabatan->nama) . '</span>';
                                                echo '<span x-show="editAsnJabatanId == \'' . $jabatan->id . '\'" class="iconify text-blue-500 ml-auto" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>';
                                                echo '</div>';
                                            }

                                            // Recursively show sub bagians
                                            $subBagians = $opd->bagians->where('parent_id', $bagian->id);
                                            foreach ($subBagians as $subBagian) {
                                                renderEditAsnJabatanTree($subBagian, $opd, $level + 1);
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($opd->bagians->where('parent_id', null) as $bagian)
                                    @php renderEditAsnJabatanTree($bagian, $opd); @endphp
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Belum ada jabatan tersedia</p>
                        @endif
                    </div>
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2" x-show="editAsnJabatanName">
                        <span class="iconify text-blue-600" data-icon="mdi:information" data-width="16" data-height="16"></span>
                        <span class="text-sm text-blue-900">
                            Dipilih: <span x-text="editAsnJabatanName" class="font-semibold"></span>
                        </span>
                    </div>
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

    // Find jabatan name
    const allJabatans = [
        ...@json($opd->jabatanKepala),
        ...@json($opd->bagians->flatMap->jabatans)
    ];
    const jabatan = allJabatans.find(j => j.id == jabatanId);

    // Get Alpine component
    const form = document.getElementById('editAsnForm');
    const alpineData = Alpine.$data(form);

    // Populate form
    document.getElementById('edit_asn_nama').value = nama;
    document.getElementById('edit_asn_nip').value = nip;

    // Set jabatan via Alpine
    alpineData.editAsnJabatanId = jabatanId;
    if (jabatan) {
        const bagians = @json($opd->bagians);
        if (jabatan.bagian_id) {
            const bagian = bagians.find(b => b.id === jabatan.bagian_id);
            alpineData.editAsnJabatanName = jabatan.nama + ' (' + (bagian ? bagian.nama : 'Unknown') + ')';
        } else {
            alpineData.editAsnJabatanName = jabatan.nama + ' (Kepala)';
        }
    }

    // Set form action
    document.getElementById('editAsnForm').action = `/admin/opds/{{ $opd->id }}/asn/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-asn' }));
});

window.addEventListener('delete-asn', function(e) {
    const { id, nama } = e.detail;

    // Set nama
    document.getElementById('delete_asn_nama').textContent = nama;

    // Set form action
    document.getElementById('deleteAsnForm').action = `/admin/opds/{{ $opd->id }}/asn/${id}`;

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

// Handle Jabatan events from tree
window.addEventListener('edit-jabatan', function(e) {
    const { id } = e.detail;

    // Find jabatan data from all jabatans
    const allJabatans = [
        ...@json($opd->jabatanKepala),
        ...@json($opd->bagians->flatMap->jabatans)
    ];
    const jabatan = allJabatans.find(j => j.id === id);

    if (!jabatan) return;

    // Get Alpine component
    const form = document.getElementById('editJabatanForm');
    const alpineData = Alpine.$data(form);

    // Populate form
    document.getElementById('edit_jabatan_nama').value = jabatan.nama;
    document.getElementById('edit_jabatan_jenis').value = jabatan.jenis_jabatan || '';
    document.getElementById('edit_jabatan_kelas').value = jabatan.kelas || '';

    // Set kebutuhan via Alpine
    const kebutuhanInput = document.getElementById('edit_jabatan_kebutuhan');
    const kebutuhanAlpine = Alpine.$data(kebutuhanInput.closest('[x-data]'));
    kebutuhanAlpine.editKebutuhan = jabatan.kebutuhan || 1;

    // Set bagian
    if (jabatan.bagian_id) {
        const bagians = @json($opd->bagians);
        const bagian = bagians.find(b => b.id === jabatan.bagian_id);
        alpineData.editJabatanBagianId = jabatan.bagian_id;
        alpineData.editJabatanBagianName = bagian ? bagian.nama : 'Jabatan Kepala (Tanpa Bagian)';
    } else {
        alpineData.editJabatanBagianId = '';
        alpineData.editJabatanBagianName = 'Jabatan Kepala (Tanpa Bagian)';
    }

    // Set form action
    document.getElementById('editJabatanForm').action = `/admin/opds/{{ $opd->id }}/jabatan/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-jabatan' }));
});

window.addEventListener('delete-jabatan', function(e) {
    const { id, nama } = e.detail;

    // Set nama
    document.getElementById('delete_jabatan_nama').textContent = nama;

    // Set form action
    document.getElementById('deleteJabatanForm').action = `/admin/opds/{{ $opd->id }}/jabatan/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-jabatan' }));
});

// Handle Bagian events from tree
window.addEventListener('edit-bagian', function(e) {
    const { id, nama } = e.detail;

    // Find bagian data
    const bagians = @json($opd->bagians);
    const bagian = bagians.find(b => b.id === id);

    // Get Alpine component
    const form = document.getElementById('editBagianForm');
    const alpineData = Alpine.$data(form);

    // Populate form
    document.getElementById('edit_bagian_nama').value = nama;

    // Set current bagian ID (to prevent selecting itself as parent)
    alpineData.currentBagianId = id;

    // Set parent
    if (bagian && bagian.parent_id) {
        const parentBagian = bagians.find(b => b.id === bagian.parent_id);
        alpineData.editParentId = bagian.parent_id;
        alpineData.editParentName = parentBagian ? parentBagian.nama : 'Tidak ada (Bagian Utama)';
    } else {
        alpineData.editParentId = '';
        alpineData.editParentName = 'Tidak ada (Bagian Utama)';
    }

    // Set form action
    document.getElementById('editBagianForm').action = `/admin/opds/{{ $opd->id }}/bagian/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-bagian' }));
});

window.addEventListener('delete-bagian', function(e) {
    const { id, nama } = e.detail;

    // Set nama
    document.getElementById('delete_bagian_nama').textContent = nama;

    // Set form action
    document.getElementById('deleteBagianForm').action = `/admin/opds/{{ $opd->id }}/bagian/${id}`;

    // Open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-bagian' }));
});
</script>
@endpush
@endsection
