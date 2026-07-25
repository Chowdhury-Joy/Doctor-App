<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Doctor;
use App\Models\ScheduleSession;

class TenantFrontendController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['scheduleSessions.chamber'])->get();
        
        $tenant = tenant();
        $slotCapType = $tenant->slot_cap_type ?? 'session';
        $dailyCap = (int) ($tenant->daily_slot_cap ?? 20);

        // Enhance schedule sessions with available capacity
        $doctors->each(function ($doctor) use ($slotCapType, $dailyCap) {
            $doctor->scheduleSessions->each(function ($session) use ($slotCapType, $dailyCap) {
                // The base cap is passed for display only; the frontend polls the
                // availability endpoint for the remaining count on the chosen date.
                $session->capacity = $slotCapType === 'day' ? $dailyCap : $session->slot_cap;
                $session->cap_type = $slotCapType;
            });
        });
        
        $component = \App\Services\ThemeService::resolveLandingComponent($tenant);
        
        return Inertia::render($component, [
            'tenant' => $tenant,
            'doctors' => $doctors
        ]);
    }
}
