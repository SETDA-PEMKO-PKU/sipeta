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
     * Relasi ke parent jabatan (self-referencing)
     */
    public function parent()
    {
        return $this->belongsTo(Jabatan::class, 'parent_id');
    }

    /**
     * Relasi ke child jabatan (sub-jabatan)
     */
    public function children()
    {
        return $this->hasMany(Jabatan::class, 'parent_id');
    }

    /**
     * Mendapatkan jabatan-jabatan dalam level yang sama (siblings)
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
     * Mendapatkan OPD langsung atau melalui parent jabatan
     */
    public function opd()
    {
        // Jika jabatan memiliki opd_id langsung (jabatan kepala OPD)
        if ($this->opd_id) {
            return $this->belongsTo(Opd::class, 'opd_id');
        }

        // Jika tidak, cari OPD melalui parent jabatan
        $parent = $this->parent;
        while ($parent) {
            if ($parent->opd_id) {
                return $parent->belongsTo(Opd::class, 'opd_id');
            }
            $parent = $parent->parent;
        }

        return null;
    }

    /**
     * Relationship langsung ke OPD untuk jabatan kepala
     */
    public function opdLangsung()
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    /**
     * Mendapatkan OPD ID dari jabatan ini atau parent-nya
     */
    public function getOpdId()
    {
        if ($this->opd_id) {
            return $this->opd_id;
        }

        $parent = $this->parent;
        while ($parent) {
            if ($parent->opd_id) {
                return $parent->opd_id;
            }
            $parent = $parent->parent;
        }

        return null;
    }

    /**
     * Mendapatkan semua descendants (child, grandchild, etc.)
     */
    public function getAllDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Mendapatkan path hierarki jabatan
     */
    public function getPath()
    {
        $path = collect([$this]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent);
            $parent = $parent->parent;
        }

        return $path;
    }

    /**
     * Check apakah jabatan ini adalah root (kepala OPD)
     */
    public function isRoot()
    {
        return $this->opd_id !== null && $this->parent_id === null;
    }

    /**
     * Check apakah jabatan ini adalah leaf (tidak punya children)
     */
    public function isLeaf()
    {
        return $this->children()->count() === 0;
    }
}
