@extends('admin.layouts.app')

@section('title', 'Log Aktivitas Admin')
@section('page-title', 'Log Aktivitas Admin')

@push('styles')
<style>
    .activity-table th {
        white-space: nowrap;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        font-weight: 600;
        padding: 0.75rem 1rem;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .activity-table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    .activity-table tbody tr:hover {
        background-color: #f9fafb;
    }
    .description-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
        .activity-table {
            font-size: 0.8125rem;
        }
        .hide-mobile {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Log Aktivitas Admin</h2>
                <p class="text-gray-600 mt-1">Pantau semua aktivitas yang dilakukan oleh admin</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.activity-logs.export', request()->query()) }}" 
                   class="btn btn-secondary inline-flex items-center gap-2">
                    <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                    Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-100 rounded-lg shrink-0">
                        <span class="iconify text-blue-600" data-icon="mdi:history" data-width="20" data-height="20"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 truncate">Total Log</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format($logs->total()) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-green-100 rounded-lg shrink-0">
                        <span class="iconify text-green-600" data-icon="mdi:login" data-width="20" data-height="20"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 truncate">Login Hari Ini</p>
                        <p class="text-lg font-bold text-gray-900" id="login-today">-</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-purple-100 rounded-lg shrink-0">
                        <span class="iconify text-purple-600" data-icon="mdi:account-group" data-width="20" data-height="20"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 truncate">Admin Aktif</p>
                        <p class="text-lg font-bold text-gray-900" id="active-admins-today">-</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-orange-100 rounded-lg shrink-0">
                        <span class="iconify text-orange-600" data-icon="mdi:chart-line" data-width="20" data-height="20"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 truncate">7 Hari Terakhir</p>
                        <p class="text-lg font-bold text-gray-900" id="week-activity">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-6">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Filter Log</h3>
            @if(request()->hasAny(['search', 'admin_id', 'action', 'module', 'start_date', 'end_date']))
                <a href="{{ route('admin.activity-logs.index') }}" class="text-xs text-primary-600 hover:text-primary-700">
                    Reset Filter
                </a>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('admin.activity-logs.index') }}" method="GET">
                <div class="filter-grid">
                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nama, email, deskripsi..."
                               class="input w-full text-sm">
                    </div>

                    <!-- Filter Admin -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Admin</label>
                        <select name="admin_id" class="input w-full text-sm">
                            <option value="">Semua Admin</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Action -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Aksi</label>
                        <select name="action" class="input w-full text-sm">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $key => $label)
                                <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Module -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Modul</label>
                        <select name="module" class="input w-full text-sm">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $key => $label)
                                <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Date From -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                               class="input w-full text-sm">
                    </div>

                    <!-- Filter Date To -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                               class="input w-full text-sm">
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary inline-flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:magnify" data-width="16" data-height="16"></span>
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="activity-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Waktu</th>
                        <th class="text-left">Admin</th>
                        <th class="text-left">Aksi</th>
                        <th class="text-left hide-mobile">Modul</th>
                        <th class="text-left hide-mobile">Deskripsi</th>
                        <th class="text-left hide-mobile">IP Address</th>
                        <th class="text-center w-16">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $log->time_ago }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $log->created_at->format('d M Y, H:i') }} WIB
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                        {{ $log->admin ? strtoupper(substr($log->admin->name, 0, 1)) : '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">
                                            {{ $log->admin->name ?? 'Unknown' }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate hide-mobile">
                                            {{ $log->admin->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $actionColors = [
                                        'login' => 'bg-green-100 text-green-700',
                                        'logout' => 'bg-yellow-100 text-yellow-700',
                                        'create' => 'bg-blue-100 text-blue-700',
                                        'update' => 'bg-cyan-100 text-cyan-700',
                                        'delete' => 'bg-red-100 text-red-700',
                                        'view' => 'bg-gray-100 text-gray-700',
                                        'export' => 'bg-purple-100 text-purple-700',
                                        'import' => 'bg-indigo-100 text-indigo-700',
                                    ];
                                    $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClass }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="hide-mobile">
                                <span class="text-sm text-gray-600">{{ $log->module_label }}</span>
                            </td>
                            <td class="hide-mobile">
                                <p class="text-sm text-gray-600 description-cell" title="{{ $log->description }}">
                                    {{ $log->description ?? '-' }}
                                </p>
                            </td>
                            <td class="hide-mobile">
                                <span class="text-xs font-mono text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded">
                                    {{ $log->ip_address ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($log->old_data || $log->new_data)
                                    <a href="{{ route('admin.activity-logs.show', $log) }}" 
                                       class="inline-flex items-center justify-center w-7 h-7 rounded bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors"
                                       title="Lihat Detail">
                                        <span class="iconify" data-icon="mdi:eye" data-width="16" data-height="16"></span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center justify-center w-7 h-7 text-gray-300">
                                        <span class="iconify" data-icon="mdi:eye-off-outline" data-width="16" data-height="16"></span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="iconify text-gray-300" data-icon="mdi:history" data-width="48" data-height="48"></span>
                                    <p class="text-gray-500">Belum ada log aktivitas</p>
                                    @if(request()->hasAny(['search', 'admin_id', 'action', 'module', 'start_date', 'end_date']))
                                        <a href="{{ route('admin.activity-logs.index') }}" class="text-sm text-primary-600 hover:underline">
                                            Reset filter untuk melihat semua log
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Fetch stats on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetch('{{ route("admin.activity-logs.stats") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('login-today').textContent = data.login_count_today;
                document.getElementById('active-admins-today').textContent = data.unique_admins_today;
                document.getElementById('week-activity').textContent = data.week_count;
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
            });
    });
</script>
@endpush
@endsection
