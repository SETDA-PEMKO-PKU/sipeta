-- =====================================================
-- OPTIMASI DATABASE UNTUK TABLE OPDS
-- =====================================================
-- Tanggal: 2026-01-08
-- Tujuan: Mempercepat pencarian dan query pada table opds
--
-- PENTING: BACKUP DATABASE TERLEBIH DAHULU!
-- Jalankan: mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
-- =====================================================

-- =====================================================
-- 1. CEK DATA YANG ADA (UNTUK VERIFIKASI)
-- =====================================================
-- Lihat jumlah data OPD
SELECT COUNT(*) as total_opd FROM opds;

-- Lihat contoh data
SELECT id, nama, created_at FROM opds LIMIT 10;

-- Cek apakah ada duplikat nama (harus 0 jika akan buat unique index)
SELECT nama, COUNT(*) as jumlah 
FROM opds 
GROUP BY nama 
HAVING COUNT(*) > 1;

-- =====================================================
-- 2. CEK INDEX YANG SUDAH ADA
-- =====================================================
SHOW INDEXES FROM opds;

-- =====================================================
-- 3. TAMBAH INDEX UNTUK PERFORMA PENCARIAN
-- =====================================================
-- Index untuk kolom 'nama' (mempercepat LIKE search)
-- PERHATIAN: Jika ada duplikat nama, hapus atau perbaiki dulu!
ALTER TABLE opds ADD INDEX opds_nama_index (nama);

-- Optional: Jika ingin nama OPD unique (tidak boleh duplikat)
-- Jalankan HANYA jika sudah yakin tidak ada duplikat:
-- ALTER TABLE opds ADD UNIQUE opds_nama_unique (nama);

-- =====================================================
-- 4. VERIFIKASI INDEX BERHASIL DIBUAT
-- =====================================================
SHOW INDEXES FROM opds;

-- Cek query performance dengan EXPLAIN
EXPLAIN SELECT * FROM opds WHERE nama LIKE '%dinas%';

-- =====================================================
-- 5. ROLLBACK (Jika Ingin Hapus Index)
-- =====================================================
-- Jika ada masalah, jalankan ini untuk menghapus index:
-- ALTER TABLE opds DROP INDEX opds_nama_index;
-- ALTER TABLE opds DROP INDEX opds_nama_unique; -- Jika unique sudah dibuat

-- =====================================================
-- 6. OPTIMASI TAMBAHAN (OPSIONAL)
-- =====================================================
-- Optimize table setelah menambah index
OPTIMIZE TABLE opds;

-- Analyze table untuk update statistics
ANALYZE TABLE opds;

-- =====================================================
-- CATATAN PENTING:
-- =====================================================
-- 1. Index akan membuat SELECT lebih cepat
-- 2. Index akan membuat INSERT/UPDATE sedikit lebih lambat (tapi tidak signifikan)
-- 3. Index akan menambah ukuran database sedikit
-- 4. Untuk table kecil (<1000 rows), efek tidak terlalu terasa
-- 5. Untuk table besar (>10000 rows), efek sangat signifikan
-- 
-- REKOMENDASI:
-- - Jalankan query CEK terlebih dahulu
-- - Backup database sebelum menjalankan ALTER TABLE
-- - Test di development environment dulu
-- - Jalankan saat traffic rendah jika production
-- =====================================================
