<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class TwoFactorRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder enables two-factor authentication for specific roles
     * that require higher security, such as Administrator and sysadmin.
     */
    public function run(): void
    {
        // Roles that should require two-factor authentication
        $rolesRequiring2FA = [
            'Administrator',
            'sysadmin',
        ];

        foreach ($rolesRequiring2FA as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->update(['requires_two_factor' => true]);
                $this->command->info("2FA enabled for role: {$roleName}");
            } else {
                $this->command->warn("Role not found: {$roleName}");
            }
        }

        $this->command->info('Two-factor authentication configuration completed.');
    }
}
