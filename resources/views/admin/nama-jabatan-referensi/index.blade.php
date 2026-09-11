@extends('admin.layouts.app')

@section('title', 'Kelola Nama Jabatan Referensi')
@section('page-title', 'Kelola Nama Jabatan Referensi')

@section('content')
<div class="container mx-auto px-4 py-6">

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Nama Jabatan Referensi</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $referensis->total() }} nama jabatan terdaftar</p>
            </div>
            <a href="{{ route('admin.nama-jabatan-referensi.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                Tambah
            </a>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.nama-jabatan-referensi.index') }}"
              class="flex flex-wrap gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama jabatan..."
                   class="rounded border-gray-300 text-sm w-64">
            <select name="jenis_jabatan" class="rounded border-gray-300 text-sm">
                <option value="">Semua Jenis</option>
                @foreach($jenisOptions as $jenis)
                    <option value="{{ $jenis }}" {{ request('jenis_jabatan') == $jenis ? 'selected' : '' }}>
                        {{ $jenis }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="bg-gray-700 hover:bg-gray-800 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                Filter
            </button>
            @if(request('search') || request('jenis_jabatan'))
            <a href="{{ route('admin.nama-jabatan-referensi.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-2 py-2">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Jabatan</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Jabatan</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($referensis as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $referensis->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $item->nama }}</td>
                        <td class="px-6 py-3">
                            @if($item->jenis_jabatan)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $item->jenis_jabatan === 'Fungsional' ? 'bg-green-100 text-green-800' :
                                   ($item->jenis_jabatan === 'Struktural' ? 'bg-blue-100 text-blue-800' :
                                   ($item->jenis_jabatan === 'Pelaksana' ? 'bg-gray-100 text-gray-700' :
                                   ($item->jenis_jabatan === 'Kepala OPD' ? 'bg-purple-100 text-purple-800' :
                                   'bg-yellow-100 text-yellow-800'))) }}">
                                {{ $item->jenis_jabatan }}
                            </span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.nama-jabatan-referensi.edit', $item) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                <form action="{{ route('admin.nama-jabatan-referensi.destroy', $item) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus nama jabatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 text-sm">
                            Tidak ada data ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($referensis->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $referensis->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
