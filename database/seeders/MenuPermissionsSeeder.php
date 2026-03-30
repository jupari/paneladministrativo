<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class MenuPermissionsSeeder extends Seeder
{
    /**
     * Extrae recursivamente los permisos de la estructura del menú.
     */
    private function extractPermissions(array $menu): array
    {
        $perms = [];
        foreach ($menu as $item) {
            if (isset($item['can'])) {
                if (is_array($item['can'])) {
                    $perms = array_merge($perms, $item['can']);
                } else {
                    $perms[] = $item['can'];
                }
            }
            if (isset($item['submenu'])) {
                $perms = array_merge($perms, $this->extractPermissions($item['submenu']));
            }
        }
        return $perms;
    }

    public function run(): void
    {
        $menu = config('adminlte.menu');
        $permissions = $this->extractPermissions($menu);
        $permissions = array_unique($permissions);
        $permissions = array_filter($permissions, fn($p) => !empty($p));

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->command->info('Permisos del menú creados/actualizados correctamente.');
    }
}
