<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Opd;
use App\Http\Middleware\CheckOpdAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Unit tests for CheckOpdAccess middleware
 * 
 * Tests that the middleware correctly validates OPD access for admin OPD
 * and allows unrestricted access for other admin roles.
 */
class CheckOpdAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin OPD can access their assigned OPD's resources
     */
    public function test_admin_opd_can_access_assigned_opd_resources(): void
    {
        // Create OPD
        $opd = Opd::create(['nama' => 'Test OPD']);
        
        // Create admin OPD assigned to this OPD
        $adminOpd = Admin::create([
            'name' => 'Admin OPD Test',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Create request with their OPD ID
        $request = Request::create('/admin/opds/' . $opd->id, 'GET');
        $request->setRouteResolver(function () use ($request, $opd) {
            $route = new \Illuminate\Routing\Route('GET', '/admin/opds/{id}', []);
            $route->bind($request);
            $route->setParameter('id', $opd->id);
            return $route;
        });

        $middleware = new CheckOpdAccess();
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that admin OPD cannot access other OPD's resources
     */
    public function test_admin_opd_cannot_access_other_opd_resources(): void
    {
        // Create two OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);
        
        // Create admin OPD assigned to OPD 1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD Test',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Create request with OPD 2's ID
        $request = Request::create('/admin/opds/' . $opd2->id, 'GET');
        $request->setRouteResolver(function () use ($request, $opd2) {
            $route = new \Illuminate\Routing\Route('GET', '/admin/opds/{id}', []);
            $route->bind($request);
            $route->setParameter('id', $opd2->id);
            return $route;
        });

        $middleware = new CheckOpdAccess();
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Anda tidak memiliki akses ke OPD ini');
        
        $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });
    }

    /**
     * Test that super admin can access all OPD resources
     */
    public function test_super_admin_can_access_all_opd_resources(): void
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

        $middleware = new CheckOpdAccess();

        // Test access to OPD 1
        $request1 = Request::create('/admin/opds/' . $opd1->id, 'GET');
        $request1->setRouteResolver(function () use ($request1, $opd1) {
            $route = new \Illuminate\Routing\Route('GET', '/admin/opds/{id}', []);
            $route->bind($request1);
            $route->setParameter('id', $opd1->id);
            return $route;
        });

        $response1 = $middleware->handle($request1, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response1->getStatusCode());

        // Test access to OPD 2
        $request2 = Request::create('/admin/opds/' . $opd2->id, 'GET');
        $request2->setRouteResolver(function () use ($request2, $opd2) {
            $route = new \Illuminate\Routing\Route('GET', '/admin/opds/{id}', []);
            $route->bind($request2);
            $route->setParameter('id', $opd2->id);
            return $route;
        });

        $response2 = $middleware->handle($request2, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response2->getStatusCode());
    }

    /**
     * Test that unauthorized access attempts are logged
     */
    public function test_unauthorized_access_attempts_are_logged(): void
    {
        // Create two OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);
        
        // Create admin OPD assigned to OPD 1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD Test',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Expect log to be written
        Log::shouldReceive('warning')
            ->once()
            ->with('Unauthorized OPD access attempt', \Mockery::on(function ($context) use ($adminOpd, $opd1, $opd2) {
                return $context['admin_id'] === $adminOpd->id
                    && $context['admin_email'] === $adminOpd->email
                    && $context['admin_opd_id'] === $opd1->id
                    && $context['requested_opd_id'] === $opd2->id
                    && isset($context['timestamp'])
                    && isset($context['ip_address'])
                    && isset($context['user_agent']);
            }));

        // Create request with OPD 2's ID
        $request = Request::create('/admin/opds/' . $opd2->id, 'GET');
        $request->setRouteResolver(function () use ($request, $opd2) {
            $route = new \Illuminate\Routing\Route('GET', '/admin/opds/{id}', []);
            $route->bind($request);
            $route->setParameter('id', $opd2->id);
            return $route;
        });

        $middleware = new CheckOpdAccess();
        
        try {
            $middleware->handle($request, function ($req) {
                return new Response('OK', 200);
            });
            $this->fail('Expected HttpException was not thrown');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /**
     * Test that middleware extracts OPD ID from query parameters
     */
    public function test_middleware_extracts_opd_id_from_query_parameters(): void
    {
        // Create two OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);
        
        // Create admin OPD assigned to OPD 1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD Test',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        $middleware = new CheckOpdAccess();

        // Should be able to filter by their own OPD
        $request1 = Request::create('/admin/pegawai?opd_id=' . $opd1->id, 'GET');
        $response1 = $middleware->handle($request1, function ($req) {
            return new Response('OK', 200);
        });
        $this->assertEquals(200, $response1->getStatusCode());

        // Should NOT be able to filter by another OPD
        $request2 = Request::create('/admin/pegawai?opd_id=' . $opd2->id, 'GET');
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware->handle($request2, function ($req) {
            return new Response('OK', 200);
        });
    }

    /**
     * Test that middleware allows requests without OPD ID
     */
    public function test_middleware_allows_requests_without_opd_id(): void
    {
        // Create OPD
        $opd = Opd::create(['nama' => 'Test OPD']);
        
        // Create admin OPD
        $adminOpd = Admin::create([
            'name' => 'Admin OPD Test',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        // Create request without OPD ID
        $request = Request::create('/admin/dashboard', 'GET');

        $middleware = new CheckOpdAccess();
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that middleware extracts OPD ID from form data
     */
    public function test_middleware_extracts_opd_id_from_form_data(): void
    {
        // Create two OPDs
        $opd1 = Opd::create(['nama' => 'OPD 1']);
        $opd2 = Opd::create(['nama' => 'OPD 2']);
        
        // Create admin OPD assigned to OPD 1
        $adminOpd = Admin::create([
            'name' => 'Admin OPD Test',
            'email' => 'adminopd@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_ADMIN_OPD,
            'is_active' => true,
            'opd_id' => $opd1->id,
        ]);

        // Authenticate as admin OPD
        $this->actingAs($adminOpd, 'admin');

        $middleware = new CheckOpdAccess();

        // Should be able to submit form with their own OPD
        $request1 = Request::create('/admin/pegawai', 'POST', ['opd_id' => $opd1->id]);
        $response1 = $middleware->handle($request1, function ($req) {
            return new Response('OK', 200);
        });
        $this->assertEquals(200, $response1->getStatusCode());

        // Should NOT be able to submit form with another OPD
        $request2 = Request::create('/admin/pegawai', 'POST', ['opd_id' => $opd2->id]);
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware->handle($request2, function ($req) {
            return new Response('OK', 200);
        });
    }
}
