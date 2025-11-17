<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatans';

    protected $fillable = [
        'nama',
        'jenis_jabatan',
        'kelas',
        'kebutuhan',
        'parent_id',
        'opd_id'
    ];

    protected $casts = [
        'kelas' => 'integer',
        'kebutuhan' => 'integer'
    ];

    /**
     * Relasi ke bagian yang memiliki jabatan ini (melalui parent_id)
     */
    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'parent_id');
    }

    /**
     * Relasi ke bagian parent
     */
    public function parentBagian()
    {
        return $this->belongsTo(Bagian::class, 'parent_id');
    }

    /**
     * Mendapatkan jabatan-jabatan dalam bagian yang sama
     */
    public function siblings()
    {
        return $this->hasMany(Jabatan::class, 'parent_id', 'parent_id')
                    ->where('id', '!=', $this->id);
    }

    /**
     * Relasi ke ASN yang memegang jabatan ini
     */
    public function asns()
    {
        return $this->hasMany(Asn::class, 'jabatan_id');
    }

    /**
     * Mendapatkan OPD langsung atau melalui bagian
     */
    public function opd()
    {
        // Jika jabatan memiliki opd_id langsung (jabatan kepala OPD)
        if ($this->opd_id) {
            return $this->belongsTo(Opd::class, 'opd_id');
        }
        // Jika jabatan terkait dengan bagian
        return $this->hasOneThrough(Opd::class, Bagian::class, 'id', 'id', 'parent_id', 'opd_id');
    }
    
    /**
     * Relationship langsung ke OPD untuk jabatan kepala
     */
    public function opdLangsung()
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    /**
     * Mendapatkan semua jabatan dalam bagian yang sama dan sub-bagian
     */
    public function getRelatedJabatans()
    {
        if (!$this->parentBagian) {
            return collect();
        }
        
        $relatedJabatans = collect();
        
        // Jabatan dalam bagian yang sama
        $relatedJabatans = $relatedJabatans->merge(
            Jabatan::where('parent_id', $this->parent_id)
                   ->where('id', '!=', $this->id)
                   ->get()
        );
        
        // Jabatan dalam sub-bagian
        foreach ($this->parentBagian->children as $subBagian) {
            $relatedJabatans = $relatedJabatans->merge($subBagian->jabatans);
        }
        
        return $relatedJabatans;
    }

    /**
     * Mendapatkan path hierarki jabatan melalui bagian
     */
    public function getPath()
    {
        $path = collect([$this]);
        $parentBagian = $this->parentBagian;
        
        while ($parentBagian) {
            $path->prepend($parentBagian);
            $parentBagian = $parentBagian->parent;
        }
        
        return $path;
    }


}