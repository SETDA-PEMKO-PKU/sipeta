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
     * Mendapatkan semua jabatan termasuk sub-jabatan untuk OPD ini
     */
    public function getAllJabatans()
    {
        // Step 1: Get root jabatan IDs for this OPD
        $rootJabatanIds = Jabatan::where('opd_id', $this->id)
                                  ->whereNull('parent_id')
                                  ->pluck('id')
                                  ->toArray();

        if (empty($rootJabatanIds)) {
            return collect();
        }

        // Step 2: Traverse tree level by level to collect all descendant IDs
        $allIds = $rootJabatanIds;
        $currentIds = $rootJabatanIds;
        $maxDepth = 15;
        $depth = 0;

        while (!empty($currentIds) && $depth < $maxDepth) {
            $childIds = Jabatan::whereIn('parent_id', $currentIds)->pluck('id')->toArray();

            if (empty($childIds)) {
                break;
            }

            $allIds = array_merge($allIds, $childIds);
            $currentIds = $childIds;
            $depth++;
        }

        // Step 3: Load only jabatans that belong to this OPD (not all OPDs)
        return Jabatan::with(['asns'])
                      ->whereIn('id', $allIds)
                      ->get()
                      ->sortBy('nama')
                      ->values();
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
     * Mendapatkan semua admin OPD yang ditugaskan ke OPD ini
     */
    public function adminOpds()
    {
        return $this->hasMany(Admin::class)->where('role', Admin::ROLE_ADMIN_OPD);
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
