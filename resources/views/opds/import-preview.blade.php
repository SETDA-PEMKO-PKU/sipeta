@extends('admin.layouts.app')

@section('title', 'Preview Import ASN - ' . $opd->nama)
@section('page-title', 'Preview Import ASN')

@section('content')
<div class="p-4 lg:p-8" x-data="importManager()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.opds.import.form', $opd->id) }}" class="text-gray-600 hover:text-gray-900" x-show="!isImporting">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">Preview Import ASN</h2>
                <p class="text-gray-600">{{ $opd->nama }}</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="alert alert-error mb-4 flex items-center gap-2 animate-fade-in">
            <span class="iconify" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Progress Bar Section (shown during import) -->
    <div x-show="isImporting" x-cloak class="mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="iconify animate-spin" data-icon="mdi:loading" data-width="22" data-height="22" x-show="!isCancelled && !isCompleted"></span>
                    <span class="iconify" data-icon="mdi:check-circle" data-width="22" data-height="22" x-show="isCompleted"></span>
                    <span class="iconify" data-icon="mdi:cancel" data-width="22" data-height="22" x-show="isCancelled"></span>
                    <span x-text="isCompleted ? 'Import Selesai' : (isCancelled ? 'Import Dibatalkan' : 'Proses Import...')"></span>
                </h3>
            </div>
            <div class="p-5">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600">Progress</span>
                        <span class="font-medium" x-text="currentIndex + ' / ' + totalRows"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div class="h-4 rounded-full transition-all duration-300 ease-out"
                             :class="isCancelled ? 'bg-red-500' : (isCompleted ? 'bg-green-500' : 'bg-gradient-to-r from-blue-500 to-indigo-600')"
                             :style="'width: ' + progressPercent + '%'"></div>
                    </div>
                    <div class="text-center text-sm mt-2 font-medium" 
                         :class="isCancelled ? 'text-red-600' : (isCompleted ? 'text-green-600' : 'text-blue-600')"
                         x-text="progressPercent.toFixed(1) + '%'"></div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-3 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900" x-text="currentIndex"></div>
                        <div class="text-xs text-gray-500">Diproses</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-green-600" x-text="importedCount"></div>
                        <div class="text-xs text-green-600">Berhasil</div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-amber-600" x-text="skippedCount"></div>
                        <div class="text-xs text-amber-600">Dilewati</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-red-600" x-text="errorCount"></div>
                        <div class="text-xs text-red-600">Error</div>
                    </div>
                </div>

                <!-- Current Item -->
                <div class="bg-gray-50 rounded-lg p-3 mb-4" x-show="currentItem && !isCompleted && !isCancelled">
                    <div class="text-xs text-gray-500 mb-1">Sedang memproses:</div>
                    <div class="font-medium text-gray-900" x-text="currentItem?.nama || '-'"></div>
                    <div class="text-sm text-gray-600" x-text="currentItem?.nip || '-'"></div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-center gap-3">
                    <button @click="cancelImport()" 
                            class="btn btn-outline border-red-300 text-red-600 hover:bg-red-50"
                            x-show="!isCompleted && !isCancelled">
                        <span class="iconify" data-icon="mdi:stop-circle" data-width="18" data-height="18"></span>
                        <span class="ml-2">Batalkan Import</span>
                    </button>
                    <a href="{{ route('admin.opds.show', $opd->id) }}" 
                       class="btn btn-success"
                       x-show="isCompleted || isCancelled">
                        <span class="iconify" data-icon="mdi:check" data-width="18" data-height="18"></span>
                        <span class="ml-2">Selesai - Lihat OPD</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards (hidden during import) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6" x-show="!isImporting">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:file-document" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Total Baris</div>
                    <div class="text-xl font-bold text-gray-900">{{ $validationResult['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Siap Import</div>
                    <div class="text-xl font-bold text-green-600">{{ $validationResult['valid_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <span class="iconify text-red-600" data-icon="mdi:alert-circle" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Error</div>
                    <div class="text-xl font-bold text-red-600">{{ $validationResult['invalid_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <span class="iconify text-amber-600" data-icon="mdi:account-check" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Sudah Ada</div>
                    <div class="text-xl font-bold text-amber-600">{{ $validationResult['existing_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <span class="iconify text-gray-600" data-icon="mdi:skip-next" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Dilewati</div>
                    <div class="text-xl font-bold text-gray-600">{{ $validationResult['skipped_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables (hidden during import) -->
    <div x-show="!isImporting">
        <!-- Valid Data Table -->
        @if(count($validationResult['valid']) > 0)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-5 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:check-circle" data-width="22" data-height="22"></span>
                        Data Siap Import ({{ count($validationResult['valid']) }})
                    </h3>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-white/90 hover:text-white">
                            <input type="checkbox" 
                                   class="checkbox checkbox-sm bg-white/20 border-white/40" 
                                   @change="toggleSelectAll($event)"
                                   :checked="allSelected">
                            <span class="text-sm">Pilih Semua</span>
                        </label>
                        <span class="bg-white/20 text-white text-sm px-3 py-1 rounded-full">
                            <span x-text="selectedCount"></span> dipilih
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-96">
                    <table class="w-full">
                        <thead class="sticky top-0">
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-12">
                                    <span class="iconify" data-icon="mdi:checkbox-marked" data-width="16" data-height="16"></span>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Baris</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIP</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jabatan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($validationResult['valid'] as $index => $row)
                                <tr class="hover:bg-green-50/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" 
                                               data-index="{{ $index }}"
                                               data-nip="{{ $row['nip'] }}"
                                               data-nama="{{ $row['nama'] }}"
                                               data-jabatan-id="{{ $row['jabatan_id'] }}"
                                               class="checkbox checkbox-sm checkbox-success row-checkbox"
                                               @change="updateSelectedCount()"
                                               checked>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-gray-400 text-sm">#{{ $row['row_number'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">{{ $row['nip'] }}</code>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-900">{{ $row['nama'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900">{{ $row['jabatan_nama'] }}</div>
                                        <div class="text-xs text-gray-400">ID: {{ $row['jabatan_id'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="iconify" data-icon="mdi:plus-circle" data-width="14" data-height="14"></span>
                                            Baru
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Existing/Skipped Data -->
        @if(isset($validationResult['existing']) && count($validationResult['existing']) > 0)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:account-check" data-width="22" data-height="22"></span>
                        Data Sudah Ada - Akan Dilewati ({{ count($validationResult['existing']) }})
                    </h3>
                </div>
                <div class="overflow-x-auto max-h-64">
                    <table class="w-full">
                        <thead class="sticky top-0">
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Baris</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIP</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama di File</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data di Database</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($validationResult['existing'] as $row)
                                <tr class="bg-amber-50/30">
                                    <td class="px-4 py-3">
                                        <span class="text-gray-400 text-sm">#{{ $row['row_number'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">{{ $row['nip'] }}</code>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-gray-700">{{ $row['nama'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900 font-medium">{{ $row['existing_info']['nama'] ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $row['existing_info']['jabatan'] ?? '-' }} • {{ $row['existing_info']['opd'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            <span class="iconify" data-icon="mdi:skip-next" data-width="14" data-height="14"></span>
                                            Dilewati
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Invalid Data Table -->
        @if(count($validationResult['invalid']) > 0)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-5 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:alert-circle" data-width="22" data-height="22"></span>
                        Data Error - Tidak Dapat Diimport ({{ count($validationResult['invalid']) }})
                    </h3>
                </div>
                <div class="overflow-x-auto max-h-64">
                    <table class="w-full">
                        <thead class="sticky top-0">
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Baris</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIP</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jabatan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($validationResult['invalid'] as $row)
                                <tr class="bg-red-50/30">
                                    <td class="px-4 py-3">
                                        <span class="text-gray-400 text-sm">#{{ $row['row_number'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($row['nip'])
                                            <code class="bg-red-100 px-2 py-1 rounded text-sm font-mono text-red-700">{{ $row['nip'] }}</code>
                                        @else
                                            <span class="text-gray-400 italic">Kosong</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($row['nama'])
                                            <span class="text-gray-700">{{ $row['nama'] }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Kosong</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-gray-500">{{ $row['jabatan_nama'] ?? $row['jabatan_id'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <ul class="space-y-1">
                                            @foreach($row['errors'] as $error)
                                                <li class="flex items-center gap-1.5 text-sm text-red-600">
                                                    <span class="iconify flex-shrink-0" data-icon="mdi:close-circle" data-width="14" data-height="14"></span>
                                                    {{ $error }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <a href="{{ route('admin.opds.import.form', $opd->id) }}" class="btn btn-outline w-full sm:w-auto">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                <span class="ml-2">Upload Ulang</span>
            </a>

            @if(count($validationResult['valid']) > 0)
                <button @click="startImport()" class="btn btn-success w-full sm:w-auto" x-bind:disabled="selectedCount === 0">
                    <span class="iconify" data-icon="mdi:database-import" data-width="18" data-height="18"></span>
                    <span class="ml-2">Import <span x-text="selectedCount"></span> Data ke Pegawai</span>
                </button>
            @else
                <div class="flex items-center gap-2 text-red-600 bg-red-50 px-4 py-2 rounded-lg">
                    <span class="iconify" data-icon="mdi:alert" data-width="18" data-height="18"></span>
                    <span class="text-sm font-medium">Tidak ada data valid untuk diimport</span>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function importManager() {
    return {
        // State
        isImporting: false,
        isCancelled: false,
        isCompleted: false,
        
        // Data
        selectedRows: [],
        currentIndex: 0,
        totalRows: 0,
        currentItem: null,
        
        // Counters
        importedCount: 0,
        skippedCount: 0,
        errorCount: 0,
        
        // Computed
        get progressPercent() {
            if (this.totalRows === 0) return 0;
            return (this.currentIndex / this.totalRows) * 100;
        },
        
        get selectedCount() {
            return document.querySelectorAll('.row-checkbox:checked').length;
        },
        
        get allSelected() {
            const total = document.querySelectorAll('.row-checkbox').length;
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            return total > 0 && total === checked;
        },
        
        // Methods
        toggleSelectAll(event) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = event.target.checked);
        },
        
        updateSelectedCount() {
            // Force reactivity update
            this.$nextTick(() => {});
        },
        
        async startImport() {
            // Collect selected rows data
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            this.selectedRows = [];
            
            checkboxes.forEach((cb, index) => {
                this.selectedRows.push({
                    index: parseInt(cb.dataset.index),
                    nip: cb.dataset.nip,
                    nama: cb.dataset.nama,
                    jabatan_id: parseInt(cb.dataset.jabatanId)
                });
            });
            
            if (this.selectedRows.length === 0) {
                alert('Tidak ada data yang dipilih');
                return;
            }
            
            // Start import
            this.isImporting = true;
            this.isCancelled = false;
            this.isCompleted = false;
            this.currentIndex = 0;
            this.totalRows = this.selectedRows.length;
            this.importedCount = 0;
            this.skippedCount = 0;
            this.errorCount = 0;
            
            // Process one by one
            for (let i = 0; i < this.selectedRows.length; i++) {
                if (this.isCancelled) break;
                
                const row = this.selectedRows[i];
                this.currentItem = row;
                
                try {
                    const response = await fetch('{{ route("admin.opds.import.single", $opd->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nip: row.nip,
                            nama: row.nama,
                            jabatan_id: row.jabatan_id,
                            index: row.index
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (result.status === 'imported') {
                            this.importedCount++;
                        } else if (result.status === 'skipped') {
                            this.skippedCount++;
                        }
                    } else {
                        this.errorCount++;
                    }
                    
                } catch (error) {
                    console.error('Import error:', error);
                    this.errorCount++;
                }
                
                this.currentIndex = i + 1;
                
                // Small delay to prevent overwhelming server
                await new Promise(resolve => setTimeout(resolve, 50));
            }
            
            if (!this.isCancelled) {
                this.isCompleted = true;
            }
            
            // Clear session
            try {
                await fetch('{{ route("admin.opds.import.clear-session", $opd->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
            } catch (e) {}
        },
        
        cancelImport() {
            if (confirm('Yakin ingin membatalkan proses import? Data yang sudah diimport tidak akan di-rollback.')) {
                this.isCancelled = true;
            }
        }
    }
}
</script>
@endpush
@endsection
