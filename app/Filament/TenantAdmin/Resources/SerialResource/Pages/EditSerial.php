<?php

namespace App\Filament\TenantAdmin\Resources\SerialResource\Pages;

use App\Filament\TenantAdmin\Resources\SerialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSerial extends EditRecord
{
    protected static string $resource = SerialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
