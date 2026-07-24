<?php

namespace App\Filament\TenantAdmin\Resources\ScheduleSessionResource\Pages;

use App\Filament\TenantAdmin\Resources\ScheduleSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateScheduleSession extends CreateRecord
{
    protected static string $resource = ScheduleSessionResource::class;
}
