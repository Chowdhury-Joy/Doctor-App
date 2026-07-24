<?php

namespace App\Filament\TenantAdmin\Resources\ScheduleSessionResource\Pages;

use App\Filament\TenantAdmin\Resources\ScheduleSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleSessions extends ListRecords
{
    protected static string $resource = ScheduleSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
