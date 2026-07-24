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
        
        return Inertia::render('Tenant/Index', [
            'tenant' => tenant(),
            'doctors' => $doctors
        ]);
    }
}
