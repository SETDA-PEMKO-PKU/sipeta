<div class="ms-{{ $level * 3 }} mb-3">
    <div class="card border-start border-3 border-secondary">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fas fa-folder-open me-2"></i>
                {{ $bagian->nama }}
                <small class="text-muted ms-2">
                    ({{ $bagian->jabatans->count() }} jabatan)
                </small>
            </h6>
        </div>
        <div class="card-body">
            <!-- Jabatan dalam bagian ini -->
            @if($bagian->jabatans->count() > 0)
                @foreach($bagian->jabatans->where('parent_id', null) as $jabatan)
                    @include('opds.partials.jabatan-item', ['jabatan' => $jabatan, 'level' => 0])
                @endforeach
            @else
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Belum ada jabatan dalam bagian ini
                </p>
            @endif

            <!-- Sub bagian -->
            @if($bagian->children->count() > 0)
                <div class="mt-3">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-sitemap me-1"></i>
                        Sub Bagian:
                    </h6>
                    @foreach($bagian->children as $subBagian)
                        @include('opds.partials.bagian-item', ['bagian' => $subBagian, 'level' => $level + 1])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>