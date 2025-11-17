@extends('admin.layouts.app')

@section('title', 'Dashboard Analytics')
@section('page-title', 'Dashboard Analytics Overview')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
    .gauge-container {
        position: relative;
        height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total OPD -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total OPD</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_opd'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="24"></span>
                </div>
            </div>
        </div>

        <!-- Total Jabatan -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Jabatan</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_jabatan'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <span class="iconify text-purple-600" data-icon="mdi:briefcase" data-width="24"></span>
                </div>
            </div>
        </div>

        <!-- Total ASN -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total ASN</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_asn'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <span class="iconify text-green-600" data-icon="mdi:account-multiple" data-width="24"></span>
                </div>
            </div>
        </div>

        <!-- Selisih -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ $stats['total_selisih'] >= 0 ? 'border-green-500' : 'border-red-500' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Selisih (B - K)</p>
                    <p class="text-3xl font-bold {{ $stats['total_selisih'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $stats['total_selisih'] > 0 ? '+' : '' }}{{ $stats['total_selisih'] }}
                    </p>
                </div>
                <div class="p-3 {{ $stats['total_selisih'] >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                    <span class="iconify {{ $stats['total_selisih'] >= 0 ? 'text-green-600' : 'text-red-600' }}"
                          data-icon="{{ $stats['total_selisih'] >= 0 ? 'mdi:trending-up' : 'mdi:trending-down' }}"
                          data-width="24"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">Kebutuhan vs Bezetting</h3>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Total Kebutuhan</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['total_kebutuhan'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Bezetting</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_bezetting'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Persentase Pemenuhan</h3>
            <div class="gauge-container">
                <div class="relative w-32 h-32">
                    <svg class="transform -rotate-90 w-32 h-32">
                        <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="12" fill="none" />
                        <circle cx="64" cy="64" r="56"
                                stroke="{{ $gaugeData['color'] }}"
                                stroke-width="12"
                                fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 56 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 56 * (1 - $gaugeData['percentage']/100) }}"
                                stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold" style="color: {{ $gaugeData['color'] }}">{{ number_format($gaugeData['percentage'], 1) }}%</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">{{ $gaugeData['label'] }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Pie Chart: Distribusi Jenis Jabatan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Jenis Jabatan</h3>
            <div class="chart-container">
                <canvas id="jenisJabatanChart"></canvas>
            </div>
        </div>

        <!-- Bar Chart: Top 10 OPD by Staffing -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 OPD - Bezetting vs Kebutuhan</h3>
            <div class="chart-container">
                <canvas id="topOpdChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Table: Top Understaffed Positions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Top 10 Jabatan dengan Kekurangan Pegawai Terbesar</h3>
            <a href="{{ route('admin.analytics.gap') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                Lihat Semua
                <span class="iconify" data-icon="mdi:arrow-right" data-width="16"></span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OPD</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kebutuhan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Bezetting</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gap</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($understaffedPositions as $position)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $position['nama_jabatan'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $position['jenis_jabatan'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $position['kelas'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $position['opd'] }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $position['kebutuhan'] }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $position['bezetting'] }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-red-600">-{{ $position['gap'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Pie Chart: Distribusi Jenis Jabatan
    const pieCtx = document.getElementById('jenisJabatanChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: @json($pieChartData),
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

    // Bar Chart: Top OPD
    const barCtx = document.getElementById('topOpdChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: @json($barChartData),
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
</script>
@endpush
