<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    use HasFactory;

    protected $table = 'bagians';

    protected $fillable = [
        'nama',
        'opd_id',
        'parent_id'
    ];

    /**
     * Relasi ke OPD yang memiliki bagian ini
     */
    public function opd()
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    /**
     * Relasi ke bagian parent
     */
    public function parent()
    {
        return $this->belongsTo(Bagian::class, 'parent_id');
    }

    /**
     * Relasi ke bagian-bagian child
     */
    public function children()
    {
        return $this->hasMany(Bagian::class, 'parent_id');
    }

    /**
     * Relasi ke jabatan-jabatan dalam bagian ini
     */
    public function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'parent_id');
    }

    /**
     * Relasi ke ASN dalam bagian ini
     */
    public function asns()
    {
        return $this->hasMany(Asn::class, 'bagian_id');
    }

    /**
     * Mendapatkan semua descendants (anak cucu) dari bagian ini
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
     * Mendapatkan path hierarki bagian
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
}
