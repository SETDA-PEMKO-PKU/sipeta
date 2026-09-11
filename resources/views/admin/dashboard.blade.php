@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="p-4 lg:p-8 space-y-6">
    <!-- Breadcrumbs -->
    @if(auth('admin')->user()->isAdminOpd() && auth('admin')->user()->opd)
    <nav class="flex items-center gap-2 text-sm text-gray-600">
        <span class="iconify" data-icon="mdi:office-building" data-width="16" data-height="16"></span>
        <span class="font-medium text-gray-900">{{ auth('admin')->user()->opd->nama }}</span>
        <span class="iconify" data-icon="mdi:chevron-right" data-width="16" data-height="16"></span>
        <span>Dashboard</span>
    </nav>
    @endif

    <!-- Welcome Card -->
    <div class="card bg-gradient-to-r from-primary-600 to-primary-700 text-white">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Selamat Datang, {{ auth('admin')->user()->name }}!</h2>
                    @if($opdInfo)
                        <p class="text-primary-100 text-lg font-semibold mb-1">{{ $opdInfo->nama }}</p>
                        <p class="text-primary-200 text-sm">Admin OPD - Kelola data OPD Anda</p>
                    @else
                        <p class="text-primary-100">Kelola sistem peta jabatan dengan mudah</p>
                    @endif
                </div>
                <div class="hidden md:block">
                    <span class="iconify text-primary-200" data-icon="mdi:chart-line" data-width="64" data-height="64"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(!$opdInfo)
        <!-- Total OPD - Only show for non-admin OPD -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total OPD</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_opd'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-blue-600" data-icon="mdi:office-building" data-width="24" data-height="24"></span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Total Jabatan -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Jabatan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_jabatan'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-purple-600" data-icon="mdi:briefcase" data-width="24" data-height="24"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total ASN -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total ASN</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_asn'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-yellow-600" data-icon="mdi:account-group" data-width="24" data-height="24"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fill Rate -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tingkat Pengisian</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['fill_rate'] }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-green-600" data-icon="mdi:chart-box" data-width="24" data-height="24"></span>
                    </div>
                </div>
            </div>
        </div>

        @if(!$opdInfo)
        <!-- Total Admin - Only show for non-admin OPD -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Admin</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_admin'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <span class="iconify text-red-600" data-icon="mdi:shield-account" data-width="24" data-height="24"></span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.opds.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <span class="iconify text-primary-600" data-icon="mdi:office-building" data-width="24" data-height="24"></span>
                    <div>
                        <p class="font-medium text-gray-900">Kelola OPD</p>
                        <p class="text-sm text-gray-600">Lihat dan kelola data OPD</p>
                    </div>
                </a>

                <a href="{{ route('admin.admins.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <span class="iconify text-primary-600" data-icon="mdi:account-group" data-width="24" data-height="24"></span>
                    <div>
                        <p class="font-medium text-gray-900">Kelola Admin</p>
                        <p class="text-sm text-gray-600">Tambah atau edit admin</p>
                    </div>
                </a>

                <a href="{{ route('admin.admins.create') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <span class="iconify text-primary-600" data-icon="mdi:account-plus" data-width="24" data-height="24"></span>
                    <div>
                        <p class="font-medium text-gray-900">Tambah Admin</p>
                        <p class="text-sm text-gray-600">Buat akun admin baru</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity Info -->
    <div class="card">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Status Sistem</span>
                    <span class="badge badge-success">Aktif</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Role Anda</span>
                    <span class="badge {{ auth('admin')->user()->isSuperAdmin() ? 'badge-primary' : 'badge-gray' }}">
                        @if(auth('admin')->user()->isSuperAdmin())
                            Super Admin
                        @elseif(auth('admin')->user()->isAdminOpd())
                            Admin OPD
                        @elseif(auth('admin')->user()->isAdminOrganisasi())
                            Admin Organisasi
                        @elseif(auth('admin')->user()->isAdminBkpsdm())
                            Admin BKPSDM
                        @else
                            Admin
                        @endif
                    </span>
                </div>
                @if($opdInfo)
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">OPD Anda</span>
                    <span class="text-gray-900 font-medium">{{ $opdInfo->nama }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-600">Terakhir Login</span>
                    <span class="text-gray-900 font-medium">{{ now()->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
