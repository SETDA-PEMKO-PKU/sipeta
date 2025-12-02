<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'opd_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Role constants
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN_ORGANISASI = 'admin_organisasi';
    const ROLE_ADMIN_BKPSDM = 'admin_bkpsdm';
    const ROLE_ADMIN_OPD = 'admin_opd';

    /**
     * Check if admin is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if admin is admin organisasi
     */
    public function isAdminOrganisasi(): bool
    {
        return $this->role === self::ROLE_ADMIN_ORGANISASI;
    }

    /**
     * Check if admin is admin BKPSDM
     */
    public function isAdminBkpsdm(): bool
    {
        return $this->role === self::ROLE_ADMIN_BKPSDM;
    }

    /**
     * Check if admin can manage ASN (tambah ASN)
     * Only super_admin and admin_bkpsdm can manage ASN
     */
    public function canManageAsn(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN_BKPSDM]);
    }

    /**
     * Check if admin can manage OPD and Jabatan
     * Only super_admin, admin_organisasi, and admin_opd can manage OPD and Jabatan
     */
    public function canManageOpdJabatan(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN_ORGANISASI, self::ROLE_ADMIN_OPD]);
    }

    /**
     * Check if admin is admin OPD
     */
    public function isAdminOpd(): bool
    {
        return $this->role === self::ROLE_ADMIN_OPD;
    }

    /**
     * Check if admin has access to specific OPD
     */
    public function hasOpdAccess($opdId): bool
    {
        // Super admin has access to all OPDs
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin OPD only has access to their assigned OPD
        if ($this->isAdminOpd()) {
            return $this->opd_id == $opdId;
        }

        // Admin organisasi and admin BKPSDM have access to all OPDs
        return true;
    }

    /**
     * Relationship to OPD
     */
    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Scope for filtering admins by OPD
     */
    public function scopeForOpd($query, $opdId)
    {
        return $query->where('opd_id', $opdId);
    }

    /**
     * Scope for active admins only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
