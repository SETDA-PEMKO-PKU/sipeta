<?php

namespace App\Http\Traits;

use App\Models\Opd;
use Illuminate\Database\Eloquent\Builder;

trait HasOpdScope
{
    /**
     * Apply OPD scope to a query based on the authenticated admin's role and OPD assignment.
     * 
     * For admin OPD: filters query to only include records from their assigned OPD
     * For other admin roles: returns query unchanged (access to all OPDs)
     *
     * @param Builder $query The Eloquent query builder instance
     * @return Builder The query builder with OPD scope applied if necessary
     */
    protected function applyOpdScope($query)
    {
        $admin = auth('admin')->user();

        // If admin is admin OPD, scope to their assigned OPD only
        if ($admin && $admin->isAdminOpd()) {
            return $query->where('opd_id', $admin->opd_id);
        }

        // For super admin, admin organisasi, and admin BKPSDM, return unscoped query
        return $query;
    }

    /**
     * Get list of OPD IDs that the authenticated admin has access to.
     * 
     * For admin OPD: returns array with only their assigned OPD ID
     * For other admin roles: returns array of all OPD IDs
     *
     * @return array Array of accessible OPD IDs
     */
    protected function getAccessibleOpdIds()
    {
        $admin = auth('admin')->user();

        // If admin is admin OPD, return only their assigned OPD
        if ($admin && $admin->isAdminOpd()) {
            return [$admin->opd_id];
        }

        // For super admin, admin organisasi, and admin BKPSDM, return all OPD IDs
        return Opd::pluck('id')->toArray();
    }

    /**
     * Validate that the authenticated admin has access to a specific OPD.
     * 
     * Throws a 403 Forbidden exception if access is denied.
     *
     * @param int $opdId The OPD ID to validate access for
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validateOpdAccess($opdId)
    {
        $admin = auth('admin')->user();

        // Use the hasOpdAccess method from Admin model
        if ($admin && !$admin->hasOpdAccess($opdId)) {
            abort(403, 'Anda tidak memiliki akses ke OPD ini');
        }
    }
}
