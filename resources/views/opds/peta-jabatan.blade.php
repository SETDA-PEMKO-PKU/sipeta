@extends('admin.layouts.app')

@section('title', 'Peta Jabatan - ' . $opd->nama)
@section('page-title', 'Peta Jabatan')

@push('styles')
<style>
    .org-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem;
        overflow-x: auto;
    }

    .org-level {
        display: flex;
        gap: 2rem;
        margin-bottom: 3rem;
        justify-content: center;
        position: relative;
    }

    .org-node {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        min-width: 200px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        position: relative;
    }

    .org-node.kepala {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, white 100%);
    }

    .org-node.struktural {
        border-color: #8b5cf6;
        background: linear-gradient(135deg, #f5f3ff 0%, white 100%);
    }

    .org-node.fungsional {
        border-color: #10b981;
        background: linear-gradient(135deg, #ecfdf5 0%, white 100%);
    }

    .org-node-header {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .org-node-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .org-node-kelas {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .org-node-stats {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-around;
        font-size: 0.75rem;
    }

    .org-node-stat {
        text-align: center;
    }

    .org-node-stat-value {
        font-weight: 700;
        font-size: 1rem;
    }

    .org-node-stat-label {
        color: #6b7280;
        margin-top: 0.125rem;
    }

    .org-connector {
        position: absolute;
        border-left: 2px solid #d1d5db;
        height: 2rem;
        top: -2rem;
        left: 50%;
        transform: translateX(-50%);
    }

    .org-horizontal-line {
        position: absolute;
        border-top: 2px solid #d1d5db;
        width: 100%;
        top: -2rem;
        left: 0;
    }

    @media print {
        body {
            background: white;
        }
        .no-print {
            display: none !important;
        }
        .org-chart {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.opds.show', $opd->id) }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Peta Jabatan</h2>
                <p class="text-gray-600">{{ $opd->nama }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="window.print()" class="btn btn-outline">
                <span class="iconify" data-icon="mdi:printer" data-width="18" data-height="18"></span>
                <span class="ml-2">Cetak</span>
            </button>
            <a href="{{ route('admin.opds.export', $opd->id) }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                <span class="ml-2">Export</span>
            </a>
        </div>
    </div>

    <!-- Organizational Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        @if($opd->jabatanKepala->count() > 0)
            <div class="org-chart">
                @foreach($opd->jabatanKepala as $kepala)
                    @include('opds.partials.peta-jabatan-node', [
                        'jabatan' => $kepala,
                        'level' => 0,
                        'isFirst' => $loop->first
                    ])
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <span class="iconify text-gray-300" data-icon="mdi:file-tree" data-width="64" data-height="64"></span>
                <p class="mt-4 text-gray-500">
                    Belum ada struktur organisasi untuk {{ $opd->nama }}
                </p>
                <div class="mt-6 no-print">
                    <a href="{{ route('admin.opds.show', $opd->id) }}" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                        <span class="ml-2">Tambah Jabatan</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white rounded-lg shadow-sm p-4 no-print">
        <h3 class="font-semibold text-gray-900 mb-3">Keterangan:</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background: linear-gradient(135deg, #eff6ff 0%, white 100%); border: 2px solid #3b82f6;"></div>
                <span class="text-sm text-gray-700">Jabatan Kepala/Root</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background: linear-gradient(135deg, #f5f3ff 0%, white 100%); border: 2px solid #8b5cf6;"></div>
                <span class="text-sm text-gray-700">Jabatan Struktural</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background: linear-gradient(135deg, #ecfdf5 0%, white 100%); border: 2px solid #10b981;"></div>
                <span class="text-sm text-gray-700">Jabatan Fungsional</span>
            </div>
        </div>
    </div>
</div>
@endsection
