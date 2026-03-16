<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\DamageType;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopOperator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder de prueba para la API móvil.
 * Crea talleres, operarios, actividades, tipos de daño y usuarios de prueba.
 *
 * Requisito: debe haber al menos una empresa en la tabla companies.
 * Ejecutar antes: php artisan db:seed --class=CompanySeeder
 *
 * Uso: php artisan db:seed --class=MobileApiSeeder
 */
class MobileApiSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = Company::value('id');
        if (!$companyId) {
            $this->command->error('No hay empresas en la BD. Ejecutar CompanySeeder primero.');
            return;
        }
        // ── Permisos ────────────────────────────────────────────────────
        $permissions = [
            'workshops.view',
            'workshops.sync',
            'operations.create',
            'operations.view',
            'damaged_garments.create',
            'damaged_garments.view',
            'orders.view',
            'orders.update_status',
            'catalog.view',
            'profile.view',
            'evidences.create',
            'evidences.view',
            'evidences.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ───────────────────────────────────────────────────────
        $adminRole    = Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        $operatorRole = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);

        $adminRole->syncPermissions($permissions);
        $supervisorRole->syncPermissions($permissions);
        $operatorRole->syncPermissions([
            'workshops.view', 'workshops.sync',
            'operations.create', 'operations.view',
            'damaged_garments.create', 'damaged_garments.view',
            'orders.view',
            'catalog.view',
            'profile.view',
            'evidences.create', 'evidences.view',
        ]);

        // ── Talleres ────────────────────────────────────────────────────
        // Sin company_id en la tabla — la relación es vía company_workshops
        $workshop1 = Workshop::firstOrCreate(
            ['code' => 'TCN-001'],
            [
                'name'              => 'Taller Norte',
                'address'           => 'Calle 45 #12-30, Bogotá',
                'coordinator_name'  => 'María López',
                'coordinator_phone' => '3001234567',
                'status'            => 'active',
            ]
        );

        $workshop2 = Workshop::firstOrCreate(
            ['code' => 'TCS-002'],
            [
                'name'              => 'Taller Sur',
                'address'           => 'Carrera 10 #5-20, Medellín',
                'coordinator_name'  => 'Carlos Rivera',
                'coordinator_phone' => '3107654321',
                'status'            => 'active',
            ]
        );

        $workshop3 = Workshop::firstOrCreate(
            ['code' => 'TCO-003'],
            [
                'name'              => 'Taller Oriente',
                'address'           => 'Av. 68 #90-10, Cali',
                'coordinator_name'  => 'Ana Torres',
                'coordinator_phone' => '3209876543',
                'status'            => 'active',
            ]
        );

        // Vincular los 3 talleres a la empresa (multi-compañía)
        $workshop1->companies()->syncWithoutDetaching([$companyId]);
        $workshop2->companies()->syncWithoutDetaching([$companyId]);
        $workshop3->companies()->syncWithoutDetaching([$companyId]);

        // ── Operarios ───────────────────────────────────────────────────
        $operators = [
            ['workshop_id' => $workshop1->id, 'name' => 'Pedro Gómez',    'code' => 'OP-001'],
            ['workshop_id' => $workshop1->id, 'name' => 'Luisa Martínez', 'code' => 'OP-002'],
            ['workshop_id' => $workshop1->id, 'name' => 'Jorge Ruiz',     'code' => 'OP-003'],
            ['workshop_id' => $workshop2->id, 'name' => 'Sandra Peña',    'code' => 'OP-004'],
            ['workshop_id' => $workshop2->id, 'name' => 'Miguel Díaz',    'code' => 'OP-005'],
            ['workshop_id' => $workshop3->id, 'name' => 'Claudia Vega',   'code' => 'OP-006'],
            ['workshop_id' => $workshop3->id, 'name' => 'Andrés Mora',    'code' => 'OP-007'],
        ];

        foreach ($operators as $op) {
            WorkshopOperator::firstOrCreate(
                ['code' => $op['code']],
                array_merge($op, ['is_active' => true])
            );
        }

        // ── Actividades (catálogo) ──────────────────────────────────────
        $activities = [
            ['code' => 'ACT-001', 'name' => 'Costura básica',        'unit_price' => 1500.00],
            ['code' => 'ACT-002', 'name' => 'Corte de tela',         'unit_price' => 1200.00],
            ['code' => 'ACT-003', 'name' => 'Bordado',               'unit_price' => 3500.00],
            ['code' => 'ACT-004', 'name' => 'Estampado',             'unit_price' => 2000.00],
            ['code' => 'ACT-005', 'name' => 'Confección camisetas',  'unit_price' => 4500.00],
            ['code' => 'ACT-006', 'name' => 'Ojales y botones',      'unit_price' => 800.00],
            ['code' => 'ACT-007', 'name' => 'Control de calidad',    'unit_price' => 500.00],
            ['code' => 'ACT-008', 'name' => 'Empaque y doblado',     'unit_price' => 600.00],
        ];

        foreach ($activities as $act) {
            Activity::firstOrCreate(
                ['code' => $act['code']],
                array_merge($act, ['company_id' => $companyId, 'is_active' => true])
            );
        }

        // ── Tipos de daño ───────────────────────────────────────────────
        $damageTypes = [
            ['code' => 'DMG-001', 'name' => 'Costura rota',           'description' => 'Hilos sueltos o rotos en costuras'],
            ['code' => 'DMG-002', 'name' => 'Mancha de tela',         'description' => 'Manchas visibles en la tela'],
            ['code' => 'DMG-003', 'name' => 'Corte incorrecto',       'description' => 'Medidas incorrectas en el corte'],
            ['code' => 'DMG-004', 'name' => 'Estampado defectuoso',   'description' => 'Problemas con el estampado o serigrafía'],
            ['code' => 'DMG-005', 'name' => 'Tela rasgada',           'description' => 'Rasgaduras o perforaciones en la tela'],
            ['code' => 'DMG-006', 'name' => 'Bordado incompleto',     'description' => 'Bordado con hilos faltantes o mal ubicado'],
            ['code' => 'DMG-007', 'name' => 'Botón faltante',         'description' => 'Falta uno o más botones u ojales'],
        ];

        foreach ($damageTypes as $dt) {
            DamageType::firstOrCreate(
                ['code' => $dt['code']],
                array_merge($dt, ['company_id' => $companyId, 'is_active' => true])
            );
        }

        // ── Usuarios de prueba ──────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@sysworks.test'],
            [
                'name'       => 'Administrador',
                'password'   => Hash::make('admin123'),
                'company_id' => $companyId,
            ]
        );
        $admin->assignRole('admin');
        $admin->workshops()->syncWithoutDetaching([$workshop1->id, $workshop2->id, $workshop3->id]);

        $operator = User::firstOrCreate(
            ['email' => 'operario@sysworks.test'],
            [
                'name'       => 'Juan Operario',
                'password'   => Hash::make('operario123'),
                'company_id' => $companyId,
            ]
        );
        $operator->assignRole('operator');
        $operator->workshops()->syncWithoutDetaching([$workshop1->id]);

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@sysworks.test'],
            [
                'name'       => 'Laura Supervisora',
                'password'   => Hash::make('super123'),
                'company_id' => $companyId,
            ]
        );
        $supervisor->assignRole('supervisor');
        $supervisor->workshops()->syncWithoutDetaching([$workshop1->id, $workshop2->id]);

        $this->command->info('✅ MobileApiSeeder completado.');
        $this->command->table(
            ['Email', 'Password', 'Rol', 'Talleres'],
            [
                ['admin@sysworks.test',      'admin123',    'admin',      'Taller Norte, Sur, Oriente'],
                ['operario@sysworks.test',   'operario123', 'operator',   'Taller Norte'],
                ['supervisor@sysworks.test', 'super123',    'supervisor', 'Taller Norte, Sur'],
            ]
        );
    }
}
