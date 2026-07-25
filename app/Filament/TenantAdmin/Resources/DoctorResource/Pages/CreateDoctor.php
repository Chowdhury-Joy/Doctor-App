<?php

namespace App\Filament\TenantAdmin\Resources\DoctorResource\Pages;

use App\Filament\TenantAdmin\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    public function mount(): void
    {
        if (! tenant()->canHaveMultipleDoctors() && \App\Models\Doctor::count() >= 1) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Tier Limit Reached')
                ->body('Solo tier is limited to a single doctor. Upgrade to add more.')
                ->send();
            
            $this->redirect(DoctorResource::getUrl('index'));
            return;
        }

        parent::mount();
    }
}
