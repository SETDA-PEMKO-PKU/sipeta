@extends('admin.layouts.app')

@section('title', 'Bezetting Jabatan')
@section('page-title', 'Bezetting & Kebutuhan per Jabatan')

@section('content')
<div class="p-4 lg:p-8" x-data="bezettingPage()">

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Global --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Total Pemenuhan</span>
                <span class="p-2 bg-blue-50 rounded-lg">
                    <span class="iconify text-blue-600" data-icon="mdi:gauge" data-width="20"></span>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $overview['total_persen'] }}%</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ number_format($overview['total_bezetting']) }} / {{ number_format($overview['total_kebutuhan']) }} jabatan
            </p>
            <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $overview['total_persen'] >= 80 ? 'bg-green-500' : ($overview['total_persen'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ min($overview['total_persen'], 100) }}%"></div>
            </div>
        </div>

        {{-- Per Jenis --}}
        @foreach($overview['per_jenis'] as $item)
        @php
            $color = $item['persen'] >= 80 ? 'green' : ($item['persen'] >= 50 ? 'yellow' : 'red');
            $bgColor = $item['jenis'] === 'Struktural' ? 'purple' : ($item['jenis'] === 'Fungsional' ? 'blue' : 'orange');
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">{{ $item['jenis'] }}</span>
                <span class="p-2 bg-{{ $bgColor }}-50 rounded-lg">
                    <span class="iconify text-{{ $bgColor }}-600"
                          data-icon="{{ $item['jenis'] === 'Struktural' ? 'mdi:sitemap' : ($item['jenis'] === 'Fungsional' ? 'mdi:briefcase' : 'mdi:account-group') }}"
                          data-width="20"></span>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $item['persen'] }}%</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ number_format($item['bezetting']) }} / {{ number_format($item['kebutuhan']) }} jabatan
            </p>
            <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-{{ $color }}-500"
                     style="width: {{ min($item['persen'], 100) }}%"></div>
            </div>
            @if($item['selisih'] > 0)
            <p class="text-xs text-red-500 mt-1.5">Kurang {{ number_format($item['selisih']) }} pegawai</p>
            @elseif($item['selisih'] < 0)
            <p class="text-xs text-green-600 mt-1.5">Surplus {{ number_format(abs($item['selisih'])) }} pegawai</p>
            @else
            <p class="text-xs text-green-600 mt-1.5">Terpenuhi</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Search Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Cari Nama Jabatan</h2>

        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <span class="iconify text-gray-400" data-icon="mdi:magnify" data-width="20"></span>
            </span>
            <input
                type="text"
                x-model="keyword"
                @input.debounce.300ms="doSearch()"
                placeholder="Ketik nama jabatan (min. 2 karakter)..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-400"
            />
            <span x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <span class="iconify animate-spin text-gray-400" data-icon="mdi:loading" data-width="18"></span>
            </span>
        </div>

        {{-- Search Results --}}
        <div x-show="keyword.length >= 2" class="mt-4" style="display:none">

            {{-- No result --}}
            <div x-show="!loading && results.length === 0" class="text-center py-8 text-gray-400 text-sm">
                Jabatan tidak ditemukan
            </div>

            {{-- Results table --}}
            <div x-show="results.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Jabatan</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Bezetting</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kebutuhan</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemenuhan</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">OPD</th>
                            <th class="py-2 px-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in results" :key="item.nama + item.jenis_jabatan">
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-3 font-medium text-gray-800" x-text="item.nama"></td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                          :class="{
                                              'bg-purple-50 text-purple-700': item.jenis_jabatan === 'Struktural',
                                              'bg-blue-50 text-blue-700': item.jenis_jabatan === 'Fungsional',
                                              'bg-orange-50 text-orange-700': item.jenis_jabatan === 'Pelaksana'
                                          }"
                                          x-text="item.jenis_jabatan"></span>
                                </td>
                                <td class="py-3 px-3 text-center font-semibold text-gray-700" x-text="item.bezetting"></td>
                                <td class="py-3 px-3 text-center text-gray-600" x-text="item.kebutuhan"></td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="font-semibold text-xs"
                                              :class="{
                                                  'text-green-600': item.persen >= 80,
                                                  'text-yellow-600': item.persen >= 50 && item.persen < 80,
                                                  'text-red-600': item.persen < 50
                                              }"
                                              x-text="item.persen + '%'"></span>
                                        <div class="w-16 bg-gray-200 rounded-full h-1">
                                            <div class="h-1 rounded-full"
                                                 :class="{
                                                     'bg-green-500': item.persen >= 80,
                                                     'bg-yellow-500': item.persen >= 50 && item.persen < 80,
                                                     'bg-red-500': item.persen < 50
                                                 }"
                                                 :style="'width:' + Math.min(item.persen, 100) + '%'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center text-gray-500 text-xs" x-text="item.jumlah_opd + ' OPD'"></td>
                                <td class="py-3 px-3 text-right">
                                    <button @click="openDetail(item.nama)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors">
                                        <span class="iconify" data-icon="mdi:eye" data-width="14"></span>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Detail Sebaran per OPD --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         @click.self="showModal = false"
         style="display:none">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-semibold text-gray-900" x-text="detail.nama_jabatan"></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Sebaran per OPD</p>
                </div>
                <button @click="showModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                    <span class="iconify" data-icon="mdi:close" data-width="18"></span>
                </button>
            </div>

            {{-- Modal Summary --}}
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <div class="grid grid-cols-4 gap-3 text-center">
                    <div>
                        <p class="text-xs text-gray-400">Jenis</p>
                        <p class="text-sm font-semibold text-gray-700 mt-0.5" x-text="detail.jenis_jabatan"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Bezetting</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5" x-text="detail.total_bezetting"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Kebutuhan</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5" x-text="detail.total_kebutuhan"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Pemenuhan</p>
                        <p class="text-sm font-bold mt-0.5"
                           :class="{
                               'text-green-600': detail.total_persen >= 80,
                               'text-yellow-600': detail.total_persen >= 50 && detail.total_persen < 80,
                               'text-red-600': detail.total_persen < 50
                           }"
                           x-text="detail.total_persen + '%'"></p>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4 overflow-y-auto"
                 :style="detail.sebaran && detail.sebaran.length > 15 ? 'max-height: 420px' : ''">

                <div x-show="loadingDetail" class="flex items-center justify-center py-12 gap-2 text-gray-400">
                    <span class="iconify animate-spin" data-icon="mdi:loading" data-width="22"></span>
                    <span class="text-sm">Memuat data...</span>
                </div>

                <div x-show="!loadingDetail && detail.sebaran && detail.sebaran.length === 0"
                     class="text-center py-12 text-gray-400 text-sm">
                    Tidak ada data sebaran
                </div>

                <table x-show="!loadingDetail && detail.sebaran && detail.sebaran.length > 0" class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">OPD</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Bezetting</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kebutuhan</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Selisih</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in detail.sebaran" :key="row.jabatan_id">
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 pr-3 text-gray-700 text-xs leading-snug" x-text="row.opd_nama"></td>
                                <td class="py-2.5 px-3 text-center text-gray-500 text-xs" x-text="row.kelas ?? '-'"></td>
                                <td class="py-2.5 px-3 text-center font-semibold text-gray-800" x-text="row.bezetting"></td>
                                <td class="py-2.5 px-3 text-center text-gray-600" x-text="row.kebutuhan"></td>
                                <td class="py-2.5 px-3 text-center text-xs font-medium"
                                    :class="row.selisih > 0 ? 'text-red-500' : (row.selisih < 0 ? 'text-green-600' : 'text-gray-400')"
                                    x-text="row.selisih > 0 ? '-' + row.selisih : (row.selisih < 0 ? '+' + Math.abs(row.selisih) : '✓')"></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="text-xs font-semibold"
                                          :class="{
                                              'text-green-600': row.persen >= 80,
                                              'text-yellow-600': row.persen >= 50 && row.persen < 80,
                                              'text-red-600': row.persen < 50
                                          }"
                                          x-text="row.persen + '%'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function bezettingPage() {
    return {
        keyword: '',
        loading: false,
        results: [],
        showModal: false,
        loadingDetail: false,
        detail: {},

        async doSearch() {
            if (this.keyword.length < 2) {
                this.results = [];
                return;
            }
            this.loading = true;
            try {
                const res = await fetch(`{{ route('admin.bezetting.search') }}?q=${encodeURIComponent(this.keyword)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.results = await res.json();
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        async openDetail(namaJabatan) {
            this.showModal = true;
            this.loadingDetail = true;
            this.detail = {};
            try {
                const res = await fetch(`{{ url('admin/bezetting/detail') }}/${encodeURIComponent(namaJabatan)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                this.detail = await res.json();
            } catch (e) {
                this.detail = { nama_jabatan: namaJabatan, sebaran: [], total_bezetting: 0, total_kebutuhan: 0, total_persen: 0, jenis_jabatan: '-' };
            } finally {
                this.loadingDetail = false;
            }
        }
    }
}
</script>
@endpush
