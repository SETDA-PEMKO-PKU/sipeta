<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asn extends Model
{
    use HasFactory;

    protected $table = 'asns';

    protected $fillable = [
        'nama',
        'nip',
        'jabatan_id'
    ];

    /**
     * Relasi ke jabatan yang dipegang ASN ini
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Mendapatkan bagian melalui jabatan
     */
    public function bagian()
    {
        return $this->hasOneThrough(Bagian::class, Jabatan::class, 'id', 'id', 'jabatan_id', 'bagian_id');
    }

    /**
     * Mendapatkan OPD melalui jabatan dan bagian
     */
    public function opd()
    {
        return $this->jabatan->bagian->opd ?? null;
    }

    /**
     * Scope untuk mencari ASN berdasarkan NIP
     */
    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    /**
     * Scope untuk mencari ASN berdasarkan nama
     */
    public function scopeByNama($query, $nama)
    {
        return $query->where('nama', 'like', '%' . $nama . '%');
    }
}