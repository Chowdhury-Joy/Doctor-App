<?php

namespace App\Filament\TenantAdmin\Resources\DoctorResource\Pages;

use App\Filament\TenantAdmin\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;
}
