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
            'theme_name',
            'payment_gateway_secret',
        ];
    }

    protected function casts(): array
    {
        return [
            'custom_code_approved_at' => 'datetime',
            'feature_flags' => 'array',
            'payment_gateway_secret' => 'encrypted',
        ];
    }

    public function canHaveMultipleDoctors(): bool
    {
        return $this->hasCapability('multiple_doctors', fn() => $this->plan_tier === 'clinic');
    }

    public function canHaveMultipleChambers(): bool
    {
        return $this->hasCapability('multiple_chambers', fn() => $this->plan_tier === 'clinic');
    }

    public function canUseLabTests(): bool
    {
        return $this->hasCapability('lab_tests', fn() => $this->plan_tier === 'clinic');
    }

    public function hasCapability(string $flag, callable $defaultRule): bool
    {
        if (is_array($this->feature_flags) && array_key_exists($flag, $this->feature_flags)) {
            return (bool) $this->feature_flags[$flag];
        }
        return $defaultRule();
    }
}
