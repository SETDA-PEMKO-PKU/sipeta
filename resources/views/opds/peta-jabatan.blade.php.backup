@extends('admin.layouts.app')

@section('title', 'Peta Jabatan - ' . $opd->nama)
@section('page-title', 'Peta Jabatan')

@push('styles')
<style>
    .org-chart-container {
        overflow-x: auto;
        overflow-y: auto;
        padding: 40px;
        background: #f9fafb;
        min-height: calc(100vh - 200px);
    }

    .org-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        min-width: fit-content;
    }

    .org-node {
        background: white;
        border: 2px solid #374151;
        border-radius: 4px;
        padding: 12px;
        margin: 10px;
        min-width: 250px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
    }

    .org-node-header {
        background: #374151;
        color: white;
        font-weight: 600;
        font-size: 13px;
        padding: 8px;
        text-align: center;
        margin: -12px -12px 12px -12px;
        border-radius: 2px 2px 0 0;
    }

    .org-node-kelas {
        background: #f3f4f6;
        font-size: 12px;
        padding: 6px 8px;
        text-align: center;
        margin: -4px -12px 12px -12px;
        border-bottom: 1px solid #d1d5db;
    }

    .org-level {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 20px;
        position: relative;
        margin-top: 40px;
        flex-wrap: wrap;
    }

    .org-line-v {
        width: 2px;
        height: 40px;
        background: #6b7280;
        margin: 0 auto;
    }

    .org-branch {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 400px;
    }

    /* Connector lines */
    .org-level::before {
        content: '';
        position: absolute;
        top: -20px;
        height: 2px;
        background: #6b7280;
        left: 10%;
        right: 10%;
    }

    .org-level.single::before {
        display: none;
    }

    .org-branch::before {
        content: '';
        position: absolute;
        top: -20px;
        width: 2px;
        height: 20px;
        background: #6b7280;
        left: 50%;
        transform: translateX(-50%);
    }

    /* Tables */
    .jabatan-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
        margin-bottom: 12px;
    }

    .jabatan-table th {
        background: #374151;
        color: white;
        padding: 6px 4px;
        border: 1px solid #374151;
        font-weight: 600;
        text-align: center;
        font-size: 10px;
    }

    .jabatan-table td {
        padding: 5px 6px;
        border: 1px solid #9ca3af;
        text-align: left;
        background: white;
    }

    .jabatan-table td.center {
        text-align: center;
    }

    .jabatan-pelaksana-header {
        background: #374151;
        color: white;
        font-weight: 600;
        font-size: 11px;
        padding: 6px 8px;
        text-align: center;
        margin: 0 -12px 8px -12px;
    }

    .pelaksana-table {
        width: 100%;
        font-size: 10px;
        border-collapse: collapse;
    }

    .pelaksana-table th {
        background: #374151;
        color: white;
        padding: 5px 4px;
        border: 1px solid #374151;
        font-weight: 600;
        text-align: center;
        font-size: 10px;
    }

    .pelaksana-table td {
        padding: 4px 5px;
        border: 1px solid #9ca3af;
        background: white;
    }

    .pelaksana-table td.center {
        text-align: center;
    }

    @media print {
        .back-btn, .btn {
            display: none !important;
        }

        .org-chart-container {
            overflow: visible;
            padding: 20px;
        }

        body {
            background: white !important;
        }

        .org-node {
            page-break-inside: avoid;
        }

        @page {
            size: A3 landscape;
            margin: 1cm;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.opds.show', $opd->id) }}" class="back-btn text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Peta Jabatan</h2>
                <p class="text-sm text-gray-600">{{ $opd->nama }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:printer" data-width="18" data-height="18"></span>
                <span class="ml-2">Cetak</span>
            </button>
        </div>
    </div>

    <!-- Organizational Chart -->
    <div class="org-chart-container card">
        <div class="org-chart">
            <!-- Kepala OPD / Sekretaris Daerah -->
            @foreach($opd->jabatanKepala as $kepala)
                <div class="org-node">
                    <div class="org-node-header">Jabatan {{ $kepala->jenis_jabatan ?? 'Struktural' }}</div>
                    <div style="padding: 12px 8px; text-align: center; font-weight: 500; font-size: 13px;">
                        {{ $kepala->nama }}
                    </div>
                    <div class="org-node-kelas">Kelas {{ $kepala->kelas ?? '-' }}</div>
                </div>

                @if($opd->bagians->where('parent_id', null)->count() > 0)
                    <div class="org-line-v"></div>
                @endif
            @endforeach

            <!-- Bagian/Asisten Level 1 -->
            @php
                $mainBagians = $opd->bagians->where('parent_id', null);
            @endphp

            @if($mainBagians->count() > 0)
                <div class="org-level {{ $mainBagians->count() == 1 ? 'single' : '' }}">
                    @foreach($mainBagians as $bagian)
                        <div class="org-branch">
                            @php
                                $jabatanStruktural = $bagian->jabatans->whereIn('jenis_jabatan', ['Struktural', 'Staf Ahli']);
                                $subBagians = $opd->bagians->where('parent_id', $bagian->id);
                                $kepalaBagianKelas = $jabatanStruktural->first()->kelas ?? '-';
                                $jenisJabatanBagian = $jabatanStruktural->first()->jenis_jabatan ?? 'Struktural';
                            @endphp

                            <div class="org-node">
                                <div class="org-node-header">Jabatan {{ $jenisJabatanBagian }}</div>
                                <div style="padding: 12px 8px; text-align: center; font-weight: 500; font-size: 13px;">
                                    {{ $bagian->nama }}
                                </div>
                                <div class="org-node-kelas">Kelas {{ $kepalaBagianKelas }}</div>

                                @if($subBagians->count() > 0)
                                    <!-- Ada Sub-Bagian -->
                                    <div class="org-line-v" style="margin: 12px auto;"></div>
                                    <div class="org-level {{ $subBagians->count() == 1 ? 'single' : '' }}" style="margin-top: 0;">
                                        @foreach($subBagians as $subBagian)
                                            <div class="org-branch" style="max-width: none;">
                                                @php
                                                    $subJabatanStruktural = $subBagian->jabatans->whereIn('jenis_jabatan', ['Struktural', 'Staf Ahli']);
                                                    $subJabatanPelaksana = $subBagian->jabatans->whereIn('jenis_jabatan', ['Fungsional', 'Pelaksana']);
                                                    $kepalaSubBagianKelas = $subJabatanStruktural->first()->kelas ?? '-';
                                                    $jenisJabatanSub = $subJabatanStruktural->first()->jenis_jabatan ?? 'Struktural';
                                                @endphp

                                                <div class="org-node">
                                                    <div class="org-node-header">Jabatan {{ $jenisJabatanSub }}</div>
                                                    <div style="padding: 12px 8px; text-align: center; font-weight: 500; font-size: 13px;">
                                                        {{ $subBagian->nama }}
                                                    </div>
                                                    <div class="org-node-kelas">Kelas {{ $kepalaSubBagianKelas }}</div>

                                                    <!-- Tabel Jabatan Pelaksana -->
                                                    @if($subJabatanPelaksana->count() > 0)
                                                        @php
                                                            $jenisJabatanPelaksana = $subJabatanPelaksana->first()->jenis_jabatan ?? 'Pelaksana';
                                                        @endphp
                                                        <div class="jabatan-pelaksana-header">Jabatan {{ $jenisJabatanPelaksana }}</div>

                                                        <table class="pelaksana-table">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 45%;">Nama Jabatan</th>
                                                                    <th style="width: 15%;">Kelas</th>
                                                                    <th style="width: 10%;">B</th>
                                                                    <th style="width: 10%;">K</th>
                                                                    <th style="width: 10%;">S</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($subJabatanPelaksana as $jabatan)
                                                                    @foreach($jabatan->asns as $index => $asn)
                                                                        <tr>
                                                                            @if($index == 0)
                                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}">{{ $jabatan->nama }}</td>
                                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->asns->count() }}</td>
                                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->kebutuhan }}</td>
                                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->asns->count() - $jabatan->kebutuhan }}</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endforeach

                                                                    @if($jabatan->asns->count() == 0)
                                                                        <tr>
                                                                            <td>{{ $jabatan->nama }}</td>
                                                                            <td class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                                                            <td class="center">0</td>
                                                                            <td class="center">{{ $jabatan->kebutuhan }}</td>
                                                                            <td class="center">-{{ $jabatan->kebutuhan }}</td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Tidak ada Sub-Bagian, tampilkan jabatan langsung -->
                                    @php
                                        $jabatanPelaksana = $bagian->jabatans->whereIn('jenis_jabatan', ['Fungsional', 'Pelaksana']);
                                    @endphp

                                    @if($jabatanPelaksana->count() > 0)
                                        @php
                                            $jenisJabatanPelaksana = $jabatanPelaksana->first()->jenis_jabatan ?? 'Pelaksana';
                                        @endphp
                                        <div class="jabatan-pelaksana-header">Jabatan {{ $jenisJabatanPelaksana }}</div>

                                        <table class="pelaksana-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 45%;">Nama Jabatan</th>
                                                    <th style="width: 15%;">Kelas</th>
                                                    <th style="width: 10%;">B</th>
                                                    <th style="width: 10%;">K</th>
                                                    <th style="width: 10%;">S</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($jabatanPelaksana as $jabatan)
                                                    @foreach($jabatan->asns as $index => $asn)
                                                        <tr>
                                                            @if($index == 0)
                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}">{{ $jabatan->nama }}</td>
                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->asns->count() }}</td>
                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->kebutuhan }}</td>
                                                                <td rowspan="{{ max(1, $jabatan->asns->count()) }}" class="center">{{ $jabatan->asns->count() - $jabatan->kebutuhan }}</td>
                                                            @endif
                                                        </tr>
                                                    @endforeach

                                                    @if($jabatan->asns->count() == 0)
                                                        <tr>
                                                            <td>{{ $jabatan->nama }}</td>
                                                            <td class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                                            <td class="center">0</td>
                                                            <td class="center">{{ $jabatan->kebutuhan }}</td>
                                                            <td class="center">-{{ $jabatan->kebutuhan }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
