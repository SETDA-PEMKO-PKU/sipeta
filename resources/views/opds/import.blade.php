@extends('admin.layouts.app')

@section('title', 'Import Struktur ASN - ' . $opd->nama)
@section('page-title', 'Import Struktur ASN')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.opds.show', $opd->id) }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">Import Struktur ASN</h2>
                <p class="text-gray-600">{{ $opd->nama }}</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-4 flex items-center gap-2 animate-fade-in">
            <span class="iconify" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-4 flex items-center gap-2 animate-fade-in">
            <span class="iconify" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:briefcase" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Total Jabatan</div>
                    <div class="text-xl font-bold text-gray-900">{{ $allJabatans->count() }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span class="iconify text-purple-600" data-icon="mdi:account-multiple" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Total Kebutuhan</div>
                    <div class="text-xl font-bold text-purple-600">{{ $totalKebutuhan }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <span class="iconify text-green-600" data-icon="mdi:account-check" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Sudah Terisi</div>
                    <div class="text-xl font-bold text-green-600">{{ $totalTerisi }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <span class="iconify text-red-600" data-icon="mdi:account-alert" data-width="20" data-height="20"></span>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Kekosongan</div>
                    <div class="text-xl font-bold text-red-600">{{ max(0, $totalKebutuhan - $totalTerisi) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <!-- Step 1: Download Template -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">1</span>
                    Download Template Excel
                </h3>
            </div>
            <div class="p-5">
                <p class="text-gray-600 mb-4">
                    Download template Excel yang sudah berisi daftar jabatan sesuai dengan struktur organisasi. 
                    Isi kolom <strong>NIP</strong> dan <strong>Nama</strong> untuk setiap baris.
                </p>

                <!-- Format Info -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-4">
                    <h4 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:table" data-width="18" data-height="18"></span>
                        Kolom Template
                    </h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-600">
                            <span class="iconify text-gray-400" data-icon="mdi:lock" data-width="14" data-height="14"></span>
                            <code class="bg-white px-2 py-0.5 rounded text-xs">no</code>
                            <span class="text-gray-400">Auto</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600">
                            <span class="iconify text-gray-400" data-icon="mdi:lock" data-width="14" data-height="14"></span>
                            <code class="bg-white px-2 py-0.5 rounded text-xs">jabatan_id</code>
                            <span class="text-gray-400">Jangan ubah</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600">
                            <span class="iconify text-gray-400" data-icon="mdi:lock" data-width="14" data-height="14"></span>
                            <code class="bg-white px-2 py-0.5 rounded text-xs">jabatan</code>
                            <span class="text-gray-400">Referensi</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600">
                            <span class="iconify text-gray-400" data-icon="mdi:lock" data-width="14" data-height="14"></span>
                            <code class="bg-white px-2 py-0.5 rounded text-xs">kebutuhan_ke</code>
                            <span class="text-gray-400">Urutan</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-700 font-medium">
                            <span class="iconify text-green-500" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                            <code class="bg-green-100 px-2 py-0.5 rounded text-xs text-green-700">nip</code>
                            <span>Diisi User</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-700 font-medium">
                            <span class="iconify text-green-500" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                            <code class="bg-green-100 px-2 py-0.5 rounded text-xs text-green-700">nama</code>
                            <span>Diisi User</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.opds.import.download-template', $opd->id) }}" 
                   class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium py-3 px-4 rounded-xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40">
                    <span class="iconify" data-icon="mdi:microsoft-excel" data-width="20" data-height="20"></span>
                    Download Template Excel (.xlsx)
                </a>
            </div>
        </div>

        <!-- Step 2: Upload File -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-5 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">2</span>
                    Upload File yang Sudah Diisi
                </h3>
            </div>
            <div class="p-5">
                <p class="text-gray-600 mb-4">
                    Setelah mengisi NIP dan Nama pada template, upload file untuk melihat preview data sebelum import.
                </p>

                <form action="{{ route('admin.opds.import.preview', $opd->id) }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      x-data="{ fileName: '', isDragging: false }"
                      @dragover.prevent="isDragging = true"
                      @dragleave.prevent="isDragging = false"
                      @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0]?.name || ''">
                    @csrf

                    <!-- Drop Zone -->
                    <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all mb-4 cursor-pointer"
                         :class="isDragging ? 'border-green-500 bg-green-50' : (fileName ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50')"
                         @click="$refs.fileInput.click()">
                        
                        <input type="file" 
                               name="csv_file" 
                               accept=".csv,.txt,.xls,.xlsx"
                               x-ref="fileInput"
                               @change="fileName = $refs.fileInput.files[0]?.name || ''"
                               class="hidden">

                        <div x-show="!fileName" class="text-gray-500">
                            <span class="iconify mx-auto mb-2" data-icon="mdi:cloud-upload" data-width="40" data-height="40"></span>
                            <p class="font-medium">Klik atau drag & drop file disini</p>
                            <p class="text-xs mt-1 text-gray-400">Format: .xlsx, .xls, .csv (max 10MB)</p>
                        </div>

                        <div x-show="fileName" class="text-green-600">
                            <span class="iconify mx-auto mb-2" data-icon="mdi:file-check" data-width="40" data-height="40"></span>
                            <p class="font-medium" x-text="fileName"></p>
                            <p class="text-xs mt-1 text-gray-500">Klik untuk ganti file</p>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4 mb-4">
                        <h4 class="font-semibold text-amber-800 mb-2 flex items-center gap-2 text-sm">
                            <span class="iconify" data-icon="mdi:lightbulb" data-width="16" data-height="16"></span>
                            Tips Import
                        </h4>
                        <ul class="text-xs text-amber-900 space-y-1">
                            <li class="flex items-center gap-1.5">
                                <span class="iconify text-amber-500" data-icon="mdi:check" data-width="12" data-height="12"></span>
                                NIP harus unik dan minimal 10 karakter
                            </li>
                            <li class="flex items-center gap-1.5">
                                <span class="iconify text-amber-500" data-icon="mdi:check" data-width="12" data-height="12"></span>
                                Baris dengan NIP kosong akan dilewati
                            </li>
                            <li class="flex items-center gap-1.5">
                                <span class="iconify text-amber-500" data-icon="mdi:check" data-width="12" data-height="12"></span>
                                ASN yang sudah ada di database akan dilewati (skip)
                            </li>
                        </ul>
                    </div>

                    <button type="submit" 
                            class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium py-3 px-4 rounded-xl transition-all shadow-lg shadow-green-500/25 hover:shadow-green-500/40 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none" 
                            x-bind:disabled="!fileName">
                        <span class="iconify" data-icon="mdi:eye" data-width="20" data-height="20"></span>
                        Preview Data Import
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Daftar Jabatan -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-5 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="iconify" data-icon="mdi:format-list-bulleted" data-width="22" data-height="22"></span>
                Daftar Jabatan dan Kebutuhan
            </h3>
            <span class="bg-white/20 text-white text-sm px-3 py-1 rounded-full">
                {{ $allJabatans->count() }} jabatan
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">Jenis</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Kelas</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Kebutuhan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Terisi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Kosong</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($allJabatans as $index => $jabatan)
                        @php
                            $bezetting = $jabatan->asns->count();
                            $kosong = max(0, $jabatan->kebutuhan - $bezetting);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="text-gray-400 text-sm">{{ $index + 1 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $jabatan->nama }}</div>
                                <div class="text-xs text-gray-400">ID: {{ $jabatan->id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                    $jabatan->jenis_jabatan == 'Struktural' ? 'bg-blue-100 text-blue-700' : 
                                    ($jabatan->jenis_jabatan == 'Fungsional' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') 
                                }}">
                                    {{ $jabatan->jenis_jabatan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-gray-600">{{ $jabatan->kelas ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-100 text-purple-700 font-semibold text-sm">
                                    {{ $jabatan->kebutuhan }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-700 font-semibold text-sm">
                                    {{ $bezetting }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($kosong > 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-700 font-semibold text-sm">
                                        {{ $kosong }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-400 font-semibold text-sm">
                                        -
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 border-t-2 border-gray-200">
                        <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700">Total</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-purple-600 text-white font-bold text-sm">
                                {{ $totalKebutuhan }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-green-600 text-white font-bold text-sm">
                                {{ $totalTerisi }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-red-600 text-white font-bold text-sm">
                                {{ max(0, $totalKebutuhan - $totalTerisi) }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
