<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToTenant;

class LabTest extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['id', 'tenant_id', 'name', 'department', 'description', 'preparation_rules', 'price', 'is_active'];
}
