<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ScheduleSession extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
