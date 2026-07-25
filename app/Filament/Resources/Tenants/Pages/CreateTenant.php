<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    use \Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->record;
        
        tenancy()->initialize($tenant);
        
        $chamber = \App\Models\Chamber::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Chamber',
            'location' => 'HQ',
            'contact_phone' => '123456789'
        ]);

        $doctor = \App\Models\Doctor::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dr. John Doe',
            'specialty' => 'General Physician',
            'email' => 'doctor@example.com'
        ]);

        \App\Models\ScheduleSession::create([
            'tenant_id' => $tenant->id,
            'chamber_id' => $chamber->id,
            'doctor_id' => $doctor->id,
            'day_of_week' => 'Monday',
            'session_name' => 'Morning Shift',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'slot_cap' => 20
        ]);

        tenancy()->end();
    }
}
