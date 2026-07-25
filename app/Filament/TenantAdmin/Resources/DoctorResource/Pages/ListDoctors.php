<?php

namespace App\Filament\TenantAdmin\Resources\DoctorResource\Pages;

use App\Filament\TenantAdmin\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDoctors extends ListRecords
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        $canCreate = tenant()->canHaveMultipleDoctors() || \App\Models\Doctor::count() === 0;

        return [
            Actions\CreateAction::make()
                ->disabled(! $canCreate)
                ->tooltip($canCreate ? null : 'Solo tier is limited to a single doctor. Upgrade to add more.'),
        ];
    }
}
