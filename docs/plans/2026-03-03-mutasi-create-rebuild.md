# Mutasi Create Rebuild Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Fix halaman `/admin/mutasi/create` agar dropdown "Pilih Atasan" bisa muncul dan dipilih dengan benar.

**Architecture:** Perbaiki dua titik: (1) backend `getJabatanStruktural()` filter diganti dari `jenis_jabatan` menjadi "jabatan yang punya anak", (2) Alpine.js di blade di-rebuild agar restore `old()` values async-aware.

**Tech Stack:** Laravel 11, Blade, Alpine.js, Tailwind CSS

---

### Task 1: Fix `getJabatanStruktural()` di MutasiController

**Files:**
- Modify: `app/Http/Controllers/Admin/MutasiController.php` (method `getJabatanStruktural`, sekitar baris 280–312)

**Step 1: Ganti isi method `getJabatanStruktural()`**

Hapus seluruh body method yang ada dan ganti dengan:

```php
public function getJabatanStruktural($opdId)
{
    $opd = Opd::with(['jabatanKepala.children.children.children.children'])->findOrFail($opdId);

    $jabatans = [];

    $traverse = function ($jabatan, $level = 0) use (&$jabatans, &$traverse) {
        // Hanya masukkan jabatan yang punya anak (bisa jadi atasan)
        if ($jabatan->children->count() > 0) {
            $jabatans[] = [
                'id'        => $jabatan->id,
                'nama'      => str_repeat('— ', $level) . $jabatan->nama,
                'parent_id' => $jabatan->parent_id,
                'level'     => $level,
            ];
            foreach ($jabatan->children as $child) {
                $traverse($child, $level + 1);
            }
        }
    };

    foreach ($opd->jabatanKepala as $jabatan) {
        $traverse($jabatan, 0);
    }

    return response()->json($jabatans);
}
```

**Step 2: Verifikasi manual di browser**

Buka URL: `/admin/mutasi/jabatan-struktural/{id_opd_yang_ada}`
Expected: JSON array jabatan yang punya children, dengan prefix `—` sesuai level.
Kalau OPD tidak punya jabatan → array kosong `[]`, itu normal.

**Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/MutasiController.php
git commit -m "fix: getJabatanStruktural return jabatan berdasarkan children, bukan jenis_jabatan"
```

---

### Task 2: Rebuild Alpine.js di `create.blade.php`

**Files:**
- Modify: `resources/views/admin/mutasi/create.blade.php` — seluruh blok `@push('scripts')`

**Step 1: Ganti blok `@push('scripts')` dengan implementasi berikut**

Hapus dari baris `@push('scripts')` sampai `@endpush`, ganti dengan:

```blade
@push('scripts')
<script>
function mutasiForm() {
    return {
        searchQuery: '',
        searchResults: [],
        showResults: false,
        selectedAsn: null,
        jenisMutasi: '',
        opdTujuanId: '',
        atasanId: '',
        jabatanTujuanId: '',
        atasanList: [],
        jabatanTujuanList: [],
        loadingAtasan: false,
        loadingJabatan: false,

        async init() {
            @if(isset($selectedAsn) && $selectedAsn)
            this.selectedAsn = {{ json_encode($selectedAsn) }};
            this.searchQuery = this.selectedAsn.nama;
            @endif

            @if(old('jenis_mutasi'))
            this.jenisMutasi = '{{ old('jenis_mutasi') }}';
            @endif

            @if(old('opd_tujuan_id'))
            this.opdTujuanId = '{{ old('opd_tujuan_id') }}';
            await this.fetchAtasanList(
                '{{ old('atasan_id') }}',
                '{{ old('jabatan_tujuan_id') }}'
            );
            @endif
        },

        async searchAsn() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }
            try {
                const res = await fetch(`{{ route('admin.mutasi.search-asn') }}?q=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = await res.json();
                this.showResults = true;
            } catch (e) {
                console.error('searchAsn error:', e);
            }
        },

        selectAsn(asn) {
            this.selectedAsn = asn;
            this.searchQuery = asn.nama;
            this.showResults = false;
            this.searchResults = [];
            if (this.jenisMutasi === 'internal_opd') {
                this.opdTujuanId = String(asn.opd_id);
                this.fetchAtasanList();
            }
        },

        clearAsn() {
            this.selectedAsn = null;
            this.searchQuery = '';
            this.searchResults = [];
        },

        onJenisMutasiChange() {
            this.atasanId = '';
            this.jabatanTujuanId = '';
            this.atasanList = [];
            this.jabatanTujuanList = [];
            if (this.jenisMutasi === 'internal_opd' && this.selectedAsn) {
                this.opdTujuanId = String(this.selectedAsn.opd_id);
                this.fetchAtasanList();
            }
        },

        async onOpdTujuanChange() {
            this.atasanId = '';
            this.jabatanTujuanId = '';
            this.atasanList = [];
            this.jabatanTujuanList = [];
            await this.fetchAtasanList();
        },

        async fetchAtasanList(restoreAtasanId = null, restoreJabatanId = null) {
            if (!this.opdTujuanId) return;

            this.loadingAtasan = true;
            try {
                const res = await fetch(`/admin/mutasi/jabatan-struktural/${this.opdTujuanId}`);
                this.atasanList = await res.json();

                if (restoreAtasanId) {
                    this.atasanId = restoreAtasanId;
                    await this.fetchJabatanByAtasan(restoreJabatanId);
                }
            } catch (e) {
                console.error('fetchAtasanList error:', e);
            } finally {
                this.loadingAtasan = false;
            }
        },

        async onAtasanChange() {
            this.jabatanTujuanId = '';
            this.jabatanTujuanList = [];
            await this.fetchJabatanByAtasan();
        },

        async fetchJabatanByAtasan(restoreJabatanId = null) {
            if (!this.opdTujuanId || !this.atasanId) return;

            this.loadingJabatan = true;
            try {
                const res = await fetch(`/admin/mutasi/jabatan-by-atasan/${this.opdTujuanId}/${this.atasanId}`);
                this.jabatanTujuanList = await res.json();

                if (restoreJabatanId) {
                    this.jabatanTujuanId = restoreJabatanId;
                }
            } catch (e) {
                console.error('fetchJabatanByAtasan error:', e);
            } finally {
                this.loadingJabatan = false;
            }
        },
    };
}
</script>
@endpush
```

**Step 2: Update event handler di elemen HTML**

Di dalam form, pastikan event handler di select atasan menggunakan `onAtasanChange()` (bukan `loadJabatanTujuanByAtasan()`):

Cari baris:
```html
<select name="atasan_id" x-model="atasanId" @change="loadJabatanTujuanByAtasan()" ...>
```

Ganti dengan:
```html
<select name="atasan_id" x-model="atasanId" @change="onAtasanChange()" ...>
```

**Step 3: Verifikasi manual di browser**

1. Buka `/admin/mutasi/create`
2. Cari pegawai → pilih salah satu
3. Pilih jenis mutasi "Antar OPD"
4. Pilih OPD Tujuan → dropdown Atasan harus muncul berisi jabatan
5. Pilih Atasan → dropdown Jabatan Tujuan harus muncul berisi jabatan di bawah atasan
6. Submit form dengan data valid → harus sukses redirect ke index

**Step 4: Test error case (old values)**

1. Submit form tanpa isi field wajib (biarkan pegawai kosong) → form harus kembali dengan error
2. Pastikan field yang sudah diisi (jenis mutasi, OPD tujuan, atasan, jabatan) ter-restore dengan benar

**Step 5: Commit**

```bash
git add resources/views/admin/mutasi/create.blade.php
git commit -m "fix: rebuild Alpine.js mutasi create — async init, proper old() restore, fix atasan handler"
```

---

## Summary of Changes

| File | Type | Perubahan |
|------|------|-----------|
| `app/Http/Controllers/Admin/MutasiController.php` | Modify | `getJabatanStruktural()`: filter by children count, bukan jenis_jabatan |
| `resources/views/admin/mutasi/create.blade.php` | Modify | Rebuild `@push('scripts')` + fix `@change` handler di select atasan |
