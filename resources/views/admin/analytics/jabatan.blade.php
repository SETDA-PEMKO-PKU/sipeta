@extends('admin.layouts.app')

@section('title', 'Analisis Jabatan')
@section('page-title', 'Analisis Jabatan')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Stats Cards: Total per Jenis -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($data['total_per_jenis'] as $jenis => $total)
        <div class="bg-white rounded-lg shadow p-6 border-l-4
            {{ $jenis == 'Struktural' ? 'border-blue-500' :
               ($jenis == 'Fungsional' ? 'border-green-500' :
               ($jenis == 'Pelaksana' ? 'border-orange-500' : 'border-purple-500')) }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ $jenis }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $total }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Avg Bezetting: {{ $data['average_bezetting_per_jenis'][$jenis] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 rounded-full
                    {{ $jenis == 'Struktural' ? 'bg-blue-100' :
                       ($jenis == 'Fungsional' ? 'bg-green-100' :
                       ($jenis == 'Pelaksana' ? 'bg-orange-100' : 'bg-purple-100')) }}">
                    <span class="iconify
                        {{ $jenis == 'Struktural' ? 'text-blue-600' :
                           ($jenis == 'Fungsional' ? 'text-green-600' :
                           ($jenis == 'Pelaksana' ? 'text-orange-600' : 'text-purple-600')) }}"
                        data-icon="mdi:briefcase"
                        data-width="24"></span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Chart: Distribusi Jenis Jabatan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Jenis Jabatan</h3>
            <div class="chart-container">
                <canvas id="jenisChart"></canvas>
            </div>
        </div>

        <!-- Chart: Jabatan Kosong vs Terisi -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Pengisian Jabatan</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-red-50 rounded-lg">
                    <p class="text-sm text-gray-600">Kosong</p>
                    <p class="text-2xl font-bold text-red-600">{{ $data['jabatan_kosong_vs_terisi']['Kosong'] }}</p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-600">Terisi</p>
                    <p class="text-2xl font-bold text-green-600">{{ $data['jabatan_kosong_vs_terisi']['Terisi'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Distribusi per Kelas -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Jabatan per Kelas</h3>
        <div style="height: 350px;">
            <canvas id="kelasChart"></canvas>
        </div>
    </div>

    <!-- Table: Average Bezetting per Jenis -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rata-rata Bezetting per Jenis Jabatan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Jabatan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jabatan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Bezetting</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['total_per_jenis'] as $jenis => $total)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full
                                    {{ $jenis == 'Struktural' ? 'bg-blue-100' :
                                       ($jenis == 'Fungsional' ? 'bg-green-100' :
                                       ($jenis == 'Pelaksana' ? 'bg-orange-100' : 'bg-purple-100')) }}">
                                    <span class="iconify
                                        {{ $jenis == 'Struktural' ? 'text-blue-600' :
                                           ($jenis == 'Fungsional' ? 'text-green-600' :
                                           ($jenis == 'Pelaksana' ? 'text-orange-600' : 'text-purple-600')) }}"
                                        data-icon="mdi:briefcase"
                                        data-width="20"></span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $jenis }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-semibold">{{ $total }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                            {{ number_format($data['average_bezetting_per_jenis'][$jenis] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @php
                                $avg = $data['average_bezetting_per_jenis'][$jenis] ?? 0;
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $avg >= 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $avg >= 1 ? 'Terisi' : 'Kurang' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart: Distribusi Jenis Jabatan
    const jenisCtx = document.getElementById('jenisChart').getContext('2d');
    new Chart(jenisCtx, {
        type: 'pie',
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
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Chart: Jabatan Kosong vs Terisi
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: @json($kosongTerisiData),
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
