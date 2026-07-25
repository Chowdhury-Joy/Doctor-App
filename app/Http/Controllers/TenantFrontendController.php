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
        
        $layout = tenant('layout_id') ?? 'HeroFirst';
        
        return Inertia::render("Tenant/Layouts/{$layout}", [
            'tenant' => tenant(),
            'doctors' => $doctors
        ]);
    }
}
