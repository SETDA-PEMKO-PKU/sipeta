# 🚀 Cara Optimasi Database OPD

## ⚠️ LANGKAH AMAN SEBELUM MULAI

### 1. Backup Database Dulu!
```bash
# Masuk ke folder project
cd /Users/yolk/Dev/sipeta

# Backup database (ganti sesuai kredensial Anda)
php artisan db:backup
# ATAU manual:
# mysqldump -u root -p sipeta_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Cek Data Yang Ada
```bash
# Masuk ke MySQL
mysql -u root -p sipeta_db

# Atau via Laravel Tinker
php artisan tinker
>>> DB::table('opds')->count()
>>> DB::table('opds')->select('nama', DB::raw('COUNT(*) as total'))->groupBy('nama')->having('total', '>', 1)->get()
```

## 📋 Pilihan Cara Optimasi

### OPSI 1: Via SQL Manual (PALING AMAN)

1. **Buka file SQL yang sudah saya buat:**
   ```bash
   cat database/optimasi_opds.sql
   ```

2. **Jalankan per baris untuk lebih aman:**
   ```bash
   # Masuk MySQL
   mysql -u root -p sipeta_db
   
   # Copy-paste SQL satu per satu dari file optimasi_opds.sql
   # Mulai dari query CEK dulu
   ```

### OPSI 2: Via Laravel Migration (Lebih Terstruktur)

```bash
# Migration sudah dibuat di:
# database/migrations/2026_01_08_000001_add_indexes_to_opds_table.php

# Cek migration yang pending
php artisan migrate:status

# Jalankan migration
php artisan migrate

# Jika ada masalah, rollback:
php artisan migrate:rollback --step=1
```

### OPSI 3: Via Laravel Tinker (Paling Fleksibel)

```bash
php artisan tinker

# Cek index yang ada
>>> DB::select("SHOW INDEXES FROM opds");

# Tambah index
>>> DB::statement("ALTER TABLE opds ADD INDEX opds_nama_index (nama)");

# Verifikasi
>>> DB::select("SHOW INDEXES FROM opds");
```

## 🎯 Hasil Yang Diharapkan

### Query SEBELUM Optimasi:
```sql
SELECT * FROM opds WHERE nama LIKE '%dinas%';
-- Type: ALL (Full table scan)
-- Rows: 1000 (scan semua data)
-- Time: 0.5 - 2 detik
```

### Query SETELAH Optimasi:
```sql
SELECT * FROM opds WHERE nama LIKE '%dinas%';
-- Type: index (Using index)
-- Rows: 10-50 (hanya yang match)
-- Time: 0.01 - 0.1 detik
```

**Peningkatan: 10-20x lebih cepat!**

## ✅ Checklist Setelah Optimasi

- [ ] Backup database berhasil
- [ ] Index berhasil dibuat (cek dengan `SHOW INDEXES`)
- [ ] Test search di aplikasi - lebih cepat?
- [ ] Tidak ada error di log
- [ ] Data tetap utuh (hitung ulang jumlah OPD)

## 🔍 Cara Test Performance

```bash
# Via Tinker
php artisan tinker

# Test query tanpa index (jika belum optimasi)
>>> DB::enableQueryLog();
>>> App\Models\Opd::where('nama', 'like', '%dinas%')->get();
>>> DB::getQueryLog();

# Lihat execution time di array terakhir
```

## 🚨 Troubleshooting

### Jika Ada Duplikat Nama:
```sql
-- Lihat duplikat
SELECT nama, COUNT(*) FROM opds GROUP BY nama HAVING COUNT(*) > 1;

-- Jangan buat unique index, cukup index biasa
ALTER TABLE opds ADD INDEX opds_nama_index (nama);
```

### Jika Migration Error:
```bash
# Rollback migration terakhir
php artisan migrate:rollback --step=1

# Atau hapus manual
mysql -u root -p sipeta_db
ALTER TABLE opds DROP INDEX opds_nama_index;
```

### Jika Performa Masih Lambat:
1. Cek koneksi database (pastikan tidak remote)
2. Cek resource server (RAM, CPU)
3. Cek query N+1 problem di aplikasi
4. Consider caching

## 📊 Monitoring

```bash
# Cek ukuran table
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'sipeta_db'
AND table_name = 'opds';

# Cek index usage
SHOW INDEX FROM opds;
```

## ❓ FAQ

**Q: Apakah data saya akan hilang?**  
A: Tidak. Index hanya menambahkan struktur data tambahan, data asli tetap utuh.

**Q: Berapa lama prosesnya?**  
A: Untuk <10,000 rows: beberapa detik. Untuk >100,000 rows: bisa 1-5 menit.

**Q: Apakah perlu downtime?**  
A: Untuk database kecil tidak perlu. Untuk production besar, sebaiknya saat traffic rendah.

**Q: Bisakah saya rollback?**  
A: Ya, tinggal hapus index dengan DROP INDEX.

---

**Rekomendasi:** Mulai dengan OPSI 1 (SQL manual) agar lebih aman dan bisa kontrol per step.
