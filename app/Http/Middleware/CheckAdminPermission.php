<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Permission to check: 'manage_asn' or 'manage_opd_jabatan'
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized');
        }

        // Super admin has all permissions
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check specific permissions
        switch ($permission) {
            case 'manage_asn':
                if (!$admin->canManageAsn()) {
                    abort(403, 'Anda tidak memiliki akses untuk mengelola ASN');
                }
                break;

            case 'manage_opd_jabatan':
                if (!$admin->canManageOpdJabatan()) {
                    abort(403, 'Anda tidak memiliki akses untuk mengelola OPD dan Jabatan');
                }
                break;

            default:
                abort(403, 'Permission tidak valid');
        }

        return $next($request);
    }
}
