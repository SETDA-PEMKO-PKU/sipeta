<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Sistem Peta Jabatan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex-shrink-0 flex flex-col"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             style="width: 16rem;">

            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-primary-600 to-primary-700 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                        <span class="iconify text-primary-600" data-icon="mdi:shield-account" data-width="20" data-height="20"></span>
                    </div>
                    <span class="text-white font-semibold text-lg">Admin Panel</span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white">
                    <span class="iconify" data-icon="mdi:close" data-width="24" data-height="24"></span>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="px-4 py-6 space-y-1 flex-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="iconify" data-icon="mdi:view-dashboard" data-width="20" data-height="20"></span>
                    <span class="font-medium">Dashboard</span>
                </a>

                <div class="pt-4 pb-2 px-4">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen</span>
                </div>

                <a href="{{ route('admin.opds.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.opds.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="iconify" data-icon="mdi:office-building" data-width="20" data-height="20"></span>
                    <span class="font-medium">Data OPD</span>
                </a>

                <a href="{{ route('admin.admins.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.admins.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="iconify" data-icon="mdi:account-group" data-width="20" data-height="20"></span>
                    <span class="font-medium">Kelola Admin</span>
                </a>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth('admin')->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth('admin')->user()->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Logout">
                            <span class="iconify" data-icon="mdi:logout" data-width="20" data-height="20"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center px-4 lg:px-8">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 mr-4">
                    <span class="iconify" data-icon="mdi:menu" data-width="24" data-height="24"></span>
                </button>

                <div class="flex-1">
                    <h1 class="text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-2">
                    @if(auth('admin')->user()->isSuperAdmin())
                        <span class="badge badge-primary text-xs">Super Admin</span>
                    @else
                        <span class="badge badge-gray text-xs">Admin</span>
                    @endif
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-auto">
                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="mx-4 lg:mx-8 mt-4">
                        <div class="alert alert-success flex items-center gap-2 animate-fade-in">
                            <span class="iconify" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mx-4 lg:mx-8 mt-4">
                        <div class="alert alert-error flex items-center gap-2 animate-fade-in">
                            <span class="iconify" data-icon="mdi:alert-circle" data-width="18" data-height="18"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-4 lg:px-8">
                <div class="text-center text-sm text-gray-600">
                    &copy; {{ date('Y') }} Sistem Peta Jabatan. All rights reserved.
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
         style="display: none;"></div>

    @stack('scripts')
</body>
</html>
