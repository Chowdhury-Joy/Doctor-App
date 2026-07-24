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

        // Find the currently served serial (last one that went to 'in_chamber' or 'completed')
        // Or if none, it's waiting for #1.
        $lastServed = Serial::where('schedule_session_id', $sessionId)
            ->where('booking_date', $date)
            ->whereIn('status', ['in_chamber', 'completed'])
            ->orderByDesc('serial_number')
            ->first();

        $nowServing = $lastServed ? $lastServed->serial_number : 0;

        return response()->json([
            'session_id' => $sessionId,
            'date' => $date,
            'now_serving' => $nowServing,
        ]);
    }
}
