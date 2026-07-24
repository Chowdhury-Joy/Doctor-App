<?php

namespace App\Services;

use App\Models\ScheduleSession;
use App\Models\Serial;
use App\Models\SlotBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BookingService
{
    /**
     * Book a slot for a patient with pessimistic locking.
     */
    public function bookSlot(int $sessionId, string $date, array $patientData): Serial
    {
        return DB::transaction(function () use ($sessionId, $date, $patientData) {
            // Lock the session record to prevent concurrent booking for this specific session
            $session = ScheduleSession::where('id', $sessionId)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if date is blocked (vacation mode)
            $isBlocked = SlotBlock::where('doctor_id', $session->doctor_id)
                ->where('block_date', $date)
                ->exists();

            if ($isBlocked) {
                throw new Exception('This date is blocked by the doctor.');
            }

            // Count existing bookings for this session on this date
            $existingBookingsCount = Serial::where('schedule_session_id', $session->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($existingBookingsCount >= $session->slot_cap) {
                throw new Exception('No slots available for this session.');
            }

            // Determine the next serial number
            $nextSerialNumber = $existingBookingsCount + 1;

            // Create the booking
            $serial = Serial::create([
                'id' => Str::uuid()->toString(),
                'doctor_id' => $session->doctor_id,
                'chamber_id' => $session->chamber_id,
                'schedule_session_id' => $session->id,
                'booking_date' => $date,
                'patient_name' => $patientData['name'],
                'patient_phone' => $patientData['phone'],
                'serial_number' => $nextSerialNumber,
                'status' => 'waiting',
                'payment_status' => 'unpaid',
            ]);

            return $serial;
        });
    }
}
