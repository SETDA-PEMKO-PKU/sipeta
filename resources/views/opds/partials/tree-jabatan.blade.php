@php
    $indent = $level * 24;
    $bezetting = $jabatan->asns->count();
    $gap = $bezetting - $jabatan->kebutuhan;
    $statusClass = $gap < 0 ? 'text-red-600' : ($gap > 0 ? 'text-green-600' : 'text-gray-600');
@endphp

<div class="tree-item" style="margin-left: {{ $indent }}px;">
    <div class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow mb-2">
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
                    ($jabatan->jenis_jabatan == 'Fungsional' ? 'badge-success' :
                    ($jabatan->jenis_jabatan == 'Staf Ahli' ? 'badge-warning' : 'badge-info'))
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

            <!-- Single ASN Display (jika hanya 1 ASN) -->
            @if($jabatan->asns->count() == 1)
                @php $asn = $jabatan->asns->first(); @endphp
                <div class="flex items-center gap-2 mt-2 text-sm">
                    <span class="iconify text-blue-500" data-icon="mdi:account" data-width="16" data-height="16"></span>
                    <span class="font-medium text-gray-900">{{ $asn->nama }}</span>
                    <span class="text-gray-500 text-xs">NIP: {{ $asn->nip }}</span>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex-shrink-0 flex items-center gap-2">
            <!-- Button Lihat Pegawai (jika lebih dari 1 ASN) -->
            @if($jabatan->asns->count() > 1)
                <button
                    @click="$dispatch('open-modal', 'view-asn-{{ $jabatan->id }}')"
                    class="btn btn-sm btn-info flex items-center gap-1">
                    <span class="iconify" data-icon="mdi:account-group" data-width="16" data-height="16"></span>
                    <span>Lihat Pegawai ({{ $jabatan->asns->count() }})</span>
                </button>
            @endif

            <button
                @click="$dispatch('open-modal', 'add-asn')"
                class="btn btn-sm btn-success flex items-center gap-1">
                <span class="iconify" data-icon="mdi:account-plus" data-width="16" data-height="16"></span>
                <span>Tambah ASN</span>
            </button>

            <button
                @click="$store.editJabatan = {{ json_encode([
                    'id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'jenis_jabatan' => $jabatan->jenis_jabatan,
                    'kelas' => $jabatan->kelas,
                    'kebutuhan' => $jabatan->kebutuhan,
                    'parent_id' => $jabatan->parent_id
                ]) }}"
                class="btn btn-sm btn-outline flex items-center gap-1">
                <span class="iconify" data-icon="mdi:pencil" data-width="16" data-height="16"></span>
                <span>Edit</span>
            </button>

            <form action="{{ route('admin.opds.jabatan.destroy', [$opd->id, $jabatan->id]) }}"
                  method="POST"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-error flex items-center gap-1">
                    <span class="iconify" data-icon="mdi:delete" data-width="16" data-height="16"></span>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: View ASN (jika lebih dari 1 ASN) -->
    @if($jabatan->asns->count() > 1)
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
                            <form action="{{ route('admin.opds.asn.destroy', [$opd->id, $asn->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus ASN {{ $asn->nama }}?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-error flex items-center gap-1">
                                    <span class="iconify" data-icon="mdi:delete" data-width="12" data-height="12"></span>
                                    <span class="text-xs">Hapus</span>
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
        <div class="ml-6">
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
