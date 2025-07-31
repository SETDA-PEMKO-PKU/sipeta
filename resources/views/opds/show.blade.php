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
        
        /* Folder Explorer Styles */
        .jabatan-explorer {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .tree-explorer {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .tree-node {
            display: flex;
            align-items: center;
            padding: 0.4rem 0;
            transition: all 0.2s ease;
            border-radius: 4px;
            margin: 2px 0;
            position: relative;
        }
        
        .tree-node:hover {
            background-color: #f8f9fa;
        }
        
        .tree-node.root-node {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border: 1px solid #2196f3;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);
        }
        
        .tree-branch {
            position: relative;
        }
        
        .tree-branch[data-level="1"] {
            border-left: 2px solid #dee2e6;
            margin-left: 20px;
        }
        
        .tree-branch[data-level="2"] {
            border-left: 2px solid #dee2e6;
            margin-left: 40px;
        }
        
        .tree-indent {
            width: 20px;
            height: 1px;
            position: relative;
        }
        
        .tree-indent::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 15px;
            height: 1px;
            background: #dee2e6;
        }
        
        .bagian-node {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin: 4px 0;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .bagian-node:hover {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .tree-node.jabatan-node {
            background: rgba(255, 255, 255, 0.7);
            border-left: 3px solid #4caf50;
            margin: 2px 0;
            padding: 6px 12px;
            border-radius: 4px;
        }
        
        .tree-node.jabatan-node:hover {
            background: rgba(76, 175, 80, 0.05);
            border-left-color: #2e7d32;
        }
        
        .tree-node.kepala-opd {
            background: rgba(220, 53, 69, 0.1);
            border-left: 4px solid #dc3545;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.1);
        }
        
        .tree-node.kepala-opd:hover {
            background: rgba(220, 53, 69, 0.15);
            border-left-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
        }
        
        .tree-toggle {
                transition: transform 0.2s ease;
            }
            
            .tree-toggle.collapsed {
                transform: rotate(-90deg);
            }
            
            .asn-node {
                background-color: #f8f9fa;
                border-left: 2px solid #17a2b8;
                margin: 2px 0;
                padding: 8px 12px;
                border-radius: 4px;
            }
            
            .asn-node:hover {
                background-color: #e9ecef;
            }
            
            .asn-node .tree-label {
                font-weight: 500;
                color: #495057;
            }
        
        .tree-content {
            margin-left: 0;
            padding-left: 0;
        }
        
        .tree-label {
            font-weight: 500;
            color: #333;
            margin-right: 0.5rem;
            flex-grow: 1;
        }
        
        /* Styling untuk tombol kecil di header */
        .card-header .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .card-header .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Responsive untuk tombol header */
        @media (max-width: 768px) {
            .card-header .btn-sm {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
            
            .card-header .btn-sm i {
                font-size: 0.7rem;
            }
            
            .card-header h4 {
                font-size: 1.1rem;
            }
        }
        
        /* Reset untuk tombol tree */
        .tree-toggle,
        .tree-actions .btn {
            transform: none !important;
        }
        
        .tree-toggle:hover,
        .tree-actions .btn:hover {
            transform: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        /* Pastikan tombol di header tidak terpengaruh rotasi */
        .card-header .btn,
        .card-header .btn-sm,
        .card-header button {
            transform: none !important;
        }
        
        /* Styling khusus untuk quick action buttons di tree */
        .tree-actions .btn-outline-success,
        .tree-actions .btn-outline-primary,
        .tree-actions .btn-outline-info {
            transform: none !important;
            transition: all 0.2s ease;
        }
        
        .tree-actions .btn-outline-success:hover,
        .tree-actions .btn-outline-primary:hover,
        .tree-actions .btn-outline-info:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
        }
        
        /* Pastikan quick action buttons tidak terpengaruh collapsed class */
        .tree-actions .btn-outline-success.collapsed,
        .tree-actions .btn-outline-primary.collapsed,
        .tree-actions .btn-outline-info.collapsed {
            transform: none !important;
        }
        
        .card-header .btn:hover,
        .card-header .btn-sm:hover,
        .card-header button:hover {
            transform: translateY(-1px) !important;
        }
        
        /* Override untuk tree-toggle yang mungkin ada di header */
        .card-header .tree-toggle,
        .card-header .collapsed {
            transform: none !important;
        }
        
        .card-header .tree-toggle:hover,
        .card-header .collapsed:hover {
            transform: translateY(-1px) !important;
        }
        
        /* CSS khusus untuk tombol aksi di header */
        .header-action-btn,
        .header-action-btn.collapsed,
        .header-action-btn:not(.collapsed) {
            transform: none !important;
            transition: all 0.2s ease !important;
        }
        
        .header-action-btn:hover,
        .header-action-btn.collapsed:hover,
        .header-action-btn:not(.collapsed):hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        }
        
        .bg-gradient:hover::before {
            left: 100%;
        }
        
        /* Responsive button styling */
        @media (max-width: 768px) {
            .btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
        
        .tree-details {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tree-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
            margin-left: auto;
            display: flex;
            gap: 4px;
        }
        
        .tree-node:hover .tree-actions {
            opacity: 1;
        }
        
        .tree-node .badge {
            font-size: 0.75rem;
        }
        
        .tree-node i {
            width: 16px;
            text-align: center;
        }
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="display-5 mb-0">
                            <i class="fas fa-building me-3"></i>
                            <span id="opd-name-display">{{ $opd->nama }}</span>
                        </h1>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-light" onclick="toggleEditMode()">
                                <i class="fas fa-edit me-2"></i>Edit Nama
                            </button>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteOpdModal">
                                <i class="fas fa-trash me-2"></i>Hapus OPD
                            </button>
                        </div>
                    </div>
                    
                    <!-- Form Edit Nama OPD (Hidden by default) -->
                    <div id="edit-name-form" class="d-none mb-3">
                        <form action="{{ route('opds.update', $opd->id) }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="nama" class="form-control" value="{{ $opd->nama }}" required style="max-width: 400px;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">
                                <i class="fas fa-times me-1"></i>Batal
                            </button>
                        </form>
                    </div>
                    
                    <p class="lead">Struktur Organisasi dan Peta Jabatan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistik OPD -->
        <div class="row mb-5">
            <!-- Card 1: Total Bagian, Total Jabatan, Total ASN -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white text-center">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan Umum</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-folder text-primary"></i>
                            <strong>{{ $opd->bagians->count() }}</strong> Bagian
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-user-tie text-success"></i>
                            <strong>{{ $opd->allJabatans->count() }}</strong> Jabatan
                        </div>
                        <div>
                            <i class="fas fa-users text-info"></i>
                            <strong>{{ $opd->allJabatans->sum(function($jabatan) { return $jabatan->asns->count(); }) }}</strong> ASN
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Bezetting, Total Kebutuhan, Total Selisih -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white text-center">
                        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Analisis Kebutuhan</h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $totalBezetting = $opd->allJabatans->sum(function($jabatan) { return $jabatan->asns->count(); });
                            $totalKebutuhan = $opd->allJabatans->sum('kebutuhan');
                            $totalSelisih = $totalBezetting - $totalKebutuhan;
                        @endphp
                        <div class="mb-2">
                            <i class="fas fa-user-check text-primary"></i>
                            <strong>{{ $totalBezetting }}</strong> Bezetting
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-clipboard-list text-warning"></i>
                            <strong>{{ $totalKebutuhan }}</strong> Kebutuhan
                        </div>
                        <div>
                            <i class="fas fa-balance-scale 
                                @if($totalSelisih > 0) text-success
                                @elseif($totalSelisih < 0) text-danger
                                @else text-muted
                                @endif"></i>
                            <strong class="
                                @if($totalSelisih > 0) text-success
                                @elseif($totalSelisih < 0) text-danger
                                @else text-muted
                                @endif">{{ $totalSelisih > 0 ? '+' : '' }}{{ $totalSelisih }}</strong> Selisih
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total masing-masing jenis jabatan -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white text-center">
                        <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Jenis Jabatan</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $jenisJabatan = $opd->allJabatans->groupBy('jenis_jabatan');
                        @endphp
                        @foreach($jenisJabatan as $jenis => $jabatans)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small>{{ $jenis }}</small>
                                <span class="badge bg-secondary">{{ $jabatans->count() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Card 4: Total masing-masing kelas jabatan -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white text-center">
                        <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Kelas Jabatan</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $kelasJabatan = $opd->allJabatans->whereNotNull('kelas')->groupBy('kelas');
                        @endphp
                        @if($kelasJabatan->count() > 0)
                            @foreach($kelasJabatan->sortKeys() as $kelas => $jabatans)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small>Kelas {{ $kelas }}</small>
                                    <span class="badge bg-secondary">{{ $jabatans->count() }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted">
                                <small>Belum ada kelas jabatan</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Manajemen Jabatan -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>
                            Daftar Jabatan - Expandable Tree Explorer
                        </h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm header-action-btn" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#formJabatan"
                                    title="Tambah Jabatan">
                                <i class="fas fa-plus me-1"></i>
                                Jabatan
                            </button>
                            <button type="button" class="btn btn-light btn-sm header-action-btn" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#formBagian"
                                    title="Tambah Bagian">
                                <i class="fas fa-folder-plus me-1"></i>
                                Bagian
                            </button>
                            <button type="button" class="btn btn-light btn-sm header-action-btn" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#formAsn"
                                    title="Tambah ASN">
                                <i class="fas fa-user-plus me-1"></i>
                                ASN
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="collapse" id="formJabatan">
                            <div class="card card-body mb-4">
                                <form action="{{ route('opds.jabatan.store', $opd->id) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="nama" class="form-label">Nama Jabatan *</label>
                                                <input type="text" class="form-control" id="nama" name="nama" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="jenis_jabatan" class="form-label">Jenis Jabatan *</label>
                                                <select class="form-select" id="jenis_jabatan" name="jenis_jabatan" required>
                                                    <option value="">Pilih Jenis Jabatan</option>
                                                    <option value="Staf Ahli">Staf Ahli</option>
                                                    <option value="Struktural">Struktural</option>
                                                    <option value="Fungsional">Fungsional</option>
                                                    <option value="Pelaksana">Pelaksana</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bagian_id" class="form-label">Bagian</label>
                                                <select class="form-select" id="bagian_id" name="bagian_id">
                                                    <option value="">Pilih Bagian</option>
                                                    @foreach($opd->bagians as $bagian)
                                                        <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="kelas" class="form-label">Kelas</label>
                                                <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Contoh: III/a">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="kebutuhan" class="form-label">Kebutuhan *</label>
                                                <input type="number" class="form-control" id="kebutuhan" name="kebutuhan" min="0" value="0" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success me-2">
                                                <i class="fas fa-save me-1"></i> Simpan Jabatan
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#formJabatan">
                                                <i class="fas fa-times me-1"></i> Batal
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Form Tambah Bagian -->
                        <div class="collapse" id="formBagian">
                            <div class="card card-body mb-4">
                                <form action="{{ route('opds.bagian.store', $opd->id) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nama_bagian" class="form-label">Nama Bagian *</label>
                                                <input type="text" class="form-control" id="nama_bagian" name="nama" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parent_bagian_id" class="form-label">Atasan (Parent Bagian)</label>
                                                <select class="form-select" id="parent_bagian_id" name="parent_id">
                                                    <option value="">Tidak Ada (Bagian Root)</option>
                                                    @foreach($opd->bagians as $bagian)
                                                        <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success me-2">
                                                <i class="fas fa-save me-1"></i> Simpan Bagian
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#formBagian">
                                                <i class="fas fa-times me-1"></i> Batal
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Form Tambah ASN -->
                        <div class="collapse" id="formAsn">
                            <div class="card card-body mb-4">
                                <form action="{{ route('opds.asn.store', $opd->id) }}" method="POST">
                                    @csrf
                                    <!-- Hidden input untuk OPD ID -->
                                    <input type="hidden" name="opd_id" value="{{ $opd->id }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="nama_asn" class="form-label">Nama ASN *</label>
                                                <input type="text" class="form-control" id="nama_asn" name="nama" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="nip_asn" class="form-label">NIP *</label>
                                                <input type="text" class="form-control" id="nip_asn" name="nip" required placeholder="Contoh: 198501012010011001">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="jabatan_asn" class="form-label">Jabatan *</label>
                                                <select class="form-select" id="jabatan_asn" name="jabatan_id" required>
                                                    <option value="">Pilih Jabatan</option>
                                                    @foreach($opd->jabatanKepala as $jabatan)
                                                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} (Kepala OPD)</option>
                                                    @endforeach
                                                    @foreach($opd->jabatans as $jabatan)
                                                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} - {{ $jabatan->parentBagian->nama ?? 'Tanpa Bagian' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="bagian_asn" class="form-label">Bagian</label>
                                                <select class="form-select" id="bagian_asn" name="bagian_id">
                                                    <option value="">Pilih Bagian (Opsional)</option>
                                                    @foreach($opd->bagians as $bagian)
                                                        <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Otomatis terisi berdasarkan jabatan yang dipilih</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-info me-2">
                                                <i class="fas fa-save me-1"></i> Simpan ASN
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#formAsn">
                                                <i class="fas fa-times me-1"></i> Batal
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Daftar Jabatan - Expandable Tree Explorer -->
        <div class="tree-explorer">
            @if($opd->bagians->count() > 0 || $opd->allJabatans->count() > 0)
                <!-- Root OPD Node -->
                <div class="tree-node root-node">
                    <i class="fas fa-building text-success me-2"></i>
                    <strong class="tree-label">{{ $opd->nama }}</strong>
                    <span class="badge bg-primary ms-2">{{ $opd->allJabatans->count() }} total jabatan</span>
                    <div class="tree-actions ms-auto">
                        <button class="btn btn-sm btn-outline-success me-1" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#formBagian"
                                title="Tambah Bagian">
                            <i class="fas fa-folder-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#formJabatan"
                                title="Tambah Jabatan">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Jabatan Kepala OPD (tanpa bagian) -->
                @foreach($opd->jabatanKepala as $jabatan)
                    <div class="tree-branch" data-level="1">
                        <div class="tree-node jabatan-node kepala-opd">
                            <div class="tree-indent"></div>
                            @if($jabatan->asns->count() > 0)
                                <i class="fas fa-chevron-down tree-toggle me-2" data-bs-toggle="collapse" data-bs-target="#asn-kepala-{{ $jabatan->id }}" style="cursor: pointer;"></i>
                            @else
                                <span class="me-4"></span>
                            @endif
                            <i class="fas fa-user-tie text-danger me-2"></i>
                            <span class="tree-label">{{ $jabatan->nama }}</span>
                            
                            @if($jabatan->jenis_jabatan)
                                <span class="badge bg-info ms-2">{{ $jabatan->jenis_jabatan }}</span>
                            @endif
                            
                            <span class="tree-details ms-2">
                                @if($jabatan->kelas)
                                    <small class="text-muted me-2">Kelas: {{ $jabatan->kelas }}</small>
                                @endif
                                <small class="text-muted me-2">Kebutuhan: {{ $jabatan->kebutuhan }}</small>
                                <small class="text-muted me-2">Bezetting: {{ $jabatan->asns->count() }}</small>
                                
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
                            </span>
                            <div class="tree-actions ms-auto">
                                <button class="btn btn-sm btn-outline-info me-1" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#formAsn"
                                        onclick="document.getElementById('jabatan_asn').value='{{ $jabatan->id }}'"
                                        title="Tambah ASN">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editJabatanModal{{ $jabatan->id }}"
                                        title="Edit Jabatan">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('opds.jabatan.destroy', [$opd->id, $jabatan->id]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus jabatan {{ $jabatan->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Jabatan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Daftar ASN untuk Kepala OPD -->
                        @if($jabatan->asns->count() > 0)
                            <div class="collapse show" id="asn-kepala-{{ $jabatan->id }}">
                                @foreach($jabatan->asns as $asn)
                                    <div class="tree-node asn-node" data-level="2">
                                        <div class="tree-indent"></div>
                                        <div class="tree-indent"></div>
                                        <i class="fas fa-user text-info me-2"></i>
                                        <span class="tree-label">
                                            {{ $asn->nama }}@if($asn->nip) | {{ $asn->nip }}@endif
                                        </span>
                                        <span class="tree-details ms-2">
                                            @if($asn->pangkat)
                                                <small class="text-muted me-2">Pangkat: {{ $asn->pangkat }}</small>
                                            @endif
                                            @if($asn->golongan)
                                                <small class="text-muted me-2">Golongan: {{ $asn->golongan }}</small>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
                
                <!-- Bagian-bagian Root Level -->
                @foreach($opd->bagians->where('parent_id', null)->sortBy('nama') as $bagian)
                    <div class="tree-branch" data-level="1">
                        <!-- Bagian Header -->
                        <div class="tree-node bagian-node" data-toggle="collapse" data-target="#bagian-{{ $bagian->id }}" role="button">
                            <div class="tree-indent"></div>
                            <i class="fas fa-chevron-down tree-toggle me-2"></i>
                            <i class="fas fa-folder text-warning me-2"></i>
                            <strong class="tree-label">{{ $bagian->nama }}</strong>
                            @php
                                $totalJabatan = $bagian->jabatans->count();
                                foreach($bagian->children as $child) {
                                    $totalJabatan += $child->jabatans->count();
                                }
                            @endphp
                            @if($totalJabatan > 0)
                                <span class="badge bg-secondary ms-2">{{ $totalJabatan }} jabatan</span>
                            @endif
                            <div class="tree-actions ms-auto">
                                <button class="btn btn-sm btn-outline-success me-1" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#formBagian"
                                        onclick="document.getElementById('parent_bagian_id').value='{{ $bagian->id }}'"
                                        title="Tambah Sub-bagian">
                                    <i class="fas fa-folder-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#formJabatan"
                                        onclick="document.getElementById('bagian_id').value='{{ $bagian->id }}'"
                                        title="Tambah Jabatan">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editBagianModal{{ $bagian->id }}"
                                        title="Edit Bagian">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('opds.bagian.destroy', [$opd->id, $bagian->id]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus bagian {{ $bagian->nama }}? Semua sub-bagian dan jabatan di dalamnya juga akan terhapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Bagian">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Bagian Content -->
                        <div class="collapse show tree-content" id="bagian-{{ $bagian->id }}">
                            <!-- Jabatan dalam bagian ini -->
                            @foreach($bagian->jabatans->sortBy('nama') as $jabatan)
                                <div class="tree-branch" data-level="2">
                                    <div class="tree-node jabatan-node" data-level="2">
                                        <div class="tree-indent"></div>
                                        <div class="tree-indent"></div>
                                        @if($jabatan->asns->count() > 0)
                                            <i class="fas fa-chevron-down tree-toggle me-2" data-bs-toggle="collapse" data-bs-target="#asn-bagian-{{ $jabatan->id }}" style="cursor: pointer;"></i>
                                        @else
                                            <span class="me-4"></span>
                                        @endif
                                        <i class="fas fa-user-tie text-primary me-2"></i>
                                        <span class="tree-label">{{ $jabatan->nama }}</span>
                                        
                                        @if($jabatan->jenis_jabatan)
                                            <span class="badge bg-info ms-2">{{ $jabatan->jenis_jabatan }}</span>
                                        @endif
                                        
                                        <span class="tree-details ms-2">
                                            @if($jabatan->kelas)
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
                                        </span>
                                        
                                        <div class="tree-actions ms-auto">
                                            <button class="btn btn-sm btn-outline-info me-1" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#formAsn"
                                                    onclick="document.getElementById('jabatan_asn').value='{{ $jabatan->id }}'"
                                                    title="Tambah ASN">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editJabatanModal{{ $jabatan->id }}"
                                                    title="Edit Jabatan">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('opds.jabatan.destroy', [$opd->id, $jabatan->id]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus jabatan {{ $jabatan->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Jabatan">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Daftar ASN untuk Jabatan di Bagian -->
                                    @if($jabatan->asns->count() > 0)
                                        <div class="collapse show" id="asn-bagian-{{ $jabatan->id }}">
                                            @foreach($jabatan->asns as $asn)
                                                <div class="tree-node asn-node" data-level="3">
                                                    <div class="tree-indent"></div>
                                                    <div class="tree-indent"></div>
                                                    <div class="tree-indent"></div>
                                                    <i class="fas fa-user text-info me-2"></i>
                                                <span class="tree-label">
                                                    {{ $asn->nama }}@if($asn->nip) | {{ $asn->nip }}@endif
                                                </span>
                                                <span class="tree-details ms-2">
                                                    @if($asn->pangkat)
                                                        <small class="text-muted me-2">Pangkat: {{ $asn->pangkat }}</small>
                                                    @endif
                                                    @if($asn->golongan)
                                                        <small class="text-muted me-2">Golongan: {{ $asn->golongan }}</small>
                                                    @endif
                                                </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            
                            <!-- Sub-bagian -->
                            @foreach($bagian->children->sortBy('nama') as $subBagian)
                                <div class="tree-branch" data-level="2">
                                    <!-- Sub-bagian Header -->
                                    <div class="tree-node bagian-node" data-toggle="collapse" data-target="#subbagian-{{ $subBagian->id }}" role="button">
                                        <div class="tree-indent"></div>
                                        <div class="tree-indent"></div>
                                        <i class="fas fa-chevron-down tree-toggle me-2"></i>
                                        <i class="fas fa-folder text-warning me-2"></i>
                                        <strong class="tree-label">{{ $subBagian->nama }}</strong>
                                        @if($subBagian->jabatans->count() > 0)
                                            <span class="badge bg-secondary ms-2">{{ $subBagian->jabatans->count() }} jabatan</span>
                                        @endif
                                        <div class="tree-actions ms-auto">
                                            <button class="btn btn-sm btn-outline-success me-1" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#formBagian"
                                                    onclick="document.getElementById('parent_bagian_id').value='{{ $subBagian->id }}'"
                                                    title="Tambah Sub-bagian">
                                                <i class="fas fa-folder-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#formJabatan"
                                                    onclick="document.getElementById('bagian_id').value='{{ $subBagian->id }}'"
                                                    title="Tambah Jabatan">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editBagianModal{{ $subBagian->id }}"
                                                    title="Edit Sub-bagian">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('opds.bagian.destroy', [$opd->id, $subBagian->id]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus sub-bagian {{ $subBagian->nama }}? Semua jabatan di dalamnya juga akan terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Sub-bagian">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Sub-bagian Content -->
                                    <div class="collapse show tree-content" id="subbagian-{{ $subBagian->id }}">
                                        @foreach($subBagian->jabatans->sortBy('nama') as $jabatan)
                                            <div class="tree-branch" data-level="3">
                                                <div class="tree-node jabatan-node" data-level="3">
                                                    <div class="tree-indent"></div>
                                                    <div class="tree-indent"></div>
                                                    <div class="tree-indent"></div>
                                                    @if($jabatan->asns->count() > 0)
                                                        <i class="fas fa-chevron-down tree-toggle me-2" data-bs-toggle="collapse" data-bs-target="#asn-subbagian-{{ $jabatan->id }}" style="cursor: pointer;"></i>
                                                    @else
                                                        <span class="me-4"></span>
                                                    @endif
                                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                                    <span class="tree-label">{{ $jabatan->nama }}</span>
                                                    
                                                    @if($jabatan->jenis_jabatan)
                                                        <span class="badge bg-info ms-2">{{ $jabatan->jenis_jabatan }}</span>
                                                    @endif
                                                    
                                                    <span class="tree-details ms-2">
                                                        @if($jabatan->kelas)
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
                                                    </span>
                                                    
                                                    <div class="tree-actions ms-auto">
                                                        <button class="btn btn-sm btn-outline-info me-1" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#formAsn"
                                                                onclick="document.getElementById('jabatan_asn').value='{{ $jabatan->id }}'"
                                                                title="Tambah ASN">
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning me-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editJabatanModal{{ $jabatan->id }}"
                                                                title="Edit Jabatan">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('opds.jabatan.destroy', [$opd->id, $jabatan->id]) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Yakin ingin menghapus jabatan {{ $jabatan->nama }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Jabatan">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <!-- Daftar ASN untuk Jabatan di Sub-bagian -->
                                                @if($jabatan->asns->count() > 0)
                                                    <div class="collapse show" id="asn-subbagian-{{ $jabatan->id }}">
                                                        @foreach($jabatan->asns as $asn)
                                                            <div class="tree-node asn-node" data-level="4">
                                                                <div class="tree-indent"></div>
                                                                <div class="tree-indent"></div>
                                                                <div class="tree-indent"></div>
                                                                <div class="tree-indent"></div>
                                                                <i class="fas fa-user text-info me-2"></i>
                                                                <span class="tree-label">
                                                                    {{ $asn->nama }}@if($asn->nip) | {{ $asn->nip }}@endif
                                                                </span>
                                                                <span class="tree-details ms-2">
                                                                    @if($asn->pangkat)
                                                                        <small class="text-muted me-2">Pangkat: {{ $asn->pangkat }}</small>
                                                                    @endif
                                                                    @if($asn->golongan)
                                                                        <small class="text-muted me-2">Golongan: {{ $asn->golongan }}</small>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                

            @else
                <div class="text-center py-5">
                    <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada struktur organisasi</h5>
                    <p class="text-muted">Tambahkan bagian dan jabatan menggunakan form di atas.</p>
                </div>
            @endif
        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Modal Edit Jabatan -->
    @foreach($opd->allJabatans as $jabatan)
    <div class="modal fade" id="editJabatanModal{{ $jabatan->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Jabatan: {{ $jabatan->nama }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('opds.jabatan.update', [$opd->id, $jabatan->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_nama_{{ $jabatan->id }}" class="form-label">Nama Jabatan *</label>
                                    <input type="text" class="form-control" id="edit_nama_{{ $jabatan->id }}" name="nama" value="{{ $jabatan->nama }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_jenis_jabatan_{{ $jabatan->id }}" class="form-label">Jenis Jabatan *</label>
                                    <select class="form-select" id="edit_jenis_jabatan_{{ $jabatan->id }}" name="jenis_jabatan" required>
                                        <option value="">Pilih Jenis Jabatan</option>
                                        <option value="Staf Ahli" {{ $jabatan->jenis_jabatan == 'Staf Ahli' ? 'selected' : '' }}>Staf Ahli</option>
                                        <option value="Struktural" {{ $jabatan->jenis_jabatan == 'Struktural' ? 'selected' : '' }}>Struktural</option>
                                        <option value="Fungsional" {{ $jabatan->jenis_jabatan == 'Fungsional' ? 'selected' : '' }}>Fungsional</option>
                                        <option value="Pelaksana" {{ $jabatan->jenis_jabatan == 'Pelaksana' ? 'selected' : '' }}>Pelaksana</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_bagian_id_{{ $jabatan->id }}" class="form-label">Bagian</label>
                                    <select class="form-select" id="edit_bagian_id_{{ $jabatan->id }}" name="bagian_id">
                                        <option value="">Pilih Bagian</option>
                                        @foreach($opd->bagians as $bagian)
                                            <option value="{{ $bagian->id }}" {{ $jabatan->parent_id == $bagian->id ? 'selected' : '' }}>{{ $bagian->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="edit_kelas_{{ $jabatan->id }}" class="form-label">Kelas</label>
                                    <input type="text" class="form-control" id="edit_kelas_{{ $jabatan->id }}" name="kelas" value="{{ $jabatan->kelas }}" placeholder="Contoh: III/a">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kebutuhan_{{ $jabatan->id }}" class="form-label">Kebutuhan *</label>
                                    <input type="number" class="form-control" id="edit_kebutuhan_{{ $jabatan->id }}" name="kebutuhan" min="0" value="{{ $jabatan->kebutuhan }}" required>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Update Jabatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Modal Edit Bagian -->
    @foreach($opd->bagians as $bagian)
    <div class="modal fade" id="editBagianModal{{ $bagian->id }}" tabindex="-1" aria-labelledby="editBagianModalLabel{{ $bagian->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('opds.bagian.update', [$opd->id, $bagian->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="editBagianModalLabel{{ $bagian->id }}">
                            <i class="fas fa-edit me-2"></i>Edit Bagian: {{ $bagian->nama }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama_bagian_{{ $bagian->id }}" class="form-label">Nama Bagian *</label>
                                    <input type="text" class="form-control" id="edit_nama_bagian_{{ $bagian->id }}" name="nama" value="{{ $bagian->nama }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_parent_bagian_{{ $bagian->id }}" class="form-label">Atasan (Parent Bagian)</label>
                                    <select class="form-select" id="edit_parent_bagian_{{ $bagian->id }}" name="parent_id">
                                        <option value="">-- Pilih Atasan --</option>
                                        @foreach($opd->bagians->where('id', '!=', $bagian->id) as $parentBagian)
                                            <option value="{{ $parentBagian->id }}" {{ $bagian->parent_id == $parentBagian->id ? 'selected' : '' }}>
                                                {{ $parentBagian->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Update Bagian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Sistem Peta Jabatan. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-complete untuk input bagian
        document.addEventListener('DOMContentLoaded', function() {
            const bagianInputs = document.querySelectorAll('input[name="bagian_nama"]');
            
            bagianInputs.forEach(function(input) {
                let timeout;
                
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    const query = this.value;
                    
                    if (query.length < 2) {
                        return;
                    }
                    
                    timeout = setTimeout(() => {
                        fetch(`/api/opds/{{ $opd->id }}/bagians/search?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                // Update datalist dengan hasil pencarian
                                const datalistId = this.getAttribute('list');
                                const datalist = document.getElementById(datalistId);
                                if (datalist) {
                                    datalist.innerHTML = '';
                                    data.forEach(bagian => {
                                        const option = document.createElement('option');
                                        option.value = bagian.nama;
                                        datalist.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching bagian data:', error);
                            });
                    }, 300); // Debounce 300ms
                });
            });
        });
        
        // Function untuk toggle edit mode nama OPD
        function toggleEditMode() {
            const displayElement = document.getElementById('opd-name-display');
            const formElement = document.getElementById('edit-name-form');
            
            if (formElement.classList.contains('d-none')) {
                formElement.classList.remove('d-none');
                displayElement.style.opacity = '0.5';
            } else {
                formElement.classList.add('d-none');
                displayElement.style.opacity = '1';
            }
        }
        
        // Tree Explorer functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle collapse/expand functionality
            const bagianNodes = document.querySelectorAll('.bagian-node[data-toggle="collapse"]');
            
            bagianNodes.forEach(node => {
                node.addEventListener('click', function(e) {
                    // Prevent action buttons from triggering collapse
                    if (e.target.closest('.tree-actions')) {
                        return;
                    }
                    
                    const target = this.getAttribute('data-target');
                    const content = document.querySelector(target);
                    const toggle = this.querySelector('.tree-toggle');
                    
                    if (content && toggle) {
                        if (content.classList.contains('show')) {
                            // Collapse
                            content.classList.remove('show');
                            toggle.classList.add('collapsed');
                            content.style.maxHeight = '0';
                            content.style.overflow = 'hidden';
                        } else {
                            // Expand
                            content.classList.add('show');
                            toggle.classList.remove('collapsed');
                            content.style.maxHeight = 'none';
                            content.style.overflow = 'visible';
                        }
                    }
                });
            });
            
            // Add smooth hover effects for tree nodes
            const treeNodes = document.querySelectorAll('.tree-node');
            treeNodes.forEach(node => {
                node.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('bagian-node')) {
                        this.style.transform = 'translateX(2px)';
                    }
                });
                
                node.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('bagian-node')) {
                        this.style.transform = 'translateX(0)';
                    }
                });
            });
            
            // Highlight tree path on hover
            const jabatanNodes = document.querySelectorAll('.tree-node.jabatan-node');
            jabatanNodes.forEach(node => {
                node.addEventListener('mouseenter', function() {
                    // Add subtle glow effect
                    this.style.boxShadow = '0 2px 8px rgba(33, 150, 243, 0.15)';
                });
                
                node.addEventListener('mouseleave', function() {
                    this.style.boxShadow = 'none';
                });
            });
            
            // Initialize all content as expanded
            const allContent = document.querySelectorAll('.tree-content');
            allContent.forEach(content => {
                content.style.maxHeight = 'none';
                content.style.overflow = 'visible';
            });
            
            // Handle ASN toggle functionality
            const asnToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
            asnToggles.forEach(toggle => {
                const targetId = toggle.getAttribute('data-bs-target');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Set initial state
                    if (targetElement.classList.contains('show')) {
                        toggle.style.transform = 'rotate(0deg)';
                    } else {
                        toggle.style.transform = 'rotate(-90deg)';
                    }
                    
                    // Add event listeners for collapse events
                    targetElement.addEventListener('show.bs.collapse', function() {
                        toggle.style.transform = 'rotate(0deg)';
                    });
                    
                    targetElement.addEventListener('hide.bs.collapse', function() {
                        toggle.style.transform = 'rotate(-90deg)';
                    });
                }
            });
            
            // Auto-fill bagian berdasarkan jabatan yang dipilih
            const jabatanSelect = document.getElementById('jabatan_asn');
            const bagianSelect = document.getElementById('bagian_asn');
            
            if (jabatanSelect && bagianSelect) {
                // Data mapping jabatan ke bagian
                const jabatanBagianMap = {
                    @foreach($opd->jabatans as $jabatan)
                        '{{ $jabatan->id }}': '{{ $jabatan->bagian_id ?? "" }}',
                    @endforeach
                };
                
                jabatanSelect.addEventListener('change', function() {
                    const selectedJabatanId = this.value;
                    const bagianId = jabatanBagianMap[selectedJabatanId];
                    
                    // Reset bagian selection
                    bagianSelect.value = '';
                    
                    // Set bagian jika ada
                    if (bagianId && bagianId !== '') {
                        bagianSelect.value = bagianId;
                    }
                });
            }
            
            // Functions untuk ASN management
            window.editAsn = function(id, nama, nip, jabatanId, bagianId) {
                 // Populate edit form
                 document.getElementById('edit_asn_id').value = id;
                 document.getElementById('edit_nama_asn').value = nama;
                 document.getElementById('edit_nip_asn').value = nip;
                 document.getElementById('edit_jabatan_asn').value = jabatanId;
                 document.getElementById('edit_bagian_asn').value = bagianId || '';
                 
                 // Set form action
                 document.getElementById('editAsnForm').action = `/opds/{{ $opd->id }}/asn/${id}`;
                 
                 // Show modal
                 const editModal = new bootstrap.Modal(document.getElementById('editAsnModal'));
                 editModal.show();
             };
            
            window.deleteAsn = function(id, nama) {
                 // Set data for delete confirmation
                 document.getElementById('delete_asn_id').value = id;
                 document.getElementById('delete_asn_nama').textContent = nama;
                 
                 // Set form action
                 document.getElementById('deleteAsnForm').action = `/opds/{{ $opd->id }}/asn/${id}`;
                 
                 // Show modal
                 const deleteModal = new bootstrap.Modal(document.getElementById('deleteAsnModal'));
                 deleteModal.show();
             };
        });
    </script>
    
    <!-- Modal Konfirmasi Hapus OPD -->
    <div class="modal fade" id="deleteOpdModal" tabindex="-1" aria-labelledby="deleteOpdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteOpdModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus OPD
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p>Anda yakin ingin menghapus OPD <strong>"{{ $opd->nama }}"</strong>?</p>
                    <p class="text-muted">Semua bagian dan jabatan yang terkait dengan OPD ini juga akan dihapus secara permanen.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <form action="{{ route('opds.destroy', $opd->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Ya, Hapus OPD
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit ASN -->
    <div class="modal fade" id="editAsnModal" tabindex="-1" aria-labelledby="editAsnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editAsnModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data ASN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAsnForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_asn_id" name="asn_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama_asn" class="form-label">Nama ASN *</label>
                                    <input type="text" class="form-control" id="edit_nama_asn" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nip_asn" class="form-label">NIP *</label>
                                    <input type="text" class="form-control" id="edit_nip_asn" name="nip" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jabatan_asn" class="form-label">Jabatan *</label>
                                    <select class="form-select" id="edit_jabatan_asn" name="jabatan_id" required>
                                        <option value="">Pilih Jabatan</option>
                                        @foreach($opd->jabatanKepala as $jabatan)
                                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} (Kepala OPD)</option>
                                        @endforeach
                                        @foreach($opd->jabatans as $jabatan)
                                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} - {{ $jabatan->parentBagian->nama ?? 'Tanpa Bagian' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_bagian_asn" class="form-label">Bagian</label>
                                    <select class="form-select" id="edit_bagian_asn" name="bagian_id">
                                        <option value="">Pilih Bagian (Opsional)</option>
                                        @foreach($opd->bagians as $bagian)
                                            <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update ASN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus ASN -->
    <div class="modal fade" id="deleteAsnModal" tabindex="-1" aria-labelledby="deleteAsnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAsnModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus ASN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p>Anda yakin ingin menghapus ASN <strong id="delete_asn_nama"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <form id="deleteAsnForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="delete_asn_id" name="asn_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Ya, Hapus ASN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>