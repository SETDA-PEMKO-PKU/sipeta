<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Mendapatkan semua jabatan yang ada di OPD ini melalui bagian
     */
    public function jabatans()
    {
        return $this->hasManyThrough(Jabatan::class, Bagian::class, 'opd_id', 'bagian_id');
    }

    /**
     * Mendapatkan semua ASN yang ada di OPD ini
     */
    public function asns()
    {
        return $this->hasManyThrough(Asn::class, Jabatan::class, 'bagian_id', 'jabatan_id')
                    ->join('bagians', 'jabatans.bagian_id', '=', 'bagians.id')
                    ->where('bagians.opd_id', $this->id);
    }
}