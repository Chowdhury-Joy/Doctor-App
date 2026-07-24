<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Doctor;
use App\Models\Chamber;
use App\Models\ScheduleSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Solo Chamber Tenant
        $soloTenant = Tenant::create([
            'id' => 'demo-solo',
            'plan_tier' => 'solo'
        ]);
        $soloTenant->domains()->create(['domain' => 'solo.getwebfield.com']);
        
        tenancy()->initialize($soloTenant);
        
        $doctor1 = Doctor::create([
            'name' => 'Dr. John Doe',
            'specialty' => 'General Physician',
        ]);
        
        $chamber1 = Chamber::create([
            'name' => 'Main Chamber',
            'location' => 'Dhaka',
            'hours' => '9 AM - 5 PM'
        ]);
        
        ScheduleSession::create([
            'chamber_id' => $chamber1->id,
            'doctor_id' => $doctor1->id,
            'day_of_week' => 1, // Monday
            'session_name' => 'Morning',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'slot_cap' => 10,
        ]);
        
        tenancy()->end();

        // 2. Clinic Tenant
        $clinicTenant = Tenant::create([
            'id' => 'demo-clinic',
            'plan_tier' => 'clinic'
        ]);
        // Also map demo.getwebfield.com to clinic for testing the single demo requested
        $clinicTenant->domains()->create(['domain' => 'demo.getwebfield.com']);
        
        tenancy()->initialize($clinicTenant);
        
        $doctor2 = Doctor::create([
            'name' => 'Dr. Jane Smith',
            'specialty' => 'Cardiology',
        ]);
        
        $doctor3 = Doctor::create([
            'name' => 'Dr. Alice Williams',
            'specialty' => 'Neurology',
        ]);
        
        $chamber2 = Chamber::create([
            'name' => 'Cardiology Wing',
            'location' => 'Building A',
            'hours' => '8 AM - 8 PM'
        ]);
        
        $chamber3 = Chamber::create([
            'name' => 'Neurology Wing',
            'location' => 'Building B',
            'hours' => '10 AM - 6 PM'
        ]);
        
        ScheduleSession::create([
            'chamber_id' => $chamber2->id,
            'doctor_id' => $doctor2->id,
            'day_of_week' => 2, // Tuesday
            'session_name' => 'Morning',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'slot_cap' => 20,
        ]);
        
        tenancy()->end();
    }
}
