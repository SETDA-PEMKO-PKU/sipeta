# System Requirements - SIPETA

**SIPETA** (Sistem Informasi Peta Jabatan ASN)  
Dokumen spesifikasi kebutuhan sistem untuk pengelolaan peta jabatan dan kepegawaian ASN.

---

## 📋 Daftar Isi

- [1. Kebutuhan Fungsional](#1-kebutuhan-fungsional)
- [2. Kebutuhan Non-Fungsional](#2-kebutuhan-non-fungsional)
- [3. Kebutuhan Teknis](#3-kebutuhan-teknis)
- [4. User Requirements](#4-user-requirements)
- [5. Kebutuhan Interface](#5-kebutuhan-interface)

---

## 1. Kebutuhan Fungsional

### 1.1 Modul Autentikasi & Otorisasi

**FR-001: Login Sistem**
- **Deskripsi**: Admin dapat login menggunakan email dan password
- **Actor**: Super Admin, Admin Organisasi, Admin BKPSDM
- **Input**: Email, Password
- **Proses**: Validasi kredensial, generate session
- **Output**: Redirect ke dashboard sesuai role
- **Business Rules**:
  - Email harus valid dan terdaftar
  - Password minimal 8 karakter
  - Max 5 percobaan login gagal (lockout 15 menit)
  - Session timeout: 120 menit

**FR-002: Role-Based Access Control (RBAC)**
- **Deskripsi**: Sistem membatasi akses berdasarkan role pengguna
- **Roles**:
  - **Super Admin**: Full access semua modul
  - **Admin Organisasi**: OPD + Jabatan only
  - **Admin BKPSDM**: ASN management only
- **Business Rules**:
  - Role ditentukan saat pembuatan akun
  - Hanya Super Admin yang bisa mengubah role
  - Akses yang tidak sesuai role akan ditolak (HTTP 403)

**FR-003: Logout**
- **Deskripsi**: Admin dapat logout dari sistem
- **Proses**: Hapus session, redirect ke login page

---

### 1.2 Modul Pengelolaan OPD

**FR-101: Tambah OPD Baru**
- **Actor**: Super Admin, Admin Organisasi
- **Input**: Nama OPD
- **Validasi**:
  - Nama wajib diisi
  - Minimal 3 karakter
  - Nama OPD unik (tidak boleh duplikat)
- **Output**: OPD baru tersimpan di database

**FR-102: Edit OPD**
- **Actor**: Super Admin, Admin Organisasi
- **Input**: ID OPD, Nama OPD baru
- **Validasi**: Sama seperti FR-101
- **Output**: Data OPD ter-update

**FR-103: Hapus OPD**
- **Actor**: Super Admin
- **Business Rules**:
  - OPD tidak bisa dihapus jika masih memiliki Jabatan (root)
  - OPD tidak bisa dihapus jika masih memiliki ASN
  - Tampilkan konfirmasi sebelum hapus
- **Output**: OPD terhapus dari sistem

**FR-104: Lihat Daftar OPD**
- **Actor**: Semua admin
- **Output**: Tabel OPD dengan kolom: Nama, Jumlah Jabatan, Jumlah ASN, Aksi
- **Features**:
  - Pagination (20 items per page)
  - Search by nama
  - Sort by nama/created_at

---

### 1.3 Modul Pengelolaan Jabatan

**FR-201: Tambah Jabatan Root (Kepala OPD)**
- **Actor**: Super Admin, Admin Organisasi
- **Input**:
  - Nama Jabatan
  - Jenis Jabatan (Struktural/Fungsional)
  - Kelas Jabatan (dropdown)
  - Kebutuhan Formasi (integer)
  - OPD (dropdown)
- **Validasi**:
  - Semua field wajib diisi
  - Kelas jabatan: 1-17
  - Kebutuhan formasi > 0
- **Business Rules**:
  - `opd_id` harus diisi
  - `parent_id` = NULL
- **Output**: Jabatan root tersimpan

**FR-202: Tambah Sub-Jabatan**
- **Actor**: Super Admin, Admin Organisasi
- **Input**:
  - Nama Jabatan
  - Jenis Jabatan
  - Kelas Jabatan
  - Kebutuhan Formasi
  - Parent Jabatan (dropdown hierarchical)
- **Business Rules**:
  - `parent_id` harus diisi
  - `opd_id` = NULL
  - Parent harus sudah exist
- **Output**: Sub-jabatan tersimpan

**FR-203: Edit Jabatan**
- **Actor**: Super Admin, Admin Organisasi
- **Input**: Data jabatan baru
- **Validasi**: Sama seperti FR-201/FR-202
- **Output**: Data jabatan ter-update

**FR-204: Hapus Jabatan**
- **Actor**: Super Admin
- **Business Rules**:
  - Tampilkan peringatan jika ada sub-jabatan (cascade delete)
  - Tampilkan peringatan jika ada ASN yang menjabat
  - Konfirmasi dua langkah untuk jabatan dengan children
- **Output**: Jabatan dan children-nya terhapus

**FR-205: Lihat Hierarki Jabatan**
- **Actor**: Semua user
- **Output**: Tree view struktur jabatan per OPD
- **Features**:
  - Expand/collapse nodes
  - Show jumlah ASN per jabatan
  - Color coding: hijau (formasi cukup), merah (formasi penuh), kuning (formasi kosong)

---

### 1.4 Modul Pengelolaan ASN

**FR-301: Tambah ASN Baru**
- **Actor**: Super Admin, Admin BKPSDM
- **Input**:
  - Nama ASN
  - NIP (18 digit)
  - Jabatan (dropdown)
  - OPD (dropdown)
- **Validasi**:
  - NIP harus 18 digit numerik
  - NIP harus unik
  - Jabatan dan OPD wajib dipilih
- **Business Rules**:
  - Cek formasi sebelum tambah
  - Warning jika formasi penuh (allow override)
- **Output**: ASN baru tersimpan

**FR-302: Edit Data ASN**
- **Actor**: Super Admin, Admin BKPSDM
- **Input**: Data ASN terbaru
- **Validasi**: Sama seperti FR-301
- **Output**: Data ASN ter-update

**FR-303: Hapus ASN**
- **Actor**: Super Admin
- **Business Rules**:
  - Konfirmasi sebelum hapus
  - Catat log penghapusan
- **Output**: ASN terhapus dari sistem

**FR-304: Mutasi Jabatan ASN**
- **Actor**: Super Admin, Admin BKPSDM
- **Input**: ASN ID, Jabatan Baru
- **Business Rules**:
  - OPD tidak berubah
  - Cek formasi jabatan baru
  - Catat tanggal mutasi
- **Output**: ASN pindah ke jabatan baru, log tersimpan

**FR-305: Mutasi OPD ASN**
- **Actor**: Super Admin, Admin BKPSDM
- **Input**: ASN ID, OPD Baru, Jabatan Baru
- **Business Rules**:
  - Jabatan harus ada di OPD tujuan
  - Cek formasi
  - Catat tanggal mutasi
- **Output**: ASN pindah ke OPD + jabatan baru

**FR-306: Lihat Daftar ASN**
- **Actor**: Semua admin
- **Output**: Tabel ASN dengan kolom: Nama, NIP, Jabatan, OPD, Aksi
- **Features**:
  - Filter by OPD
  - Filter by Jabatan
  - Search by nama/NIP
  - Pagination
  - Export to Excel/PDF

---

### 1.5 Modul Peta Jabatan & Visualisasi

**FR-401: Generate Peta Jabatan**
- **Actor**: Semua user (termasuk public)
- **Input**: Pilih OPD
- **Output**: Organizational chart hierarchical
- **Features**:
  - Interactive tree view
  - Show foto/avatar ASN (optional)
  - Show nama jabatan, kelas, dan nama ASN
  - Color coding formasi
  - Zoom in/out
  - Print friendly

**FR-402: Dashboard Statistik**
- **Actor**: Semua admin
- **Output**: Dashboard dengan metrics:
  - Total OPD
  - Total Jabatan
  - Total ASN
  - Formasi Terisi vs Kosong (chart)
  - ASN per OPD (chart)
  - Distribusi Jenis Jabatan (pie chart)

**FR-403: Laporan Formasi Jabatan**
- **Actor**: Semua admin
- **Output**: Tabel formasi dengan kolom:
  - Jabatan, Kebutuhan, Terisi, Kosong, %
- **Features**:
  - Filter by OPD
  - Sort by kolom
  - Export to Excel/PDF

**FR-404: Export Data**
- **Actor**: Semua admin
- **Format**: PDF, Excel, CSV
- **Data yang bisa di-export**:
  - Daftar OPD
  - Struktur Jabatan per OPD
  - Daftar ASN
  - Peta Jabatan (PDF)
  - Laporan Formasi

---

### 1.6 Modul Admin Management

**FR-501: Tambah Admin Baru**
- **Actor**: Super Admin only
- **Input**: Nama, Email, Password, Role
- **Validasi**:
  - Email unik
  - Password minimal 8 karakter
  - Role valid (super_admin, admin_organisasi, admin_bkpsdm)
- **Output**: Admin baru tersimpan

**FR-502: Edit Admin**
- **Actor**: Super Admin
- **Input**: Data admin terbaru
- **Output**: Data admin ter-update

**FR-503: Nonaktifkan Admin**
- **Actor**: Super Admin
- **Business Rules**:
  - Admin tidak dihapus, hanya di-nonaktifkan
  - Admin nonaktif tidak bisa login
- **Output**: `is_active = false`

**FR-504: Reset Password Admin**
- **Actor**: Super Admin
- **Output**: Password baru digenerate dan dikirim via email

---

## 2. Kebutuhan Non-Fungsional

### 2.1 Performance

**NFR-101: Response Time**
- Halaman load < 2 detik (untuk data normal)
- API response < 500ms
- Query database < 200ms

**NFR-102: Concurrent Users**
- Sistem harus support minimal 100 concurrent users
- No performance degradation hingga 50 concurrent users

**NFR-103: Database Optimization**
- Index pada semua foreign keys
- Index pada NIP (unique)
- Index pada email (unique)

### 2.2 Security

**NFR-201: Password Security**
- Password harus di-hash menggunakan bcrypt
- Minimal password strength: medium
- Password tidak boleh sama dengan 3 password terakhir

**NFR-202: Data Protection**
- HTTPS only (enforce SSL)
- CSRF protection pada semua form
- XSS protection (escape output)
- SQL Injection prevention (prepared statements)

**NFR-203: Session Security**
- Session timeout: 120 menit
- Secure cookies (httpOnly, sameSite)
- Session regeneration setelah login

**NFR-204: Audit Trail**
- Log semua aktivitas CRUD
- Log login/logout
- Log mutasi ASN
- Retention: 1 tahun

### 2.3 Usability

**NFR-301: User Interface**
- Responsive design (mobile, tablet, desktop)
- Intuitive navigation (max 3 clicks to any feature)
- Consistent UI/UX pattern
- Breadcrumb navigation

**NFR-302: Accessibility**
- WCAG 2.1 Level AA compliance
- Keyboard navigation support
- Screen reader friendly

**NFR-303: Error Handling**
- User-friendly error messages
- Validation errors harus jelas dan spesifik
- No technical error exposed to user

### 2.4 Reliability

**NFR-401: Availability**
- Uptime: 99.5% (downtime max 3.6 jam/bulan)
- Scheduled maintenance: off-peak hours only

**NFR-402: Data Backup**
- Daily incremental backup
- Weekly full backup
- Retention: daily (7 days), weekly (4 weeks), monthly (12 months)
- Backup testing: monthly

**NFR-403: Disaster Recovery**
- RTO (Recovery Time Objective): < 4 jam
- RPO (Recovery Point Objective): < 24 jam

### 2.5 Maintainability

**NFR-501: Code Quality**
- PSR-12 coding standard
- Code documentation
- Unit test coverage: > 70%

**NFR-502: Logging**
- Application logs (error, warning, info)
- Log rotation: daily
- Log retention: 30 days

---

## 3. Kebutuhan Teknis

### 3.1 Server Requirements

**Production Server:**
- **OS**: Ubuntu 22.04 LTS atau CentOS 8
- **Web Server**: Nginx 1.20+ atau Apache 2.4+
- **PHP**: 8.1 atau 8.2
- **Database**: MySQL 8.0+ atau MariaDB 10.6+
- **RAM**: Minimum 4GB, Recommended 8GB
- **Storage**: Minimum 50GB SSD
- **CPU**: Minimum 2 cores, Recommended 4 cores

**Development Server:**
- Laravel Herd / Laravel Sail / XAMPP
- PHP 8.1+
- MySQL 8.0+
- Node.js 18+ (untuk asset compilation)

### 3.2 PHP Extensions Required

```
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo
- GD (untuk image processing)
```

### 3.3 Software Stack

**Backend:**
- Framework: Laravel 10.x
- Authentication: Laravel Sanctum / Breeze
- ORM: Eloquent
- Validation: Form Requests

**Frontend:**
- Template Engine: Blade
- CSS Framework: Tailwind CSS 3.x
- JavaScript: Alpine.js / Vue.js 3
- Charts: ApexCharts
- Orgchart: OrgChart.js atau D3.js

**Database:**
- RDBMS: MySQL 8.0+
- Charset: utf8mb4
- Collation: utf8mb4_unicode_ci

**Tools:**
- Version Control: Git
- Dependency Manager: Composer, NPM
- Task Runner: Vite
- Testing: PHPUnit, Laravel Dusk (optional)

### 3.4 Browser Support

- Chrome 90+ ✅
- Firefox 88+ ✅
- Safari 14+ ✅
- Edge 90+ ✅
- Opera 76+ ✅
- IE: Not supported ❌

---

## 4. User Requirements

### 4.1 Super Admin

**Kebutuhan Utama:**
- Akses penuh ke semua modul
- Kelola admin/user lain
- Lihat audit log
- Backup & restore data
- System configuration

**Use Cases:**
1. Menambah admin organisasi baru
2. Reset password admin yang lupa
3. Monitoring aktivitas sistem
4. Export full database
5. Konfigurasi sistem

### 4.2 Admin Organisasi

**Kebutuhan Utama:**
- Kelola OPD
- Kelola Jabatan (tambah, edit, hapus)
- Buat struktur hierarki jabatan
- Lihat peta jabatan
- Generate laporan organisasi

**Use Cases:**
1. Tambah OPD baru
2. Buat struktur jabatan untuk OPD baru
3. Update hierarki jabatan
4. Export struktur organisasi
5. Lihat formasi jabatan

### 4.3 Admin BKPSDM

**Kebutuhan Utama:**
- Kelola data ASN
- Mutasi jabatan ASN
- Mutasi OPD ASN
- Lihat peta jabatan
- Generate laporan kepegawaian

**Use Cases:**
1. Input ASN baru
2. Update data ASN
3. Proses mutasi jabatan
4. Proses mutasi OPD
5. Export daftar ASN

### 4.4 Public User

**Kebutuhan Utama:**
- Lihat peta jabatan (read-only)
- Cari ASN berdasarkan nama/NIP
- Lihat profil ASN (jika fitur diaktifkan)

**Use Cases:**
1. Browse struktur organisasi pemerintah
2. Cari pegawai tertentu
3. Lihat kontak OPD

---

## 5. Kebutuhan Interface

### 5.1 Dashboard Layout

```
┌─────────────────────────────────────────────────────────┐
│  LOGO SIPETA    [Home] [OPD] [Jabatan] [ASN]   [User ▼] │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  Dashboard > [Breadcrumb]                                │
│                                                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │ Total    │  │ Total    │  │ Total    │  │ Formasi  │ │
│  │ OPD      │  │ Jabatan  │  │ ASN      │  │ Kosong   │ │
│  │   15     │  │   240    │  │   1,850  │  │    85    │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘ │
│                                                           │
│  ┌────────────────────┐  ┌──────────────────────────┐   │
│  │  Chart:            │  │  Recent Activities:      │   │
│  │  ASN per OPD       │  │  - ASN baru: John Doe    │   │
│  │  (Bar Chart)       │  │  - Mutasi: Jane Smith    │   │
│  │                    │  │  - OPD baru: Dinas XYZ   │   │
│  └────────────────────┘  └──────────────────────────┘   │
│                                                           │
└─────────────────────────────────────────────────────────┘
```

### 5.2 Form Design Pattern

**Standard Form:**
```
┌─────────────────────────────────────┐
│  [Label]                            │
│  [                Input             ]│
│  Help text atau error message       │
└─────────────────────────────────────┘

Buttons: [Cancel]  [Save]
```

**Validation:**
- Real-time validation (on blur)
- Inline error messages (red text below field)
- Success state (green border)
- Error state (red border)

### 5.3 Table Design Pattern

```
┌────────────────────────────────────────────────────────┐
│  [Search: ___]  [Filter ▼]  [+ Tambah]  [Export ▼]    │
├────┬─────────┬─────────┬─────────┬──────────┬─────────┤
│ No │ Nama    │ Email   │ Role    │ Status   │ Aksi    │
├────┼─────────┼─────────┼─────────┼──────────┼─────────┤
│ 1  │ Admin 1 │ a@a.com │ Super   │ ● Aktif  │ [✎][🗑] │
│ 2  │ Admin 2 │ b@b.com │ OPD     │ ● Aktif  │ [✎][🗑] │
└────┴─────────┴─────────┴─────────┴──────────┴─────────┘
         Showing 1-20 of 100    [◄][1][2][3][►]
```

### 5.4 Color Scheme

**Primary Colors:**
- Primary: `#3B82F6` (Blue)
- Success: `#10B981` (Green)
- Warning: `#F59E0B` (Orange)
- Danger: `#EF4444` (Red)
- Info: `#06B6D4` (Cyan)

**Status Colors:**
- Formasi Kosong: Yellow
- Formasi Terisi: Green
- Formasi Penuh: Red
- Active: Green
- Inactive: Gray

### 5.5 Responsive Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

**Mobile Adaptations:**
- Stack cards vertically
- Hamburger menu
- Simplified tables (accordion style)
- Single column forms

---

## 6. Data Requirements

### 6.1 Data Volume Estimates

**Year 1:**
- OPD: ~30
- Jabatan: ~500
- ASN: ~3,000
- Admin: ~10
- Total Records: ~3,540

**Year 5 (Projected):**
- OPD: ~50
- Jabatan: ~1,000
- ASN: ~5,000
- Total Records: ~6,060

### 6.2 Data Retention Policy

| Data Type | Retention Period |
|-----------|------------------|
| Active ASN Data | Permanent |
| Retired ASN Data | 10 years |
| Audit Logs | 1 year |
| Backup Files | Daily (7d), Weekly (4w), Monthly (12m) |
| Session Data | 2 hours |

### 6.3 Data Migration

**Import dari sistem lama:**
- Format: Excel (.xlsx) atau CSV
- Template disediakan sistem
- Validasi data sebelum import
- Preview sebelum commit
- Rollback capability

**Export:**
- Format: PDF, Excel, CSV
- Batch export untuk data besar
- Scheduled export (optional)

---

## 7. Integration Requirements (Future)

### 7.1 External System Integration

**SIMPEG (Sistem Informasi Manajemen Pegawai):**
- Sync data ASN (nama, NIP)
- Bidirectional sync

**SIASN (Sistem Informasi ASN Nasional):**
- Submit data ke BKN
- Validasi NIP
- Pull master data

**E-Kinerja:**
- Sync jabatan untuk penilaian kinerja

### 7.2 API Requirements

- RESTful API
- JSON format
- Token-based authentication (Bearer Token)
- Rate limiting: 100 requests/minute
- API versioning (v1, v2, dst)
- API documentation (Swagger/OpenAPI)

---

## 8. Testing Requirements

### 8.1 Unit Testing

- Coverage: > 70%
- Test all model relationships
- Test business logic
- Test validation rules

### 8.2 Integration Testing

- Test API endpoints
- Test database queries
- Test external integrations

### 8.3 User Acceptance Testing (UAT)

- Test setiap use case
- Test dengan user nyata
- Feedback loop untuk improvement

### 8.4 Performance Testing

- Load testing: 100 concurrent users
- Stress testing: find breaking point
- Database query optimization

---

## 9. Deployment Requirements

### 9.1 Environment

- **Development**: Local developer machines
- **Staging**: Mirror of production
- **Production**: Live server

### 9.2 CI/CD Pipeline

```
[Git Push] → [Run Tests] → [Build Assets] → [Deploy to Staging] → [UAT] → [Deploy to Production]
```

### 9.3 Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Seeders run (if needed)
- [ ] Assets compiled and cached
- [ ] Cache cleared
- [ ] Permissions set correctly
- [ ] Backup created
- [ ] SSL certificate valid
- [ ] Monitoring enabled

---

## 10. Documentation Requirements

**Required Documentation:**
1. ✅ ERD (Database Schema)
2. ✅ System Flow Diagram
3. ✅ System Requirements (this document)
4. User Manual (for end users)
5. Admin Manual (for administrators)
6. API Documentation (if API enabled)
7. Installation Guide
8. Deployment Guide

---

*Dokumentasi dibuat: 20 November 2025*  
*Sistem: SIPETA (Sistem Informasi Peta Jabatan ASN)*  
*Versi: 1.0*
