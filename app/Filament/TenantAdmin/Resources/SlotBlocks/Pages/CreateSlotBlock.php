<?php

namespace App\Filament\TenantAdmin\Resources\SlotBlocks\Pages;

use App\Filament\TenantAdmin\Resources\SlotBlocks\SlotBlockResource;
use App\Models\Serial;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateSlotBlock extends CreateRecord
{
    protected static string $resource = SlotBlockResource::class;

    protected function afterCreate(): void
    {
        $block = $this->record;

        // A block scoped to a doctor only affects that doctor; a block with no
        // doctor closes the whole chamber for the day.
        $serials = Serial::query()
            ->whereDate('booking_date', $block->block_date)
            ->where('status', '!=', 'cancelled')
            ->when(
                $block->doctor_id,
                fn ($query) => $query->where('doctor_id', $block->doctor_id),
                fn ($query) => $query->where('chamber_id', $block->chamber_id),
            )
            ->get();

        if ($serials->isEmpty()) {
            Notification::make()
                ->title('Slot blocked successfully.')
                ->success()
                ->send();

            return;
        }

        Serial::whereIn('id', $serials->pluck('id'))->update(['status' => 'cancelled']);

        $reason = $block->reason ?: 'a schedule change';

        $links = $serials
            ->map(function (Serial $serial) use ($block, $reason) {
                $name = e($serial->patient_name);
                $phone = $serial->normalised_phone;

                if (! $phone) {
                    return "{$name} — no valid phone number on file (" . e($serial->patient_phone) . ')';
                }

                $message = rawurlencode(sprintf(
                    'Hello %s, unfortunately your appointment on %s has been cancelled due to %s. Please rebook.',
                    $serial->patient_name,
                    Carbon::parse($block->block_date)->toDateString(),
                    $reason,
                ));

                return "<a href='https://wa.me/{$phone}?text={$message}' target='_blank' style='text-decoration: underline;'>Notify {$name}</a>";
            })
            ->implode('<br>');

        Notification::make()
            ->title($serials->count() . ' booking(s) cancelled')
            ->body('The following patients need to be notified:<br>' . $links)
            ->warning()
            ->persistent()
            ->send();
    }
}
