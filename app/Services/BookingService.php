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

            // Weekday validation
            $requestedDay = \Carbon\Carbon::parse($date)->dayOfWeek;
            if ($requestedDay !== $session->day_of_week) {
                throw new Exception('The selected date does not match the session schedule.');
            }

            // Check if date is blocked (vacation mode)
            $isBlocked = SlotBlock::where('block_date', $date)
                ->where(function ($query) use ($session) {
                    $query->where('doctor_id', $session->doctor_id)
                          ->orWhere(function ($q) use ($session) {
                              $q->where('chamber_id', $session->chamber_id)->whereNull('doctor_id');
                          });
                })->exists();

            if ($isBlocked) {
                throw new Exception('This date is blocked by the doctor.');
            }

            // Determine slot cap type from tenant settings
            $tenant = tenant();
            $slotCapType = $tenant->slot_cap_type ?? 'session';
            $dailyCap = (int) ($tenant->daily_slot_cap ?? 20);

            if ($slotCapType === 'day') {
                $existingBookingsCount = Serial::where('booking_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                if ($existingBookingsCount >= $dailyCap) {
                    throw new Exception('Daily slot limit reached.');
                }
            } else {
                // Count existing bookings for this session on this date
                $sessionBookingsCount = Serial::where('schedule_session_id', $session->id)
                    ->where('booking_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                if ($sessionBookingsCount >= $session->slot_cap) {
                    throw new Exception('No slots available for this session.');
                }
            }

            // Derive the serial number from the highest issued so far, including
            // cancelled rows. Counting only active bookings would reissue a number
            // already held by a waiting patient once an earlier booking is cancelled.
            $nextSerialNumber = (int) Serial::where('schedule_session_id', $session->id)
                ->where('booking_date', $date)
                ->max('serial_number') + 1;


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
