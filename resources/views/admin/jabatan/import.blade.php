@extends('admin.layouts.app')

@section('title', 'Import Data Jabatan')
@section('page-title', 'Import Data Jabatan')

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
        <span class="font-medium text-gray-900">Import Data</span>
    </nav>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content: Upload Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="iconify text-blue-600" data-icon="mdi:file-upload" data-width="24" data-height="24"></span>
                    Upload File Import
                </h2>

                <form action="{{ route('admin.jabatan.import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- OPD Selection for non-admin_opd -->
                    @if(!auth('admin')->user()->isAdminOpd())
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih OPD (untuk download template)
                        </label>
                        <select id="opd_select" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Semua OPD --</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ $selectedOpdId == $opd->id ? 'selected' : '' }}>
                                    {{ $opd->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih OPD untuk mendapatkan template yang sudah include daftar jabatan existing</p>
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">OPD Anda</label>
                        <div class="w-full rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 flex items-center gap-2">
                            <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                            <span class="font-medium text-blue-900">{{ auth('admin')->user()->opd->nama }}</span>
                        </div>
                        <input type="hidden" id="opd_select" value="{{ auth('admin')->user()->opd_id }}">
                    </div>
                    @endif

                    <!-- Download Template -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <span class="iconify text-blue-600 mt-1" data-icon="mdi:file-excel" data-width="24" data-height="24"></span>
                            <div class="flex-1">
                                <h4 class="font-medium text-blue-900">Download Template Excel</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Download template untuk memastikan format data sesuai. Template berisi:
                                </p>
                                <ul class="text-sm text-blue-700 mt-2 list-disc list-inside">
                                    <li>Kolom-kolom yang diperlukan (nama, jenis_jabatan, kelas, dll)</li>
                                    <li>Daftar OPD beserta ID-nya</li>
                                    <li>Daftar jabatan existing untuk referensi parent</li>
                                </ul>
                                <button type="button" id="btn-download-template" class="mt-3 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                                    Download Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            File Excel (XLS/XLSX) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" name="file" id="file" accept=".xls,.xlsx" required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:border-blue-500">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Maksimal ukuran file: 10MB</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('admin.jabatan.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <span class="iconify" data-icon="mdi:eye" data-width="18" data-height="18"></span>
                            Preview Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar: Instructions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="iconify text-yellow-500" data-icon="mdi:lightbulb-on" data-width="24" data-height="24"></span>
                    Panduan Import
                </h3>

                <div class="space-y-4 text-sm text-gray-600">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-1">1. Download Template</h4>
                        <p>Download template Excel terlebih dahulu untuk memastikan format data sesuai.</p>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-800 mb-1">2. Isi Data</h4>
                        <p>Isi data jabatan sesuai kolom yang tersedia:</p>
                        <ul class="list-disc list-inside mt-1 text-xs">
                            <li><strong>nama</strong> - Nama jabatan (wajib)</li>
                            <li><strong>jenis_jabatan</strong> - Struktural/Fungsional</li>
                            <li><strong>kelas</strong> - Kelas jabatan (1-17)</li>
                            <li><strong>kebutuhan</strong> - Jumlah formasi</li>
                            <li><strong>parent_nama</strong> - Nama jabatan atasan</li>
                            <li><strong>opd_id</strong> - ID OPD (wajib)</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-800 mb-1">3. Urutan Import</h4>
                        <p>Untuk hierarki parent-child, pastikan jabatan parent sudah ada (atau diimport terlebih dahulu di baris atas).</p>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-800 mb-1">4. Review & Import</h4>
                        <p>Setelah upload, Anda akan melihat preview data sebelum proses import dilakukan.</p>
                    </div>
                </div>

                <!-- Format Example -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-2 text-sm">Contoh Format:</h4>
                    <div class="overflow-x-auto">
                        <table class="text-xs w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-1 px-2 text-left">nama</th>
                                    <th class="py-1 px-2 text-left">jenis</th>
                                    <th class="py-1 px-2 text-left">kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-1 px-2">Kepala Dinas</td>
                                    <td class="py-1 px-2">Struktural</td>
                                    <td class="py-1 px-2">14</td>
                                </tr>
                                <tr>
                                    <td class="py-1 px-2">Sekretaris</td>
                                    <td class="py-1 px-2">Struktural</td>
                                    <td class="py-1 px-2">12</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnDownload = document.getElementById('btn-download-template');
    const opdSelect = document.getElementById('opd_select');

    btnDownload.addEventListener('click', function() {
        let url = '{{ route("admin.jabatan.import.download-template") }}';
        const opdId = opdSelect.value;
        if (opdId) {
            url += '?opd_id=' + opdId;
        }
        window.location.href = url;
    });
});
</script>
@endpush
@endsection
