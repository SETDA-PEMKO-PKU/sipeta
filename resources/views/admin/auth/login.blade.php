<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin - AKUPETA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
</head>
<body class="h-full">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo & Title -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center shadow-lg">
                    <span class="iconify text-white" data-icon="mdi:shield-account" data-width="32" data-height="32"></span>
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Admin Login
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    AKUPETA - Aplikasi Kendali Utama Peta Jabatan
                </p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                    <div class="flex">
                        <span class="iconify text-green-400" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                        <p class="ml-3 text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex">
                        <span class="iconify text-red-400" data-icon="mdi:alert-circle" data-width="20" data-height="20"></span>
                        <p class="ml-3 text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form class="mt-8 space-y-6" action="{{ route('admin.login') }}" method="POST">
                @csrf
                <div class="rounded-lg shadow-sm bg-white p-8 space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="iconify text-gray-400" data-icon="mdi:email" data-width="20" data-height="20"></span>
                            </div>
                            <input id="email"
                                   name="email"
                                   type="email"
                                   autocomplete="email"
                                   required
                                   value="{{ old('email') }}"
                                   class="appearance-none relative block w-full pl-10 px-3 py-2.5 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('email') border-red-300 @enderror"
                                   placeholder="admin@example.com">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="iconify text-gray-400" data-icon="mdi:lock" data-width="20" data-height="20"></span>
                            </div>
                            <input id="password"
                                   name="password"
                                   type="password"
                                   autocomplete="current-password"
                                   required
                                   class="appearance-none relative block w-full pl-10 px-3 py-2.5 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember"
                                   name="remember"
                                   type="checkbox"
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <span class="iconify" data-icon="mdi:login" data-width="20" data-height="20"></span>
                            </span>
                            Masuk
                        </button>
                    </div>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-600">
                <p>&copy; {{ date('Y') }} AKUPETA - Aplikasi Kendali Utama Peta Jabatan. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
