<?php

namespace App\Services;

use App\Models\ScheduleSession;
use App\Models\LabCollectionSlot;
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
    public function bookSlot(string $bookableType, string|int $bookableId, string $date, array $patientData, array $testIds = []): Serial
    {
        return DB::transaction(function () use ($bookableType, $bookableId, $date, $patientData, $testIds) {
            $modelClass = $bookableType === 'lab_slot' ? LabCollectionSlot::class : ScheduleSession::class;
            
            // Lock the bookable record
            $bookable = $modelClass::where('id', $bookableId)
                ->lockForUpdate()
                ->firstOrFail();

            // Weekday validation
            $requestedDay = \Carbon\Carbon::parse($date)->dayOfWeek;
            if ($requestedDay !== $bookable->day_of_week) {
                throw new Exception('The selected date does not match the schedule.');
            }

            // Check if date is blocked (vacation mode)
            if ($bookable instanceof ScheduleSession) {
                $isBlocked = SlotBlock::where('block_date', $date)
                    ->where(function ($query) use ($bookable) {
                        $query->where('doctor_id', $bookable->doctor_id)
                              ->orWhere(function ($q) use ($bookable) {
                                  $q->where('chamber_id', $bookable->chamber_id)->whereNull('doctor_id');
                              });
                    })->exists();

                if ($isBlocked) {
                    throw new Exception('This date is blocked by the doctor.');
                }
            }

            // Capacity checks
            $tenant = tenant();
            
            if ($bookable instanceof ScheduleSession) {
                $slotCapType = $tenant->slot_cap_type ?? 'session';
                $dailyCap = (int) ($tenant->daily_slot_cap ?? 20);

                if ($slotCapType === 'day') {
                    $existingBookingsCount = Serial::where('booking_date', $date)
                        ->where('bookable_type', ScheduleSession::class)
                        ->where('status', '!=', 'cancelled')
                        ->count();

                    if ($existingBookingsCount >= $dailyCap) {
                        throw new Exception('Daily slot limit reached.');
                    }
                } else {
                    $sessionBookingsCount = Serial::where('bookable_type', ScheduleSession::class)
                        ->where('bookable_id', $bookable->id)
                        ->where('booking_date', $date)
                        ->where('status', '!=', 'cancelled')
                        ->count();

                    if ($sessionBookingsCount >= $bookable->slot_cap) {
                        throw new Exception('No slots available for this session.');
                    }
                }
            } else {
                $slotBookingsCount = Serial::where('bookable_type', LabCollectionSlot::class)
                    ->where('bookable_id', $bookable->id)
                    ->where('booking_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                if ($slotBookingsCount >= $bookable->slot_cap) {
                    throw new Exception('No slots available for this collection time.');
                }
            }

            $nextSerialNumber = (int) Serial::where('bookable_type', get_class($bookable))
                ->where('bookable_id', $bookable->id)
                ->where('booking_date', $date)
                ->max('serial_number') + 1;

            $serial = Serial::create([
                'id' => Str::uuid()->toString(),
                'doctor_id' => $bookable instanceof ScheduleSession ? $bookable->doctor_id : null,
                'chamber_id' => $bookable instanceof ScheduleSession ? $bookable->chamber_id : null,
                'bookable_type' => get_class($bookable),
                'bookable_id' => $bookable->id,
                'booking_date' => $date,
                'patient_name' => $patientData['name'],
                'patient_phone' => $patientData['phone'],
                'serial_number' => $nextSerialNumber,
                'status' => 'waiting',
                'payment_status' => 'unpaid',
            ]);

            if ($bookable instanceof LabCollectionSlot && !empty($testIds)) {
                $tests = \App\Models\LabTest::whereIn('id', $testIds)->get();
                $totalPrice = 0;
                
                foreach ($tests as $test) {
                    DB::table('booking_lab_tests')->insert([
                        'serial_id' => $serial->id,
                        'lab_test_id' => $test->id,
                        'price' => $test->price,
                    ]);
                    $totalPrice += $test->price;
                }
                
                // Optionally update total price on serial if added
            }

            return $serial;
        });
    }
}
