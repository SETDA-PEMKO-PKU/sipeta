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
        'jabatan_id',
        'opd_id'
    ];

    /**
     * Relasi ke jabatan yang dipegang ASN ini
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Relasi langsung ke OPD
     */
    public function opd()
    {
        return $this->belongsTo(Opd::class, 'opd_id');
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
