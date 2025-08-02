<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Peta Jabatan - Dashboard OPD</title>
    <meta name="description" content="Sistem Peta Jabatan untuk mengelola struktur organisasi perangkat daerah">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Modern CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --dark-gradient: linear-gradient(135deg, #434343 0%, #000000 100%);
            
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #4facfe;
            --warning-color: #43e97b;
            
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --text-muted: #a0aec0;
            
            --bg-primary: #ffffff;
            --bg-secondary: #f7fafc;
            --bg-accent: #edf2f7;
            
            --border-color: #e2e8f0;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 20px;
            
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Modern Header */
        .modern-header {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0 6rem;
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
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .modern-header .container {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .hero-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        /* Modern Cards */
        .modern-container {
            margin-top: -3rem;
            position: relative;
            z-index: 3;
        }

        .stats-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        /* Modern Search */
        .search-section {
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .modern-search {
            position: relative;
        }

        .modern-search input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-xl);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-secondary);
        }

        .modern-search input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: var(--bg-primary);
        }

        .modern-search .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* Modern OPD Grid */
        .opd-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .opd-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .opd-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .opd-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .opd-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .opd-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .opd-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .opd-id {
            background: var(--bg-accent);
            color: var(--text-secondary);
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .opd-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .opd-stat {
            text-align: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }

        .opd-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .opd-stat-number.bagian {
            color: var(--success-color);
        }

        .opd-stat-number.jabatan {
            color: var(--warning-color);
        }

        .opd-stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .opd-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-modern {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline-modern:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Modern Footer */
        .modern-footer {
            background: var(--dark-gradient);
            color: white;
            padding: 3rem 0;
            margin-top: 4rem;
        }

        .modern-footer .container {
            text-align: center;
        }

        .footer-content {
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .opd-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .opd-header {
                flex-direction: column;
                text-align: center;
            }
            
            .opd-icon {
                margin: 0 auto 1rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .opd-stats {
                grid-template-columns: 1fr;
            }
            
            .opd-actions {
                flex-direction: column;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
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
    </style>
    
    <!-- Additional Styles for Ripple Effect -->
    <style>
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(102, 126, 234, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .tooltip {
            position: absolute;
            background: var(--text-primary);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            animation: tooltip-show 0.2s ease-out forwards;
        }
        
        @keyframes tooltip-show {
            to { opacity: 1; }
        }
        
        #noResults {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Modern Header Section -->
    <header class="modern-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="hero-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h1 class="hero-title">
                        Sistem Peta Jabatan
                    </h1>
                    <p class="hero-subtitle">
                        Platform digital untuk mengelola dan memvisualisasikan struktur organisasi perangkat daerah secara komprehensif
                    </p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="modern-container">
        <div class="container">
            
            <!-- Statistics Overview -->
            <div class="stats-card">
                <div class="row align-items-center mb-3">
                    <div class="col-md-8">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                            Ringkasan Organisasi
                        </h2>
                        <p class="text-muted mb-0">Overview struktur organisasi perangkat daerah</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-primary fs-6" id="resultsCounter">{{ $opds->count() }} OPD</span>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">{{ $opds->count() }}</div>
                        <div class="stat-label">Total OPD</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $opds->sum(function($opd) { return $opd->bagians->count(); }) }}</div>
                        <div class="stat-label">Total Bagian</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $opds->sum(function($opd) { return $opd->jabatanKepala->count() + $opd->bagians->sum(function($bagian) { return $bagian->jabatans->count(); }); }) }}</div>
                        <div class="stat-label">Total Jabatan</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $opds->sum(function($opd) { return $opd->bagians->sum(function($bagian) { return $bagian->jabatans->sum(function($jabatan) { return $jabatan->asns->count(); }); }); }) }}</div>
                        <div class="stat-label">Total ASN</div>
                    </div>
                </div>
            </div>

            @if($opds->count() > 0)
                <!-- Search Section -->
                <div class="search-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="h5 mb-3">
                                <i class="fas fa-search me-2"></i>
                                Cari Organisasi Perangkat Daerah
                            </h3>
                            <div class="modern-search">
                                <i class="fas fa-search search-icon"></i>
                                <input 
                                    type="text" 
                                    id="searchOPD" 
                                    placeholder="Ketik nama OPD atau ID untuk mencari..."
                                    autocomplete="off"
                                >
                                <button type="button" id="clearSearch" class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-3" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex flex-column align-items-md-end">
                                <small class="text-muted mb-1">Filter berdasarkan:</small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="sortBy" id="sortName" value="name" checked>
                                    <label class="btn btn-outline-primary" for="sortName">Nama</label>
                                    
                                    <input type="radio" class="btn-check" name="sortBy" id="sortId" value="id">
                                    <label class="btn btn-outline-primary" for="sortId">ID</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OPD Grid -->
                <div class="opd-grid">
                    @foreach($opds->sortBy('nama') as $opd)
                        <div class="opd-card" data-opd-id="{{ $opd->id }}" data-opd-name="{{ strtolower($opd->nama) }}">
                            <div class="opd-header">
                                <div class="opd-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="opd-info">
                                    <h3>{{ $opd->nama }}</h3>
                                    <span class="opd-id">ID: {{ $opd->id }}</span>
                                </div>
                            </div>
                            
                            <div class="opd-stats">
                                <div class="opd-stat">
                                    <div class="opd-stat-number bagian">{{ $opd->bagians->count() }}</div>
                                    <div class="opd-stat-label">
                                        <i class="fas fa-users me-1"></i>
                                        Bagian
                                    </div>
                                </div>
                                <div class="opd-stat">
                                    <div class="opd-stat-number jabatan">{{ $opd->jabatanKepala->count() + $opd->bagians->sum(function($bagian) { return $bagian->jabatans->count(); }) }}</div>
                                    <div class="opd-stat-label">
                                        <i class="fas fa-user-tie me-1"></i>
                                        Jabatan
                                    </div>
                                </div>
                            </div>
                            
                            <div class="opd-actions">
                                <a href="{{ route('opds.show', $opd->id) }}" 
                                   class="btn-modern btn-primary-modern"
                                   data-tooltip="Lihat detail struktur organisasi">
                                    <i class="fas fa-eye me-2"></i>
                                    Lihat Detail
                                </a>
                                <a href="{{ route('api.opds.tree', $opd->id) }}" 
                                   class="btn-modern btn-outline-modern"
                                   data-tooltip="Download data dalam format JSON"
                                   target="_blank">
                                    <i class="fas fa-download me-2"></i>
                                    Export
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Tidak Ada Hasil Ditemukan</h3>
                    <p>Coba gunakan kata kunci yang berbeda atau periksa ejaan pencarian Anda.</p>
                </div>

            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Belum Ada Data OPD</h3>
                    <p>Sistem belum memiliki data Organisasi Perangkat Daerah. Silakan tambahkan data OPD terlebih dahulu untuk memulai.</p>
                    <div class="mt-4">
                        <a href="#" class="btn-modern btn-primary-modern">
                            <i class="fas fa-plus me-2"></i>
                            Tambah OPD Pertama
                        </a>
                    </div>
                </div>
            @endif
            
        </div>
    </main>

    <!-- Modern Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <div class="row">
                    <div class="col-md-6 text-md-start text-center">
                        <p class="mb-2">
                            <i class="fas fa-building me-2"></i>
                            <strong>Sistem Peta Jabatan</strong>
                        </p>
                        <p class="mb-0 opacity-75">Platform manajemen struktur organisasi digital</p>
                    </div>
                    <div class="col-md-6 text-md-end text-center mt-3 mt-md-0">
                        <p class="mb-2">
                            <i class="fas fa-calendar me-2"></i>
                            &copy; {{ date('Y') }} All rights reserved
                        </p>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-code me-2"></i>
                            Powered by Laravel {{ app()->version() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Modern JavaScript -->
    <script>
        // Initialize modern features
        document.addEventListener('DOMContentLoaded', function() {
            initializeAnimations();
            initializeSearch();
            initializeStats();
            initializeCardEffects();
        });

        // Animation initialization
        function initializeAnimations() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, observerOptions);

            // Observe all cards
            document.querySelectorAll('.opd-card, .stats-card, .search-section').forEach(card => {
                observer.observe(card);
            });
        }

        // Search functionality
        function initializeSearch() {
            const searchInput = document.getElementById('searchOPD');
            const clearButton = document.getElementById('clearSearch');
            const sortRadios = document.querySelectorAll('input[name="sortBy"]');
            
            if (searchInput) {
                searchInput.addEventListener('input', debounce(performSearch, 300));
                
                if (clearButton) {
                    searchInput.addEventListener('input', function() {
                        if (this.value.length > 0) {
                            clearButton.style.display = 'block';
                        } else {
                            clearButton.style.display = 'none';
                        }
                    });
                    
                    clearButton.addEventListener('click', function() {
                        searchInput.value = '';
                        searchInput.dispatchEvent(new Event('input'));
                        searchInput.focus();
                    });
                }
            }
            
            // Sort functionality
            sortRadios.forEach(radio => {
                radio.addEventListener('change', performSort);
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput?.focus();
                }
                
                // Escape to clear search
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                    searchInput.blur();
                }
            });
        }

        function performSearch() {
            const searchTerm = document.getElementById('searchOPD').value.toLowerCase();
            const cards = document.querySelectorAll('.opd-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const opdName = card.dataset.opdName;
                const opdId = card.dataset.opdId;
                const isVisible = opdName.includes(searchTerm) || opdId.includes(searchTerm);
                
                card.style.display = isVisible ? 'block' : 'none';
                if (isVisible) visibleCount++;
            });

            // Update results counter
            const resultsCounter = document.getElementById('resultsCounter');
            if (resultsCounter) {
                resultsCounter.textContent = `${visibleCount} OPD`;
            }

            // Show/hide no results message
            const noResults = document.getElementById('noResults');
            if (noResults) {
                noResults.style.display = visibleCount === 0 && searchTerm ? 'block' : 'none';
            }
        }

        function performSort() {
            const sortValue = document.querySelector('input[name="sortBy"]:checked').value;
            const container = document.querySelector('.opd-grid');
            const cards = Array.from(container.children).filter(child => child.classList.contains('opd-card'));

            cards.sort((a, b) => {
                if (sortValue === 'name') {
                    const nameA = a.dataset.opdName;
                    const nameB = b.dataset.opdName;
                    return nameA.localeCompare(nameB);
                } else if (sortValue === 'id') {
                    const idA = parseInt(a.dataset.opdId);
                    const idB = parseInt(b.dataset.opdId);
                    return idA - idB;
                }
                return 0;
            });

            // Clear container and re-append sorted cards
            const noResults = document.getElementById('noResults');
            container.innerHTML = '';
            cards.forEach(card => {
                container.appendChild(card);
                card.classList.add('slide-up');
            });
            if (noResults) container.appendChild(noResults);
        }

        // Statistics counter animation
        function initializeStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                if (!isNaN(target)) {
                    animateCounter(stat, 0, target, 2000);
                }
            });
        }

        function animateCounter(element, start, end, duration) {
            const startTime = performance.now();
            
            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const current = Math.floor(start + (end - start) * easeOutQuart(progress));
                element.textContent = current;
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            }
            
            requestAnimationFrame(updateCounter);
        }

        function easeOutQuart(t) {
            return 1 - Math.pow(1 - t, 4);
        }

        // Card hover effects
        function initializeCardEffects() {
            const cards = document.querySelectorAll('.opd-card');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
                
                // Ripple effect on click
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        }

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Theme toggle (placeholder for future implementation)
        function toggleTheme() {
            // Implementation for dark/light theme toggle
            console.log('Theme toggle clicked');
        }

        // Tooltip initialization
        function initializeTooltips() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            
            tooltipElements.forEach(element => {
                element.addEventListener('mouseenter', function(e) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = this.dataset.tooltip;
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                });
                
                element.addEventListener('mouseleave', function() {
                    const tooltip = document.querySelector('.tooltip');
                    if (tooltip) tooltip.remove();
                });
            });
        }

        // Export functions for external use
        window.ModernOPD = {
            search: performSearch,
            sort: performSort,
            toggleTheme: toggleTheme
        };
    </script>
</body>
</html>