@php
    $indent = $level * 24;
    $bezetting = $jabatan->asns->count();
    $gap = $bezetting - $jabatan->kebutuhan;
    $statusClass = $gap < 0 ? 'text-red-600' : ($gap > 0 ? 'text-green-600' : 'text-gray-600');
    $hasChildren = $jabatan->children->count() > 0;
@endphp

<div class="tree-item" style="margin-left: {{ $indent }}px;" x-data="{ expanded: true }">
    <div class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow mb-2">
        <!-- Collapse Button -->
        @if($hasChildren)
            <button @click="expanded = !expanded" class="flex-shrink-0 p-1 hover:bg-gray-100 rounded transition-colors" title="Expand/Collapse">
                <span class="iconify text-gray-600 transition-transform duration-200"
                      :class="expanded ? '' : '-rotate-90'"
                      data-icon="mdi:chevron-down"
                      data-width="20"
                      data-height="20"></span>
            </button>
        @else
            <div class="flex-shrink-0 w-7"></div>
        @endif

        <!-- Icon -->
        <div class="flex-shrink-0">
            @if($jabatan->isRoot())
                <span class="iconify text-blue-500" data-icon="mdi:account-tie" data-width="24" data-height="24"></span>
            @else
                <span class="iconify text-gray-500" data-icon="mdi:briefcase-outline" data-width="24" data-height="24"></span>
            @endif
        </div>

        <!-- Jabatan Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <h4 class="font-semibold text-gray-900 truncate">{{ $jabatan->nama }}</h4>
                <span class="badge badge-sm {{
                    $jabatan->jenis_jabatan == 'Struktural' ? 'badge-primary' :
                    ($jabatan->jenis_jabatan == 'Fungsional' ? 'badge-success' : 'badge-info')
                }}">
                    {{ $jabatan->jenis_jabatan }}
                </span>
                @if($jabatan->kelas)
                    <span class="text-xs text-gray-500">Kelas {{ $jabatan->kelas }}</span>
                @endif
            </div>
            <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                <span class="flex items-center gap-1">
                    <span class="iconify" data-icon="mdi:account-multiple" data-width="14" data-height="14"></span>
                    Kebutuhan: <span class="font-medium">{{ $jabatan->kebutuhan }}</span>
                </span>
                <span class="flex items-center gap-1">
                    <span class="iconify" data-icon="mdi:account-check" data-width="14" data-height="14"></span>
                    Bezetting: <span class="font-medium">{{ $bezetting }}</span>
                </span>
                <span class="flex items-center gap-1 {{ $statusClass }}">
                    <span class="iconify" data-icon="mdi:{{ $gap < 0 ? 'alert-circle' : ($gap > 0 ? 'arrow-up-circle' : 'check-circle') }}" data-width="14" data-height="14"></span>
                    Selisih: <span class="font-medium">{{ $gap >= 0 ? '+' : '' }}{{ $gap }}</span>
                </span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex-shrink-0 flex items-center gap-2">
            <!-- Button Tambah Jabatan - hanya tampil jika bukan Pelaksana/Fungsional -->
            @if(!in_array($jabatan->jenis_jabatan, ['Pelaksana', 'Fungsional']))
                <button
                    @click="$dispatch('open-modal', 'add-sub-jabatan-{{ $jabatan->id }}')"
                    class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white border-0"
                    title="Tambah Sub-Jabatan">
                    <span class="iconify" data-icon="mdi:plus-circle" data-width="14" data-height="14"></span>
                    <span class="ml-1">Jabatan</span>
                </button>
            @endif

            <!-- Button Lihat Pegawai -->
            @if($jabatan->asns->count() > 0)
                <button
                    @click="$dispatch('open-modal', 'view-asn-{{ $jabatan->id }}')"
                    class="btn btn-sm bg-purple-500 hover:bg-purple-600 text-white border-0"
                    title="Lihat Daftar Pegawai">
                    <span class="iconify" data-icon="mdi:account-group" data-width="14" data-height="14"></span>
                    <span class="ml-1">Pegawai ({{ $jabatan->asns->count() }})</span>
                </button>
            @endif

            <!-- Button Tambah ASN -->
            <button
                @click="$dispatch('open-modal', 'add-asn')"
                class="btn btn-sm bg-green-500 hover:bg-green-600 text-white border-0"
                title="Tambah Pegawai/ASN">
                <span class="iconify" data-icon="mdi:account-plus" data-width="14" data-height="14"></span>
                <span class="ml-1">ASN</span>
            </button>

            <!-- Button Edit -->
            <button
                @click="$dispatch('edit-jabatan', {{ json_encode([
                    'id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'kebutuhan' => $jabatan->kebutuhan,
                    'parent_id' => $jabatan->parent_id
                ]) }})"
                class="btn btn-sm btn-square bg-amber-500 hover:bg-amber-600 text-white border-0"
                title="Edit Jabatan">
                <span class="iconify" data-icon="mdi:pencil" data-width="16" data-height="16"></span>
            </button>

            <!-- Button Hapus -->
            <form action="{{ route('admin.opds.jabatan.destroy', [$opd->id, $jabatan->id]) }}"
                  method="POST"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-square bg-red-500 hover:bg-red-600 text-white border-0" title="Hapus Jabatan">
                    <span class="iconify" data-icon="mdi:delete" data-width="16" data-height="16"></span>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Tambah Sub-Jabatan -->
    <x-modal name="add-sub-jabatan-{{ $jabatan->id }}" title="Tambah Sub-Jabatan di bawah {{ $jabatan->nama }}" maxWidth="lg">
        <form action="{{ route('admin.opds.jabatan.store', $opd->id) }}" method="POST">
            @csrf
            <input type="hidden" name="parent_jabatan_id" value="{{ $jabatan->id }}">

            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center gap-2 text-sm text-blue-800">
                        <span class="iconify" data-icon="mdi:information" data-width="18" data-height="18"></span>
                        <div>
                            <strong>Parent Jabatan:</strong> {{ $jabatan->nama }}<br>
                            <span class="text-xs">Sub-jabatan baru akan ditambahkan di bawah jabatan ini</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                    <input type="text" name="nama" required class="input w-full" placeholder="Contoh: Kepala Seksi Kurikulum">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Jabatan</label>
                    <select name="jenis_jabatan" required class="input w-full">
                        <option value="">Pilih Jenis Jabatan</option>
                        <option value="Struktural">Struktural</option>
                        <option value="Fungsional">Fungsional</option>
                        <option value="Pelaksana">Pelaksana</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Jabatan</label>
                    <input type="number" name="kelas" class="input w-full" placeholder="Contoh: 9" min="1" max="17">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan</label>
                    <input type="number" name="kebutuhan" required class="input w-full" placeholder="Jumlah kebutuhan" min="0" value="1">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'add-sub-jabatan-{{ $jabatan->id }}')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Sub-Jabatan</button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: View ASN (untuk semua yang punya ASN) -->
    @if($jabatan->asns->count() > 0)
        <x-modal name="view-asn-{{ $jabatan->id }}" title="Daftar ASN - {{ $jabatan->nama }}" maxWidth="lg">
            <div class="space-y-3">
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Jabatan:</span> {{ $jabatan->nama }}<br>
                        <span class="font-semibold">Total ASN:</span> {{ $jabatan->asns->count() }} dari {{ $jabatan->kebutuhan }} kebutuhan
                    </div>
                </div>

                @foreach($jabatan->asns as $asn)
                    <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="iconify text-blue-600" data-icon="mdi:account" data-width="20" data-height="20"></span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $asn->nama }}</div>
                                <div class="text-xs text-gray-500">NIP: {{ $asn->nip }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="$dispatch('edit-asn', {{ json_encode([
                                    'id' => $asn->id,
                                    'nama' => $asn->nama,
                                    'nip' => $asn->nip,
                                    'jabatan_id' => $asn->jabatan_id
                                ]) }})"
                                class="btn btn-xs btn-square bg-amber-500 hover:bg-amber-600 text-white border-0"
                                title="Edit Pegawai">
                                <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                            </button>
                            <form action="{{ route('admin.opds.asn.destroy', [$opd->id, $asn->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus ASN {{ $asn->nama }}?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-square bg-red-500 hover:bg-red-600 text-white border-0" title="Hapus Pegawai">
                                    <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="$dispatch('close-modal', 'view-asn-{{ $jabatan->id }}')" class="btn btn-outline">
                    Tutup
                </button>
            </div>
        </x-modal>
    @endif

    <!-- Child Jabatan (Recursive) -->
    @if($jabatan->children->count() > 0)
        <div x-show="expanded"
             x-collapse
             class="ml-6">
            @foreach($jabatan->children as $childJabatan)
                @include('opds.partials.tree-jabatan', [
                    'jabatan' => $childJabatan,
                    'opd' => $opd,
                    'level' => $level + 1
                ])
            @endforeach
        </div>
    @endif
</div>
