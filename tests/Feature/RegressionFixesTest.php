<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\PaymentTransaction;
use App\Models\ScheduleSession;
use App\Models\Serial;
use App\Models\Tenant;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegressionFixesTest extends TestCase
{
    use RefreshDatabase;

    private function makeSession(string $tenantId = 'fix-tenant'): ScheduleSession
    {
        $tenant = Tenant::create(['id' => $tenantId]);
        tenancy()->initialize($tenant);

        $doctor = Doctor::create(['name' => 'Dr. Fix']);
        $chamber = Chamber::create(['name' => 'C', 'location' => 'L']);

        return ScheduleSession::create([
            'chamber_id' => $chamber->id,
            'doctor_id' => $doctor->id,
            'day_of_week' => 1, // Monday
            'session_name' => 'Morning',
            'slot_cap' => 10,
        ]);
    }

    public function test_cancelled_booking_does_not_cause_duplicate_serial_numbers(): void
    {
        $session = $this->makeSession();
        $svc = new BookingService();
        $date = '2026-08-03'; // Monday

        $svc->bookSlot($session->id, $date, ['name' => 'P1', 'phone' => '01700000001']);
        $s2 = $svc->bookSlot($session->id, $date, ['name' => 'P2', 'phone' => '01700000002']);
        $svc->bookSlot($session->id, $date, ['name' => 'P3', 'phone' => '01700000003']);

        $s2->update(['status' => 'cancelled']);

        $s4 = $svc->bookSlot($session->id, $date, ['name' => 'P4', 'phone' => '01700000004']);

        $this->assertSame(4, $s4->serial_number, 'next serial must continue past the cancelled one');

        $active = Serial::where('schedule_session_id', $session->id)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('serial_number')->all();

        $this->assertSame(count($active), count(array_unique($active)), 'no duplicate serial numbers');
    }

    public function test_doctor_bio_and_credentials_persist(): void
    {
        $this->makeSession('fix-doctor-tenant');

        $doctor = Doctor::create([
            'name' => 'Dr. Full',
            'photo' => 'photo.jpg',
            'specialty' => 'Cardiology',
            'credentials' => 'MBBS, FCPS',
            'bio' => 'A bio.',
        ]);

        $this->assertSame('photo.jpg', $doctor->fresh()->photo);
        $this->assertSame('MBBS, FCPS', $doctor->fresh()->credentials);
        $this->assertSame('A bio.', $doctor->fresh()->bio);
    }

    public function test_payment_transaction_id_persists_for_idempotency(): void
    {
        $session = $this->makeSession('fix-pay-tenant');
        $serial = (new BookingService())->bookSlot($session->id, '2026-08-03', ['name' => 'P', 'phone' => '01700000001']);

        $payload = ['serial_id' => $serial->id, 'status' => 'VALID', 'trx_id' => 'TRX-1'];

        foreach ([1, 2] as $attempt) {
            PaymentTransaction::updateOrCreate(
                ['gateway' => 'bkash', 'transaction_id' => 'TRX-1'],
                ['serial_id' => $serial->id, 'webhook_payload' => json_encode($payload), 'verified_at' => now()],
            );
        }

        $this->assertSame('TRX-1', PaymentTransaction::first()->transaction_id);
        $this->assertSame(1, PaymentTransaction::count(), 'retry must not duplicate the transaction');
    }

    public function test_demo_seeders_run_and_produce_both_tiers(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $solo = Tenant::find('demo-solo');
        $clinic = Tenant::find('demo-clinic');

        $this->assertNotNull($solo);
        $this->assertNotNull($clinic);
        $this->assertSame('Dr. John Doe Chamber', $solo->name);
        $this->assertSame('day', $clinic->slot_cap_type);
        $this->assertSame(1, $solo->domains()->count());

        // Clinic tier must exercise multiple doctors sharing a weekday.
        tenancy()->initialize($clinic);
        $this->assertSame(2, Doctor::count());
        $this->assertSame(3, ScheduleSession::count());
        $this->assertSame(3, ScheduleSession::where('day_of_week', 2)->count());
        tenancy()->end();

        $this->assertDatabaseHas('users', ['email' => 'solo@example.com', 'role' => 'tenant_admin', 'tenant_id' => 'demo-solo']);
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => 'super_admin']);
    }

    public function test_phone_normalisation_for_whatsapp(): void
    {
        $session = $this->makeSession('fix-phone-tenant');
        $svc = new BookingService();

        $local = $svc->bookSlot($session->id, '2026-08-03', ['name' => 'Local', 'phone' => '01823894527']);
        $this->assertSame('8801823894527', $local->normalised_phone);
        $this->assertStringContainsString('wa.me/8801823894527', $local->whatsapp_link);

        $intl = $svc->bookSlot($session->id, '2026-08-03', ['name' => 'Intl', 'phone' => '+8801823894527']);
        $this->assertSame('8801823894527', $intl->normalised_phone);
    }
}
