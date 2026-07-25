<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class SlotBlock extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'doctor_id', 'chamber_id', 'block_date', 'reason'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function chamber()
    {
        return $this->belongsTo(Chamber::class);
    }
}
