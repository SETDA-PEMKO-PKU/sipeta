@extends('admin.layouts.app')

@section('title', 'Laporan & Export')
@section('page-title', 'Laporan & Export Analisis')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Info Card -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mb-6 rounded-lg">
        <div class="flex items-start">
            <span class="iconify text-blue-600 mt-1 mr-3" data-icon="mdi:information" data-width="24"></span>
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-1">Export Analytics Data</h3>
                <p class="text-sm text-blue-800">Pilih jenis laporan yang ingin di-export dan format file yang diinginkan.</p>
            </div>
        </div>
    </div>

    <!-- Export Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Export Overview -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Overview Analytics</h3>
                        <p class="text-sm text-blue-100">Statistik keseluruhan sistem</p>
                    </div>
                    <span class="iconify" data-icon="mdi:view-dashboard" data-width="40"></span>
                </div>
            </div>
            <div class="p-6">
                <ul class="text-sm text-gray-600 space-y-2 mb-6">
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Statistik OPD
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Persentase pemenuhan
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Top 10 OPD
                    </li>
                </ul>
                <div class="flex gap-2">
                    <a href="{{ route('admin.analytics.export.excel', ['type' => 'overview']) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-excel" data-width="18"></span>
                        Excel
                    </a>
                    <a href="{{ route('admin.analytics.export.pdf', ['type' => 'overview']) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-pdf" data-width="18"></span>
                        PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Export per OPD -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Analytics per OPD</h3>
                        <p class="text-sm text-purple-100">Data detail per OPD</p>
                    </div>
                    <span class="iconify" data-icon="mdi:office-building" data-width="40"></span>
                </div>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.analytics.export.excel') }}" method="GET" class="mb-4">
                    <input type="hidden" name="type" value="opd">
                    <label for="opd_excel" class="block text-sm font-medium text-gray-700 mb-2">Pilih OPD:</label>
                    <select name="opd_id" id="opd_excel" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 mb-3" required>
                        <option value="">-- Pilih OPD --</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-excel" data-width="18"></span>
                        Export Excel
                    </button>
                </form>

                <form action="{{ route('admin.analytics.export.pdf') }}" method="GET">
                    <input type="hidden" name="type" value="opd">
                    <select name="opd_id" id="opd_pdf" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 mb-3" required>
                        <option value="">-- Pilih OPD --</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-pdf" data-width="18"></span>
                        Export PDF
                    </button>
                </form>
            </div>
        </div>

        <!-- Export Kepegawaian -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Data Kepegawaian</h3>
                        <p class="text-sm text-green-100">Distribusi ASN</p>
                    </div>
                    <span class="iconify" data-icon="mdi:account-multiple" data-width="40"></span>
                </div>
            </div>
            <div class="p-6">
                <ul class="text-sm text-gray-600 space-y-2 mb-6">
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Distribusi per OPD
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Distribusi per jenis
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Distribusi per kelas
                    </li>
                </ul>
                <div class="flex gap-2">
                    <a href="{{ route('admin.analytics.export.excel', ['type' => 'kepegawaian']) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-excel" data-width="18"></span>
                        Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Jabatan -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Data Jabatan</h3>
                        <p class="text-sm text-orange-100">Statistik jabatan</p>
                    </div>
                    <span class="iconify" data-icon="mdi:briefcase" data-width="40"></span>
                </div>
            </div>
            <div class="p-6">
                <ul class="text-sm text-gray-600 space-y-2 mb-6">
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Total per jenis
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Distribusi per kelas
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Rata-rata bezetting
                    </li>
                </ul>
                <div class="flex gap-2">
                    <a href="{{ route('admin.analytics.export.excel', ['type' => 'jabatan']) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-excel" data-width="18"></span>
                        Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Gap Analysis -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Gap Analysis</h3>
                        <p class="text-sm text-red-100">Analisis kekurangan</p>
                    </div>
                    <span class="iconify" data-icon="mdi:chart-line" data-width="40"></span>
                </div>
            </div>
            <div class="p-6">
                <ul class="text-sm text-gray-600 space-y-2 mb-6">
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Understaffed positions
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Overstaffed positions
                    </li>
                    <li class="flex items-center">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi:check-circle" data-width="16"></span>
                        Prioritas rekrutmen
                    </li>
                </ul>
                <div class="flex gap-2">
                    <a href="{{ route('admin.analytics.export.excel', ['type' => 'gap']) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-excel" data-width="18"></span>
                        Excel
                    </a>
                    <a href="{{ route('admin.analytics.export.pdf', ['type' => 'gap']) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <span class="iconify" data-icon="mdi:file-pdf" data-width="18"></span>
                        PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Quick Links</h3>
                        <p class="text-sm text-gray-300">Akses cepat</p>
                    </div>
                    <span class="iconify" data-icon="mdi:link-variant" data-width="40"></span>
                </div>
            </div>
            <div class="p-6 space-y-3">
                <a href="{{ route('admin.analytics.overview') }}"
                   class="block w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-center">
                    <span class="iconify mr-2" data-icon="mdi:view-dashboard" data-width="16"></span>
                    Dashboard Overview
                </a>
                <a href="{{ route('admin.analytics.opd') }}"
                   class="block w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-center">
                    <span class="iconify mr-2" data-icon="mdi:office-building" data-width="16"></span>
                    Analytics OPD
                </a>
                <a href="{{ route('admin.analytics.kepegawaian') }}"
                   class="block w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-center">
                    <span class="iconify mr-2" data-icon="mdi:account-multiple" data-width="16"></span>
                    Analytics Kepegawaian
                </a>
                <a href="{{ route('admin.analytics.jabatan') }}"
                   class="block w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-center">
                    <span class="iconify mr-2" data-icon="mdi:briefcase" data-width="16"></span>
                    Analytics Jabatan
                </a>
                <a href="{{ route('admin.analytics.gap') }}"
                   class="block w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-center">
                    <span class="iconify mr-2" data-icon="mdi:chart-line" data-width="16"></span>
                    Gap Analysis
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
