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
     * Only super_admin and admin_organisasi can manage OPD and Jabatan
     */
    public function canManageOpdJabatan(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN_ORGANISASI]);
    }

    /**
     * Scope for active admins only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
