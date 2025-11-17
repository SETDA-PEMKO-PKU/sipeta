@extends('admin.layouts.app')

@section('title', 'Gap Analysis')
@section('page-title', 'Gap Analysis - Bezetting vs Kebutuhan')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Export Button -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.analytics.export.excel', ['type' => 'gap']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <span class="iconify" data-icon="mdi:file-excel" data-width="18"></span>
            Export ke Excel
        </a>
    </div>

    <!-- Heat Map: Selisih per OPD -->
    <div class="bg-white rounded-lg shadow p-6 mb-6" x-data="{
        search: '',
        filterType: 'all',
        currentPage: 1,
        perPage: 10,
        allData: {{ json_encode(array_values($heatMapData)) }},
        get filteredData() {
            let data = this.allData;

            if (this.search) {
                data = data.filter(item =>
                    item.label.toLowerCase().includes(this.search.toLowerCase())
                );
            }

            if (this.filterType !== 'all') {
                if (this.filterType === 'understaffed') {
                    data = data.filter(item => item.value < 0);
                } else if (this.filterType === 'overstaffed') {
                    data = data.filter(item => item.value > 0);
                } else if (this.filterType === 'balanced') {
                    data = data.filter(item => item.value === 0);
                }
            }

            return data;
        },
        get paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + parseInt(this.perPage);
            return this.filteredData.slice(start, end);
        },
        get totalPages() {
            return Math.ceil(this.filteredData.length / this.perPage);
        }
    }">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-4">
            <h3 class="text-lg font-semibold text-gray-900">Heat Map Selisih Staffing per OPD</h3>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           @input="currentPage = 1"
                           placeholder="Cari OPD..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm w-full sm:w-auto">
                    <span class="iconify absolute left-3 top-2.5 text-gray-400" data-icon="mdi:magnify" data-width="18"></span>
                </div>

                <select x-model="filterType" @change="currentPage = 1" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500 text-sm no-tom-select">
                    <option value="all">Semua Status</option>
                    <option value="understaffed">Kekurangan</option>
                    <option value="balanced">Seimbang</option>
                    <option value="overstaffed">Kelebihan</option>
                </select>

                <select x-model="perPage" @change="currentPage = 1" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500 text-sm no-tom-select">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                    <option value="100">Tampilkan Semua</option>
                </select>
            </div>
        </div>

        <!-- Debug Info -->
        <div class="mb-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
            <p>Data loaded: <span x-text="Array.isArray(allData) ? allData.length : 'Not an array'"></span> items</p>
            <p>Type: <span x-text="typeof allData"></span></p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-600">Total OPD</p>
                <p class="text-xl font-bold text-gray-900" x-text="filteredData.length"></p>
            </div>
            <div class="bg-red-50 rounded-lg p-3">
                <p class="text-xs text-red-600">Kekurangan</p>
                <p class="text-xl font-bold text-red-700" x-text="filteredData.filter(i => i.value < 0).length"></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-600">Seimbang</p>
                <p class="text-xl font-bold text-gray-700" x-text="filteredData.filter(i => i.value === 0).length"></p>
            </div>
            <div class="bg-blue-50 rounded-lg p-3">
                <p class="text-xs text-blue-600">Kelebihan</p>
                <p class="text-xl font-bold text-blue-700" x-text="filteredData.filter(i => i.value > 0).length"></p>
            </div>
        </div>

        <div class="space-y-2 mb-4">
            <template x-for="item in paginatedData" :key="item.label">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900" x-text="item.label"></span>
                            <span class="text-sm text-gray-600">
                                Bezetting: <span class="font-semibold" x-text="item.bezetting"></span> /
                                Kebutuhan: <span class="font-semibold" x-text="item.kebutuhan"></span>
                            </span>
                        </div>
                        <div class="relative h-8 bg-gray-100 rounded-full overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center z-10">
                                <span class="text-xs font-semibold text-white mix-blend-difference"
                                      x-text="(item.value > 0 ? '+' : '') + item.value + ' (' + item.percentage.toFixed(1) + '%)'">
                                </span>
                            </div>
                            <div class="absolute inset-y-0 left-0 rounded-full transition-all"
                                 :style="'width: ' + Math.min(Math.abs(item.percentage), 100) + '%; background-color: ' + item.color">
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="filteredData.length === 0" class="text-center py-8">
                <div class="flex justify-center mb-2">
                    <span class="iconify text-gray-400" data-icon="mdi:database-search" data-width="48"></span>
                </div>
                <p class="text-gray-600">Tidak ada data OPD yang sesuai dengan pencarian</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between border-t pt-4 gap-3" x-show="totalPages > 1">
            <p class="text-sm text-gray-600">
                Menampilkan <span x-text="((currentPage - 1) * perPage) + 1"></span> -
                <span x-text="Math.min(currentPage * perPage, filteredData.length)"></span> dari
                <span x-text="filteredData.length"></span> OPD
            </p>

            <div class="flex items-center gap-2">
                <button @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-sm inline-flex items-center">
                    <span class="iconify" data-icon="mdi:chevron-left" data-width="18"></span>
                </button>

                <span class="text-sm text-gray-600">
                    Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>
                </span>

                <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-sm inline-flex items-center">
                    <span class="iconify" data-icon="mdi:chevron-right" data-width="18"></span>
                </button>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 flex flex-wrap items-center gap-4 text-sm">
            <span class="font-medium text-gray-700">Keterangan:</span>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #ef4444;"></div>
                <span class="text-gray-600">Kekurangan &gt; 10</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #f59e0b;"></div>
                <span class="text-gray-600">Kekurangan 1-10</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #94a3b8;"></div>
                <span class="text-gray-600">Seimbang</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #22c55e;"></div>
                <span class="text-gray-600">Kelebihan 1-10</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #3b82f6;"></div>
                <span class="text-gray-600">Kelebihan &gt; 10</span>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Table: Understaffed Positions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Jabatan Kekurangan Pegawai</h3>
                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full">
                    {{ count($data['understaffed_positions']) }} Jabatan
                </span>
            </div>
            <div class="overflow-x-auto" style="max-height: 600px;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gap</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data['understaffed_positions'] as $position)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $position['nama_jabatan'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $position['jenis_jabatan'] }} - Kelas {{ $position['kelas'] }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($position['opd'], 20) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    -{{ $position['gap'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center">
                                <span class="iconify text-green-500 mb-2" data-icon="mdi:check-circle" data-width="48"></span>
                                <p class="text-green-600 font-medium">Tidak ada jabatan yang kekurangan pegawai!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table: Overstaffed Positions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Jabatan Kelebihan Pegawai</h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                    {{ count($data['overstaffed_positions']) }} Jabatan
                </span>
            </div>
            <div class="overflow-x-auto" style="max-height: 600px;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gap</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data['overstaffed_positions'] as $position)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $position['nama_jabatan'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $position['jenis_jabatan'] }} - Kelas {{ $position['kelas'] }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($position['opd'], 20) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    +{{ $position['gap'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center">
                                <span class="iconify text-gray-400 mb-2" data-icon="mdi:information" data-width="48"></span>
                                <p class="text-gray-600">Tidak ada jabatan yang kelebihan pegawai</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Priority Recruitment List -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Prioritas Rekrutmen</h3>
            <span class="text-sm text-gray-600">Berdasarkan gap dan tingkat kepentingan jabatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OPD</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gap</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Skor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data['priority_recruitment'] as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                {{ $index < 3 ? 'bg-red-100 text-red-800' :
                                   ($index < 7 ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['jabatan'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item['jenis'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item['kelas'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($item['opd'], 25) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-red-600">-{{ $item['gap'] }}</td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">{{ $item['prioritas'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
