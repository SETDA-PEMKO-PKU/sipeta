<div class="jabatan-item {{ $level > 0 ? 'child' : '' }}" style="margin-left: {{ $level * 2 }}rem;">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="mb-1">
                <i class="fas fa-user-tie me-2"></i>
                {{ $jabatan->nama }}
            </h6>
            <div class="mt-2">
                @if($jabatan->jenis_jabatan)
                    <span class="badge bg-info me-1">{{ $jabatan->jenis_jabatan }}</span>
                @endif
                
                @if($jabatan->kelas && $jabatan->kelas !== 'null')
                    <small class="text-muted me-2">Kelas: {{ $jabatan->kelas }}</small>
                @endif
                
                <small class="text-muted me-2">Bezetting: {{ $jabatan->asns->count() }}</small>
                <small class="text-muted me-2">Kebutuhan: {{ $jabatan->kebutuhan }}</small>
                
                @php
                    $selisih = $jabatan->asns->count() - $jabatan->kebutuhan;
                @endphp
                @if($selisih > 0)
                    <small class="text-warning me-2">+/-: +{{ $selisih }}</small>
                @elseif($selisih < 0)
                    <small class="text-danger me-2">+/-: {{ $selisih }}</small>
                @else
                    <small class="text-success me-2">+/-: 0</small>
                @endif
            </div>
        </div>
        <div class="col-md-4">
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
                        <span class="badge bg-light text-dark position-relative">
                            <i class="fas fa-user me-1"></i>
                            {{ $asn->nama }}
                            <small class="text-muted">({{ $asn->nip }})</small>
                            
                            <!-- Tombol aksi ASN -->
                            <div class="btn-group btn-group-sm ms-2" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="editAsn({{ $asn->id }}, '{{ $asn->nama }}', '{{ $asn->nip }}', {{ $asn->jabatan_id }}, {{ $asn->bagian_id ?? 'null' }})" 
                                        title="Edit ASN">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        onclick="deleteAsn({{ $asn->id }}, '{{ $asn->nama }}')" 
                                        title="Hapus ASN">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
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