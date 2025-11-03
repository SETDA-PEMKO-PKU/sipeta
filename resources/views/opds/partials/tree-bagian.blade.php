@php
    $hasChildren = $bagian->children->count() > 0 || $bagian->jabatans->count() > 0;
    $nodeClass = $level == 0 ? 'tree-node-bagian' : 'tree-node-sub-bagian';
    $treeId = 'bagian-' . $bagian->id;
@endphp

<div class="tree-item" data-tree-node="{{ $treeId }}" x-data="{ expanded: false }" role="treeitem" :aria-expanded="expanded">
    <div class="tree-node {{ $nodeClass }} group"
         :style="{ marginLeft: '{{ $level * 1.5 }}rem' }"
         @click="expanded = !expanded"
         tabindex="0"
         @keydown.enter="expanded = !expanded"
         @keydown.space.prevent="expanded = !expanded"
         :aria-label="'Bagian: {{ $bagian->nama }}, {{ $bagian->jabatans->count() }} jabatan'">

        <!-- Toggle Button -->
        @if($hasChildren)
            <button class="tree-toggle"
                    @click.stop="expanded = !expanded"
                    :aria-label="expanded ? 'Collapse section' : 'Expand section'"
                    type="button">
                <span class="iconify transition-transform duration-200"
                      :class="expanded ? 'rotate-90' : ''"
                      data-icon="mdi:chevron-right"
                      data-width="14"
                      data-height="14"></span>
            </button>
        @else
            <span class="w-5"></span>
        @endif

        <!-- Icon -->
        <span class="iconify flex-shrink-0 {{ $level == 0 ? 'text-blue-600' : 'text-indigo-600' }}"
              :data-icon="'{{ $level == 0 ? 'mdi:folder' : 'mdi:folder-open' }}'"
              data-width="18"
              data-height="18"
              aria-hidden="true"></span>

        <!-- Label -->
        <div class="flex-1 min-w-0">
            <span class="font-semibold text-gray-900 truncate leading-tight">{{ $bagian->nama }}</span>
        </div>

        <!-- Count Badge -->
        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white/50 rounded-full text-xs text-gray-600 font-medium">
            <span class="iconify text-gray-400" data-icon="mdi:account-tie" data-width="12" data-height="12"></span>
            {{ $bagian->jabatans->count() }}
        </span>

        <!-- Actions -->
        <div class="tree-actions" role="group" aria-label="Actions for {{ $bagian->nama }}">
            <button @click.stop="$dispatch('edit-bagian', { id: {{ $bagian->id }}, nama: '{{ $bagian->nama }}' })"
                    class="p-1.5 hover:bg-yellow-100 rounded-md text-yellow-600 transition-colors"
                    title="Edit Bagian"
                    aria-label="Edit {{ $bagian->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
            </button>
            <button @click.stop="$dispatch('delete-bagian', { id: {{ $bagian->id }}, nama: '{{ $bagian->nama }}' })"
                    class="p-1.5 hover:bg-red-100 rounded-md text-red-600 transition-colors"
                    title="Hapus Bagian"
                    aria-label="Hapus {{ $bagian->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
            </button>
        </div>
    </div>

    <!-- Children -->
    @if($hasChildren)
        <div x-show="expanded"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="tree-children space-y-1 mt-1"
             role="group">

            <!-- Sub Bagian -->
            @foreach($bagian->children as $subBagian)
                @include('opds.partials.tree-bagian', [
                    'bagian' => $subBagian,
                    'level' => $level + 1
                ])
            @endforeach

            <!-- Jabatan -->
            @foreach($bagian->jabatans as $jabatan)
                @include('opds.partials.tree-jabatan', [
                    'jabatan' => $jabatan,
                    'type' => 'jabatan',
                    'level' => $level + 1
                ])
            @endforeach
        </div>
    @endif
</div>
