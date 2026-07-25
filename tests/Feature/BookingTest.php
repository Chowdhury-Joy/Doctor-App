<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\ScheduleSession;
use App\Models\Tenant;
use App\Models\SlotBlock;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        tenancy()->initialize($this->tenant);

        $this->doctor = Doctor::create(['name' => 'Dr. Test']);
        $this->chamber = Chamber::create(['name' => 'Test Chamber', 'location' => 'Test Loc']);
        
        $this->session = ScheduleSession::create([
            'chamber_id' => $this->chamber->id,
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'session_name' => 'Morning',
            'slot_cap' => 2,
        ]);
        
        $this->bookingService = new BookingService();
    }

    public function test_can_book_slot()
    {
        $serial = $this->bookingService->bookSlot(
            $this->session->id,
            '2026-08-03',
            ['name' => 'John Doe', 'phone' => '1234567890']
        );

        $this->assertDatabaseHas('serials', [
            'id' => $serial->id,
            'patient_name' => 'John Doe',
            'serial_number' => 1,
        ]);
    }

    public function test_cannot_book_when_slot_cap_reached()
    {
        $this->bookingService->bookSlot($this->session->id, '2026-08-03', ['name' => 'Patient 1', 'phone' => '123']);
        $this->bookingService->bookSlot($this->session->id, '2026-08-03', ['name' => 'Patient 2', 'phone' => '123']);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No slots available for this session.');
        
        $this->bookingService->bookSlot($this->session->id, '2026-08-03', ['name' => 'Patient 3', 'phone' => '123']);
    }

    public function test_cannot_book_on_blocked_date()
    {
        SlotBlock::create([
            'doctor_id' => $this->doctor->id,
            'block_date' => '2026-08-03',
            'reason' => 'Vacation'
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This date is blocked by the doctor.');
        
        $this->bookingService->bookSlot($this->session->id, '2026-08-03', ['name' => 'Patient 1', 'phone' => '123']);
    }
}
