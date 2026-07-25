<?php

namespace App\Filament\TenantAdmin\Resources\SlotBlocks\Pages;

use App\Filament\TenantAdmin\Resources\SlotBlocks\SlotBlockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSlotBlock extends CreateRecord
{
    protected static string $resource = SlotBlockResource::class;

    protected function afterCreate(): void
    {
        $block = $this->record;

        $serials = \App\Models\Serial::where('doctor_id', $block->doctor_id)
            ->where('booking_date', $block->block_date)
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($serials->isEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('Slot blocked successfully.')
                ->success()
                ->send();
            return;
        }

        // Cancel them
        \App\Models\Serial::whereIn('id', $serials->pluck('id'))->update(['status' => 'cancelled']);

        $links = [];
        foreach ($serials as $serial) {
            $phone = $serial->patient_phone;
            $msg = urlencode("Hello {$serial->patient_name}, unfortunately your appointment on {$block->block_date} has been cancelled due to: {$block->reason}. Please rebook.");
            $links[] = "<a href='https://wa.me/{$phone}?text={$msg}' target='_blank' style='text-decoration: underline;'>Notify {$serial->patient_name}</a>";
        }

        $htmlLinks = implode('<br>', $links);

        \Filament\Notifications\Notification::make()
            ->title('Slot blocked & Serials cancelled')
            ->body('The following patients need to be notified:<br>' . $htmlLinks)
            ->success()
            ->persistent()
            ->send();
    }
}
