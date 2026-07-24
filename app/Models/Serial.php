<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToTenant;

class Serial extends Model
{
    use HasUuids, BelongsToTenant;

    protected $guarded = [];

    /**
     * Get a WhatsApp deep link to message the patient about their booking.
     */
    public function getWhatsappLinkAttribute()
    {
        // Strip non-numeric characters from phone
        $phone = preg_replace('/[^0-9]/', '', $this->patient_phone);
        
        // Assume Bangladesh country code if starts with 01
        if (str_starts_with($phone, '01') && strlen($phone) == 11) {
            $phone = '88' . $phone;
        }

        $message = urlencode("Hello {$this->patient_name}, your booking (Serial #{$this->serial_number}) for {$this->booking_date} is confirmed. View your live ticket here: " . route('booking.show', $this->id));
        
        return "https://wa.me/{$phone}?text={$message}";
    }
}
