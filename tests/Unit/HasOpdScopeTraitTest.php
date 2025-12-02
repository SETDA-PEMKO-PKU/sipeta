<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Opd;
use App\Models\Jabatan;
use App\Http\Traits\HasOpdScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

/**
 * Test the HasOpdScope trait functionality
 */
class HasOpdScopeTraitTest extends TestCase
{
    use RefreshDatabase, HasOpdScope;

    /**
     * Test applyOpdScope method for admin OPD
     */
    public function test_apply_opd_scope_filters_for_admin_opd(): void
    {
        // Create OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);

        // Create jabatan for each OPD
        $jabatan1 = Jabatan::create([
            'nama' => 'Jabatan 1',
            'opd_id' => $opd1->id,
            'kelas' => 10,
        ]);
        $jabatan2 = Jabatan::create([
            'nama' => 'Jabatan 2',
            'opd_id' => $opd2->id,
            'kelas' => 10,
        ]);

        // Create admin OPD assigned to opd1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Apply OPD scope to query
        $query = Jabatan::query();
        $scopedQuery = $this->applyOpdScope($query);
        $results = $scopedQuery->get();

        // Should only return jabatan from opd1
        $this->assertCount(1, $results);
        $this->assertEquals($jabatan1->id, $results->first()->id);
        $this->assertEquals($opd1->id, $results->first()->opd_id);
    }

    /**
     * Test applyOpdScope method for super admin (no filtering)
     */
    public function test_apply_opd_scope_does_not_filter_for_super_admin(): void
    {
        // Create OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);

        // Create jabatan for each OPD
        Jabatan::create([
            'nama' => 'Jabatan 1',
            'opd_id' => $opd1->id,
            'kelas' => 10,
        ]);
        Jabatan::create([
            'nama' => 'Jabatan 2',
            'opd_id' => $opd2->id,
            'kelas' => 10,
        ]);

        // Create super admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'opd_id' => null,
        ]);

        // Authenticate as super admin
        $this->actingAs($superAdmin, 'admin');

        // Apply OPD scope to query
        $query = Jabatan::query();
        $scopedQuery = $this->applyOpdScope($query);
        $results = $scopedQuery->get();

        // Should return all jabatan (no filtering)
        $this->assertCount(2, $results);
    }

    /**
     * Test getAccessibleOpdIds method for admin OPD
     */
    public function test_get_accessible_opd_ids_returns_single_opd_for_admin_opd(): void
    {
        // Create OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);

        // Create admin OPD assigned to opd1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Get accessible OPD IDs
        $accessibleIds = $this->getAccessibleOpdIds();

        // Should only return opd1
        $this->assertCount(1, $accessibleIds);
        $this->assertContains($opd1->id, $accessibleIds);
        $this->assertNotContains($opd2->id, $accessibleIds);
    }

    /**
     * Test getAccessibleOpdIds method for super admin
     */
    public function test_get_accessible_opd_ids_returns_all_opds_for_super_admin(): void
    {
        // Create OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);

        // Create super admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'opd_id' => null,
        ]);

        // Authenticate as super admin
        $this->actingAs($superAdmin, 'admin');

        // Get accessible OPD IDs
        $accessibleIds = $this->getAccessibleOpdIds();

        // Should return all OPDs
        $this->assertCount(2, $accessibleIds);
        $this->assertContains($opd1->id, $accessibleIds);
        $this->assertContains($opd2->id, $accessibleIds);
    }

    /**
     * Test validateOpdAccess method allows access for admin OPD to their OPD
     */
    public function test_validate_opd_access_allows_access_for_admin_opd_to_their_opd(): void
    {
        // Create OPD
        $opd = Opd::create(['nama' => 'OPD 1']);

        // Create admin OPD assigned to opd
        $adminOpd = Admin::create([
            'name' => 'Admin OPD',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Should not throw exception
        $this->validateOpdAccess($opd->id);
        
        // If we get here, validation passed
        $this->assertTrue(true);
    }

    /**
     * Test validateOpdAccess method denies access for admin OPD to other OPD
     */
    public function test_validate_opd_access_denies_access_for_admin_opd_to_other_opd(): void
    {
        // Create OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);

        // Create admin OPD assigned to opd1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Should throw 403 exception
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Anda tidak memiliki akses ke OPD ini');
        
        $this->validateOpdAccess($opd2->id);
    }

    /**
     * Test validateOpdAccess method allows access for super admin to any OPD
     */
    public function test_validate_opd_access_allows_access_for_super_admin_to_any_opd(): void
    {
        // Create OPD
        $opd = Opd::create(['nama' => 'OPD 1']);

        // Create super admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'opd_id' => null,
        ]);

        // Authenticate as super admin
        $this->actingAs($superAdmin, 'admin');

        // Should not throw exception
        $this->validateOpdAccess($opd->id);
        
        // If we get here, validation passed
        $this->assertTrue(true);
    }
}
