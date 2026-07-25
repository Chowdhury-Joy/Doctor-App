<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    // The wizard is defined in TenantForm's own schema (Filament\Schemas\Components\Wizard),
    // not via this page's HasWizard concern. HasWizard::form() replaces the resource's
    // entire form with Wizard::make($this->getSteps()) — since getSteps() defaults to an
    // empty array here, using both together silently rendered a wizard with zero steps.
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->record;
        
        tenancy()->initialize($tenant);
        
        $chamber = \App\Models\Chamber::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Chamber',
            'location' => 'HQ'
        ]);

        $doctor = \App\Models\Doctor::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dr. John Doe',
            'specialty' => 'General Physician'
        ]);

        \App\Models\ScheduleSession::create([
            'tenant_id' => $tenant->id,
            'chamber_id' => $chamber->id,
            'doctor_id' => $doctor->id,
            'day_of_week' => 1, // Monday
            'session_name' => 'Morning Shift',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'slot_cap' => 20
        ]);

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $tenant->domains()->create(['domain' => "{$tenant->id}.{$baseDomain}"]);

        tenancy()->end();
    }
}
