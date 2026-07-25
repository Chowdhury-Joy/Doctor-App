<?php

namespace App\Filament\TenantAdmin\Resources\ChamberResource\Pages;

use App\Filament\TenantAdmin\Resources\ChamberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChambers extends ListRecords
{
    protected static string $resource = ChamberResource::class;

    protected function getHeaderActions(): array
    {
        $canCreate = tenant()->canHaveMultipleChambers() || \App\Models\Chamber::count() === 0;

        return [
            Actions\CreateAction::make()
                ->disabled(! $canCreate)
                ->tooltip($canCreate ? null : 'Solo tier is limited to a single chamber. Upgrade to add more.'),
        ];
    }
}
