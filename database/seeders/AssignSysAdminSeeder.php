<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignSysAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Encontrar el primer usuario o crear uno de prueba
        $user = User::first();

        if (!$user) {
            // Crear usuario sysadmin de prueba si no existe ningún usuario
            $user = User::create([
                'name' => 'SysAdmin User',
                'email' => 'sysadmin@empresa.com',
                'password' => bcrypt('Password123'),
                'identificacion' => '12345678',
                'active' => 1,
            ]);
        }

        // Asegurar que el rol sysadmin existe
        $sysadminRole = Role::firstOrCreate(['name' => 'sysadmin'], ['guard_name' => 'web']);

        // Asignar rol sysadmin al usuario
        if (!$user->hasRole('sysadmin')) {
            $user->assignRole('sysadmin');
            $this->command->info("Rol sysadmin asignado al usuario: {$user->name} ({$user->email})");
        } else {
            $this->command->info("El usuario {$user->name} ya tiene rol sysadmin");
        }
    }
}
