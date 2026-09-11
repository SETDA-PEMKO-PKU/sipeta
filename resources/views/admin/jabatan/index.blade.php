@extends('admin.layouts.app')

@section('title', 'Manajemen Jabatan')
@section('page-title', 'Manajemen Jabatan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    @if(auth('admin')->user()->isAdminOpd() && auth('admin')->user()->opd)
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <span class="iconify" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
        <span class="font-medium text-gray-900">{{ auth('admin')->user()->opd->nama }}</span>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span>Daftar Jabatan</span>
    </nav>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Jabatan</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.jabatan.export', request()->only(['search', 'opd_id', 'jenis_jabatan', 'kelas'])) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded flex items-center gap-2">
                <span class="iconify" data-icon="mdi:file-download" data-width="18" data-height="18"></span>
                Export Excel
            </a>
            <a href="{{ route('admin.jabatan.import.form') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center gap-2">
                <span class="iconify" data-icon="mdi:file-upload" data-width="18" data-height="18"></span>
                Import Excel
            </a>
            <a href="{{ route('admin.jabatan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Tambah Jabatan
            </a>
        </div>
    </div>

    <!-- Search & Filters (single form) -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.jabatan.index') }}">
            {{-- Search row --}}
            <div class="flex gap-2 mb-4">
                <div class="relative flex-1 min-w-0">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <span class="iconify text-gray-400" data-icon="mdi:magnify" data-width="20" data-height="20"></span>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama jabatan..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <button type="submit" class="shrink-0 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg">Cari</button>
                @if(request()->hasAny(['search', 'opd_id', 'jenis_jabatan', 'kelas']))
                <a href="{{ route('admin.jabatan.index') }}" class="shrink-0 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">Reset</a>
                @endif
            </div>

            {{-- Filter row --}}
            <div class="grid grid-cols-1 md:grid-cols-{{ auth('admin')->user()->isAdminOpd() ? '3' : '4' }} gap-4">
                @if(!auth('admin')->user()->isAdminOpd())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">OPD</label>
                    <select name="opd_id" class="w-full rounded border-gray-300">
                        <option value="">Semua OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ request('opd_id') == $opd->id ? 'selected' : '' }}>
                                {{ $opd->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <!-- Admin OPD: Show OPD name as info -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">OPD Anda</label>
                    <div class="w-full rounded border border-blue-200 bg-blue-50 px-3 py-2 flex items-center gap-2">
                        <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
                        <span class="text-sm font-medium text-blue-900">{{ auth('admin')->user()->opd->nama }}</span>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Jabatan</label>
                    <select name="jenis_jabatan" class="w-full rounded border-gray-300">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisJabatans as $jenis)
                            <option value="{{ $jenis }}" {{ request('jenis_jabatan') == $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                    <select name="kelas" class="w-full rounded border-gray-300">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasJabatans as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                Kelas {{ $kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded w-full">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Jabatan Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Jabatan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bagian/Bidang</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kelas</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kebutuhan</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Bezetting</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($jabatans as $jabatan)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $jabatan->nama }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $jabatan->opdLangsung ? $jabatan->opdLangsung->nama : ($jabatan->parent ? $jabatan->parent->opdLangsung->nama ?? '-' : '-') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $jabatan->parent ? $jabatan->parent->nama : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $jabatan->jenis_jabatan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">
                            {{ $jabatan->kelas ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div x-data="{
                                editing: false,
                                value: {{ $jabatan->kebutuhan ?? 0 }},
                                original: {{ $jabatan->kebutuhan ?? 0 }},
                                saving: false,
                                save() {
                                    this.saving = true;
                                    fetch('{{ route('admin.jabatan.updateKebutuhan', $jabatan->id) }}', {
                                        method: 'PATCH',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        },
                                        body: JSON.stringify({ kebutuhan: this.value })
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            this.original = data.kebutuhan;
                                            this.value = data.kebutuhan;
                                        }
                                        this.editing = false;
                                        this.saving = false;
                                    })
                                    .catch(() => { this.saving = false; });
                                },
                                cancel() { this.value = this.original; this.editing = false; }
                            }" class="inline-flex items-center justify-center gap-1">
                                <template x-if="!editing">
                                    <button @click="editing = true"
                                        class="px-2 py-0.5 rounded hover:bg-blue-50 text-gray-700 font-medium border border-transparent hover:border-blue-200 transition"
                                        title="Klik untuk edit">
                                        <span x-text="value"></span>
                                    </button>
                                </template>
                                <template x-if="editing">
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model.number="value" min="0" max="9999"
                                            class="w-16 text-center border border-blue-400 rounded px-1 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            @keydown.enter="save()"
                                            @keydown.escape="cancel()"
                                            x-init="$nextTick(() => $el.focus())" />
                                        <button @click="save()" :disabled="saving"
                                            class="text-green-600 hover:text-green-800 disabled:opacity-50" title="Simpan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                        <button @click="cancel()" class="text-red-400 hover:text-red-600" title="Batal">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            @if($jabatan->asns->count() > 0)
                            <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                                <button @click="open = !open"
                                    class="px-2 py-0.5 rounded font-medium text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition"
                                    title="Lihat daftar pegawai">
                                    {{ $jabatan->asns->count() }}
                                </button>
                                <div x-show="open" x-transition
                                    class="absolute z-50 right-0 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-lg text-left"
                                    style="display:none">
                                    <div class="px-3 py-2 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        Pegawai ({{ $jabatan->asns->count() }})
                                    </div>
                                    <ul class="max-h-48 overflow-y-auto divide-y divide-gray-50">
                                        @foreach($jabatan->asns as $asn)
                                        <li class="px-3 py-2">
                                            <div class="text-sm font-medium text-gray-800">{{ $asn->nama }}</div>
                                            <div class="text-xs text-gray-400">{{ $asn->nip }}</div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                            <a href="{{ route('admin.jabatan.show', $jabatan->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                            Tidak ada data jabatan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $jabatans->links() }}
    </div>
</div>
@endsection
