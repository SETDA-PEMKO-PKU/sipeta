@extends('admin.layouts.app')

@section('title', 'Reset Password Admin OPD')
@section('page-title', 'Reset Password Admin OPD')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ route('admin.admins.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="24" data-height="24"></span>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Reset Password Admin OPD</h2>
        </div>
        <p class="text-gray-600">Reset password untuk semua Admin OPD dan download kredensial baru dalam format Excel.</p>
    </div>

    <!-- Warning Card -->
    <div class="card mb-6 border-orange-200 bg-orange-50">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-orange-600" data-icon="mdi:alert" data-width="24" data-height="24"></span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-orange-800 mb-2">Perhatian!</h3>
                    <ul class="text-orange-700 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="iconify text-orange-500 mt-0.5" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                            <span>Password lama <strong>tidak dapat dikembalikan</strong> setelah di-reset</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-orange-500 mt-0.5" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                            <span>Password baru akan digenerate secara <strong>random (8 karakter)</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="iconify text-orange-500 mt-0.5" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                            <span><strong>Simpan file Excel</strong> dengan baik karena password hanya bisa dilihat sekali</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-blue-600" data-icon="mdi:account-key" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Admin OPD</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalAdminOpd }}</p>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <span class="iconify text-green-600" data-icon="mdi:lock-reset" data-width="24" data-height="24"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Password Akan Direset</p>
                    <p class="text-2xl font-bold text-gray-900" id="resetCount">{{ $totalAdminOpd }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($totalAdminOpd > 0)
    <!-- Progress Section (Hidden initially) -->
    <div id="progressSection" class="card mb-6 hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Proses Reset Password</h3>
                <span id="progressText" class="text-sm text-gray-600">0 / {{ $totalAdminOpd }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div id="progressBar" class="bg-gradient-to-r from-orange-500 to-orange-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="currentAdminName" class="text-sm text-gray-600">Mempersiapkan...</p>
        </div>
    </div>

    <!-- Preview Table -->
    <div id="previewSection" class="card mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Admin OPD</h3>
            <p class="text-sm text-gray-500 mt-1">Password untuk semua admin berikut akan di-reset</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OPD</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($previewData as $index => $item)
                        <tr class="hover:bg-gray-50" id="row-{{ $item['admin_id'] }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['admin_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <code class="px-2 py-1 bg-gray-100 text-gray-800 rounded">{{ $item['email'] }}</code>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item['opd_nama'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div id="actionButtons" class="flex items-center gap-4">
        <button type="button" onclick="confirmReset()" class="btn bg-orange-600 hover:bg-orange-700 text-white" id="resetBtn">
            <span class="iconify" data-icon="mdi:lock-reset" data-width="18" data-height="18"></span>
            <span class="ml-2">Reset Semua Password</span>
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
                    <h3 class="text-lg font-semibold text-green-800">Reset Password Selesai!</h3>
                    <p class="text-green-700 text-sm" id="successMessage">0 password berhasil di-reset</p>
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
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="iconify text-gray-400" data-icon="mdi:account-off" data-width="32" data-height="32"></span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Admin OPD</h3>
            <p class="text-gray-600 mb-6">Belum ada admin OPD yang terdaftar di sistem.</p>
            <a href="{{ route('admin.admins.generate-opd') }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:account-multiple-plus" data-width="18" data-height="18"></span>
                <span class="ml-2">Generate Admin OPD</span>
            </a>
        </div>
    </div>
    @endif
</div>

@if($totalAdminOpd > 0)
<script>
const adminList = @json($previewData);
let resetAdmins = [];
let currentIndex = 0;
let isProcessing = false;

function confirmReset() {
    if (confirm('Apakah Anda yakin ingin mereset password untuk {{ $totalAdminOpd }} admin OPD?\n\nPassword lama TIDAK DAPAT dikembalikan!')) {
        startReset();
    }
}

async function startReset() {
    if (isProcessing) return;
    isProcessing = true;
    
    // Show progress, hide preview
    document.getElementById('progressSection').classList.remove('hidden');
    document.getElementById('actionButtons').classList.add('hidden');
    document.getElementById('resetBtn').disabled = true;
    
    // Start processing
    await processNextAdmin();
}

async function processNextAdmin() {
    if (currentIndex >= adminList.length) {
        // All done
        finishReset();
        return;
    }
    
    const admin = adminList[currentIndex];
    const total = adminList.length;
    
    // Update progress UI
    document.getElementById('currentAdminName').textContent = `Mereset password: ${admin.admin_name}`;
    document.getElementById('progressText').textContent = `${currentIndex + 1} / ${total}`;
    document.getElementById('progressBar').style.width = `${((currentIndex + 1) / total) * 100}%`;
    
    // Highlight current row
    const row = document.getElementById(`row-${admin.admin_id}`);
    if (row) {
        row.classList.add('bg-yellow-50');
    }
    
    try {
        const response = await fetch('{{ route("admin.admins.reset-password.single") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ admin_id: admin.admin_id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            resetAdmins.push(result.data);
            
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
        console.error('Error resetting password for admin:', admin.admin_name, error);
        if (row) {
            row.classList.remove('bg-yellow-50');
            row.classList.add('bg-red-50');
        }
    }
    
    currentIndex++;
    
    // Small delay to prevent overwhelming the server
    await new Promise(resolve => setTimeout(resolve, 100));
    
    // Process next
    await processNextAdmin();
}

function finishReset() {
    isProcessing = false;
    
    document.getElementById('progressSection').classList.add('hidden');
    document.getElementById('previewSection').classList.add('hidden');
    document.getElementById('downloadSection').classList.remove('hidden');
    document.getElementById('successMessage').textContent = `${resetAdmins.length} password berhasil di-reset`;
    
    // Update count
    document.getElementById('resetCount').textContent = '0';
}

function downloadExcel() {
    if (resetAdmins.length === 0) {
        alert('Tidak ada data untuk diunduh');
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.admins.reset-password.download") }}';
    
    // Add CSRF token
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    
    // Add data as form inputs
    resetAdmins.forEach((admin, index) => {
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
