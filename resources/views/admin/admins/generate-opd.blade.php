@extends('admin.layouts.app')

@section('title', 'Generate Admin OPD')
@section('page-title', 'Generate Admin OPD')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ route('admin.admins.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="24" data-height="24"></span>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Generate Admin OPD</h2>
        </div>
        <p class="text-gray-600">Generate akun admin untuk setiap OPD yang belum memiliki admin. Setiap OPD akan mendapatkan 1 akun admin.</p>
    </div>

    <!-- Info Card -->
    <div class="card mb-6">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:information" data-width="24" data-height="24"></span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Informasi</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Setiap OPD akan mendapatkan 1 akun admin dengan role <strong>Admin OPD</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Email akan digenerate dari nama OPD (contoh: <strong>Dinas Kesehatan</strong> → <strong>dinaskesehatan@pku.go.id</strong>)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Password akan digenerate secara otomatis (8 karakter random)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-blue-500 mt-0.5" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>Hasil akhir akan berupa file Excel berisi email dan password masing-masing admin</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-orange-500 mt-0.5" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                            <span class="text-orange-600">OPD yang sudah memiliki admin akan dilewati</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total OPD</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalOpd }}</p>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-green-600" data-icon="mdi:account-check" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">OPD Sudah Punya Admin</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $opdWithAdmin }}</p>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-orange-600" data-icon="mdi:account-plus" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">OPD Belum Punya Admin</p>
                    <p class="text-2xl font-bold text-gray-900" id="remainingCount">{{ $opdWithoutAdmin }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($opdWithoutAdmin > 0)
    <!-- Progress Section (Hidden initially) -->
    <div id="progressSection" class="card mb-6 hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Proses Generate Admin</h3>
                <span id="progressText" class="text-sm text-gray-600">0 / {{ $opdWithoutAdmin }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div id="progressBar" class="bg-gradient-to-r from-primary-500 to-primary-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="currentOpdName" class="text-sm text-gray-600">Mempersiapkan...</p>
        </div>
    </div>

    <!-- Preview Table -->
    <div id="previewSection" class="card mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Preview Admin yang Akan Digenerate</h3>
            <p class="text-sm text-gray-500 mt-1">Berikut daftar OPD yang belum memiliki admin dan email yang akan digenerate</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OPD</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($previewData as $index => $item)
                        <tr class="hover:bg-gray-50" id="row-{{ $item['opd_id'] }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item['opd_nama'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['admin_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <code class="px-2 py-1 bg-gray-100 text-gray-800 rounded">{{ $item['email'] }}</code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div id="actionButtons" class="flex items-center gap-4">
        <button type="button" onclick="startGenerate()" class="btn btn-primary" id="generateBtn">
            <span class="iconify" data-icon="mdi:play" data-width="18" data-height="18"></span>
            <span class="ml-2">Mulai Generate</span>
        </button>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline">
            <span class="iconify" data-icon="mdi:close" data-width="18" data-height="18"></span>
            <span class="ml-2">Batal</span>
        </a>
    </div>

    <!-- Download Button (Hidden initially) -->
    <div id="downloadSection" class="hidden">
        <div class="card p-6 bg-green-50 border-green-200">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="28" data-height="28"></span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-green-800">Generate Selesai!</h3>
                    <p class="text-green-700 text-sm" id="successMessage">0 admin berhasil digenerate</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="downloadExcel()" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                        <span class="ml-2">Download Excel</span>
                    </button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline">
                        <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                        <span class="ml-2">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="iconify text-green-600" data-icon="mdi:check-all" data-width="32" data-height="32"></span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Semua OPD Sudah Memiliki Admin</h3>
            <p class="text-gray-600 mb-6">Tidak ada OPD yang perlu digenerate akun admin baru.</p>
            <a href="{{ route('admin.admins.index') }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="18" data-height="18"></span>
                <span class="ml-2">Kembali ke Daftar Admin</span>
            </a>
        </div>
    </div>
    @endif
</div>

@if($opdWithoutAdmin > 0)
<script>
const opdList = @json($previewData);
let generatedAdmins = [];
let currentIndex = 0;
let isGenerating = false;

async function startGenerate() {
    if (isGenerating) return;
    isGenerating = true;
    
    // Show progress, hide preview
    document.getElementById('progressSection').classList.remove('hidden');
    document.getElementById('actionButtons').classList.add('hidden');
    document.getElementById('generateBtn').disabled = true;
    
    // Start processing
    await processNextOpd();
}

async function processNextOpd() {
    if (currentIndex >= opdList.length) {
        // All done
        finishGenerate();
        return;
    }
    
    const opd = opdList[currentIndex];
    const total = opdList.length;
    
    // Update progress UI
    document.getElementById('currentOpdName').textContent = `Membuat admin untuk: ${opd.opd_nama}`;
    document.getElementById('progressText').textContent = `${currentIndex + 1} / ${total}`;
    document.getElementById('progressBar').style.width = `${((currentIndex + 1) / total) * 100}%`;
    
    // Highlight current row
    const row = document.getElementById(`row-${opd.opd_id}`);
    if (row) {
        row.classList.add('bg-yellow-50');
    }
    
    try {
        const response = await fetch('{{ route("admin.admins.generate-opd.single") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ opd_id: opd.opd_id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            generatedAdmins.push(result.data);
            
            // Mark row as success
            if (row) {
                row.classList.remove('bg-yellow-50');
                row.classList.add('bg-green-50');
                const firstCell = row.querySelector('td');
                if (firstCell) {
                    firstCell.innerHTML = `<span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>`;
                }
            }
        } else {
            // Mark row as error
            if (row) {
                row.classList.remove('bg-yellow-50');
                row.classList.add('bg-red-50');
            }
        }
    } catch (error) {
        console.error('Error generating admin for OPD:', opd.opd_nama, error);
        if (row) {
            row.classList.remove('bg-yellow-50');
            row.classList.add('bg-red-50');
        }
    }
    
    currentIndex++;
    
    // Small delay to prevent overwhelming the server
    await new Promise(resolve => setTimeout(resolve, 100));
    
    // Process next
    await processNextOpd();
}

function finishGenerate() {
    isGenerating = false;
    
    document.getElementById('progressSection').classList.add('hidden');
    document.getElementById('previewSection').classList.add('hidden');
    document.getElementById('downloadSection').classList.remove('hidden');
    document.getElementById('successMessage').textContent = `${generatedAdmins.length} admin berhasil digenerate`;
    
    // Update remaining count
    document.getElementById('remainingCount').textContent = '0';
}

function downloadExcel() {
    if (generatedAdmins.length === 0) {
        alert('Tidak ada data untuk diunduh');
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.admins.generate-opd.download") }}';
    
    // Add CSRF token
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    
    // Add data as JSON
    generatedAdmins.forEach((admin, index) => {
        Object.keys(admin).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `data[${index}][${key}]`;
            input.value = admin[key];
            form.appendChild(input);
        });
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endif
@endsection
