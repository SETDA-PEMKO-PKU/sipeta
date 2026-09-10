@extends('admin.layouts.app')

@section('title', 'Detail Log Aktivitas')
@section('page-title', 'Detail Log Aktivitas')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.activity-logs.index') }}" 
           class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
            <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            Kembali ke Log Aktivitas
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <div class="card-header border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Aktivitas</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Waktu</label>
                            <p class="mt-1 text-gray-900">
                                {{ $activityLog->time_ago }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $activityLog->created_at->format('d F Y, H:i:s') }} WIB
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">IP Address</label>
                            <p class="mt-1 font-mono text-gray-900">
                                {{ $activityLog->ip_address ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Aksi</label>
                            <p class="mt-1">
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
                                    $colorClass = $actionColors[$activityLog->action] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                    {{ $activityLog->action_label }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Modul</label>
                            <p class="mt-1 text-gray-900">{{ $activityLog->module_label }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Deskripsi</label>
                            <p class="mt-1 text-gray-900">{{ $activityLog->description ?? '-' }}</p>
                        </div>
                        @if($activityLog->model_type && $activityLog->model_id)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Model Type</label>
                            <p class="mt-1 font-mono text-sm text-gray-900">{{ $activityLog->model_type }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Model ID</label>
                            <p class="mt-1 text-gray-900">{{ $activityLog->model_id }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Data Changes -->
            @if($activityLog->old_data || $activityLog->new_data)
            <div class="card">
                <div class="card-header border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Perubahan Data</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @if($activityLog->old_data)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                Data Sebelum
                            </h4>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-xs text-red-800 whitespace-pre-wrap">{{ json_encode($activityLog->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                        @endif
                        @if($activityLog->new_data)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                Data Sesudah
                            </h4>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-xs text-green-800 whitespace-pre-wrap">{{ json_encode($activityLog->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- User Agent -->
            @if($activityLog->user_agent)
            <div class="card">
                <div class="card-header border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Browser</h3>
                </div>
                <div class="card-body">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 font-mono break-all">{{ $activityLog->user_agent }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Admin Info Sidebar -->
        <div class="lg:col-span-1">
            <div class="card sticky top-4">
                <div class="card-header border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Admin</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ $activityLog->admin ? strtoupper(substr($activityLog->admin->name, 0, 1)) : '?' }}
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">
                                {{ $activityLog->admin->name ?? 'Unknown' }}
                            </h4>
                            <p class="text-sm text-gray-500">
                                {{ $activityLog->admin->email ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Role</label>
                            <p class="mt-1">
                                @if($activityLog->admin)
                                    @if($activityLog->admin->isSuperAdmin())
                                        <span class="badge badge-primary">Super Admin</span>
                                    @elseif($activityLog->admin->isAdminOpd())
                                        <span class="badge badge-warning">Admin OPD</span>
                                    @elseif($activityLog->admin->isAdminOrganisasi())
                                        <span class="badge badge-success">Admin Organisasi</span>
                                    @elseif($activityLog->admin->isAdminBkpsdm())
                                        <span class="badge badge-info">Admin BKPSDM</span>
                                    @else
                                        <span class="badge badge-gray">{{ $activityLog->admin->role }}</span>
                                    @endif
                                @else
                                    <span class="badge badge-gray">-</span>
                                @endif
                            </p>
                        </div>
                        
                        @if($activityLog->admin && $activityLog->admin->opd)
                        <div>
                            <label class="text-sm font-medium text-gray-500">OPD</label>
                            <p class="mt-1 text-gray-900">{{ $activityLog->admin->opd->nama }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1">
                                @if($activityLog->admin && $activityLog->admin->is_active)
                                    <span class="inline-flex items-center gap-1 text-green-600">
                                        <span class="iconify" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-red-600">
                                        <span class="iconify" data-icon="mdi:close-circle" data-width="16" data-height="16"></span>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
