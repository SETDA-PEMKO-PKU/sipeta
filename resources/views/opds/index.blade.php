<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar OPD - Sistem Peta Jabatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
            border-radius: 0 0 10px 10px;
        }
        
        .table {
            font-size: 0.9rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .search-box {
            border-radius: 20px;
            border: 1px solid #ced4da;
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        
        .search-box:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.1rem rgba(0,123,255,.15);
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-building me-3"></i>
                        Sistem Peta Jabatan
                    </h1>
                    <p class="lead">Pilih Organisasi Perangkat Daerah untuk melihat struktur jabatan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-dark">
                        <i class="fas fa-list me-2"></i>
                        Daftar OPD
                    </h2>
                    <span class="badge bg-primary fs-6">{{ $opds->count() }} OPD</span>
                </div>
            </div>
        </div>

        @if($opds->count() > 0)
            <div class="row">
                <div class="col-12">
                    <!-- Search Box -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control search-box" id="searchOPD" placeholder="Cari nama OPD...">
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover" id="opdTable">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="40%">Nama OPD</th>
                                    <th width="15%" class="text-center">Jumlah Bagian</th>
                                    <th width="15%" class="text-center">Jumlah Jabatan</th>
                                    <th width="25%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($opds->sortBy('id') as $index => $opd)
                                    <tr>
                                        <td class="fw-bold">{{ $opd->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                <span class="fw-semibold">{{ $opd->nama }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                <i class="fas fa-users me-1"></i>
                                                {{ $opd->bagians->count() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                <i class="fas fa-user-tie me-1"></i>
                                                {{ $opd->jabatans->count() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('opds.show', $opd->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Belum Ada Data OPD</h4>
                        <p class="mb-0">Silakan tambahkan data OPD terlebih dahulu.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Sistem Peta Jabatan. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchOPD').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#opdTable tbody tr');
            
            tableRows.forEach(function(row) {
                const opdName = row.querySelector('td:nth-child(2) span').textContent.toLowerCase();
                
                if (opdName.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>