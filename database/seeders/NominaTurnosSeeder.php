<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NominaTurnosSeeder extends Seeder
{
    public function run(): void
    {
        $turnos = [
            [
                'nombre'                => 'Turno Diurno',
                'descripcion'           => 'Jornada ordinaria diurna. Horas de trabajo entre 6am y 10pm.',
                'tipo_ordinaria'        => 'diurna',
                'es_dominical_festivo'  => false,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => true,
                'tiene_extras_nocturnas'=> false,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
            [
                'nombre'                => 'Turno Nocturno',
                'descripcion'           => 'Jornada ordinaria nocturna. Horas de trabajo entre 10pm y 6am.',
                'tipo_ordinaria'        => 'nocturna',
                'es_dominical_festivo'  => false,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => false,
                'tiene_extras_nocturnas'=> true,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
            [
                'nombre'                => 'Turno Mixto (día → noche)',
                'descripcion'           => 'Jornada que inicia en horas diurnas y termina en nocturnas.',
                'tipo_ordinaria'        => 'diurna',
                'es_dominical_festivo'  => false,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => false,
                'tiene_extras_nocturnas'=> true,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
            [
                'nombre'                => 'Turno Sábado',
                'descripcion'           => 'Jornada de sábado en horario diurno.',
                'tipo_ordinaria'        => 'diurna',
                'es_dominical_festivo'  => false,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => true,
                'tiene_extras_nocturnas'=> false,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
            [
                'nombre'                => 'Turno Dominical Diurno',
                'descripcion'           => 'Trabajo en domingo o festivo en horario diurno. Aplica recargo del 75%.',
                'tipo_ordinaria'        => 'diurna',
                'es_dominical_festivo'  => true,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => true,
                'tiene_extras_nocturnas'=> false,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
            [
                'nombre'                => 'Turno Dominical Nocturno',
                'descripcion'           => 'Trabajo en domingo o festivo en horario nocturno. Aplica recargo del 110%.',
                'tipo_ordinaria'        => 'nocturna',
                'es_dominical_festivo'  => true,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => false,
                'tiene_extras_nocturnas'=> true,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
            [
                'nombre'                => 'Turno Festivo Diurno',
                'descripcion'           => 'Trabajo en día festivo en horario diurno.',
                'tipo_ordinaria'        => 'diurna',
                'es_dominical_festivo'  => true,
                'max_horas_ordinarias'  => 7,
                'tiene_extras_diurnas'  => true,
                'tiene_extras_nocturnas'=> false,
                'max_horas_extras'      => 2,
                'active'                => true,
            ],
        ];

        foreach ($turnos as $turno) {
            DB::table('nom_turnos')->updateOrInsert(
                ['nombre' => $turno['nombre']],
                array_merge($turno, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
