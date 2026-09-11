@extends('admin.layouts.app')

@section('title', 'Edit Nama Jabatan Referensi')
@section('page-title', 'Edit Nama Jabatan Referensi')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.nama-jabatan-referensi.update', $namaJabatanReferensi) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="jenis_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jenis Jabatan</label>
                    <select name="jenis_jabatan" id="jenis_jabatan"
                            class="w-full rounded border-gray-300 @error('jenis_jabatan') border-red-500 @enderror">
                        <option value="">-- Pilih Jenis Jabatan --</option>
                        @foreach($jenisOptions as $jenis)
                            <option value="{{ $jenis }}"
                                {{ old('jenis_jabatan', $namaJabatanReferensi->jenis_jabatan) == $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_jabatan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Jabatan *</label>
                    <input type="text" name="nama" id="nama"
                           value="{{ old('nama', $namaJabatanReferensi->nama) }}"
                           class="w-full rounded border-gray-300 @error('nama') border-red-500 @enderror"
                           required autofocus>
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.nama-jabatan-referensi.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded text-sm">
                        Batal
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
