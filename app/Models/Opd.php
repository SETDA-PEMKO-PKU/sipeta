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
     * Mendapatkan semua jabatan termasuk sub-jabatan (SUPER OPTIMIZED)
     * Menggunakan hanya 2 query untuk menghindari N+1 problem
     */
    public function getAllJabatans()
    {
        // Get all root jabatan IDs for this OPD
        $rootJabatanIds = Jabatan::where('opd_id', $this->id)
                                  ->whereNull('parent_id')
                                  ->pluck('id')
                                  ->toArray();
        
        if (empty($rootJabatanIds)) {
            return collect();
        }

        // Get ALL jabatans at once (no filtering by OPD - will collect all)
        // This is faster than querying per level
        $allJabatans = Jabatan::with(['asns'])
                              ->get()
                              ->keyBy('id');
        
        // Build list of IDs belonging to this OPD using in-memory traversal
        $opdJabatanIds = $rootJabatanIds;
        $queue = $rootJabatanIds;
        
        while (!empty($queue)) {
            $currentId = array_shift($queue);
            
            // Find children of current jabatan
            foreach ($allJabatans as $jabatan) {
                if ($jabatan->parent_id == $currentId && !in_array($jabatan->id, $opdJabatanIds)) {
                    $opdJabatanIds[] = $jabatan->id;
                    $queue[] = $jabatan->id;
                }
            }
        }

        // Return only jabatans belonging to this OPD
        return $allJabatans->whereIn('id', $opdJabatanIds)
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
