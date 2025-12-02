<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckOpdAccess
{
    /**
     * Handle an incoming request.
     *
     * Validates that admin OPD only accesses their assigned OPD's resources.
     * Super admin and other admin roles bypass OPD checks.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();
        
        // Super admin and other roles bypass OPD check
        if (!$admin || !$admin->isAdminOpd()) {
            return $next($request);
        }
        
        // Extract OPD ID from request (route parameter, query, or form data)
        $requestedOpdId = $this->extractOpdId($request);
        
        // If OPD ID present in request, validate access
        if ($requestedOpdId && !$admin->hasOpdAccess($requestedOpdId)) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized OPD access attempt', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'admin_opd_id' => $admin->opd_id,
                'requested_opd_id' => $requestedOpdId,
                'resource_type' => $this->getResourceType($request),
                'resource_id' => $this->getResourceId($request),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            
            abort(403, 'Anda tidak memiliki akses ke OPD ini');
        }
        
        return $next($request);
    }
    
    /**
     * Extract OPD ID from request
     * Checks route parameters, query string, and form data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int|null
     */
    protected function extractOpdId(Request $request): ?int
    {
        // Check route parameters first (most common)
        // Handles routes like /admin/opds/{id} or /admin/opds/{opd}/jabatan
        if ($request->route('id')) {
            return (int) $request->route('id');
        }
        
        if ($request->route('opd')) {
            // Could be an OPD model instance or ID
            $opd = $request->route('opd');
            return is_object($opd) ? $opd->id : (int) $opd;
        }
        
        // Check query string (for filters like ?opd_id=1)
        if ($request->query('opd_id')) {
            return (int) $request->query('opd_id');
        }
        
        // Check form data (for create/update operations)
        if ($request->input('opd_id')) {
            return (int) $request->input('opd_id');
        }
        
        return null;
    }
    
    /**
     * Get resource type from request for logging
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getResourceType(Request $request): string
    {
        $path = $request->path();
        
        if (str_contains($path, '/pegawai')) {
            return 'ASN';
        }
        
        if (str_contains($path, '/jabatan')) {
            return 'Jabatan';
        }
        
        if (str_contains($path, '/analytics')) {
            return 'Analytics';
        }
        
        if (str_contains($path, '/opds')) {
            return 'OPD';
        }
        
        return 'Unknown';
    }
    
    /**
     * Get resource ID from request for logging
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getResourceId(Request $request): ?string
    {
        // Try to get specific resource IDs from route
        if ($request->route('jabatan')) {
            $jabatan = $request->route('jabatan');
            return 'jabatan_' . (is_object($jabatan) ? $jabatan->id : $jabatan);
        }
        
        if ($request->route('asn')) {
            $asn = $request->route('asn');
            return 'asn_' . (is_object($asn) ? $asn->id : $asn);
        }
        
        if ($request->route('id')) {
            return 'id_' . $request->route('id');
        }
        
        return null;
    }
}
