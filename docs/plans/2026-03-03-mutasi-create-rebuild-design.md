# Design: Rebuild mutasi/create (fix pilih atasan)

## Problem
- Dropdown "Pilih Atasan" kosong karena `getJabatanStruktural()` filter berdasarkan `jenis_jabatan IN ['Struktural', 'Kepala', '']` — nilai `'Kepala'` dan `''` bukan enum valid, dan jabatan `'Staf Ahli'` tidak masuk filter.
- Rekursi berhenti jika parent tidak cocok filter, sehingga seluruh subtree ikut hilang.
- `init()` di Alpine.js restore `atasan_id` dari `old()` sebelum data atasan selesai di-fetch (async race condition).

## Approach
Pendekatan B: perbaiki 2 API yang sudah ada, rebuild blade dengan JS yang benar.

## Backend Changes

### `MutasiController::getJabatanStruktural($opdId)`
- Load semua jabatan untuk OPD tersebut (jabatan kepala + semua descendants via eager load)
- Kumpulkan semua `parent_id` yang ada → itulah jabatan yang punya anak
- Return flat list hanya jabatan yang muncul sebagai parent, dengan level depth untuk prefix `—`
- Tidak ada filter `jenis_jabatan` sama sekali

## Frontend Changes

### `resources/views/admin/mutasi/create.blade.php`
- Rebuild Alpine.js `mutasiForm()` dengan:
  - `init()` async-aware: restore `atasan_id` dan `jabatan_tujuan_id` dari `old()` SETELAH data fetch selesai
  - `onOpdTujuanChange()` terima optional params untuk restore old values
  - Tidak ada perubahan struktur HTML/UI (cards, fields tetap sama)

## Files Changed
- `app/Http/Controllers/Admin/MutasiController.php` — method `getJabatanStruktural()` only
- `resources/views/admin/mutasi/create.blade.php` — full rebuild of Alpine.js block

## Files NOT Changed
- `getJabatanByAtasan()` — already correct
- `store()` — no validation changes
- Routes — no changes
