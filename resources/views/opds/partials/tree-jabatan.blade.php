@php
    $bezetting = $jabatan->asns->count();
    $kebutuhan = $jabatan->kebutuhan;
    $badgeClass = 'badge-gray';
    if ($bezetting > 0) {
        if ($bezetting == $kebutuhan) $badgeClass = 'badge-success';
        elseif ($bezetting < $kebutuhan) $badgeClass = 'badge-warning';
        else $badgeClass = 'badge-danger';
    }
    $treeId = 'jabatan-' . $jabatan->id;
@endphp

<div class="tree-item" data-tree-node="{{ $treeId }}" x-data="{ expanded: false }" role="treeitem" :aria-expanded="expanded">
    <div class="tree-node tree-node-{{ $type }} group"
         :style="{ marginLeft: '{{ $level * 1.5 }}rem' }"
         @click="expanded = !expanded"
         tabindex="0"
         @keydown.enter="expanded = !expanded"
         @keydown.space.prevent="expanded = !expanded"
         :aria-label="'Jabatan: {{ $jabatan->nama }}, Bezetting: {{ $bezetting }}, Kebutuhan: {{ $kebutuhan }}'">

        <!-- Toggle Button -->
        @if($jabatan->asns->count() > 0)
            <button class="tree-toggle"
                    @click.stop="expanded = !expanded"
                    :aria-label="expanded ? 'Collapse ASN list' : 'Expand ASN list'"
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
        <span class="iconify flex-shrink-0 {{ $type === 'kepala' ? 'text-amber-600' : 'text-green-600' }}"
              :data-icon="'{{ $type === 'kepala' ? 'mdi:crown' : 'mdi:account-tie' }}'"
              data-width="18"
              data-height="18"
              aria-hidden="true"></span>

        <!-- Label -->
        <div class="flex-1 min-w-0">
            <div class="font-medium text-gray-900 truncate leading-tight">{{ $jabatan->nama }}</div>
            <div class="flex items-center gap-3 text-xs text-gray-600 mt-0.5">
                @if($jabatan->jenis_jabatan)
                    <span class="inline-flex items-center gap-1">
                        <span class="iconify text-gray-400" data-icon="mdi:tag" data-width="12" data-height="12"></span>
                        {{ $jabatan->jenis_jabatan }}
                    </span>
                @endif
                @if($jabatan->kelas)
                    <span class="inline-flex items-center gap-1">
                        <span class="iconify text-gray-400" data-icon="mdi:certificate" data-width="12" data-height="12"></span>
                        Kelas {{ $jabatan->kelas }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Bezetting Badge -->
        <span class="badge {{ $badgeClass }} text-xs whitespace-nowrap font-semibold" aria-label="Bezetting {{ $bezetting }} dari {{ $kebutuhan }}">
            {{ $bezetting }}/{{ $kebutuhan }}
        </span>

        <!-- Actions -->
        <div class="tree-actions" role="group" aria-label="Actions for {{ $jabatan->nama }}">
            <button @click.stop="$dispatch('add-asn', { jabatanId: {{ $jabatan->id }}, nama: '{{ $jabatan->nama }}' })"
                    class="p-1.5 hover:bg-green-100 rounded-md text-green-600 transition-colors"
                    title="Tambah ASN"
                    aria-label="Tambah ASN ke {{ $jabatan->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:account-plus" data-width="14" data-height="14"></span>
            </button>
            <button @click.stop="$dispatch('edit-jabatan', { id: {{ $jabatan->id }} })"
                    class="p-1.5 hover:bg-yellow-100 rounded-md text-yellow-600 transition-colors"
                    title="Edit Jabatan"
                    aria-label="Edit {{ $jabatan->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
            </button>
            <button @click.stop="$dispatch('delete-jabatan', { id: {{ $jabatan->id }}, nama: '{{ $jabatan->nama }}' })"
                    class="p-1.5 hover:bg-red-100 rounded-md text-red-600 transition-colors"
                    title="Hapus Jabatan"
                    aria-label="Hapus {{ $jabatan->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
            </button>
        </div>
    </div>

    <!-- ASN List -->
    @if($jabatan->asns->count() > 0)
        <div x-show="expanded"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="tree-children space-y-1 mt-1"
             :style="{ marginLeft: '{{ ($level + 1) * 1.5 }}rem' }"
             role="group">
            @foreach($jabatan->asns as $asn)
                <div class="tree-node tree-node-asn group"
                     role="treeitem"
                     tabindex="0"
                     aria-label="ASN: {{ $asn->nama }}, NIP: {{ $asn->nip }}">
                    <span class="w-5"></span>
                    <span class="iconify text-purple-600 flex-shrink-0" data-icon="mdi:account" data-width="14" data-height="14" aria-hidden="true"></span>
                    <span class="flex-1 text-gray-900 font-medium">{{ $asn->nama }}</span>
                    <span class="text-gray-500 text-xs font-mono">{{ $asn->nip }}</span>
                    <div class="tree-actions" role="group" aria-label="Actions for {{ $asn->nama }}">
                        <button @click.stop="$dispatch('edit-asn', { id: {{ $asn->id }}, nama: '{{ $asn->nama }}', nip: '{{ $asn->nip }}', jabatanId: {{ $jabatan->id }} })"
                                class="p-1 hover:bg-yellow-100 rounded text-yellow-600 transition-colors"
                                title="Edit ASN"
                                aria-label="Edit {{ $asn->nama }}"
                                type="button">
                            <span class="iconify" data-icon="mdi:pencil" data-width="12" data-height="12"></span>
                        </button>
                        <button @click.stop="$dispatch('delete-asn', { id: {{ $asn->id }}, nama: '{{ $asn->nama }}' })"
                                class="p-1 hover:bg-red-100 rounded text-red-600 transition-colors"
                                title="Hapus ASN"
                                aria-label="Hapus {{ $asn->nama }}"
                                type="button">
                            <span class="iconify" data-icon="mdi:delete" data-width="12" data-height="12"></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
