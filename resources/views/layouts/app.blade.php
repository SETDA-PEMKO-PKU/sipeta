<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Peta Jabatan')</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Iconify -->
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    @stack('styles')
</head>
<body class="h-full">
    <!-- Toast Notifications -->
    <div x-data class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 320px;">
        <template x-for="message in $store.toast.messages" :key="message.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full"
                 class="px-4 py-3 rounded-lg shadow-lg"
                 :class="{
                     'bg-green-500 text-white': message.type === 'success',
                     'bg-red-500 text-white': message.type === 'error',
                     'bg-yellow-500 text-white': message.type === 'warning',
                     'bg-blue-500 text-white': message.type === 'info'
                 }">
                <div class="flex items-center gap-2">
                    <span class="iconify" :data-icon="{
                        'mdi:check-circle': message.type === 'success',
                        'mdi:alert-circle': message.type === 'error',
                        'mdi:alert': message.type === 'warning',
                        'mdi:information': message.type === 'info'
                    }[message.type]" data-width="20" data-height="20"></span>
                    <span class="text-sm font-medium" x-text="message.message"></span>
                    <button @click="$store.toast.remove(message.id)" class="ml-2 hover:opacity-75">
                        <span class="iconify" data-icon="mdi:close" data-width="14" data-height="14"></span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    @yield('content')

    @stack('scripts')
</body>
</html>
