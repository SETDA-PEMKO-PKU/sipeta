@extends('admin.layouts.app')

@section('title', 'Detail Mutasi')
@section('page-title', 'Detail Mutasi')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('admin.mutasi.index') }}" class="hover:text-primary-600">Mutasi Pegawai</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span class="text-gray-900">Detail Mutasi</span>
    </nav>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Detail Mutasi</h2>
            <p class="text-gray-600 mt-1">{{ $mutasi->asn->nama ?? '-' }} - {{ $mutasi->tanggal_mutasi->format('d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.mutasi.riwayat', $mutasi->asn_id) }}" class="btn btn-secondary">
                <span class="iconify" data-icon="mdi:history" data-width="18" data-height="18"></span>
                <span class="ml-2">Riwayat Mutasi</span>
            </a>
            <a href="{{ route('admin.mutasi.index') }}" class="btn btn-outline">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                <span class="ml-2">Kembali</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Card: Data Pegawai -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:account" data-width="20" data-height="20"></span>
                        Data Pegawai
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($mutasi->asn->nama ?? 'A', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-semibold text-gray-900">{{ $mutasi->asn->nama ?? '-' }}</h4>
                            <p class="text-gray-600">NIP: {{ $mutasi->asn->nip ?? '-' }}</p>
                            <div class="mt-3 flex items-center gap-2">
                                @if($mutasi->jenis_mutasi === 'antar_opd')
                                    <span class="badge badge-success">Mutasi Antar OPD</span>
                                @else
                                    <span class="badge badge-info">Mutasi Internal OPD</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Detail Mutasi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:swap-horizontal" data-width="20" data-height="20"></span>
                        Detail Perpindahan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- OPD Asal -->
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="iconify text-red-600" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                                <span class="text-sm font-semibold text-red-700">OPD ASAL</span>
                            </div>
                            <h4 class="font-semibold text-gray-900">{{ $mutasi->opdAsal->nama ?? '-' }}</h4>
                            @if($mutasi->jabatanAsal)
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">Jabatan:</span> {{ $mutasi->jabatanAsal->nama }}
                                </p>
                            @endif
                        </div>

                        <!-- Arrow -->
                        <div class="hidden md:flex items-center justify-center absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="iconify text-gray-600" data-icon="mdi:arrow-right" data-width="24" data-height="24"></span>
                            </div>
                        </div>

                        <!-- OPD Tujuan -->
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="iconify text-green-600" data-icon="mdi:office-building-marker" data-width="20" data-height="20"></span>
                                <span class="text-sm font-semibold text-green-700">OPD TUJUAN</span>
                            </div>
                            <h4 class="font-semibold text-gray-900">{{ $mutasi->opdTujuan->nama ?? '-' }}</h4>
                            @if($mutasi->jabatanTujuan)
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">Jabatan:</span> {{ $mutasi->jabatanTujuan->nama }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Mobile Arrow -->
                    <div class="flex md:hidden justify-center my-4">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="iconify text-gray-600" data-icon="mdi:arrow-down" data-width="24" data-height="24"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Data SK -->
            @if($mutasi->nomor_sk || $mutasi->tanggal_sk || $mutasi->keterangan)
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:file-document" data-width="20" data-height="20"></span>
                        Data Surat Keputusan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($mutasi->nomor_sk)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nomor SK</label>
                            <p class="text-gray-900 font-medium">{{ $mutasi->nomor_sk }}</p>
                        </div>
                        @endif

                        @if($mutasi->tanggal_sk)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal SK</label>
                            <p class="text-gray-900">{{ $mutasi->tanggal_sk->format('d F Y') }}</p>
                        </div>
                        @endif
                    </div>

                    @if($mutasi->keterangan)
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Keterangan</label>
                        <p class="text-gray-900 mt-1">{{ $mutasi->keterangan }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Card: Info Mutasi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:information" data-width="20" data-height="20"></span>
                        Informasi
                    </h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Tanggal Mutasi</label>
                        <p class="text-gray-900 font-semibold">{{ $mutasi->tanggal_mutasi->format('d F Y') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Jenis Mutasi</label>
                        <p class="text-gray-900">{{ $mutasi->jenis_mutasi_label }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <label class="text-sm font-medium text-gray-500">Dibuat Oleh</label>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                {{ strtoupper(substr($mutasi->createdBy->name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $mutasi->createdBy->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $mutasi->createdBy->role ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Tanggal Input</label>
                        <p class="text-gray-900">{{ $mutasi->created_at->format('d F Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Card: Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="iconify inline-block mr-2" data-icon="mdi:lightning-bolt" data-width="20" data-height="20"></span>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body space-y-2">
                    <a href="{{ route('admin.mutasi.create', ['asn_id' => $mutasi->asn_id]) }}" 
                       class="btn btn-outline w-full justify-start">
                        <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                        <span class="ml-2">Mutasi Lagi</span>
                    </a>
                    <a href="{{ route('admin.pegawai.edit', $mutasi->asn_id) }}" 
                       class="btn btn-outline w-full justify-start">
                        <span class="iconify" data-icon="mdi:account-edit" data-width="18" data-height="18"></span>
                        <span class="ml-2">Edit Data Pegawai</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
