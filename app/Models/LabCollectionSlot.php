<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToTenant;

class LabCollectionSlot extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['id', 'tenant_id', 'day_of_week', 'start_time', 'end_time', 'slot_cap', 'is_active'];
}
