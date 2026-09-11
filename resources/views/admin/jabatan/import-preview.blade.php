@extends('admin.layouts.app')

@section('title', 'Preview Import Jabatan')
@section('page-title', 'Preview Import Jabatan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm text-gray-600 mb-4">
        <a href="{{ route('admin.jabatan.index') }}" class="hover:text-blue-600">
            <span class="iconify" data-icon="mdi:briefcase" data-width="16" data-height="16"></span>
        </a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <a href="{{ route('admin.jabatan.index') }}" class="hover:text-blue-600">Manajemen Jabatan</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <a href="{{ route('admin.jabatan.import.form') }}" class="hover:text-blue-600">Import Data</a>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span class="font-medium text-gray-900">Preview</span>
    </nav>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Data</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $validationResult['total'] }}</p>
                </div>
                <span class="iconify text-blue-500" data-icon="mdi:file-document-multiple" data-width="32" data-height="32"></span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Siap Import</p>
                    <p class="text-2xl font-bold text-green-600">{{ $validationResult['valid_count'] }}</p>
                </div>
                <span class="iconify text-green-500" data-icon="mdi:check-circle" data-width="32" data-height="32"></span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sudah Ada</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $validationResult['existing_count'] }}</p>
                </div>
                <span class="iconify text-yellow-500" data-icon="mdi:alert-circle" data-width="32" data-height="32"></span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Error</p>
                    <p class="text-2xl font-bold text-red-600">{{ $validationResult['invalid_count'] }}</p>
                </div>
                <span class="iconify text-red-500" data-icon="mdi:close-circle" data-width="32" data-height="32"></span>
            </div>
        </div>
    </div>

    <!-- Progress Bar (hidden initially) -->
    <div id="import-progress" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Import</h3>
        <div class="relative pt-1">
            <div class="flex mb-2 items-center justify-between">
                <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200" id="progress-status">
                        Memproses...
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-blue-600" id="progress-text">
                        0%
                    </span>
                </div>
            </div>
            <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-blue-100">
                <div id="progress-bar" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <span id="progress-current">0</span>
                <span id="progress-total">{{ $validationResult['valid_count'] }}</span>
            </div>
        </div>
        <div class="mt-4 flex gap-3">
            <button type="button" id="btn-cancel-import" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                Batalkan Import
            </button>
        </div>
    </div>

    <!-- Import Result (hidden initially) -->
    <div id="import-result" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
        <div class="flex items-center gap-3 mb-4">
            <span class="iconify text-green-500" data-icon="mdi:check-circle" data-width="32" data-height="32"></span>
            <h3 class="text-lg font-semibold text-gray-800">Import Selesai!</h3>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-green-600" id="result-imported">0</p>
                <p class="text-sm text-green-700">Berhasil Import</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-yellow-600" id="result-skipped">0</p>
                <p class="text-sm text-yellow-700">Dilewati</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-red-600" id="result-error">0</p>
                <p class="text-sm text-red-700">Error</p>
            </div>
        </div>
        <a href="{{ route('admin.jabatan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
            <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
            Kembali ke Daftar Jabatan
        </a>
    </div>

    <!-- Action Buttons -->
    <div id="action-buttons" class="flex justify-between items-center mb-6">
        <a href="{{ route('admin.jabatan.import.form') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
            Kembali
        </a>

        @if($validationResult['valid_count'] > 0)
        <button type="button" id="btn-start-import" class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
            <span class="iconify" data-icon="mdi:database-import" data-width="20" data-height="20"></span>
            Import {{ $validationResult['valid_count'] }} Data Valid
        </button>
        @else
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded-lg">
            Tidak ada data valid untuk diimport
        </div>
        @endif
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b">
            <nav class="flex -mb-px">
                <button type="button" data-tab="valid" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-green-500 text-green-600 bg-green-50">
                    <span class="iconify inline-block mr-1" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                    Siap Import ({{ $validationResult['valid_count'] }})
                </button>
                <button type="button" data-tab="existing" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <span class="iconify inline-block mr-1" data-icon="mdi:alert-circle" data-width="16" data-height="16"></span>
                    Sudah Ada ({{ $validationResult['existing_count'] }})
                </button>
                <button type="button" data-tab="invalid" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <span class="iconify inline-block mr-1" data-icon="mdi:close-circle" data-width="16" data-height="16"></span>
                    Error ({{ $validationResult['invalid_count'] }})
                </button>
            </nav>
        </div>

        <!-- Valid Data Tab -->
        <div id="tab-valid" class="tab-content p-4">
            @if(count($validationResult['valid']) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Baris</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kebutuhan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($validationResult['valid'] as $index => $row)
                        <tr id="row-{{ $index }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['jenis_jabatan'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['kelas'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['kebutuhan'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['parent_nama'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['opd_nama'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="row-status inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Menunggu
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <span class="iconify mx-auto mb-2" data-icon="mdi:file-document-outline" data-width="48" data-height="48"></span>
                <p>Tidak ada data valid untuk diimport</p>
            </div>
            @endif
        </div>

        <!-- Existing Data Tab -->
        <div id="tab-existing" class="tab-content p-4 hidden">
            @if(count($validationResult['existing']) > 0)
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700">
                    <span class="iconify inline-block mr-1" data-icon="mdi:information" data-width="16" data-height="16"></span>
                    Data berikut sudah ada di database dan akan dilewati saat import.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Baris</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($validationResult['existing'] as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['jenis_jabatan'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['kelas'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['opd_nama'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Sudah ada di database
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <span class="iconify mx-auto mb-2" data-icon="mdi:check-all" data-width="48" data-height="48"></span>
                <p>Tidak ada data duplikat</p>
            </div>
            @endif
        </div>

        <!-- Invalid Data Tab -->
        <div id="tab-invalid" class="tab-content p-4 hidden">
            @if(count($validationResult['invalid']) > 0)
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700">
                    <span class="iconify inline-block mr-1" data-icon="mdi:alert" data-width="16" data-height="16"></span>
                    Data berikut memiliki error dan tidak dapat diimport. Perbaiki file Excel dan upload ulang.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Baris</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OPD ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($validationResult['invalid'] as $row)
                        <tr class="hover:bg-gray-50 bg-red-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['nama'] ?: '(kosong)' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['opd_id'] ?: '(kosong)' }}</td>
                            <td class="px-4 py-3">
                                <ul class="list-disc list-inside text-xs text-red-600">
                                    @foreach($row['errors'] as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <span class="iconify mx-auto mb-2" data-icon="mdi:check-circle" data-width="48" data-height="48"></span>
                <p>Tidak ada data dengan error</p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;

            tabBtns.forEach(b => {
                b.classList.remove('border-green-500', 'border-yellow-500', 'border-red-500', 'text-green-600', 'text-yellow-600', 'text-red-600', 'bg-green-50', 'bg-yellow-50', 'bg-red-50');
                b.classList.add('border-transparent', 'text-gray-500');
            });

            tabContents.forEach(c => c.classList.add('hidden'));

            const colors = { valid: 'green', existing: 'yellow', invalid: 'red' };
            const color = colors[tab];
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add(`border-${color}-500`, `text-${color}-600`, `bg-${color}-50`);

            document.getElementById('tab-' + tab).classList.remove('hidden');
        });
    });

    // Import functionality
    const btnStartImport = document.getElementById('btn-start-import');
    const btnCancelImport = document.getElementById('btn-cancel-import');
    const actionButtons = document.getElementById('action-buttons');
    const importProgress = document.getElementById('import-progress');
    const importResult = document.getElementById('import-result');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressStatus = document.getElementById('progress-status');
    const progressCurrent = document.getElementById('progress-current');
    const progressTotal = document.getElementById('progress-total');

    let isImporting = false;
    let isCancelled = false;

    const validData = @json($validationResult['valid']);
    const validDataArray = Object.entries(validData);

    if (btnStartImport) {
        btnStartImport.addEventListener('click', async function() {
            if (isImporting) return;
            isImporting = true;
            isCancelled = false;

            actionButtons.classList.add('hidden');
            importProgress.classList.remove('hidden');

            let imported = 0;
            let skipped = 0;
            let errors = 0;

            for (let i = 0; i < validDataArray.length; i++) {
                if (isCancelled) {
                    progressStatus.textContent = 'Dibatalkan';
                    progressStatus.classList.remove('bg-blue-200', 'text-blue-600');
                    progressStatus.classList.add('bg-red-200', 'text-red-600');
                    break;
                }

                const [index, row] = validDataArray[i];
                const progress = Math.round(((i + 1) / validDataArray.length) * 100);

                progressBar.style.width = progress + '%';
                progressText.textContent = progress + '%';
                progressCurrent.textContent = i + 1;

                try {
                    const response = await fetch('{{ route("admin.jabatan.import.single") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nama: row.nama,
                            jenis_jabatan: row.jenis_jabatan,
                            kelas: row.kelas,
                            kebutuhan: row.kebutuhan,
                            parent_nama: row.parent_nama,
                            opd_id: row.opd_id,
                            index: index
                        })
                    });

                    const result = await response.json();
                    const rowElement = document.getElementById('row-' + index);
                    const statusElement = rowElement ? rowElement.querySelector('.row-status') : null;

                    if (result.success) {
                        if (result.status === 'imported') {
                            imported++;
                            if (statusElement) {
                                statusElement.classList.remove('bg-blue-100', 'text-blue-800');
                                statusElement.classList.add('bg-green-100', 'text-green-800');
                                statusElement.textContent = 'Berhasil';
                            }
                        } else if (result.status === 'skipped') {
                            skipped++;
                            if (statusElement) {
                                statusElement.classList.remove('bg-blue-100', 'text-blue-800');
                                statusElement.classList.add('bg-yellow-100', 'text-yellow-800');
                                statusElement.textContent = 'Dilewati';
                            }
                        }
                    } else {
                        errors++;
                        if (statusElement) {
                            statusElement.classList.remove('bg-blue-100', 'text-blue-800');
                            statusElement.classList.add('bg-red-100', 'text-red-800');
                            statusElement.textContent = 'Error';
                        }
                    }
                } catch (error) {
                    errors++;
                    const rowElement = document.getElementById('row-' + index);
                    const statusElement = rowElement ? rowElement.querySelector('.row-status') : null;
                    if (statusElement) {
                        statusElement.classList.remove('bg-blue-100', 'text-blue-800');
                        statusElement.classList.add('bg-red-100', 'text-red-800');
                        statusElement.textContent = 'Error';
                    }
                }
            }

            // Show result
            if (!isCancelled) {
                progressStatus.textContent = 'Selesai';
                progressStatus.classList.remove('bg-blue-200', 'text-blue-600');
                progressStatus.classList.add('bg-green-200', 'text-green-600');
            }

            document.getElementById('result-imported').textContent = imported;
            document.getElementById('result-skipped').textContent = skipped;
            document.getElementById('result-error').textContent = errors;

            setTimeout(() => {
                importProgress.classList.add('hidden');
                importResult.classList.remove('hidden');
            }, 1000);

            isImporting = false;
        });
    }

    if (btnCancelImport) {
        btnCancelImport.addEventListener('click', function() {
            if (confirm('Yakin ingin membatalkan import? Data yang sudah diimport tidak akan dibatalkan.')) {
                isCancelled = true;
            }
        });
    }
});
</script>
@endpush
@endsection
