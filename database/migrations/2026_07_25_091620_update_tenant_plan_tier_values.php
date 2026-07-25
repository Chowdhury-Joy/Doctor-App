<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('tenants')->where('plan_tier', 'basic')->update(['plan_tier' => 'solo']);
        \Illuminate\Support\Facades\DB::table('tenants')->where('plan_tier', 'premium')->update(['plan_tier' => 'clinic']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('tenants')->where('plan_tier', 'solo')->update(['plan_tier' => 'basic']);
        \Illuminate\Support\Facades\DB::table('tenants')->where('plan_tier', 'clinic')->update(['plan_tier' => 'premium']);
    }
};
