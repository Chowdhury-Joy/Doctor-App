<?php

namespace App\Filament\TenantAdmin\Resources\ChamberResource\Pages;

use App\Filament\TenantAdmin\Resources\ChamberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChamber extends CreateRecord
{
    protected static string $resource = ChamberResource::class;

    public function mount(): void
    {
        if (! tenant()->canHaveMultipleChambers() && \App\Models\Chamber::count() >= 1) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Tier Limit Reached')
                ->body('Solo tier is limited to a single chamber. Upgrade to add more.')
                ->send();
            
            $this->redirect(ChamberResource::getUrl('index'));
            return;
        }

        parent::mount();
    }
}
