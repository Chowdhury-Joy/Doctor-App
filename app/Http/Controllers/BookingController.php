<?php

namespace App\Http\Controllers;

use App\Models\Serial;
use App\Models\ScheduleSession;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Exception;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('schedule_sessions', 'id')->where('tenant_id', tenant('id'))
            ],
            'date' => 'required|date|after_or_equal:today',
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(?:\+?88|01)?\d{11}$/'],
        ]);

        try {
            $serial = $this->bookingService->bookSlot(
                $validated['session_id'],
                $validated['date'],
                ['name' => $validated['name'], 'phone' => $validated['phone']]
            );

            // In an Inertia app, we would redirect to the status page.
            return redirect()->route('booking.show', ['id' => $serial->id]);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        // Using UUID
        $serial = Serial::with(['doctor', 'chamber', 'scheduleSession'])
            ->where('id', $id)
            ->firstOrFail();

        return \Inertia\Inertia::render('Booking/Show', [
            'serial' => $serial
        ]);
    }

    public function checkAvailability(Request $request, $sessionId)
    {
        $request->validate(['date' => 'required|date']);
        
        $session = ScheduleSession::findOrFail($sessionId);
        
        $tenant = tenant();
        $slotCapType = $tenant->slot_cap_type ?? 'session';
        $dailyCap = (int) ($tenant->daily_slot_cap ?? 20);
        
        $date = $request->input('date');

        if ($slotCapType === 'day') {
            $existingBookingsCount = Serial::where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->count();
            return response()->json([
                'booked' => $existingBookingsCount,
                'capacity' => $dailyCap,
                'available' => max(0, $dailyCap - $existingBookingsCount)
            ]);
        } else {
            $sessionBookingsCount = Serial::where('schedule_session_id', $session->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->count();
            return response()->json([
                'booked' => $sessionBookingsCount,
                'capacity' => $session->slot_cap,
                'available' => max(0, $session->slot_cap - $sessionBookingsCount)
            ]);
        }
    }
}
