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
        return [
            Actions\CreateAction::make(),
        ];
    }
}
