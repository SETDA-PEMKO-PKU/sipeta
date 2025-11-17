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

<div class="tree-item" data-tree-node="{{ $treeId }}" x-data="{ showPegawaiModal: false }" role="treeitem">
    <div class="tree-node tree-node-{{ $type }} group"
         :style="{ marginLeft: '{{ $level * 1.5 }}rem' }"
         tabindex="0"
         :aria-label="'Jabatan: {{ $jabatan->nama }}, Bezetting: {{ $bezetting }}, Kebutuhan: {{ $kebutuhan }}'">

        <!-- Placeholder for alignment -->
        <span class="w-5"></span>

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
        <div class="tree-actions flex gap-1" role="group" aria-label="Actions for {{ $jabatan->nama }}">
            @if($jabatan->asns->count() > 0)
                <button @click.stop="showPegawaiModal = true"
                        class="flex items-center gap-1 px-2 py-1.5 hover:bg-purple-100 rounded-md text-purple-600 transition-colors text-xs font-medium"
                        title="Lihat Pegawai"
                        aria-label="Lihat Pegawai di {{ $jabatan->nama }}"
                        type="button">
                    <span class="iconify" data-icon="mdi:account-group" data-width="14" data-height="14"></span>
                    <span>Lihat Pegawai</span>
                </button>
            @endif
            @if($bezetting < $kebutuhan)
                <button @click.stop="$dispatch('add-asn', { jabatanId: {{ $jabatan->id }}, nama: '{{ $jabatan->nama }}' })"
                        class="flex items-center gap-1 px-2 py-1.5 hover:bg-green-100 rounded-md text-green-600 transition-colors text-xs font-medium"
                        title="Tambah ASN"
                        aria-label="Tambah ASN ke {{ $jabatan->nama }}"
                        type="button">
                    <span class="iconify" data-icon="mdi:account-plus" data-width="14" data-height="14"></span>
                    <span>Tambah</span>
                </button>
            @endif
            <button @click.stop="$dispatch('edit-jabatan', { id: {{ $jabatan->id }} })"
                    class="flex items-center gap-1 px-2 py-1.5 hover:bg-yellow-100 rounded-md text-yellow-600 transition-colors text-xs font-medium"
                    title="Edit Jabatan"
                    aria-label="Edit {{ $jabatan->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                <span>Edit</span>
            </button>
            <button @click.stop="$dispatch('delete-jabatan', { id: {{ $jabatan->id }}, nama: '{{ $jabatan->nama }}' })"
                    class="flex items-center gap-1 px-2 py-1.5 hover:bg-red-100 rounded-md text-red-600 transition-colors text-xs font-medium"
                    title="Hapus Jabatan"
                    aria-label="Hapus {{ $jabatan->nama }}"
                    type="button">
                <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                <span>Hapus</span>
            </button>
        </div>
    </div>

    <!-- Modal Pegawai -->
    @if($jabatan->asns->count() > 0)
        <div x-show="showPegawaiModal"
             @click.self="showPegawaiModal = false"
             @keydown.escape.window="showPegawaiModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none; background-color: rgba(0, 0, 0, 0.5);">
            <div @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Daftar Pegawai</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $jabatan->nama }}</p>
                    </div>
                    <button @click="showPegawaiModal = false"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="iconify" data-icon="mdi:close" data-width="24" data-height="24"></span>
                    </button>
                </div>

                <!-- Content -->
                <div class="px-6 py-4 overflow-y-auto max-h-[calc(80vh-140px)]">
                    <div class="space-y-2">
                        @foreach($jabatan->asns as $asn)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group">
                                <span class="iconify text-purple-600 flex-shrink-0" data-icon="mdi:account-circle" data-width="32" data-height="32"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900">{{ $asn->nama }}</div>
                                    <div class="text-sm text-gray-500 font-mono">{{ $asn->nip }}</div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click.stop="showPegawaiModal = false; $dispatch('edit-asn', { id: {{ $asn->id }}, nama: '{{ $asn->nama }}', nip: '{{ $asn->nip }}', jabatanId: {{ $jabatan->id }} })"
                                            class="p-1.5 hover:bg-yellow-100 rounded text-yellow-600 transition-colors"
                                            title="Edit ASN">
                                        <span class="iconify" data-icon="mdi:pencil" data-width="16" data-height="16"></span>
                                    </button>
                                    <button @click.stop="showPegawaiModal = false; $dispatch('delete-asn', { id: {{ $asn->id }}, nama: '{{ $asn->nama }}' })"
                                            class="p-1.5 hover:bg-red-100 rounded text-red-600 transition-colors"
                                            title="Hapus ASN">
                                        <span class="iconify" data-icon="mdi:delete" data-width="16" data-height="16"></span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Total: <span class="font-semibold">{{ $jabatan->asns->count() }}</span> pegawai
                    </div>
                    <button @click="showPegawaiModal = false"
                            class="btn btn-outline btn-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
