<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToTenant;

class Serial extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['id', 'tenant_id', 'doctor_id', 'chamber_id', 'bookable_type', 'bookable_id', 'booking_date', 'patient_name', 'patient_phone', 'serial_number', 'status', 'payment_status', 'payment_reference'];

    /**
     * The patient's phone in wa.me format (88XXXXXXXXXXX), or null if unusable.
     */
    public function getNormalisedPhoneAttribute(): ?string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $this->patient_phone);

        // Handle variations of BD numbers
        if (str_starts_with($phone, '+880')) {
            $phone = substr($phone, 1); // remove +
        } elseif (str_starts_with($phone, '880') && strlen($phone) === 13) {
            // Already correct
        } elseif (str_starts_with($phone, '01') && strlen($phone) === 11) {
            $phone = '88' . $phone;
        } else {
            // Unrecognised format, fall back to digits only
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        return $phone === '' ? null : $phone;
    }

    /**
     * Get a WhatsApp deep link to message the patient about their booking.
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        $phone = $this->normalised_phone;

        if (! $phone) {
            return null;
        }

        $message = rawurlencode("Hello {$this->patient_name}, your booking (Serial #{$this->serial_number}) for {$this->booking_date} is confirmed. View your live ticket here: " . route('booking.show', $this->id));

        return "https://wa.me/{$phone}?text={$message}";
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function chamber()
    {
        return $this->belongsTo(Chamber::class);
    }

    public function bookable()
    {
        return $this->morphTo();
    }
}
