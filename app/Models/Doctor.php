<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Doctor extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'photo', 'specialty', 'credentials', 'bio'];

    public function scheduleSessions()
    {
        return $this->hasMany(ScheduleSession::class);
    }
}
