<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Opd;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Feature: opd-admin-management, Property 21: Permission check includes OPD validation
 * Validates: Requirements 6.1, 6.2
 * 
 * Property: For any permission check for an admin OPD, the system should verify both 
 * the admin's role and that the requested resource belongs to their assigned OPD
 */
class AdminOpdAccessPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Minimum number of iterations for property-based testing
     */
    private const MIN_ITERATIONS = 100;

    /**
     * Test that admin OPD access validation includes both role and OPD assignment checks
     * 
     * This property test verifies that:
     * 1. Admin OPD can only access their assigned OPD
     * 2. Super admin can access all OPDs
     * 3. Admin organisasi can access all OPDs
     * 4. Admin BKPSDM can access all OPDs
     * 5. Permission check considers both role and OPD assignment
     */
    public function test_permission_check_includes_opd_validation(): void
    {
        $iterations = self::MIN_ITERATIONS;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random test data
            $numOpds = rand(2, 5);
            $opds = $this->generateOpds($numOpds);
            
            // Test admin OPD access validation
            $this->assertAdminOpdAccessProperty($opds);
            
            // Test super admin unrestricted access
            $this->assertSuperAdminAccessProperty($opds);
            
            // Test admin organisasi unrestricted access
            $this->assertAdminOrganisasiAccessProperty($opds);
            
            // Test admin BKPSDM unrestricted access
            $this->assertAdminBkpsdmAccessProperty($opds);
        }
    }

    /**
     * Property: Admin OPD can only access their assigned OPD
     */
    private function assertAdminOpdAccessProperty(array $opds): void
    {
        // Pick a random OPD to assign to admin
        $assignedOpd = $opds[array_rand($opds)];
        
        // Create admin OPD with assigned OPD
        $adminOpd = Admin::create([
            'name' => 'Test Admin OPD ' . uniqid(),
            'email' => 'adminopd' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $assignedOpd->id,
        ]);

        // Admin OPD should have access to their assigned OPD
        $this->assertTrue(
            $adminOpd->hasOpdAccess($assignedOpd->id),
            "Admin OPD should have access to their assigned OPD (ID: {$assignedOpd->id})"
        );

        // Admin OPD should NOT have access to other OPDs
        foreach ($opds as $opd) {
            if ($opd->id !== $assignedOpd->id) {
                $this->assertFalse(
                    $adminOpd->hasOpdAccess($opd->id),
                    "Admin OPD should NOT have access to OPD ID: {$opd->id} (assigned to: {$assignedOpd->id})"
                );
            }
        }

        // Verify role check is working
        $this->assertTrue(
            $adminOpd->isAdminOpd(),
            "Admin should be identified as admin OPD"
        );
    }

    /**
     * Property: Super admin has unrestricted access to all OPDs
     */
    private function assertSuperAdminAccessProperty(array $opds): void
    {
        $superAdmin = Admin::create([
            'name' => 'Test Super Admin ' . uniqid(),
            'email' => 'superadmin' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'opd_id' => null,
        ]);

        // Super admin should have access to all OPDs
        foreach ($opds as $opd) {
            $this->assertTrue(
                $superAdmin->hasOpdAccess($opd->id),
                "Super admin should have access to all OPDs (testing OPD ID: {$opd->id})"
            );
        }

        // Verify role check
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isAdminOpd());
    }

    /**
     * Property: Admin organisasi has unrestricted access to all OPDs
     */
    private function assertAdminOrganisasiAccessProperty(array $opds): void
    {
        $adminOrg = Admin::create([
            'name' => 'Test Admin Organisasi ' . uniqid(),
            'email' => 'adminorg' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_ORGANISASI,
            'is_active' => true,
            'opd_id' => null,
        ]);

        // Admin organisasi should have access to all OPDs
        foreach ($opds as $opd) {
            $this->assertTrue(
                $adminOrg->hasOpdAccess($opd->id),
                "Admin organisasi should have access to all OPDs (testing OPD ID: {$opd->id})"
            );
        }

        // Verify role check
        $this->assertTrue($adminOrg->isAdminOrganisasi());
        $this->assertFalse($adminOrg->isAdminOpd());
    }

    /**
     * Property: Admin BKPSDM has unrestricted access to all OPDs
     */
    private function assertAdminBkpsdmAccessProperty(array $opds): void
    {
        $adminBkpsdm = Admin::create([
            'name' => 'Test Admin BKPSDM ' . uniqid(),
            'email' => 'adminbkpsdm' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_BKPSDM,
            'is_active' => true,
            'opd_id' => null,
        ]);

        // Admin BKPSDM should have access to all OPDs
        foreach ($opds as $opd) {
            $this->assertTrue(
                $adminBkpsdm->hasOpdAccess($opd->id),
                "Admin BKPSDM should have access to all OPDs (testing OPD ID: {$opd->id})"
            );
        }

        // Verify role check
        $this->assertTrue($adminBkpsdm->isAdminBkpsdm());
        $this->assertFalse($adminBkpsdm->isAdminOpd());
    }

    /**
     * Generate random OPDs for testing
     */
    private function generateOpds(int $count): array
    {
        $opds = [];
        for ($i = 0; $i < $count; $i++) {
            $opds[] = Opd::create([
                'nama' => 'OPD Test ' . uniqid(),
            ]);
        }
        return $opds;
    }
}
