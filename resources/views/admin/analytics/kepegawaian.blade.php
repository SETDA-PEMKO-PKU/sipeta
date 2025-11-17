@extends('admin.layouts.app')

@section('title', 'Analytics Kepegawaian')
@section('page-title', 'Analytics Kepegawaian')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 350px;
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Data</h3>
        <form method="GET" action="{{ route('admin.analytics.kepegawaian') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="opd_id" class="block text-sm font-medium text-gray-700 mb-2">OPD</label>
                <select name="opd_id" id="opd_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua OPD</option>
                    @foreach($opds as $opd)
                        <option value="{{ $opd->id }}" {{ $filters['opd_id'] == $opd->id ? 'selected' : '' }}>
                            {{ $opd->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="jenis_jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jenis Jabatan</label>
                <select name="jenis_jabatan" id="jenis_jabatan" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisJabatanList as $jenis)
                        <option value="{{ $jenis }}" {{ $filters['jenis_jabatan'] == $jenis ? 'selected' : '' }}>
                            {{ $jenis }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                <select name="kelas" id="kelas" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas }}" {{ $filters['kelas'] == $kelas ? 'selected' : '' }}>
                            Kelas {{ $kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <span class="iconify mr-2" data-icon="mdi:filter" data-width="18"></span>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Stats Card -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow p-8 mb-6 text-white">
        <div class="text-center">
            <p class="text-sm font-medium text-blue-100 mb-2">Total ASN {{ $filters['opd_id'] ? '(Filtered)' : '' }}</p>
            <p class="text-5xl font-bold">{{ $data['total_asn'] }}</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Chart: Distribusi ASN per OPD -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi ASN per OPD</h3>
            <div class="chart-container">
                <canvas id="opdChart"></canvas>
            </div>
        </div>

        <!-- Chart: Distribusi per Jenis Jabatan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi per Jenis Jabatan</h3>
            <div class="chart-container">
                <canvas id="jenisChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Distribusi per Kelas -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi ASN per Kelas Jabatan</h3>
        <div style="height: 400px;">
            <canvas id="kelasChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart: Distribusi ASN per OPD
    const opdCtx = document.getElementById('opdChart').getContext('2d');
    new Chart(opdCtx, {
        type: 'bar',
        data: @json($opdChartData),
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false,
                }
            }
        }
    });

    // Chart: Distribusi per Jenis Jabatan
    const jenisCtx = document.getElementById('jenisChart').getContext('2d');
    new Chart(jenisCtx, {
        type: 'doughnut',
        data: @json($jenisChartData),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Chart: Distribusi per Kelas
    const kelasCtx = document.getElementById('kelasChart').getContext('2d');
    new Chart(kelasCtx, {
        type: 'bar',
        data: @json($kelasChartData),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false,
                }
            }
        }
    });
</script>
@endpush
