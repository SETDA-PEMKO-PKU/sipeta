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
     * Mendapatkan semua jabatan yang ada di OPD ini (direct relationship)
     */
    public function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'opd_id');
    }

    /**
     * Mendapatkan jabatan kepala OPD (jabatan root level)
     */
    public function jabatanKepala()
    {
        return $this->hasMany(Jabatan::class, 'opd_id')->whereNull('parent_id');
    }

    /**
     * Mendapatkan semua jabatan termasuk sub-jabatan
     */
    public function getAllJabatans()
    {
        $allJabatans = collect();

        // Dapatkan jabatan kepala (root)
        $jabatanKepala = $this->jabatanKepala()->get();

        foreach ($jabatanKepala as $kepala) {
            $allJabatans->push($kepala);
            // Dapatkan semua descendants
            $allJabatans = $allJabatans->merge($kepala->getAllDescendants());
        }

        return $allJabatans;
    }

    /**
     * Mendapatkan hierarki jabatan dalam bentuk tree
     */
    public function getJabatanTree()
    {
        return $this->jabatanKepala()->with('children.children.children')->get();
    }

    /**
     * Mendapatkan semua ASN yang ada di OPD ini
     */
    public function asns()
    {
        return $this->hasMany(Asn::class, 'opd_id');
    }

    /**
     * Hitung total jabatan di OPD ini
     */
    public function getTotalJabatanAttribute()
    {
        return $this->getAllJabatans()->count();
    }

    /**
     * Hitung total ASN di OPD ini
     */
    public function getTotalAsnAttribute()
    {
        return $this->asns()->count();
    }
}
