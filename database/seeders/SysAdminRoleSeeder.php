<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SysAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol sysadmin si no existe
        $sysadminRole = Role::firstOrCreate([
            'name' => 'sysadmin',
        ], [
            'guard_name' => 'web',
        ]);

        // Permisos específicos para gestión de empresas
        $companyPermissions = [
            'companies.index',
            'companies.create', 
            'companies.edit',
            'companies.destroy',
            'companies.show',
        ];

        // Permisos específicos para gestión de roles y permisos
        $rolePermissionPermissions = [
            'roles.index',
            'roles.create',
            'roles.edit', 
            'roles.destroy',
            'permission.index',
            'permission.create',
            'permission.edit',
            'permission.destroy',
        ];

        // Crear todos los permisos si no existen
        $allPermissions = array_merge($companyPermissions, $rolePermissionPermissions);
        
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
            ], [
                'guard_name' => 'web',
            ]);
        }

        // Asignar todos los permisos al rol sysadmin
        $sysadminRole->syncPermissions($allPermissions);

        $this->command->info('Rol sysadmin creado/actualizado con todos los permisos de empresas y roles/permisos.');
    }
}