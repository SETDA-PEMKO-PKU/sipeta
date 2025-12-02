@extends('admin.layouts.app')

@section('title', 'Detail Jabatan')
@section('page-title', 'Detail Jabatan')

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
        <span>Detail Jabatan</span>
    </nav>
    @endif

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">{{ $jabatan->nama }}</h2>
                <div class="space-x-2">
                    <a href="{{ route('admin.jabatan.edit', $jabatan->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Edit
                    </a>
                    <a href="{{ route('admin.jabatan.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Kembali
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">OPD</h3>
                    <p class="text-lg text-gray-900">{{ $jabatan->opdLangsung ? $jabatan->opdLangsung->nama : ($jabatan->parent ? $jabatan->parent->opdLangsung->nama ?? '-' : '-') }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Parent Jabatan</h3>
                    <p class="text-lg text-gray-900">{{ $jabatan->parent ? $jabatan->parent->nama : 'Tidak ada (Jabatan Kepala)' }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Jenis Jabatan</h3>
                    <p class="text-lg text-gray-900">{{ $jabatan->jenis_jabatan ?? '-' }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Kelas</h3>
                    <p class="text-lg text-gray-900">{{ $jabatan->kelas ? 'Kelas ' . $jabatan->kelas : '-' }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Kebutuhan</h3>
                    <p class="text-lg text-gray-900">{{ $jabatan->kebutuhan ?? 0 }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Terisi</h3>
                    <p class="text-lg text-gray-900">{{ $jabatan->asns->count() }}</p>
                </div>
            </div>

            @if($jabatan->children->count() > 0)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Sub-Jabatan</h3>
                <div class="bg-gray-50 rounded p-4">
                    <ul class="space-y-2">
                        @foreach($jabatan->children as $child)
                            <li class="flex items-center justify-between">
                                <span class="text-gray-700">{{ $child->nama }}</span>
                                <a href="{{ route('admin.jabatan.show', $child->id) }}" class="text-blue-600 hover:text-blue-900">
                                    Lihat Detail
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if($jabatan->asns->count() > 0)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">ASN yang Ditugaskan</h3>
                <div class="bg-gray-50 rounded p-4">
                    <ul class="space-y-2">
                        @foreach($jabatan->asns as $asn)
                            <li class="flex items-center justify-between">
                                <div>
                                    <span class="text-gray-700 font-medium">{{ $asn->nama }}</span>
                                    <span class="text-gray-500 text-sm ml-2">NIP: {{ $asn->nip }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
