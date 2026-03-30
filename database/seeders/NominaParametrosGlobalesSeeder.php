<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NominaParametrosGlobalesSeeder extends Seeder
{
    public function run(): void
    {
        $parametros = [
            [
                'vigencia'                  => 2024,
                'smlv'                      => 1300000.00,
                'aux_transporte'            => 162000.00,
                'uvt'                       => 47065.00,
                'tope_exoneracion_ley1607'  => 10,
                'active'                    => true,
            ],
            [
                'vigencia'                  => 2025,
                'smlv'                      => 1423500.00,
                'aux_transporte'            => 200000.00,
                'uvt'                       => 49799.00,
                'tope_exoneracion_ley1607'  => 10,
                'active'                    => true,
            ],
            [
                'vigencia'                  => 2026,
                'smlv'                      => 1423500.00, // Actualizar cuando se publique decreto
                'aux_transporte'            => 200000.00,
                'uvt'                       => 49799.00,
                'tope_exoneracion_ley1607'  => 10,
                'active'                    => true,
            ],
        ];

        foreach ($parametros as $parametro) {
            DB::table('nom_parametros_globales')->updateOrInsert(
                ['vigencia' => $parametro['vigencia']],
                array_merge($parametro, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
