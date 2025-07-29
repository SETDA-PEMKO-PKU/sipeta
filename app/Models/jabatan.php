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
        'kelas',
        'kebutuhan',
        'bezetting',
        'bagian_id',
        'parent_id'
    ];

    protected $casts = [
        'kebutuhan' => 'integer',
        'bezetting' => 'integer'
    ];

    /**
     * Relasi ke bagian yang memiliki jabatan ini
     */
    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id');
    }

    /**
     * Relasi ke jabatan parent
     */
    public function parent()
    {
        return $this->belongsTo(Jabatan::class, 'parent_id');
    }

    /**
     * Relasi ke jabatan-jabatan child
     */
    public function children()
    {
        return $this->hasMany(Jabatan::class, 'parent_id');
    }

    /**
     * Relasi ke ASN yang memegang jabatan ini
     */
    public function asns()
    {
        return $this->hasMany(Asn::class, 'jabatan_id');
    }

    /**
     * Mendapatkan OPD melalui bagian
     */
    public function opd()
    {
        return $this->hasOneThrough(Opd::class, Bagian::class, 'id', 'id', 'bagian_id', 'opd_id');
    }

    /**
     * Mendapatkan semua descendants (anak cucu) dari jabatan ini
     */
    public function descendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
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
     * Menghitung persentase bezetting
     */
    public function getBezettingPercentageAttribute()
    {
        if ($this->kebutuhan == 0) {
            return 0;
        }
        
        return round(($this->bezetting / $this->kebutuhan) * 100, 2);
    }
}