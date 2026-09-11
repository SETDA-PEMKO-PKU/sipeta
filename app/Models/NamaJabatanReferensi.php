<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NamaJabatanReferensi extends Model
{
    use HasFactory;

    protected $table = 'nama_jabatan_referensis';

    protected $fillable = [
        'nama',
        'jenis_jabatan',
    ];

    /**
     * Scope by jenis jabatan
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_jabatan', $jenis);
    }

    /**
     * Daftar jenis jabatan yang valid
     */
    public static function jenisOptions(): array
    {
        return ['Kepala OPD', 'Kepala', 'Struktural', 'Fungsional', 'Pelaksana'];
    }
}
