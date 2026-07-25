<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // NOTE: do not add WithoutModelEvents here. The BelongsToTenant trait assigns
    // tenant_id from a `creating` model event, so suppressing events makes every
    // tenant-scoped insert fail the tenant_id NOT NULL constraint.

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Platform Owner',
            'email' => 'admin@example.com',
            'role' => 'super_admin',
        ]);

        $this->call(TenantSeeder::class);

        // One tenant-admin login per demo tenant.
        User::factory()->create([
            'name' => 'Solo Chamber Staff',
            'email' => 'solo@example.com',
            'role' => 'tenant_admin',
            'tenant_id' => 'demo-solo',
        ]);

        User::factory()->create([
            'name' => 'Clinic Staff',
            'email' => 'clinic@example.com',
            'role' => 'tenant_admin',
            'tenant_id' => 'demo-clinic',
        ]);
    }
}
