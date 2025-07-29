<div class="jabatan-item {{ $level > 0 ? 'child' : '' }}" style="margin-left: {{ $level * 2 }}rem;">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h6 class="mb-1">
                <i class="fas fa-user-tie me-2"></i>
                {{ $jabatan->nama }}
            </h6>
            @if($jabatan->kelas && $jabatan->kelas !== 'null')
                <small class="text-muted">
                    <i class="fas fa-star me-1"></i>
                    Kelas: {{ $jabatan->kelas }}
                </small>
            @endif
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <small class="text-muted d-block">Kebutuhan vs Bezetting</small>
                <strong class="text-primary">{{ $jabatan->bezetting }}/{{ $jabatan->kebutuhan }}</strong>
                @php
                    $percentage = $jabatan->kebutuhan > 0 ? ($jabatan->bezetting / $jabatan->kebutuhan) * 100 : 0;
                    $barClass = $percentage >= 80 ? 'bezetting-good' : ($percentage >= 50 ? 'bezetting-warning' : 'bezetting-danger');
                @endphp
                <div class="bezetting-bar mt-1">
                    <div class="bezetting-fill {{ $barClass }}" style="width: {{ min($percentage, 100) }}%"></div>
                </div>
                <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-end">
                @if($jabatan->asns->count() > 0)
                    <span class="badge bg-success">
                        <i class="fas fa-users me-1"></i>
                        {{ $jabatan->asns->count() }} ASN
                    </span>
                @else
                    <span class="badge bg-secondary">
                        <i class="fas fa-user-slash me-1"></i>
                        Kosong
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- ASN yang memegang jabatan ini -->
    @if($jabatan->asns->count() > 0)
        <div class="mt-2">
            <small class="text-muted">ASN:</small>
            <div class="ms-3">
                @foreach($jabatan->asns as $asn)
                    <div class="d-inline-block me-3 mb-1">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-user me-1"></i>
                            {{ $asn->nama }}
                            <small class="text-muted">({{ $asn->nip }})</small>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Jabatan anak (sub jabatan) -->
    @if($jabatan->children->count() > 0)
        <div class="mt-3">
            @foreach($jabatan->children as $childJabatan)
                @include('opds.partials.jabatan-item', ['jabatan' => $childJabatan, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>