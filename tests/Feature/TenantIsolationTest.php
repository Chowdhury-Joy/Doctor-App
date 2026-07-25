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

        // Create models for Tenant A
        tenancy()->initialize($tenantA);
        $chamberA = \App\Models\Chamber::create(['name' => 'Chamber A', 'location' => 'HQ A', 'contact_phone' => '111']);
        $doctorA = Doctor::create(['name' => 'Doctor A', 'specialty' => 'Cardiology']);
        $sessionA = \App\Models\ScheduleSession::create([
            'chamber_id' => $chamberA->id,
            'doctor_id' => $doctorA->id,
            'day_of_week' => 'Monday',
            'session_name' => 'Morning',
            'start_time' => '09:00',
            'end_time' => '13:00',
            'slot_cap' => 10
        ]);
        $serialA = \App\Models\Serial::create([
            'schedule_session_id' => $sessionA->id,
            'chamber_id' => $chamberA->id,
            'doctor_id' => $doctorA->id,
            'booking_date' => '2026-08-01',
            'patient_name' => 'Patient A',
            'patient_phone' => '111111',
            'serial_number' => 1,
            'status' => 'waiting'
        ]);
        \App\Models\PaymentTransaction::create([
            'serial_id' => $serialA->id,
            'gateway' => 'bkash',
            'amount' => 500,
            'transaction_id' => 'TXN111'
        ]);
        \App\Models\SlotBlock::create([
            'doctor_id' => $doctorA->id,
            'block_date' => '2026-08-10',
            'reason' => 'Vacation'
        ]);
        tenancy()->end();

        // Create models for Tenant B
        tenancy()->initialize($tenantB);
        $chamberB = \App\Models\Chamber::create(['name' => 'Chamber B', 'location' => 'HQ B', 'contact_phone' => '222']);
        $doctorB = Doctor::create(['name' => 'Doctor B', 'specialty' => 'Neurology']);
        $sessionB = \App\Models\ScheduleSession::create([
            'chamber_id' => $chamberB->id,
            'doctor_id' => $doctorB->id,
            'day_of_week' => 'Tuesday',
            'session_name' => 'Evening',
            'start_time' => '15:00',
            'end_time' => '19:00',
            'slot_cap' => 10
        ]);
        $serialB = \App\Models\Serial::create([
            'schedule_session_id' => $sessionB->id,
            'chamber_id' => $chamberB->id,
            'doctor_id' => $doctorB->id,
            'booking_date' => '2026-08-02',
            'patient_name' => 'Patient B',
            'patient_phone' => '222222',
            'serial_number' => 1,
            'status' => 'waiting'
        ]);
        \App\Models\PaymentTransaction::create([
            'serial_id' => $serialB->id,
            'gateway' => 'nagad',
            'amount' => 500,
            'transaction_id' => 'TXN222'
        ]);
        \App\Models\SlotBlock::create([
            'doctor_id' => $doctorB->id,
            'block_date' => '2026-08-11',
            'reason' => 'Sick'
        ]);
        tenancy()->end();

        // Initialize Tenant A and check
        tenancy()->initialize($tenantA);
        
        $this->assertCount(1, Doctor::all());
        $this->assertCount(1, \App\Models\Chamber::all());
        $this->assertCount(1, \App\Models\ScheduleSession::all());
        $this->assertCount(1, \App\Models\Serial::all());
        $this->assertCount(1, \App\Models\PaymentTransaction::all());
        $this->assertCount(1, \App\Models\SlotBlock::all());
        
        $this->assertEquals('Doctor A', Doctor::first()->name);
        $this->assertEquals('Chamber A', \App\Models\Chamber::first()->name);

        tenancy()->end();

        // Initialize Tenant B and check
        tenancy()->initialize($tenantB);
        
        $this->assertCount(1, Doctor::all());
        $this->assertCount(1, \App\Models\Chamber::all());
        $this->assertCount(1, \App\Models\ScheduleSession::all());
        $this->assertCount(1, \App\Models\Serial::all());
        $this->assertCount(1, \App\Models\PaymentTransaction::all());
        $this->assertCount(1, \App\Models\SlotBlock::all());

        $this->assertEquals('Doctor B', Doctor::first()->name);
        $this->assertEquals('Chamber B', \App\Models\Chamber::first()->name);

        tenancy()->end();
    }
}
