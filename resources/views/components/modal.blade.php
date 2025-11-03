@props(['name', 'title' => '', 'maxWidth' => 'md'])

@php
$maxWidthClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$maxWidth];
@endphp

<div x-data="{ show: false }"
     @open-modal.window="if ($event.detail === '{{ $name }}') { show = true; }"
     @close-modal.window="if ($event.detail === '{{ $name }}') { show = false; }"
     @keydown.escape.window="if (show) show = false"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-50"
     style="display: none;">

    <!-- Backdrop -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

    <!-- Modal Container - dengan z-index lebih tinggi dari backdrop -->
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.outside="show = false"
                 class="relative bg-white rounded-lg shadow-xl w-full {{ $maxWidthClass }}">

                <!-- Header -->
                @if($title)
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition-colors" type="button">
                        <span class="iconify" data-icon="mdi:close" data-width="20" data-height="20"></span>
                    </button>
                </div>
                @endif

                <!-- Content -->
                <div class="px-4 py-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
