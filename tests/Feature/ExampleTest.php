<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_central_domain_root_redirects_to_the_super_admin_panel(): void
    {
        $response = $this->get('http://localhost/');

        $response->assertRedirect();
    }

    public function test_a_tenant_domain_root_is_not_shadowed_by_the_central_route(): void
    {
        $tenant = Tenant::create(['id' => 'route-probe', 'layout_id' => 'Minimal', 'name' => 'Route Probe']);
        $tenant->domains()->create(['domain' => 'route-probe.localhost']);

        $response = $this->get('http://route-probe.localhost/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tenant/Layouts/Minimal'));
    }
}
