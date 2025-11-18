@php
    $bezetting = $jabatan->asns->count();
    $gap = $bezetting - $jabatan->kebutuhan;

    $nodeClass = 'org-node ';
    if ($jabatan->isRoot()) {
        $nodeClass .= 'kepala';
    } elseif ($jabatan->jenis_jabatan == 'Struktural') {
        $nodeClass .= 'struktural';
    } else {
        $nodeClass .= 'fungsional';
    }
@endphp

<div style="display: flex; flex-direction: column; align-items: center;">
    <!-- Node -->
    <div class="{{ $nodeClass }}">
        @if($level > 0)
            <div class="org-connector"></div>
        @endif

        <div class="org-node-header">
            {{ $jabatan->jenis_jabatan }}
        </div>
        <div class="org-node-title">
            {{ $jabatan->nama }}
        </div>
        @if($jabatan->kelas)
            <div class="org-node-kelas">Kelas {{ $jabatan->kelas }}</div>
        @endif

        <div class="org-node-stats">
            <div class="org-node-stat">
                <div class="org-node-stat-value text-blue-600">{{ $jabatan->kebutuhan }}</div>
                <div class="org-node-stat-label">Kebutuhan</div>
            </div>
            <div class="org-node-stat">
                <div class="org-node-stat-value text-green-600">{{ $bezetting }}</div>
                <div class="org-node-stat-label">Bezetting</div>
            </div>
            <div class="org-node-stat">
                <div class="org-node-stat-value {{ $gap < 0 ? 'text-red-600' : ($gap > 0 ? 'text-orange-600' : 'text-gray-600') }}">
                    {{ $gap >= 0 ? '+' : '' }}{{ $gap }}
                </div>
                <div class="org-node-stat-label">Selisih</div>
            </div>
        </div>

        @if($jabatan->asns->count() > 0)
            <div class="mt-2 pt-2 border-t border-gray-200">
                <div class="text-xs text-gray-600 font-semibold mb-1">ASN:</div>
                @foreach($jabatan->asns->take(3) as $asn)
                    <div class="text-xs text-gray-700">{{ $asn->nama }}</div>
                @endforeach
                @if($jabatan->asns->count() > 3)
                    <div class="text-xs text-gray-500 mt-1">+{{ $jabatan->asns->count() - 3 }} lainnya</div>
                @endif
            </div>
        @endif
    </div>

    <!-- Children -->
    @if($jabatan->children->count() > 0)
        <div class="org-level" style="margin-top: 2rem; position: relative;">
            @if($jabatan->children->count() > 1)
                <div class="org-horizontal-line"></div>
            @endif

            @foreach($jabatan->children as $child)
                @include('opds.partials.peta-jabatan-node', [
                    'jabatan' => $child,
                    'level' => $level + 1,
                    'isFirst' => $loop->first
                ])
            @endforeach
        </div>
    @endif
</div>
