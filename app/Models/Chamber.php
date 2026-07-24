<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class Chamber extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
