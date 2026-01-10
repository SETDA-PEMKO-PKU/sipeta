@extends('admin.layouts.app')

@section('title', 'Log Aktivitas Admin')
@section('page-title', 'Log Aktivitas Admin')

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

    <!-- Filters Card -->
    <div class="card mb-6">
        <div class="card-body">
            <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nama, email, atau deskripsi..."
                               class="input w-full">
                    </div>

                    <!-- Filter Admin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin</label>
                        <select name="admin_id" class="input w-full">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Aksi</label>
                        <select name="action" class="input w-full">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modul</label>
                        <select name="module" class="input w-full">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $key => $label)
                                <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <div class="flex gap-2">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                   class="input w-full text-sm" placeholder="Dari">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                   class="input w-full text-sm" placeholder="Sampai">
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary inline-flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:magnify" data-width="18" data-height="18"></span>
                        Filter
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Waktu</th>
                            <th class="text-left">Admin</th>
                            <th class="text-left">Aksi</th>
                            <th class="text-left">Modul</th>
                            <th class="text-left">Deskripsi</th>
                            <th class="text-left">IP Address</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $log->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $log->created_at->format('H:i:s') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                            {{ $log->admin ? strtoupper(substr($log->admin->name, 0, 1)) : '?' }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log->admin->name ?? 'Unknown' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->admin->email ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $actionColors = [
                                            'login' => 'bg-green-100 text-green-800',
                                            'logout' => 'bg-yellow-100 text-yellow-800',
                                            'create' => 'bg-blue-100 text-blue-800',
                                            'update' => 'bg-cyan-100 text-cyan-800',
                                            'delete' => 'bg-red-100 text-red-800',
                                            'view' => 'bg-gray-100 text-gray-800',
                                            'export' => 'bg-purple-100 text-purple-800',
                                            'import' => 'bg-indigo-100 text-indigo-800',
                                        ];
                                        $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm text-gray-600">{{ $log->module_label }}</span>
                                </td>
                                <td class="max-w-xs">
                                    <p class="text-sm text-gray-600 truncate" title="{{ $log->description }}">
                                        {{ $log->description ?? '-' }}
                                    </p>
                                </td>
                                <td>
                                    <span class="text-xs font-mono text-gray-500">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($log->old_data || $log->new_data)
                                        <a href="{{ route('admin.activity-logs.show', $log) }}" 
                                           class="text-primary-600 hover:text-primary-700">
                                            <span class="iconify" data-icon="mdi:eye" data-width="18" data-height="18"></span>
                                        </a>
                                    @else
                                        <span class="text-gray-300">
                                            <span class="iconify" data-icon="mdi:eye-off" data-width="18" data-height="18"></span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="iconify text-gray-300" data-icon="mdi:history" data-width="48" data-height="48"></span>
                                        <p class="text-gray-500">Belum ada log aktivitas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="card-footer border-t border-gray-200 px-6 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <span class="iconify text-blue-600" data-icon="mdi:history" data-width="24" data-height="24"></span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Log</p>
                        <p class="text-xl font-bold text-gray-900">{{ $logs->total() }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <span class="iconify text-green-600" data-icon="mdi:login" data-width="24" data-height="24"></span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Login Hari Ini</p>
                        <p class="text-xl font-bold text-gray-900" id="login-today">-</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <span class="iconify text-purple-600" data-icon="mdi:account-group" data-width="24" data-height="24"></span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Admin Aktif Hari Ini</p>
                        <p class="text-xl font-bold text-gray-900" id="active-admins-today">-</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <span class="iconify text-orange-600" data-icon="mdi:chart-line" data-width="24" data-height="24"></span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Aktivitas 7 Hari</p>
                        <p class="text-xl font-bold text-gray-900" id="week-activity">-</p>
                    </div>
                </div>
            </div>
        </div>
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
