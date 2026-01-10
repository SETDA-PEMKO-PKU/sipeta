<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'old_data',
        'new_data',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    /**
     * Action constants
     */
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';

    /**
     * Module constants
     */
    const MODULE_AUTH = 'auth';
    const MODULE_OPD = 'opd';
    const MODULE_JABATAN = 'jabatan';
    const MODULE_PEGAWAI = 'pegawai';
    const MODULE_ASN = 'asn';
    const MODULE_ADMIN = 'admin';
    const MODULE_ANALYTICS = 'analytics';

    /**
     * Get the admin that owns the activity log.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the related model for this log entry.
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->morphTo('model', 'model_type', 'model_id');
        }
        return null;
    }

    /**
     * Log an activity
     */
    public static function log(
        string $action,
        ?string $module = null,
        ?string $description = null,
        $model = null,
        ?array $oldData = null,
        ?array $newData = null
    ): ?self {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return null;
        }

        return self::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_data' => $oldData,
            'new_data' => $newData,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
        ]);
    }

    /**
     * Log login activity
     */
    public static function logLogin(Admin $admin): self
    {
        return self::create([
            'admin_id' => $admin->id,
            'action' => self::ACTION_LOGIN,
            'module' => self::MODULE_AUTH,
            'description' => "Admin {$admin->name} berhasil login",
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log logout activity
     */
    public static function logLogout(Admin $admin): self
    {
        return self::create([
            'admin_id' => $admin->id,
            'action' => self::ACTION_LOGOUT,
            'module' => self::MODULE_AUTH,
            'description' => "Admin {$admin->name} logout",
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get action label in Indonesian
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_CREATE => 'Tambah Data',
            self::ACTION_UPDATE => 'Edit Data',
            self::ACTION_DELETE => 'Hapus Data',
            self::ACTION_VIEW => 'Lihat Data',
            self::ACTION_EXPORT => 'Export Data',
            self::ACTION_IMPORT => 'Import Data',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get module label in Indonesian
     */
    public function getModuleLabelAttribute(): string
    {
        return match($this->module) {
            self::MODULE_AUTH => 'Autentikasi',
            self::MODULE_OPD => 'OPD',
            self::MODULE_JABATAN => 'Jabatan',
            self::MODULE_PEGAWAI => 'Pegawai',
            self::MODULE_ASN => 'ASN',
            self::MODULE_ADMIN => 'Admin',
            self::MODULE_ANALYTICS => 'Analytics',
            default => ucfirst($this->module ?? '-'),
        };
    }

    /**
     * Get action badge color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            self::ACTION_LOGIN => 'success',
            self::ACTION_LOGOUT => 'warning',
            self::ACTION_CREATE => 'primary',
            self::ACTION_UPDATE => 'info',
            self::ACTION_DELETE => 'error',
            self::ACTION_VIEW => 'gray',
            self::ACTION_EXPORT => 'secondary',
            self::ACTION_IMPORT => 'secondary',
            default => 'gray',
        };
    }

    /**
     * Scope for filtering by admin
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope for filtering by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
