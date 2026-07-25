<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\ScheduleSession;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerDayTenantPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_day_cap_tenant_homepage_renders(): void
    {
        $tenant = Tenant::create([
            'id' => 'perday',
            'layout_id' => 'HeroFirst',
            'name' => 'Per Day Clinic',
            'slot_cap_type' => 'day',
            'daily_slot_cap' => 40,
        ]);
        $tenant->domains()->create(['domain' => 'perday.localhost']);

        tenancy()->initialize($tenant);
        $doctor = Doctor::create(['name' => 'Dr. Day', 'specialty' => 'GP']);
        $chamber = Chamber::create(['name' => 'Main', 'location' => 'Dhaka']);
        ScheduleSession::create([
            'chamber_id' => $chamber->id,
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'session_name' => 'Morning',
            'slot_cap' => 10,
        ]);
        tenancy()->end();

        $response = $this->get('http://perday.localhost/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Layouts/HeroFirst')
            ->where('doctors.0.schedule_sessions.0.capacity', 40)
            ->where('doctors.0.schedule_sessions.0.cap_type', 'day'));
    }

    public function test_tenant_columns_are_stored_as_real_columns_not_json(): void
    {
        Tenant::create([
            'id' => 'billing-probe',
            'billing_status' => 'read_only',
            'plan_tier' => 'clinic',
            'layout_id' => 'Minimal',
            'name' => 'Virtual Attr Clinic',
        ]);

        // Real columns must be queryable in SQL, not buried in the data blob.
        $this->assertSame(1, Tenant::where('billing_status', 'read_only')->count());
        $this->assertSame(1, Tenant::where('plan_tier', 'clinic')->count());

        // Attributes without a dedicated column still round-trip via `data`.
        $this->assertSame('Virtual Attr Clinic', Tenant::find('billing-probe')->name);
    }
}
