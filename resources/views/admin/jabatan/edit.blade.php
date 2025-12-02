@extends('admin.layouts.app')

@section('title', 'Edit Jabatan')
@section('page-title', 'Edit Jabatan')

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
        <span>Edit Jabatan</span>
    </nav>
    @endif

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.jabatan.update', $jabatan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Jabatan *</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $jabatan->nama) }}" 
                           class="w-full rounded border-gray-300 @error('nama') border-red-500 @enderror" required>
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if(!auth('admin')->user()->isAdminOpd())
                <div class="mb-4">
                    <label for="opd_id" class="block text-sm font-medium text-gray-700 mb-2">OPD *</label>
                    <select name="opd_id" id="opd_id" 
                            class="w-full rounded border-gray-300 @error('opd_id') border-red-500 @enderror" required>
                        <option value="">Pilih OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ old('opd_id', $jabatan->opd_id) == $opd->id ? 'selected' : '' }}>
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
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">OPD</label>
                    <input type="text" value="{{ auth('admin')->user()->opd->nama }}" class="w-full rounded border-gray-300 bg-gray-100" readonly>
                </div>
                @endif

                <div class="mb-4">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">Parent Jabatan</label>
                    <select name="parent_id" id="parent_id" class="w-full rounded border-gray-300">
                        <option value="">Tidak ada (Jabatan Kepala)</option>
                        @foreach($parentJabatans as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $jabatan->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="jenis_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jenis Jabatan</label>
                    <input type="text" name="jenis_jabatan" id="jenis_jabatan" value="{{ old('jenis_jabatan', $jabatan->jenis_jabatan) }}" 
                           class="w-full rounded border-gray-300">
                    @error('jenis_jabatan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                    <input type="number" name="kelas" id="kelas" value="{{ old('kelas', $jabatan->kelas) }}" 
                           min="1" max="17" class="w-full rounded border-gray-300">
                    @error('kelas')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="kebutuhan" class="block text-sm font-medium text-gray-700 mb-2">Kebutuhan</label>
                    <input type="number" name="kebutuhan" id="kebutuhan" value="{{ old('kebutuhan', $jabatan->kebutuhan) }}" 
                           min="0" class="w-full rounded border-gray-300">
                    @error('kebutuhan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.jabatan.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
