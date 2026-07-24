<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToTenant;

class Serial extends Model
{
    use HasUuids, BelongsToTenant;

    protected $guarded = [];
}
