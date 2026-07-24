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
            'session_id' => 'required|exists:schedule_sessions,id',
            'date' => 'required|date|after_or_equal:today',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
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

        return response()->json([
            'serial' => $serial
        ]);
        // For Inertia we would do: return Inertia::render('Booking/Show', ['serial' => $serial]);
    }
}
