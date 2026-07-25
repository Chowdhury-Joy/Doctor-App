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

    public function create()
    {
        $doctors = \App\Models\Doctor::with(['scheduleSessions.chamber'])->get();
        
        $tenant = tenant();
        $slotCapType = $tenant->slot_cap_type ?? 'session';
        $dailyCap = (int) ($tenant->daily_slot_cap ?? 20);

        $doctors->each(function ($doctor) use ($slotCapType, $dailyCap) {
            $doctor->scheduleSessions->each(function ($session) use ($slotCapType, $dailyCap) {
                $session->capacity = $slotCapType === 'day' ? $dailyCap : $session->slot_cap;
                $session->cap_type = $slotCapType;
            });
        });
        
        $labSlots = [];
        $labTests = [];
        if ($tenant->plan_tier === 'clinic') {
            $labSlots = \App\Models\LabCollectionSlot::where('is_active', true)->get();
            $labTests = \App\Models\LabTest::where('is_active', true)->get();
        }
        
        return \Inertia\Inertia::render('Booking/Index', [
            'tenant' => $tenant,
            'doctors' => $doctors,
            'labSlots' => $labSlots,
            'labTests' => $labTests,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bookable_type' => 'required|in:session,lab_slot',
            'bookable_id' => 'required',
            'date' => 'required|date|after_or_equal:today',
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(?:\+?88|01)?\d{11}$/'],
            'test_ids' => 'nullable|array',
            'test_ids.*' => 'exists:lab_tests,id',
        ]);

        if ($validated['bookable_type'] === 'session') {
            $request->validate([
                'bookable_id' => [\Illuminate\Validation\Rule::exists('schedule_sessions', 'id')->where('tenant_id', tenant('id'))],
            ]);
        } else {
            $request->validate([
                'bookable_id' => [\Illuminate\Validation\Rule::exists('lab_collection_slots', 'id')->where('tenant_id', tenant('id'))],
                'test_ids' => 'required|array|min:1',
            ]);
        }

        try {
            $serial = $this->bookingService->bookSlot(
                $validated['bookable_type'],
                $validated['bookable_id'],
                $validated['date'],
                ['name' => $validated['name'], 'phone' => $validated['phone']],
                $validated['test_ids'] ?? []
            );

            return redirect()->route('booking.show', ['id' => $serial->id]);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $serial = Serial::with(['doctor', 'chamber', 'bookable'])->where('id', $id)->firstOrFail();

        // If it's a lab slot, load the tests
        if ($serial->bookable_type === \App\Models\LabCollectionSlot::class) {
            $tests = \Illuminate\Support\Facades\DB::table('booking_lab_tests')
                ->join('lab_tests', 'booking_lab_tests.lab_test_id', '=', 'lab_tests.id')
                ->where('booking_lab_tests.serial_id', $serial->id)
                ->select('lab_tests.name', 'booking_lab_tests.price')
                ->get();
            $serial->setAttribute('lab_tests', $tests);
            $serial->setAttribute('total_price', $tests->sum('price'));
        }

        return \Inertia\Inertia::render('Booking/Show', [
            'serial' => $serial
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'bookable_type' => 'required|in:session,lab_slot',
            'bookable_id' => 'required',
        ]);
        
        $date = $request->input('date');
        $tenant = tenant();
        
        if ($request->bookable_type === 'session') {
            $session = ScheduleSession::findOrFail($request->bookable_id);
            $slotCapType = $tenant->slot_cap_type ?? 'session';
            $dailyCap = (int) ($tenant->daily_slot_cap ?? 20);

            if ($slotCapType === 'day') {
                $existingBookingsCount = Serial::where('booking_date', $date)
                    ->where('bookable_type', ScheduleSession::class)
                    ->where('status', '!=', 'cancelled')
                    ->count();
                return response()->json([
                    'booked' => $existingBookingsCount,
                    'capacity' => $dailyCap,
                    'available' => max(0, $dailyCap - $existingBookingsCount)
                ]);
            } else {
                $sessionBookingsCount = Serial::where('bookable_type', ScheduleSession::class)
                    ->where('bookable_id', $session->id)
                    ->where('booking_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->count();
                return response()->json([
                    'booked' => $sessionBookingsCount,
                    'capacity' => $session->slot_cap,
                    'available' => max(0, $session->slot_cap - $sessionBookingsCount)
                ]);
            }
        } else {
            $slot = \App\Models\LabCollectionSlot::findOrFail($request->bookable_id);
            $slotBookingsCount = Serial::where('bookable_type', \App\Models\LabCollectionSlot::class)
                ->where('bookable_id', $slot->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->count();
            return response()->json([
                'booked' => $slotBookingsCount,
                'capacity' => $slot->slot_cap,
                'available' => max(0, $slot->slot_cap - $slotBookingsCount)
            ]);
        }
    }
}
