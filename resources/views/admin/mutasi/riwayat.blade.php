@extends('admin.layouts.app')

@section('title', 'Riwayat Mutasi - ' . $asn->nama)
@section('page-title', 'Riwayat Mutasi')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('admin.mutasi.index') }}" class="hover:text-primary-600">Mutasi Pegawai</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span class="text-gray-900">Riwayat Mutasi</span>
    </nav>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Riwayat Mutasi</h2>
            <p class="text-gray-600 mt-1">{{ $asn->nama }} - NIP: {{ $asn->nip }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.mutasi.create', ['asn_id' => $asn->id]) }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                <span class="ml-2">Buat Mutasi Baru</span>
            </a>
            <a href="{{ route('admin.mutasi.index') }}" class="btn btn-outline">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                <span class="ml-2">Kembali</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Riwayat Timeline -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:timeline" data-width="20" data-height="20"></span>
                        Timeline Mutasi
                    </h3>
                </div>
                <div class="card-body">
                    @if($riwayat->count() > 0)
                        <div class="space-y-6">
                            @foreach($riwayat as $index => $mutasi)
                                <div class="relative pl-8 pb-6 {{ !$loop->last ? 'border-l-2 border-gray-200' : '' }}">
                                    <!-- Timeline Dot -->
                                    <div class="absolute left-0 top-0 -translate-x-1/2 w-4 h-4 rounded-full {{ $index === 0 ? 'bg-primary-500' : 'bg-gray-300' }}"></div>
                                    
                                    <div class="card bg-gray-50">
                                        <div class="card-body">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <span class="text-sm font-semibold text-primary-600">{{ $mutasi->tanggal_mutasi->format('d F Y') }}</span>
                                                    @if($mutasi->jenis_mutasi === 'antar_opd')
                                                        <span class="badge badge-success badge-sm ml-2">Antar OPD</span>
                                                    @else
                                                        <span class="badge badge-info badge-sm ml-2">Internal</span>
                                                    @endif
                                                </div>
                                                <a href="{{ route('admin.mutasi.show', $mutasi) }}" class="text-gray-400 hover:text-primary-600">
                                                    <span class="iconify" data-icon="mdi:open-in-new" data-width="16" data-height="16"></span>
                                                </a>
                                            </div>

                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="flex items-start gap-2">
                                                    <span class="iconify text-red-500 flex-shrink-0 mt-0.5" data-icon="mdi:map-marker" data-width="16" data-height="16"></span>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Dari</p>
                                                        <p class="text-sm font-medium text-gray-900">{{ $mutasi->opdAsal->nama ?? '-' }}</p>
                                                        @if($mutasi->jabatanAsal)
                                                            <p class="text-xs text-gray-500">{{ $mutasi->jabatanAsal->nama }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <span class="iconify text-green-500 flex-shrink-0 mt-0.5" data-icon="mdi:map-marker-check" data-width="16" data-height="16"></span>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Ke</p>
                                                        <p class="text-sm font-medium text-gray-900">{{ $mutasi->opdTujuan->nama ?? '-' }}</p>
                                                        @if($mutasi->jabatanTujuan)
                                                            <p class="text-xs text-gray-500">{{ $mutasi->jabatanTujuan->nama }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if($mutasi->nomor_sk)
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <p class="text-xs text-gray-500">
                                                        <span class="font-medium">No. SK:</span> {{ $mutasi->nomor_sk }}
                                                        @if($mutasi->tanggal_sk)
                                                            | <span class="font-medium">Tgl SK:</span> {{ $mutasi->tanggal_sk->format('d/m/Y') }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif

                                            @if($mutasi->keterangan)
                                                <div class="mt-2">
                                                    <p class="text-xs text-gray-600">{{ $mutasi->keterangan }}</p>
                                                </div>
                                            @endif

                                            <div class="mt-3 text-xs text-gray-400">
                                                Diinput oleh: {{ $mutasi->createdBy->name ?? '-' }} pada {{ $mutasi->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <span class="iconify mx-auto mb-2" data-icon="mdi:timeline-outline" data-width="48" data-height="48"></span>
                            <p class="text-lg font-medium">Belum ada riwayat mutasi</p>
                            <p class="text-sm">Pegawai ini belum pernah dimutasi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: Info Pegawai -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:account" data-width="20" data-height="20"></span>
                        Info Pegawai
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($asn->nama, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $asn->nama }}</h4>
                            <p class="text-sm text-gray-600">NIP: {{ $asn->nip }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-gray-200">
                        <div>
                            <label class="text-xs font-medium text-gray-500">OPD Saat Ini</label>
                            <p class="text-sm text-gray-900">{{ $asn->opd->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Jabatan Saat Ini</label>
                            <p class="text-sm text-gray-900">{{ $asn->jabatan->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Total Mutasi</label>
                            <p class="text-sm text-gray-900">{{ $riwayat->count() }} kali</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.pegawai.edit', $asn->id) }}" class="btn btn-outline w-full">
                            <span class="iconify" data-icon="mdi:account-edit" data-width="18" data-height="18"></span>
                            <span class="ml-2">Edit Data Pegawai</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
