<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_isolation_prevents_cross_tenant_data_leakage()
    {
        // Create Tenant A
        $tenantA = Tenant::create(['id' => 'tenant-a']);
        
        // Create Tenant B
        $tenantB = Tenant::create(['id' => 'tenant-b']);

        // Create doctors for Tenant A
        tenancy()->initialize($tenantA);
        $doctorA = Doctor::create([
            'name' => 'Doctor A',
            'specialty' => 'Cardiology',
        ]);
        tenancy()->end();

        // Create doctors for Tenant B
        tenancy()->initialize($tenantB);
        $doctorB = Doctor::create([
            'name' => 'Doctor B',
            'specialty' => 'Neurology',
        ]);
        tenancy()->end();

        // Without initialization, it should get all if no tenant context
        // But let's initialize Tenant A and check
        tenancy()->initialize($tenantA);
        
        $doctors = Doctor::all();
        $this->assertCount(1, $doctors);
        $this->assertEquals('Doctor A', $doctors->first()->name);

        tenancy()->end();

        // Initialize Tenant B and check
        tenancy()->initialize($tenantB);
        
        $doctors = Doctor::all();
        $this->assertCount(1, $doctors);
        $this->assertEquals('Doctor B', $doctors->first()->name);

        tenancy()->end();
    }
}
