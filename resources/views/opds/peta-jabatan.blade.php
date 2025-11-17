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
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin: 10px;
        min-width: 200px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
    }

    .org-node.kepala {
        border-color: #f59e0b;
        background: #fffbeb;
    }

    .org-node.bagian {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .org-node.sub-bagian {
        border-color: #8b5cf6;
        background: #f5f3ff;
    }

    .org-node-header {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        color: #111827;
        text-align: center;
    }

    .org-node-kelas {
        font-size: 12px;
        color: #6b7280;
        text-align: center;
        margin-bottom: 8px;
    }

    .org-node-table {
        width: 100%;
        font-size: 11px;
        margin-top: 8px;
        border-collapse: collapse;
    }

    .org-node-table th {
        background: #f3f4f6;
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        font-weight: 600;
        text-align: center;
    }

    .org-node-table td {
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        text-align: left;
    }

    .org-node-table td.center {
        text-align: center;
    }

    .org-level {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 40px;
        position: relative;
        margin-top: 40px;
    }

    .org-line-v {
        width: 2px;
        height: 40px;
        background: #9ca3af;
        margin: 0 auto;
    }

    .org-branch {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Connector lines */
    .org-level::before {
        content: '';
        position: absolute;
        top: -20px;
        height: 2px;
        background: #9ca3af;
        left: 0;
        right: 0;
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
        background: #9ca3af;
        left: 50%;
        transform: translateX(-50%);
    }

    .total-row {
        background: #fef3c7 !important;
        font-weight: 600;
    }

    .sub-bagian-wrapper {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 40px;
        flex-wrap: wrap;
        position: relative;
    }

    .sub-bagian-wrapper::before {
        content: '';
        position: absolute;
        top: -20px;
        height: 2px;
        background: #9ca3af;
        left: 0;
        right: 0;
    }

    .sub-bagian-wrapper.single::before {
        display: none;
    }

    .sub-bagian-item {
        position: relative;
    }

    .sub-bagian-item::before {
        content: '';
        position: absolute;
        top: -20px;
        width: 2px;
        height: 20px;
        background: #9ca3af;
        left: 50%;
        transform: translateX(-50%);
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
            <!-- Kepala OPD -->
            @foreach($opd->jabatanKepala as $kepala)
                <div class="org-node kepala">
                    <div class="org-node-header">{{ $opd->nama }}</div>
                    <div class="org-node-kelas">Kelas : {{ $kepala->kelas ?? '-' }}</div>

                    @if($kepala->asns->count() > 0)
                        <table class="org-node-table">
                            <thead>
                                <tr>
                                    <th>Jabatan</th>
                                    <th>KELAS</th>
                                    <th>B</th>
                                    <th>K</th>
                                    <th>+/-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $kepala->nama }}</td>
                                    <td class="center">{{ $kepala->kelas ?? '-' }}</td>
                                    <td class="center">{{ $kepala->asns->count() }}</td>
                                    <td class="center">{{ $kepala->kebutuhan }}</td>
                                    <td class="center">{{ $kepala->asns->count() - $kepala->kebutuhan }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>

                @if($opd->bagians->where('parent_id', null)->count() > 0)
                    <div class="org-line-v"></div>
                @endif
            @endforeach

            <!-- Staf Ahli (jika ada) -->
            @php
                $stafAhli = $opd->bagians->where('parent_id', null)->filter(function($b) {
                    return str_contains(strtoupper($b->nama), 'STAF AHLI');
                })->first();
            @endphp
            @if($stafAhli && $stafAhli->jabatans->count() > 0)
                <div class="org-node bagian">
                    <div class="org-node-header">{{ $stafAhli->nama }}</div>

                    <table class="org-node-table">
                        <thead>
                            <tr>
                                <th>Jabatan</th>
                                <th>KELAS</th>
                                <th>B</th>
                                <th>K</th>
                                <th>+/-</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stafAhli->jabatans as $jabatan)
                                <tr>
                                    <td>{{ $jabatan->nama }}</td>
                                    <td class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                    <td class="center">{{ $jabatan->asns->count() }}</td>
                                    <td class="center">{{ $jabatan->kebutuhan }}</td>
                                    <td class="center">{{ $jabatan->asns->count() - $jabatan->kebutuhan }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td>Total</td>
                                <td class="center">-</td>
                                <td class="center">{{ $stafAhli->jabatans->sum(fn($j) => $j->asns->count()) }}</td>
                                <td class="center">{{ $stafAhli->jabatans->sum('kebutuhan') }}</td>
                                <td class="center">{{ $stafAhli->jabatans->sum(fn($j) => $j->asns->count()) - $stafAhli->jabatans->sum('kebutuhan') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="org-line-v"></div>
            @endif

            <!-- Asisten/Bagian Level 1 -->
            @php
                $mainBagians = $opd->bagians->where('parent_id', null)->reject(function($b) {
                    return str_contains(strtoupper($b->nama), 'STAF AHLI');
                });
            @endphp

            @if($mainBagians->count() > 0)
                <div class="org-level {{ $mainBagians->count() == 1 ? 'single' : '' }}">
                    @foreach($mainBagians as $bagian)
                        <div class="org-branch">
                            <div class="org-node bagian">
                                <div class="org-node-header">{{ $bagian->nama }}</div>
                                <div class="org-node-kelas">
                                    @if($bagian->jabatans->first())
                                        Kelas : {{ $bagian->jabatans->first()->kelas ?? '-' }}
                                    @endif
                                </div>

                                @php
                                    $subBagians = $opd->bagians->where('parent_id', $bagian->id);
                                @endphp

                                @if($subBagians->count() > 0)
                                    <!-- Has Sub-Bagian -->
                                    <div class="org-line-v"></div>
                                    <div class="sub-bagian-wrapper {{ $subBagians->count() == 1 ? 'single' : '' }}">
                                        @foreach($subBagians as $subBagian)
                                            <div class="sub-bagian-item">
                                                <div class="org-node sub-bagian">
                                                    <div class="org-node-header">{{ $subBagian->nama }}</div>
                                                    <div class="org-node-kelas">
                                                        @if($subBagian->jabatans->first())
                                                            Kelas : {{ $subBagian->jabatans->first()->kelas ?? '-' }}
                                                        @endif
                                                    </div>

                                                    @if($subBagian->jabatans->count() > 0)
                                                        <table class="org-node-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Jabatan</th>
                                                                    <th>KELAS</th>
                                                                    <th>B</th>
                                                                    <th>K</th>
                                                                    <th>+/-</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($subBagian->jabatans as $jabatan)
                                                                <tr>
                                                                    <td>{{ $jabatan->nama }}</td>
                                                                    <td class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                                                    <td class="center">{{ $jabatan->asns->count() }}</td>
                                                                    <td class="center">{{ $jabatan->kebutuhan }}</td>
                                                                    <td class="center">{{ $jabatan->asns->count() - $jabatan->kebutuhan }}</td>
                                                                </tr>
                                                                @endforeach
                                                                <tr class="total-row">
                                                                    <td>Total</td>
                                                                    <td class="center">-</td>
                                                                    <td class="center">{{ $subBagian->jabatans->sum(fn($j) => $j->asns->count()) }}</td>
                                                                    <td class="center">{{ $subBagian->jabatans->sum('kebutuhan') }}</td>
                                                                    <td class="center">{{ $subBagian->jabatans->sum(fn($j) => $j->asns->count()) - $subBagian->jabatans->sum('kebutuhan') }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- No Sub-Bagian, show direct jabatan -->
                                    @if($bagian->jabatans->count() > 0)
                                        <table class="org-node-table">
                                            <thead>
                                                <tr>
                                                    <th>Jabatan</th>
                                                    <th>KELAS</th>
                                                    <th>B</th>
                                                    <th>K</th>
                                                    <th>+/-</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bagian->jabatans as $jabatan)
                                                <tr>
                                                    <td>{{ $jabatan->nama }}</td>
                                                    <td class="center">{{ $jabatan->kelas ?? '-' }}</td>
                                                    <td class="center">{{ $jabatan->asns->count() }}</td>
                                                    <td class="center">{{ $jabatan->kebutuhan }}</td>
                                                    <td class="center">{{ $jabatan->asns->count() - $jabatan->kebutuhan }}</td>
                                                </tr>
                                                @endforeach
                                                <tr class="total-row">
                                                    <td>Total</td>
                                                    <td class="center">-</td>
                                                    <td class="center">{{ $bagian->jabatans->sum(fn($j) => $j->asns->count()) }}</td>
                                                    <td class="center">{{ $bagian->jabatans->sum('kebutuhan') }}</td>
                                                    <td class="center">{{ $bagian->jabatans->sum(fn($j) => $j->asns->count()) - $bagian->jabatans->sum('kebutuhan') }}</td>
                                                </tr>
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
