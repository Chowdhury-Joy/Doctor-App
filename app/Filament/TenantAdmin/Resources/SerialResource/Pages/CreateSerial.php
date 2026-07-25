<?php

namespace App\Filament\TenantAdmin\Resources\SerialResource\Pages;

use App\Filament\TenantAdmin\Resources\SerialResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSerial extends CreateRecord
{
    protected static string $resource = SerialResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $bookingService = app(\App\Services\BookingService::class);
        
        return $bookingService->bookSlot(
            $data['schedule_session_id'],
            $data['booking_date'],
            [
                'name' => $data['patient_name'],
                'phone' => $data['patient_phone']
            ]
        );
    }
}
