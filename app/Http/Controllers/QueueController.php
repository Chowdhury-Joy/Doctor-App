<?php

namespace App\Http\Controllers;

use App\Models\Serial;
use App\Models\ScheduleSession;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Get the live queue status for a specific session on a specific date.
     */
    public function status(Request $request, $sessionId)
    {
        $date = $request->query('date', now()->toDateString());

        // Find the currently served serial (active in_chamber)
        $inChamber = Serial::where('schedule_session_id', $sessionId)
            ->where('booking_date', $date)
            ->where('status', 'in_chamber')
            ->orderBy('serial_number')
            ->first();

        $nowServing = $inChamber ? $inChamber->serial_number : 0;

        return response()->json([
            'session_id' => $sessionId,
            'date' => $date,
            'now_serving' => $nowServing,
        ]);
    }
}
