@extends('admin.layouts.app')

@section('title', 'Tambah Jabatan')
@section('page-title', 'Tambah Jabatan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    @if(auth('admin')->user()->isAdminOpd() && auth('admin')->user()->opd)
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <span class="iconify" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
        <span class="font-medium text-gray-900">{{ auth('admin')->user()->opd->nama }}</span>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <a href="{{ route('admin.jabatan.index') }}" class="hover:text-gray-900">Daftar Jabatan</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span>Tambah Jabatan</span>
    </nav>
    @endif

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.jabatan.store') }}" method="POST"
                  x-data="{
                      jenis: '{{ old('jenis_jabatan') }}',
                      namaManual: false,
                      referensi: {{ json_encode($namaReferensi) }},
                      get namaOptions() {
                          if (!this.jenis || !this.referensi[this.jenis]) return [];
                          return this.referensi[this.jenis].map(r => r.nama);
                      }
                  }">
                @csrf

                {{-- 1. Jabatan ini berada di bawah --}}
                <div class="mb-4">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Jabatan Ini Berada di Bawah
                    </label>
                    @if($defaultParent && !old('parent_id'))
                        <input type="text" value="{{ $defaultParent->nama }}"
                               class="w-full rounded border-gray-300 bg-gray-50 text-gray-600" readonly>
                        <input type="hidden" name="parent_id" value="{{ $defaultParent->id }}">
                        <p class="text-xs text-gray-400 mt-1">
                            Diisi otomatis.
                            <a href="{{ route('admin.jabatan.create') }}" class="underline hover:text-gray-600">Pilih manual</a>
                        </p>
                    @else
                        <select name="parent_id" id="parent_id" class="w-full rounded border-gray-300">
                            <option value="">Tidak ada (Jabatan Kepala)</option>
                            @foreach($parentJabatans as $parent)
                                <option value="{{ $parent->id }}"
                                    {{ (old('parent_id', $defaultParentId) == $parent->id) ? 'selected' : '' }}>
                                    {{ $parent->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                {{-- 2. Jenis Jabatan --}}
                <div class="mb-4">
                    <label for="jenis_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jenis Jabatan</label>
                    <select name="jenis_jabatan" id="jenis_jabatan"
                            x-model="jenis"
                            class="w-full rounded border-gray-300 @error('jenis_jabatan') border-red-500 @enderror">
                        <option value="">-- Pilih Jenis Jabatan --</option>
                        @foreach($jenisOptions as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                        @endforeach
                    </select>
                    @error('jenis_jabatan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Nama Jabatan --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Jabatan *</label>
                        <button type="button"
                                @click="namaManual = !namaManual"
                                class="text-xs text-blue-600 hover:text-blue-800 underline"
                                x-text="namaManual ? 'Pilih dari daftar' : 'Input manual'">
                        </button>
                    </div>

                    {{-- Dropdown dari referensi --}}
                    <div x-show="!namaManual">
                        <select name="nama" id="nama"
                                class="w-full rounded border-gray-300 no-tom-select @error('nama') border-red-500 @enderror"
                                :required="!namaManual">
                            <option value="" x-text="!jenis ? '-- Pilih jenis jabatan dulu --' : (namaOptions.length === 0 ? '-- Tidak ada referensi, gunakan input manual --' : '-- Pilih nama jabatan --')"></option>
                            <template x-for="item in namaOptions" :key="item">
                                <option :value="item" x-text="item"
                                    :selected="item === '{{ old('nama') }}'">
                                </option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-400 mt-1" x-show="!jenis">Pilih jenis jabatan terlebih dahulu untuk melihat daftar nama.</p>
                        <p class="text-xs text-gray-400 mt-1" x-show="jenis && namaOptions.length === 0">Belum ada referensi untuk jenis ini. Gunakan input manual.</p>
                    </div>

                    {{-- Input manual --}}
                    <div x-show="namaManual">
                        <input type="text" name="nama" id="nama_manual"
                               value="{{ old('nama') }}"
                               class="w-full rounded border-gray-300 @error('nama') border-red-500 @enderror"
                               :required="namaManual">
                    </div>

                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- OPD --}}
                @if(!auth('admin')->user()->isAdminOpd())
                <div class="mb-4">
                    <label for="opd_id" class="block text-sm font-medium text-gray-700 mb-2">OPD *</label>
                    <select name="opd_id" id="opd_id"
                            class="w-full rounded border-gray-300 @error('opd_id') border-red-500 @enderror" required>
                        <option value="">Pilih OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>
                                {{ $opd->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('opd_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @else
                <input type="hidden" name="opd_id" value="{{ auth('admin')->user()->opd_id }}">
                @endif

                {{-- 4. Kelas Jabatan --}}
                <div class="mb-4">
                    <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">Kelas Jabatan</label>
                    <input type="number" name="kelas" id="kelas" value="{{ old('kelas') }}"
                           min="1" max="17"
                           class="w-full rounded border-gray-300 @error('kelas') border-red-500 @enderror">
                    @error('kelas')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 5. Kebutuhan --}}
                <div class="mb-6">
                    <label for="kebutuhan" class="block text-sm font-medium text-gray-700 mb-2">Kebutuhan</label>
                    <input type="number" name="kebutuhan" id="kebutuhan" value="{{ old('kebutuhan') }}"
                           min="0"
                           class="w-full rounded border-gray-300 @error('kebutuhan') border-red-500 @enderror">
                    @error('kebutuhan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.jabatan.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
