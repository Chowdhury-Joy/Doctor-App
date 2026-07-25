<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class PaymentTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'serial_id', 'gateway', 'transaction_id', 'webhook_payload', 'verified_at'];
}
