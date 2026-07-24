<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class PaymentTransaction extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
