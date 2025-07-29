<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $opd->nama }} - Detail OPD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .bagian-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .bagian-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .jabatan-item {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 0.75rem;
            margin: 0.5rem 0;
            border-radius: 0 4px 4px 0;
        }
        .jabatan-item.child {
            margin-left: 2rem;
            border-left-color: #6c757d;
        }
        .stats-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
        }
        .bezetting-bar {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }
        .bezetting-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        .bezetting-good { background: #28a745; }
        .bezetting-warning { background: #ffc107; }
        .bezetting-danger { background: #dc3545; }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('opds.index') }}" class="text-white">
                                    <i class="fas fa-home"></i> Beranda
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-white" aria-current="page">
                                {{ $opd->nama }}
                            </li>
                        </ol>
                    </nav>
                    <h1 class="display-5 mb-3">
                        <i class="fas fa-building me-3"></i>
                        {{ $opd->nama }}
                    </h1>
                    <p class="lead">Struktur Organisasi dan Peta Jabatan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Statistik OPD -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3 class="mb-1">{{ $opd->bagians->count() }}</h3>
                    <small>Total Bagian</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-user-tie fa-2x mb-2"></i>
                    <h3 class="mb-1">{{ $opd->jabatans->count() }}</h3>
                    <small>Total Jabatan</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <h3 class="mb-1">{{ $opd->jabatans->sum('kebutuhan') }}</h3>
                    <small>Kebutuhan ASN</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h3 class="mb-1">{{ $opd->jabatans->sum('bezetting') }}</h3>
                    <small>Bezetting ASN</small>
                </div>
            </div>
        </div>

        <!-- Struktur Organisasi -->
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-sitemap me-2"></i>
                    Struktur Organisasi
                </h2>

                @if($opd->bagians->count() > 0)
                    @foreach($opd->bagians->where('parent_id', null) as $bagian)
                        <div class="bagian-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-folder me-2"></i>
                                    {{ $bagian->nama }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Jabatan dalam bagian ini -->
                                @if($bagian->jabatans->count() > 0)
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-user-tie me-1"></i>
                                        Jabatan ({{ $bagian->jabatans->count() }})
                                    </h6>
                                    @foreach($bagian->jabatans->where('parent_id', null) as $jabatan)
                                        @include('opds.partials.jabatan-item', ['jabatan' => $jabatan, 'level' => 0])
                                    @endforeach
                                @endif

                                <!-- Sub bagian -->
                                @if($bagian->children->count() > 0)
                                    <h6 class="text-muted mb-3 mt-4">
                                        <i class="fas fa-folder-open me-1"></i>
                                        Sub Bagian ({{ $bagian->children->count() }})
                                    </h6>
                                    @foreach($bagian->children as $subBagian)
                                        @include('opds.partials.bagian-item', ['bagian' => $subBagian, 'level' => 1])
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Belum Ada Struktur Organisasi</h4>
                        <p class="mb-0">Struktur organisasi untuk OPD ini belum tersedia.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Sistem Peta Jabatan. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>