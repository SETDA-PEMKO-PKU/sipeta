@extends('admin.layouts.app')

@section('title', 'Kelola Admin')
@section('page-title', 'Kelola Admin')

@push('styles')
<style>
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }

    .admin-name-cell {
        max-width: 200px;
    }

    .admin-email-cell {
        max-width: 250px;
    }

    .admin-opd-cell {
        max-width: 200px;
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Daftar Administrator</h2>
            <p class="text-gray-600 mt-1">Kelola akun administrator sistem</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.admins.reset-password') }}" class="btn bg-orange-600 hover:bg-orange-700 text-white">
                <span class="iconify" data-icon="mdi:lock-reset" data-width="18" data-height="18"></span>
                <span class="ml-2">Reset Password</span>
            </a>
            <a href="{{ route('admin.admins.generate-opd') }}" class="btn btn-outline">
                <span class="iconify" data-icon="mdi:account-multiple-plus" data-width="18" data-height="18"></span>
                <span class="ml-2">Generate Admin OPD</span>
            </a>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                <span class="ml-2">Tambah Admin</span>
            </a>
        </div>
    </div>

    <!-- Bulk Actions Bar (Hidden by default) -->
    <div id="bulkActionsBar" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4 hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="iconify text-red-600" data-icon="mdi:alert-circle" data-width="20" data-height="20"></span>
                <span class="text-red-700 font-medium">
                    <span id="selectedCount">0</span> admin dipilih
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="clearSelection()" class="btn btn-outline text-sm py-1.5 px-3">
                    <span class="iconify" data-icon="mdi:close" data-width="16" data-height="16"></span>
                    <span class="ml-1">Batal</span>
                </button>
                <button type="button" onclick="confirmBulkDelete()" class="btn bg-red-600 hover:bg-red-700 text-white text-sm py-1.5 px-3">
                    <span class="iconify" data-icon="mdi:delete" data-width="16" data-height="16"></span>
                    <span class="ml-1">Hapus Terpilih</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Admin List -->
    <div class="card">
        <div class="overflow-x-auto">
            <form id="bulkDeleteForm" action="{{ route('admin.admins.bulk-destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-center" style="width: 40px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider admin-name-cell">Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider admin-email-cell">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider" style="width: 140px;">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider admin-opd-cell">OPD</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider" style="width: 100px;">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider" style="width: 100px;">Terdaftar</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($admins as $admin)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 text-center">
                                    @if($admin->id !== auth('admin')->id())
                                        <input type="checkbox" name="ids[]" value="{{ $admin->id }}" 
                                               onchange="updateBulkActions()"
                                               class="admin-checkbox w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    @else
                                        <span class="text-gray-300" title="Tidak bisa menghapus akun sendiri">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4" style="max-width: 200px;">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate" title="{{ $admin->name }}">
                                                {{ \Illuminate\Support\Str::limit($admin->name, 30) }}
                                            </div>
                                            @if($admin->id === auth('admin')->id())
                                                <span class="text-xs text-primary-600">(Anda)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4" style="max-width: 250px;">
                                    <div class="text-sm text-gray-900 truncate font-mono text-xs" title="{{ $admin->email }}">
                                        {{ \Illuminate\Support\Str::limit($admin->email, 35) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" style="width: 140px;">
                                    @if($admin->isSuperAdmin())
                                        <span class="badge badge-primary">Super Admin</span>
                                    @elseif($admin->isAdminOrganisasi())
                                        <span class="badge badge-success">Admin Organisasi</span>
                                    @elseif($admin->isAdminBkpsdm())
                                        <span class="badge badge-info">Admin BKPSDM</span>
                                    @elseif($admin->isAdminOpd())
                                        <span class="badge badge-warning">Admin OPD</span>
                                    @else
                                        <span class="badge badge-gray">{{ $admin->role }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4" style="max-width: 200px;">
                                    @if($admin->opd)
                                        <div class="text-sm text-gray-900 truncate" title="{{ $admin->opd->nama }}">
                                            {{ \Illuminate\Support\Str::limit($admin->opd->nama, 35) }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" style="width: 100px;">
                                    @if($admin->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-error">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" style="width: 100px;">
                                    {{ $admin->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center" style="width: 180px;">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.admins.edit', $admin) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                                            <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                                            <span class="ml-1">Edit</span>
                                        </a>

                                        @if($admin->id !== auth('admin')->id())
                                            <form action="{{ route('admin.admins.destroy', $admin) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                                                    <span class="ml-1">Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <span class="iconify text-gray-300" data-icon="mdi:account-group" data-width="48" data-height="48"></span>
                                    <p class="text-gray-500 mt-2">Belum ada data admin</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <!-- Pagination with Per Page Selector -->
    <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <!-- Per Page Selector -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Tampilkan:</label>
            <form method="GET" action="{{ route('admin.admins.index') }}" class="inline-block">
                <select name="per_page"
                        onchange="this.form.submit()"
                        class="input text-sm py-1 px-2 pr-8 w-auto">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page', 10) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
            </form>
            <span class="text-sm text-gray-600">per halaman</span>
        </div>

        <!-- Pagination Links -->
        <div class="flex-1 flex justify-end">
            {{ $admins->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.admin-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.admin-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectAllCheckbox = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.admin-checkbox');
    
    document.getElementById('selectedCount').textContent = count;
    
    if (count > 0) {
        bulkBar.classList.remove('hidden');
    } else {
        bulkBar.classList.add('hidden');
    }
    
    // Update select all checkbox state
    if (allCheckboxes.length > 0) {
        selectAllCheckbox.checked = count === allCheckboxes.length;
        selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.admin-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    document.getElementById('selectAll').indeterminate = false;
    updateBulkActions();
}

function confirmBulkDelete() {
    const count = document.querySelectorAll('.admin-checkbox:checked').length;
    if (count > 0 && confirm(`Apakah Anda yakin ingin menghapus ${count} admin yang dipilih?`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}
</script>
@endsection
