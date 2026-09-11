<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiPegawai extends Model
{
    use HasFactory;

    protected $table = 'mutasi_pegawai';

    protected $fillable = [
        'asn_id',
        'opd_asal_id',
        'opd_tujuan_id',
        'jabatan_asal_id',
        'jabatan_tujuan_id',
        'tanggal_mutasi',
        'nomor_sk',
        'tanggal_sk',
        'keterangan',
        'jenis_mutasi',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
        'tanggal_sk' => 'date',
    ];

    /**
     * Jenis Mutasi constants
     */
    const JENIS_ANTAR_OPD = 'antar_opd';
    const JENIS_INTERNAL_OPD = 'internal_opd';

    /**
     * Relasi ke ASN yang dimutasi
     */
    public function asn()
    {
        return $this->belongsTo(Asn::class, 'asn_id');
    }

    /**
     * Relasi ke OPD asal
     */
    public function opdAsal()
    {
        return $this->belongsTo(Opd::class, 'opd_asal_id');
    }

    /**
     * Relasi ke OPD tujuan
     */
    public function opdTujuan()
    {
        return $this->belongsTo(Opd::class, 'opd_tujuan_id');
    }

    /**
     * Relasi ke Jabatan asal
     */
    public function jabatanAsal()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_asal_id');
    }

    /**
     * Relasi ke Jabatan tujuan
     */
    public function jabatanTujuan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_tujuan_id');
    }

    /**
     * Relasi ke Admin yang membuat mutasi
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get jenis mutasi label
     */
    public function getJenisMutasiLabelAttribute(): string
    {
        return match($this->jenis_mutasi) {
            self::JENIS_ANTAR_OPD => 'Mutasi Antar OPD',
            self::JENIS_INTERNAL_OPD => 'Mutasi Internal OPD',
            default => ucfirst(str_replace('_', ' ', $this->jenis_mutasi)),
        };
    }

    /**
     * Scope untuk filter berdasarkan jenis mutasi
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_mutasi', $jenis);
    }

    /**
     * Scope untuk filter berdasarkan OPD asal
     */
    public function scopeByOpdAsal($query, $opdId)
    {
        return $query->where('opd_asal_id', $opdId);
    }

    /**
     * Scope untuk filter berdasarkan OPD tujuan
     */
    public function scopeByOpdTujuan($query, $opdId)
    {
        return $query->where('opd_tujuan_id', $opdId);
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_mutasi', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan ASN
     */
    public function scopeByAsn($query, $asnId)
    {
        return $query->where('asn_id', $asnId);
    }
}
