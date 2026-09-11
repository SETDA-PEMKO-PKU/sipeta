@extends('admin.layouts.app')

@section('title', 'Analisis per OPD')
@section('page-title', 'Analisis per OPD')

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
    <!-- OPD Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.analytics.opd') }}" id="opdForm" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="opd_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih OPD</label>
                <select name="opd_id" id="opd_id" class="w-full" placeholder="Cari OPD...">
                    <option value="">-- Pilih OPD --</option>
                    @foreach($opds as $opd)
                        <option value="{{ $opd->id }}" {{ $selectedOpdId == $opd->id ? 'selected' : '' }}>
                            {{ $opd->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedOpdId)
            <div>
                <a href="{{ route('admin.analytics.export.pdf', ['type' => 'opd', 'opd_id' => $selectedOpdId]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <span class="iconify" data-icon="mdi:file-pdf" data-width="18"></span>
                    Export PDF
                </a>
            </div>
            @endif
        </form>
    </div>

    @if($data)
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Bagian</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $data['total_bagian'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <span class="iconify text-blue-600" data-icon="mdi:folder-multiple" data-width="24"></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Jabatan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $data['total_jabatan'] }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <span class="iconify text-purple-600" data-icon="mdi:briefcase" data-width="24"></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Kebutuhan</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $data['total_kebutuhan'] }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <span class="iconify text-orange-600" data-icon="mdi:account-clock" data-width="24"></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Bezetting</p>
                        <p class="text-3xl font-bold text-green-600">{{ $data['total_bezetting'] }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <span class="iconify text-green-600" data-icon="mdi:account-check" data-width="24"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pemenuhan Kebutuhan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">Selisih</p>
                    <p class="text-4xl font-bold {{ $data['total_selisih'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $data['total_selisih'] > 0 ? '+' : '' }}{{ $data['total_selisih'] }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">Persentase Pemenuhan</p>
                    <p class="text-4xl font-bold text-blue-600">{{ number_format($data['persentase_pemenuhan'], 1) }}%</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">Status</p>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                        {{ $data['persentase_pemenuhan'] >= 90 ? 'bg-green-100 text-green-800' :
                           ($data['persentase_pemenuhan'] >= 75 ? 'bg-blue-100 text-blue-800' :
                           ($data['persentase_pemenuhan'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                        @if($data['persentase_pemenuhan'] >= 90) Sangat Baik
                        @elseif($data['persentase_pemenuhan'] >= 75) Baik
                        @elseif($data['persentase_pemenuhan'] >= 50) Cukup
                        @else Kurang
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Chart: Bezetting vs Kebutuhan per Bagian -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bezetting vs Kebutuhan per Bagian</h3>
                <div class="chart-container">
                    <canvas id="bagianChart"></canvas>
                </div>
            </div>

            <!-- Chart: Distribusi Pegawai -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Pegawai per Jenis Jabatan</h3>
                <div class="chart-container">
                    <canvas id="distribusiChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Table: Jabatan Kosong -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Jabatan Kosong</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bagian</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kebutuhan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data['jabatan_kosong'] as $jabatan)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $jabatan['nama_jabatan'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $jabatan['jenis_jabatan'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $jabatan['kelas'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $jabatan['bagian'] }}</td>
                            <td class="px-6 py-4 text-sm text-right text-red-600 font-semibold">{{ $jabatan['kebutuhan'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center py-4">
                                    <span class="iconify text-green-500 mb-2" data-icon="mdi:check-circle" data-width="48"></span>
                                    <p class="text-green-600 font-medium">Semua jabatan sudah terisi!</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow p-12">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <span class="iconify text-gray-400" data-icon="mdi:chart-box-outline" data-width="64"></span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pilih OPD untuk Melihat Analisis</h3>
                <p class="text-gray-600">Silakan pilih OPD dari dropdown di atas untuk melihat data analytics.</p>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if($data && $bagianChartData && $distribusiPegawaiData)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart: Bezetting vs Kebutuhan per Bagian
    const bagianCtx = document.getElementById('bagianChart').getContext('2d');
    new Chart(bagianCtx, {
        type: 'bar',
        data: @json($bagianChartData),
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
                    position: 'bottom',
                }
            }
        }
    });

    // Chart: Distribusi Pegawai
    const distribusiCtx = document.getElementById('distribusiChart').getContext('2d');
    new Chart(distribusiCtx, {
        type: 'doughnut',
        data: @json($distribusiPegawaiData),
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
</script>
@endif
@endpush
