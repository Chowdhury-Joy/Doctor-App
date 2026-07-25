<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant
{
    use HasDomains;

    /**
     * Columns that exist on the tenants table in their own right.
     *
     * Anything not listed here is treated by stancl's VirtualColumn trait as a
     * virtual attribute and folded into the `data` JSON blob. Without this list
     * the dedicated columns added by the migration are never written, so SQL
     * filters (e.g. `where('billing_status', 'read_only')`) silently match
     * nothing even though the attribute reads back correctly in PHP.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'template_id',
            'layout_id',
            'custom_code',
            'custom_code_approved_at',
            'billing_status',
            'plan_tier',
            'feature_flags',
        ];
    }

    protected function casts(): array
    {
        return [
            'custom_code_approved_at' => 'datetime',
            'feature_flags' => 'array',
        ];
    }
}
