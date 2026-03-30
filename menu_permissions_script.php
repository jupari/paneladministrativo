<?php
// Script independiente para poblar la tabla permissions con los permisos del menú
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Permission;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Boot Laravel
$kernel->bootstrap();

$menu = config('adminlte.menu');

function extractPermissions(array $menu): array {
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
            $perms = array_merge($perms, extractPermissions($item['submenu']));
        }
    }
    return $perms;
}

$permissions = extractPermissions($menu);
$permissions = array_unique($permissions);
$permissions = array_filter($permissions, fn($p) => !empty($p));

foreach ($permissions as $perm) {
    Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    echo "Permiso creado/actualizado: $perm\n";
}

echo "Permisos del menú creados/actualizados correctamente.\n";
