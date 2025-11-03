<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $opd->nama }} - Detail OPD</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Modern Design System */
        :root {
            /* Gradients */
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-info: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --gradient-dark: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            
            /* Colors */
            --color-primary: #667eea;
            --color-secondary: #6c757d;
            --color-success: #4facfe;
            --color-info: #43e97b;
            --color-warning: #fa709a;
            --color-danger: #f5576c;
            --color-light: #f8f9fa;
            --color-dark: #343a40;
            
            /* Text Colors */
            --text-primary: #2d3748;
            --text-secondary: #4a5568;
            --text-muted: #718096;
            --text-light: #a0aec0;
            
            /* Background Colors */
            --bg-primary: #ffffff;
            --bg-secondary: #f7fafc;
            --bg-tertiary: #edf2f7;
            
            /* Border Colors */
            --border-light: #e2e8f0;
            --border-medium: #cbd5e0;
            --border-dark: #a0aec0;
            
            /* Shadows */
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        /* Global Styles */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Modern Header */
        .modern-header {
            background: var(--gradient-primary);
            color: white;
            padding: var(--spacing-2xl) 0;
            margin-bottom: var(--spacing-xl);
            position: relative;
            overflow: hidden;
        }

        .modern-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
        }

        .modern-header .container {
            position: relative;
            z-index: 1;
        }

        .modern-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: var(--spacing-sm);
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .modern-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Modern Cards */
        .modern-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .modern-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .modern-card .card-header {
            border: none;
            padding: var(--spacing-lg);
            font-weight: 600;
            color: white;
        }

        .modern-card .card-body {
            padding: var(--spacing-lg);
        }

        /* Statistics Cards */
        .stats-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stats-card .card-header {
            border: none;
            padding: var(--spacing-lg);
            font-weight: 600;
            color: white;
        }

        .stats-card .card-body {
            padding: var(--spacing-lg);
        }

        .stats-card .card-header.bg-primary {
            background: var(--gradient-primary) !important;
        }

        .stats-card .card-header.bg-success {
            background: var(--gradient-success) !important;
        }

        .stats-card .card-header.bg-info {
            background: var(--gradient-info) !important;
        }

        .stats-card .card-header.bg-warning {
            background: var(--gradient-warning) !important;
        }

        /* Modern Buttons */
        .btn {
            border-radius: var(--radius-md);
            font-weight: 500;
            padding: var(--spacing-sm) var(--spacing-lg);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: var(--gradient-primary);
        }

        .btn-success {
            background: var(--gradient-success);
        }

        .btn-info {
            background: var(--gradient-info);
        }

        .btn-warning {
            background: var(--gradient-warning);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
        }

        /* Form Styles */
        .form-control, .form-select {
            border-radius: var(--radius-md);
            border: 1px solid var(--border-medium);
            padding: var(--spacing-sm) var(--spacing-md);
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Alert Styles */
        .alert {
            border-radius: var(--radius-md);
            border: none;
            box-shadow: var(--shadow-sm);
        }

        /* Tree View Styles */
        .tree-explorer {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: var(--spacing-md);
            box-shadow: var(--shadow-md);
        }
        
        .tree-item {
            margin-bottom: 4px;
        }
        
        .tree-node {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            transition: all 0.2s ease;
            border-radius: var(--radius-sm);
            position: relative;
            cursor: pointer;
            min-height: 36px;
        }
        
        .tree-node:hover {
            background-color: var(--bg-secondary);
        }
        
        /* Kepala OPD - Level 0 */
        .tree-node.kepala-opd {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #f39c12;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
            margin-bottom: 8px;
        }
        
        .tree-node.kepala-opd:hover {
            background: linear-gradient(135deg, #fff3cd 0%, #fdcb6e 100%);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        /* Bagian - Level 1 */
        .tree-node.bagian-node {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #2196f3;
            font-weight: 500;
            margin-bottom: 4px;
            position: relative;
        }
        
        .tree-node.bagian-node:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 100%);
        }
        
        /* Sub-Bagian - Level 2+ */
        .tree-node.sub-bagian-node {
            background: linear-gradient(135deg, #f0f8ff 0%, #d6ebff 100%);
            border: 1px solid #1976d2;
            border-left: 3px solid #1976d2;
            font-weight: 500;
            margin-bottom: 4px;
            position: relative;
        }
        
        .tree-node.sub-bagian-node:hover {
            background: linear-gradient(135deg, #e8f4fd 0%, #c2e0ff 100%);
        }
        
        .tree-node.sub-bagian-node .tree-icon {
            color: #1976d2;
        }
        
        /* Jabatan - Level 2 */
        .tree-node.jabatan-node {
            background: rgba(76, 175, 80, 0.08);
            border-left: 3px solid #4caf50;
            margin-left: 40px;
            font-weight: 400;
            font-size: 0.9rem;
        }
        
        .tree-node.jabatan-node:hover {
            background: rgba(76, 175, 80, 0.15);
            border-left-color: #2e7d32;
        }
        
        /* ASN - Level 3 */
        .tree-node.asn-node {
            background-color: rgba(23, 162, 184, 0.05);
            border-left: 2px solid #17a2b8;
            margin-left: 60px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            min-height: 32px;
            padding: 6px 10px;
        }
        
        .tree-node.asn-node:hover {
            background-color: rgba(23, 162, 184, 0.1);
        }
        
        /* Tree Toggle Button */
        .tree-toggle {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.8rem;
            padding: 2px 6px;
            margin-right: 8px;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            cursor: pointer;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .tree-toggle:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
        
        .tree-toggle.collapsed {
            transform: rotate(-90deg);
        }
        
        /* Tree Icon */
        .tree-icon {
            margin-right: 8px;
            width: 16px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .tree-label {
            font-weight: inherit;
            color: inherit;
            margin-right: auto;
            flex-grow: 1;
        }
        
        .tree-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-right: 8px;
            white-space: nowrap;
        }
        
        .tree-actions {
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .tree-node:hover .tree-actions {
            opacity: 1;
        }
        
        .tree-action-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.7rem;
            padding: 4px 6px;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            cursor: pointer;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .tree-action-btn:hover {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
        }
        
        .tree-action-btn.edit:hover {
            background: #d4edda;
            color: #155724;
        }
        
        .tree-action-btn.delete:hover {
            background: #f8d7da;
            color: #721c24;
        }
        
        
        .tree-action-btn.view:hover {
            background: #cfe2ff;
            color: #084298;
        }
        
        .tree-action-btn.add:hover {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* Bezetting Badge */
        .bezetting-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            margin-left: 0.5rem;
            white-space: nowrap;
        }
        
        .bezetting-badge.match {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .bezetting-badge.under {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .bezetting-badge.over {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .bezetting-badge.empty {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        /* Collapsible Content */
        .tree-children {
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            border-left: 2px solid #e8e8e8;
            margin-left: 15px;
            padding-left: 20px;
        }
        
        .tree-children.collapsed {
            max-height: 0;
            opacity: 0;
        }
        
        .tree-children:not(.collapsed) {
            max-height: 1000px;
            opacity: 1;
        }
        
        /* Enhanced Visual Guide Lines for Clear Hierarchy */
        .tree-item {
            position: relative;
        }
        
        /* Horizontal connectors */
        .tree-children .tree-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 18px;
            width: 20px;
            height: 2px;
            background: #e8e8e8;
            z-index: 1;
        }
        
        /* Last item connector - removes vertical line after last item */
        .tree-children .tree-item:last-child::after {
            content: '';
            position: absolute;
            left: -17px;
            top: 20px;
            bottom: -10px;
            width: 2px;
            background: white;
            z-index: 2;
        }
        
        /* Different colors for different hierarchy levels */
        .tree-children {
            border-left-color: #d0d0d0;
        }
        
        .tree-children .tree-item::before {
            background: #d0d0d0;
        }
        
        /* Level 2 (Sub-bagian) */
        .tree-children .tree-children {
            border-left-color: #b8b8b8;
        }
        
        .tree-children .tree-children .tree-item::before {
            background: #b8b8b8;
        }
        
        /* Level 3 (Jabatan) */
        .tree-children .tree-children .tree-children {
            border-left-color: #a0a0a0;
        }
        
        .tree-children .tree-children .tree-children .tree-item::before {
            background: #a0a0a0;
        }
        
        /* Level 4 (ASN) */
        .tree-children .tree-children .tree-children .tree-children {
            border-left-color: #888888;
        }
        
        .tree-children .tree-children .tree-children .tree-children .tree-item::before {
            background: #888888;
        }
        
        /* Enhanced node styling for better hierarchy visualization */
        .tree-node {
            position: relative;
            z-index: 3;
            transition: all 0.2s ease;
            border-radius: 6px;
            margin: 2px 0;
        }
        
        .tree-node:hover {
            background-color: rgba(59, 130, 246, 0.05);
            transform: translateX(2px);
        }
        
        /* Add hierarchy indicators (dots) */
        .tree-children .tree-node::after {
            content: '';
            position: absolute;
            left: -30px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ccc;
            border: 2px solid white;
            z-index: 4;
        }
        
        .tree-children .tree-node.bagian-node::after {
            background: #2196f3;
        }
        
        .tree-children .tree-node.sub-bagian-node::after {
            background: #1976d2;
        }
        
        .tree-children .tree-node.jabatan-node::after {
            background: #4caf50;
        }
        
        .tree-children .tree-node.asn-node::after {
            background: #9c27b0;
            width: 6px;
            height: 6px;
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .scale-in {
            animation: scaleIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from { 
                opacity: 0;
                transform: scale(0.9);
            }
            to { 
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-header h1 {
                font-size: 2rem;
            }
            
            .modern-header {
                padding: var(--spacing-xl) 0;
            }
        }
    </style>
</head>

<body>
    <!-- Modern Header -->
    <div class="modern-header fade-in">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('opds.index') }}" class="text-white-50 text-decoration-none">
                            <i class="fas fa-home me-1"></i>Daftar OPD
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">
                        {{ $opd->nama }}
                    </li>
                </ol>
            </nav>

            <!-- Header Content -->
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <h1 class="mb-0 me-3" id="opdNama">{{ $opd->nama }}</h1>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" onclick="toggleEditNama()" title="Edit Nama OPD">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteOpdModal" title="Hapus OPD">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Form Edit Nama (Hidden by default) -->
                    <form action="{{ route('opds.update', $opd->id) }}" method="POST" id="editNamaForm" style="display: none;" class="mb-3">
                        @csrf
                        @method('PUT')
                        <div class="input-group">
                            <input type="text" class="form-control" name="nama" value="{{ $opd->nama }}" required>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditNama()">
                                <i class="fas fa-times me-1"></i>Batal
                            </button>
                        </div>
                    </form>
                    
                    <p class="lead mb-0">
                        <i class="fas fa-building me-2"></i>
                        Manajemen struktur organisasi dan jabatan
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('opds.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar OPD
                        </a>
                        <a href="{{ route('opds.export', $opd->id) }}" class="btn btn-warning">
                            <i class="fas fa-download me-2"></i>Export Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show slide-up" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show slide-up" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show slide-up" role="alert">
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
        <div class="row mb-5 fade-in">
            <!-- Card 1: Total Bagian, Total Jabatan, Total ASN -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card scale-in">
                    <div class="card-header bg-primary text-center">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan Umum</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-folder text-primary"></i>
                            <strong>{{ $opd->bagians->count() }}</strong> Bagian
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-user-tie text-success"></i>
                            <strong>{{ $opd->jabatanKepala->count() + $opd->bagians->sum(function($bagian) { return $bagian->jabatans->count(); }) }}</strong> Jabatan
                        </div>
                        <div>
                            <i class="fas fa-users text-info"></i>
                            <strong>{{ $opd->bagians->sum(function($bagian) { return $bagian->jabatans->sum(function($jabatan) { return $jabatan->asns->count(); }); }) + $opd->jabatanKepala->sum(function($jabatan) { return $jabatan->asns->count(); }) }}</strong> ASN
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Bezetting, Total Kebutuhan, Total Selisih -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card scale-in">
                    <div class="card-header bg-success text-center">
                        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Analisis Kebutuhan</h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $allJabatans = $opd->jabatanKepala->concat($opd->bagians->flatMap->jabatans);
                            $totalBezetting = $allJabatans->sum(function($jabatan) { return $jabatan->asns->count(); });
                            $totalKebutuhan = $allJabatans->sum('kebutuhan');
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
                <div class="stats-card scale-in">
                    <div class="card-header bg-info text-center">
                        <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Jenis Jabatan</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $jenisJabatan = $allJabatans->groupBy('jenis_jabatan');
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
                <div class="stats-card scale-in">
                    <div class="card-header bg-warning text-center">
                        <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Kelas Jabatan</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $kelasJabatan = $allJabatans->whereNotNull('kelas')->groupBy('kelas');
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
                <div class="modern-card slide-up">
                    <div class="card-header bg-success d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>
                            Daftar Jabatan - Tree Explorer
                        </h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addJabatanModal"
                                    title="Tambah Jabatan">
                                <i class="fas fa-plus me-1"></i>
                                Jabatan
                            </button>
                            <button type="button" class="btn btn-light btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addBagianModal"
                                    title="Tambah Bagian">
                                <i class="fas fa-folder-plus me-1"></i>
                                Bagian
                            </button>
                            
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Tree Explorer -->
                        <div class="tree-explorer">
                            <!-- Kepala OPD -->
                            @if($opd->jabatanKepala->count() > 0)
                                @foreach($opd->jabatanKepala as $jabatan)
                                    @php
                                        $bezetting = $jabatan->asns->count();
                                        $kebutuhan = $jabatan->kebutuhan;
                                        $selisih = $bezetting - $kebutuhan;
                                        $selisihClass = $selisih > 0 ? 'text-danger' : ($selisih < 0 ? 'text-primary' : 'text-success');
                                        $selisihIcon = $selisih > 0 ? '+' : '';
                                    @endphp
                                    <div class="tree-item">
                                        <div class="tree-node kepala-opd" onclick="toggleTreeNode(this)">
                                            @if($jabatan->asns->count() > 0)
                                                <button class="tree-toggle">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            @else
                                                <span class="tree-toggle"></span>
                                            @endif
                                            <div class="tree-icon">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                            <div class="tree-label">
                                                <div class="jabatan-title">{{ $jabatan->nama }}</div>
                                                <div class="jabatan-details text-muted small">
                                                    <span class="me-3"><strong>Jenis:</strong> {{ $jabatan->jenis_jabatan ?: '-' }}</span>
                                                    <span class="me-3"><strong>Kelas:</strong> {{ $jabatan->kelas ?: '-' }}</span>
                                                    <span class="me-3"><strong>Bezetting:</strong> {{ $bezetting }}</span>
                                                    <span class="me-3"><strong>Kebutuhan:</strong> {{ $kebutuhan }}</span>
                                                    <span class="{{ $selisihClass }}"><strong>+/-:</strong> {{ $selisihIcon }}{{ $selisih }}</span>
                                                </div>
                                            </div>
                                            <div class="tree-actions">
                                                <button class="tree-action-btn edit" onclick="event.stopPropagation(); editJabatan({{ $jabatan->id }}, '{{ addslashes($jabatan->nama) }}', null, '{{ addslashes($jabatan->jenis_jabatan ?: '') }}', {{ $jabatan->kelas ?: 'null' }}, {{ $jabatan->kebutuhan }})" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="tree-action-btn delete" onclick="event.stopPropagation(); deleteJabatan({{ $jabatan->id }}, '{{ addslashes($jabatan->nama) }}')" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- ASN untuk Kepala OPD -->
                                        @if($jabatan->asns->count() > 0)
                                            <div class="tree-children collapsed">
                                                @foreach($jabatan->asns as $asn)
                                                    <div class="tree-node asn-node">
                                                        <span class="tree-toggle"></span>
                                                        <div class="tree-icon">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="tree-label">
                                                            {{ $asn->nama }}
                                                        </div>
                                                        <div class="tree-meta">
                                                            {{ $asn->nip }}
                                                        </div>
                                                        <div class="tree-actions">
                                                            <button class="tree-action-btn edit" onclick="editAsn({{ $asn->id }}, '{{ $asn->nama }}', '{{ $asn->nip }}', {{ $jabatan->id }}, '')" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="tree-action-btn delete" onclick="deleteAsn({{ $asn->id }}, '{{ $asn->nama }}')" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <!-- Bagian dan Jabatan -->
                            @php
                                // Anonymous function to render bagian tree recursively
                                $renderBagianTree = function($bagians, $level = 0) use (&$renderBagianTree) {
                                    foreach($bagians as $bagian) {
                                        $hasChildren = $bagian->children->count() > 0 || $bagian->jabatans->count() > 0;
                                        $nodeClass = $level == 0 ? 'bagian-node' : 'sub-bagian-node';
                                        echo '<div class="tree-item">';
                                        echo '<div class="tree-node ' . $nodeClass . '" onclick="toggleTreeNode(this)">';
                                        
                                        if($hasChildren) {
                                            echo '<button class="tree-toggle"><i class="fas fa-chevron-down"></i></button>';
                                        } else {
                                            echo '<span class="tree-toggle"></span>';
                                        }
                                        
                                        echo '<div class="tree-icon">';
                                        echo $level == 0 ? '<i class="fas fa-folder"></i>' : '<i class="fas fa-folder-open"></i>';
                                        echo '</div>';
                                        echo '<div class="tree-label">' . $bagian->nama . '</div>';
                                        echo '<div class="tree-meta">' . $bagian->jabatans->count() . ' jabatan</div>';
                                        echo '<div class="tree-actions">';
                                        echo '<button class="tree-action-btn edit" onclick="event.stopPropagation(); editBagian(' . $bagian->id . ', \'' . addslashes($bagian->nama) . '\', ' . ($bagian->parent_id ?: 'null') . ')" title="Edit">';
                                        echo '<i class="fas fa-edit"></i></button>';
                                        echo '<button class="tree-action-btn delete" onclick="event.stopPropagation(); deleteBagian(' . $bagian->id . ', \'' . addslashes($bagian->nama) . '\')" title="Hapus">';
                                        echo '<i class="fas fa-trash"></i></button>';
                                        echo '</div></div>';
                                        
                                        if($hasChildren) {
                                            echo '<div class="tree-children' . ($level > 0 ? ' level-' . ($level + 1) : '') . '">';
                                            
                                            // Render sub-bagian first
                                            if($bagian->children->count() > 0) {
                                                $renderBagianTree($bagian->children, $level + 1);
                                            }
                                            
                                            // Then render jabatan
                             foreach($bagian->jabatans as $jabatan) {
                                 $bezetting = $jabatan->asns->count();
                                 $kebutuhan = $jabatan->kebutuhan;
                                 $selisih = $bezetting - $kebutuhan;
                                 $selisihClass = $selisih > 0 ? 'text-danger' : ($selisih < 0 ? 'text-primary' : 'text-success');
                                 $selisihIcon = $selisih > 0 ? '+' : '';
                                 
                                 echo '<div class="tree-item">';
                                echo '<div class="tree-node jabatan-node" onclick="toggleTreeNode(this)">';
                                
                                if($jabatan->asns->count() > 0) {
                                    echo '<button class="tree-toggle"><i class="fas fa-chevron-down"></i></button>';
                                } else {
                                    echo '<span class="tree-toggle"></span>';
                                }
                                
                                echo '<div class="tree-icon"><i class="fas fa-user-tie"></i></div>';
                                echo '<div class="tree-label">';
                                echo '<div class="jabatan-title">' . $jabatan->nama . '</div>';
                                echo '<div class="jabatan-details text-muted small">';
                                echo '<span class="me-3"><strong>Jenis:</strong> ' . ($jabatan->jenis_jabatan ?: '-') . '</span>';
                                echo '<span class="me-3"><strong>Kelas:</strong> ' . ($jabatan->kelas ?: '-') . '</span>';
                                echo '<span class="me-3"><strong>Bezetting:</strong> ' . $bezetting . '</span>';
                                echo '<span class="me-3"><strong>Kebutuhan:</strong> ' . $kebutuhan . '</span>';
                                echo '<span class="' . $selisihClass . '"><strong>+/-:</strong> ' . $selisihIcon . $selisih . '</span>';
                                echo '</div>';
                                echo '</div>';
                                echo '<div class="tree-actions">';
                                echo '<button class="tree-action-btn edit" onclick="event.stopPropagation(); editJabatan(' . $jabatan->id . ', \'' . addslashes($jabatan->nama) . '\', ' . ($jabatan->bagian_id ?: 'null') . ', \'' . addslashes($jabatan->jenis_jabatan ?: '') . '\', ' . ($jabatan->kelas ?: 'null') . ', ' . $jabatan->kebutuhan . ')" title="Edit">';
                                echo '<i class="fas fa-edit"></i></button>';
                                echo '<button class="tree-action-btn delete" onclick="event.stopPropagation(); deleteJabatan(' . $jabatan->id . ', \'' . addslashes($jabatan->nama) . '\')" title="Hapus">';
                                echo '<i class="fas fa-trash"></i></button>';
                                echo '</div></div>';
                                                
                                                // ASN dalam Jabatan
                                                 if($jabatan->asns->count() > 0) {
                                                     echo '<div class="tree-children collapsed">';
                                                     foreach($jabatan->asns as $asn) {
                                                         echo '<div class="tree-node asn-node">';
                                                        echo '<span class="tree-toggle"></span>';
                                                        echo '<div class="tree-icon"><i class="fas fa-user"></i></div>';
                                                        echo '<div class="tree-label">' . $asn->nama . '</div>';
                                                        echo '<div class="tree-meta">' . $asn->nip . '</div>';
                                                        echo '<div class="tree-actions">';
                                                        echo '<button class="tree-action-btn edit" onclick="editAsn(' . $asn->id . ', \'' . $asn->nama . '\', \'' . $asn->nip . '\', ' . $jabatan->id . ', ' . $bagian->id . ')" title="Edit">';
                                                        echo '<i class="fas fa-edit"></i></button>';
                                                        echo '<button class="tree-action-btn delete" onclick="deleteAsn(' . $asn->id . ', \'' . $asn->nama . '\')" title="Hapus">';
                                                        echo '<i class="fas fa-trash"></i></button>';
                                                        echo '</div></div>';
                                                    }
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                };
                                
                                // Get only root bagians (parent_id is null) and render them
                                $rootBagians = $opd->bagians->where('parent_id', null);
                                $renderBagianTree($rootBagians);
                            @endphp
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Tree Explorer Toggle Function
        function toggleTreeNode(nodeElement) {
            const treeItem = nodeElement.closest('.tree-item');
            const children = treeItem.querySelector('.tree-children');
            const toggle = nodeElement.querySelector('.tree-toggle i');
            
            if (children) {
                if (children.classList.contains('collapsed')) {
                    children.classList.remove('collapsed');
                    if (toggle) {
                        toggle.classList.remove('fa-chevron-right');
                        toggle.classList.add('fa-chevron-down');
                    }
                } else {
                    children.classList.add('collapsed');
                    if (toggle) {
                        toggle.classList.remove('fa-chevron-down');
                        toggle.classList.add('fa-chevron-right');
                    }
                }
            }
        }

        // Initialize tree explorer default state
        document.addEventListener('DOMContentLoaded', function() {
            // Set default state: expand bagian, collapse ASN
            const asnContainers = document.querySelectorAll('.jabatan-node .tree-children');
            const kepalaOpdContainers = document.querySelectorAll('.kepala-opd .tree-children');
            
            // Collapse all ASN by default
            asnContainers.forEach(container => {
                container.classList.add('collapsed');
                const toggle = container.parentElement.querySelector('.tree-toggle i');
                if (toggle) {
                    toggle.classList.remove('fa-chevron-down');
                    toggle.classList.add('fa-chevron-right');
                }
            });

            // Collapse Kepala OPD ASN by default
            kepalaOpdContainers.forEach(container => {
                container.classList.add('collapsed');
                const toggle = container.parentElement.querySelector('.tree-toggle i');
                if (toggle) {
                    toggle.classList.remove('fa-chevron-down');
                    toggle.classList.add('fa-chevron-right');
                }
            });
        });

        // Toggle edit nama OPD
        function toggleEditNama() {
            const namaDisplay = document.getElementById('opdNama');
            const editForm = document.getElementById('editNamaForm');
            
            if (editForm.style.display === 'none') {
                namaDisplay.style.display = 'none';
                editForm.style.display = 'block';
                editForm.querySelector('input').focus();
            } else {
                namaDisplay.style.display = 'block';
                editForm.style.display = 'none';
            }
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

        // Functions untuk Bagian management
        window.editBagian = function(id, nama, parentId) {
            // Populate edit form
            document.getElementById('edit_bagian_id').value = id;
            document.getElementById('edit_nama_bagian').value = nama;
            document.getElementById('edit_parent_bagian_id').value = parentId || '';
            
            // Set form action
            document.getElementById('editBagianForm').action = `/opds/{{ $opd->id }}/bagian/${id}`;
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editBagianModal'));
            editModal.show();
        };

        window.deleteBagian = function(id, nama) {
            // Set data for delete confirmation
            document.getElementById('delete_bagian_id').value = id;
            document.getElementById('delete_bagian_nama').textContent = nama;
            
            // Set form action
            document.getElementById('deleteBagianForm').action = `/opds/{{ $opd->id }}/bagian/${id}`;
            
            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteBagianModal'));
            deleteModal.show();
        };

        // Functions untuk Jabatan management
        window.editJabatan = function(id, nama, bagianId, jenisJabatan, kelas, kebutuhan) {
            // Populate edit form
            document.getElementById('edit_jabatan_id').value = id;
            document.getElementById('edit_nama_jabatan').value = nama;
            document.getElementById('edit_bagian_jabatan_id').value = bagianId || '';
            document.getElementById('edit_jenis_jabatan').value = jenisJabatan || '';
            document.getElementById('edit_kelas_jabatan').value = kelas || '';
            document.getElementById('edit_kebutuhan_jabatan').value = kebutuhan || 1;
            
            // Set form action
            document.getElementById('editJabatanForm').action = `/opds/{{ $opd->id }}/jabatan/${id}`;
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editJabatanModal'));
            editModal.show();
        };

        window.deleteJabatan = function(id, nama) {
            // Set data for delete confirmation
            document.getElementById('delete_jabatan_id').value = id;
            document.getElementById('delete_jabatan_nama').textContent = nama;
            
            // Set form action
            document.getElementById('deleteJabatanForm').action = `/opds/{{ $opd->id }}/jabatan/${id}`;
            
            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteJabatanModal'));
            deleteModal.show();
        };

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to tree nodes
            const treeNodes = document.querySelectorAll('.tree-node');
            treeNodes.forEach(node => {
                node.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 2px 8px rgba(102, 126, 234, 0.15)';
                });
                
                node.addEventListener('mouseleave', function() {
                    this.style.boxShadow = 'none';
                });
            });
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
                                        @foreach($opd->bagians as $bagian)
                                            @foreach($bagian->jabatans as $jabatan)
                                                <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} - {{ $bagian->nama }}</option>
                                            @endforeach
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

    <!-- Modal Edit Bagian -->
    <div class="modal fade" id="editBagianModal" tabindex="-1" aria-labelledby="editBagianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="editBagianModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Bagian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBagianForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_bagian_id" name="bagian_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="edit_nama_bagian" class="form-label">Nama Bagian *</label>
                                    <input type="text" class="form-control" id="edit_nama_bagian" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_parent_bagian_id" class="form-label">Bagian Induk</label>
                                    <select class="form-select" id="edit_parent_bagian_id" name="parent_id">
                                        <option value="">Bagian Utama (Tidak ada induk)</option>
                                        @foreach($opd->bagians->where('parent_id', null) as $bagian)
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
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Update Bagian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Bagian -->
    <div class="modal fade" id="deleteBagianModal" tabindex="-1" aria-labelledby="deleteBagianModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteBagianModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Bagian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p>Anda yakin ingin menghapus bagian <strong id="delete_bagian_nama"></strong>?</p>
                    <p class="text-muted">Semua jabatan dan ASN yang terkait dengan bagian ini juga akan dihapus secara permanen.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <form id="deleteBagianForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="delete_bagian_id" name="bagian_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Ya, Hapus Bagian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Jabatan -->
    <div class="modal fade" id="editJabatanModal" tabindex="-1" aria-labelledby="editJabatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editJabatanModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Data Jabatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editJabatanForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_jabatan_id" name="jabatan_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama_jabatan" class="form-label">Nama Jabatan *</label>
                                    <input type="text" class="form-control" id="edit_nama_jabatan" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_bagian_jabatan_id" class="form-label">Bagian</label>
                                    <select class="form-select" id="edit_bagian_jabatan_id" name="bagian_id">
                                        <option value="">Pilih Bagian (Opsional untuk Kepala OPD)</option>
                                        @foreach($opd->bagians as $bagian)
                                            <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_jenis_jabatan" class="form-label">Jenis Jabatan</label>
                                    <select class="form-select" id="edit_jenis_jabatan" name="jenis_jabatan">
                                        <option value="">Pilih Jenis</option>
                                        <option value="Struktural">Struktural</option>
                                        <option value="Fungsional">Fungsional</option>
                                        <option value="Pelaksana">Pelaksana</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_kelas_jabatan" class="form-label">Kelas</label>
                                    <input type="number" class="form-control" id="edit_kelas_jabatan" name="kelas" min="1" max="17">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_kebutuhan_jabatan" class="form-label">Kebutuhan *</label>
                                    <input type="number" class="form-control" id="edit_kebutuhan_jabatan" name="kebutuhan" min="1" value="1" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Jabatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Jabatan -->
    <div class="modal fade" id="deleteJabatanModal" tabindex="-1" aria-labelledby="deleteJabatanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteJabatanModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Jabatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p>Anda yakin ingin menghapus jabatan <strong id="delete_jabatan_nama"></strong>?</p>
                    <p class="text-muted">Semua ASN yang terkait dengan jabatan ini juga akan dihapus secara permanen.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <form id="deleteJabatanForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="delete_jabatan_id" name="jabatan_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Ya, Hapus Jabatan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Jabatan -->
    <div class="modal fade" id="addJabatanModal" tabindex="-1" aria-labelledby="addJabatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addJabatanModalLabel">
                        <i class="fas fa-user-tie me-2"></i>Tambah Jabatan Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('opds.jabatan.store', $opd->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_jabatan" class="form-label">Nama Jabatan *</label>
                                    <input type="text" class="form-control" id="nama_jabatan" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bagian_id" class="form-label">Bagian</label>
                                    <select class="form-select" id="bagian_id" name="bagian_id">
                                        <option value="">Pilih Bagian (Opsional untuk Kepala OPD)</option>
                                        @foreach($opd->bagians as $bagian)
                                            <option value="{{ $bagian->id }}">{{ $bagian->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="jenis_jabatan" class="form-label">Jenis Jabatan</label>
                                    <select class="form-select" id="jenis_jabatan" name="jenis_jabatan">
                                        <option value="">Pilih Jenis</option>
                                        <option value="Struktural">Struktural</option>
                                        <option value="Fungsional">Fungsional</option>
                                        <option value="Pelaksana">Pelaksana</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="kelas" class="form-label">Kelas</label>
                                    <input type="number" class="form-control" id="kelas" name="kelas" min="1" max="17">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="kebutuhan" class="form-label">Kebutuhan *</label>
                                    <input type="number" class="form-control" id="kebutuhan" name="kebutuhan" min="1" value="1" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Jabatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Bagian -->
    <div class="modal fade" id="addBagianModal" tabindex="-1" aria-labelledby="addBagianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addBagianModalLabel">
                        <i class="fas fa-folder-plus me-2"></i>Tambah Bagian Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('opds.bagian.store', $opd->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nama_bagian" class="form-label">Nama Bagian *</label>
                                    <input type="text" class="form-control" id="nama_bagian" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="parent_bagian_id" class="form-label">Bagian Induk</label>
                                    <select class="form-select" id="parent_bagian_id" name="parent_id">
                                        <option value="">Bagian Utama (Tidak ada induk)</option>
                                        @foreach($opd->bagians->where('parent_id', null) as $bagian)
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
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Simpan Bagian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah ASN -->
    <div class="modal fade" id="addAsnModal" tabindex="-1" aria-labelledby="addAsnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="addAsnModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Tambah ASN Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('opds.asn.store', $opd->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_asn" class="form-label">Nama ASN *</label>
                                    <input type="text" class="form-control" id="nama_asn" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nip_asn" class="form-label">NIP *</label>
                                    <input type="text" class="form-control" id="nip_asn" name="nip" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jabatan_asn" class="form-label">Jabatan *</label>
                                    <select class="form-select" id="jabatan_asn" name="jabatan_id" required>
                                        <option value="">Pilih Jabatan</option>
                                        @foreach($opd->jabatanKepala as $jabatan)
                                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} (Kepala OPD)</option>
                                        @endforeach
                                        @foreach($opd->bagians as $bagian)
                                            @foreach($bagian->jabatans as $jabatan)
                                                <option value="{{ $jabatan->id }}">{{ $jabatan->nama }} - {{ $bagian->nama }}</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bagian_asn" class="form-label">Bagian</label>
                                    <select class="form-select" id="bagian_asn" name="bagian_id">
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
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save me-1"></i>Simpan ASN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>