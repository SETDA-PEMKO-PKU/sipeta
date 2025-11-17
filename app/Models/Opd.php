<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Opd extends Model
{
    use HasFactory;

    protected $table = 'opds';

    protected $fillable = [
        'nama'
    ];

    /**
     * Relasi ke bagian-bagian yang dimiliki OPD ini
     */
    public function bagians()
    {
        return $this->hasMany(Bagian::class, 'opd_id');
    }

    /**
     * Mendapatkan semua jabatan yang ada di OPD ini
     * Termasuk jabatan yang terkait dengan bagian dan jabatan pimpinan OPD (tanpa bagian)
     */
    public function jabatans()
    {
        // Menggunakan hasManyThrough dengan parent_id yang sekarang mereferensikan bagians
        return $this->hasManyThrough(Jabatan::class, Bagian::class, 'opd_id', 'parent_id');
    }

    /**
     * Mendapatkan jabatan kepala OPD (jabatan tanpa bagian)
     */
    public function jabatanKepala()
    {
        return $this->hasMany(Jabatan::class, 'opd_id')->whereNull('parent_id');
    }

    /**
     * Mendapatkan semua jabatan termasuk kepala OPD
     */
    public function getAllJabatans()
    {
        // Gabungkan jabatan kepala OPD dan jabatan dari bagian
        $jabatanKepala = $this->jabatanKepala()->get();
        $jabatanBagian = $this->jabatans()->get();

        return $jabatanKepala->merge($jabatanBagian);
    }

    /**
     * Mendapatkan semua ASN yang ada di OPD ini
     */
    public function asns()
    {
        return $this->hasMany(Asn::class, 'opd_id');
    }
}
