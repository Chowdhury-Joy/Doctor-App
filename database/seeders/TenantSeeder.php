<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Doctor;
use App\Models\Chamber;
use App\Models\ScheduleSession;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Contact number shown on the public site and used for WhatsApp deep links.
     */
    private const CONTACT_PHONE = '8801823894527';

    public function run(): void
    {
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        // 1. Solo Chamber Tenant — single doctor, one session, low slot cap.
        $soloTenant = Tenant::create([
            'id' => 'demo-solo',
            'plan_tier' => 'solo',
            'layout_id' => 'HeroFirst',
            'name' => 'Dr. John Doe Chamber',
            'contact_phone' => self::CONTACT_PHONE,
            'theme_color' => '#0ea5e9',
            'slot_cap_type' => 'session',
        ]);
        $soloTenant->domains()->create(['domain' => "demo-solo.{$baseDomain}"]);

        tenancy()->initialize($soloTenant);

        $doctor1 = Doctor::create([
            'name' => 'Dr. John Doe',
            'specialty' => 'General Physician',
            'credentials' => 'MBBS, FCPS (Medicine)',
            'bio' => 'General physician with over 15 years of experience in primary care.',
        ]);

        $chamber1 = Chamber::create([
            'name' => 'Main Chamber',
            'location' => 'Dhanmondi, Dhaka',
            'hours' => '9 AM - 5 PM',
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

        // 2. Clinic Tenant — multiple doctors and chambers, higher slot cap,
        //    with two doctors sharing a weekday to surface scheduling conflicts.
        $clinicTenant = Tenant::create([
            'id' => 'demo-clinic',
            'plan_tier' => 'clinic',
            'layout_id' => 'ClinicStyle',
            'name' => 'Popular Diagnostic Clinic',
            'contact_phone' => self::CONTACT_PHONE,
            'theme_color' => '#0f766e',
            'slot_cap_type' => 'day',
            'daily_slot_cap' => 40,
        ]);
        $clinicTenant->domains()->create(['domain' => "demo-clinic.{$baseDomain}"]);

        tenancy()->initialize($clinicTenant);

        $doctor2 = Doctor::create([
            'name' => 'Dr. Jane Smith',
            'specialty' => 'Cardiology',
            'credentials' => 'MBBS, MD (Cardiology)',
            'bio' => 'Consultant cardiologist specialising in preventive heart care.',
        ]);

        $doctor3 = Doctor::create([
            'name' => 'Dr. Alice Williams',
            'specialty' => 'Neurology',
            'credentials' => 'MBBS, FCPS (Neurology)',
            'bio' => 'Neurologist with a focus on headache and seizure management.',
        ]);

        $chamber2 = Chamber::create([
            'name' => 'Cardiology Wing',
            'location' => 'Building A, Level 3',
            'hours' => '8 AM - 8 PM',
        ]);

        $chamber3 = Chamber::create([
            'name' => 'Neurology Wing',
            'location' => 'Building B, Level 2',
            'hours' => '10 AM - 6 PM',
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

        ScheduleSession::create([
            'chamber_id' => $chamber2->id,
            'doctor_id' => $doctor2->id,
            'day_of_week' => 2, // Tuesday
            'session_name' => 'Evening',
            'start_time' => '16:00:00',
            'end_time' => '20:00:00',
            'slot_cap' => 15,
        ]);

        // Same weekday as the cardiology sessions, different doctor and chamber.
        ScheduleSession::create([
            'chamber_id' => $chamber3->id,
            'doctor_id' => $doctor3->id,
            'day_of_week' => 2, // Tuesday
            'session_name' => 'Morning',
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'slot_cap' => 12,
        ]);

        tenancy()->end();
    }
}
