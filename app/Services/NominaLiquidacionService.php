<?php

namespace App\Services;

use App\Models\Cargo;
use App\Models\NominaArlNivel;
use App\Models\NominaParametrosGlobal;
use Illuminate\Support\Facades\DB;

/**
 * Motor de Liquidacion de Nomina para Cotizaciones.
 *
 * Calcula el costo de cotizacion por horas trabajadas usando las tarifas
 * pre-calculadas de cargos_tabla_precios (SS + prestaciones + utilidad ya incluidos).
 */
class NominaLiquidacionService
{
    /**
     * Calcula el costo de cotizacion para un cargo con horas trabajadas.
     *
     * Todos los campos "dias_*" y "dominicales_*" representan HORAS, no dias.
     *
     * @param array $input {
     *   cargo_id, anio, cantidad_personas,
     *   dias_diurnos (horas ordinarias diurnas),
     *   dias_nocturnos (horas nocturnas +35%),
     *   dominicales_diurnos (horas dom/fest diurnas +75%),
     *   dominicales_nocturnos (horas dom/fest nocturnas +110%),
     *   horas_extra_diurnas (HED x1.25),
     *   horas_extra_nocturnas (HEN x1.75),
     *   horas_extra_dom_diurnas (HEDD x2.00),
     *   horas_extra_dom_nocturnas (HEDN x2.50),
     * }
     */
    public function calcular(array $input): array
    {
        // PASO 1: Resolver parametros base
        $params   = NominaParametrosGlobal::paraAno((int)($input['año'] ?? date('Y')));
        $cargo    = Cargo::findOrFail((int)$input['cargo_id']);
        $salBase  = $cargo->salario_base !== null
            ? (float)$cargo->salario_base
            : (float)$params->smlv;

        $arlNivel      = (int)($cargo->arl_nivel ?? 1);
        $arlPorcentaje = $this->getPorcentajeArl($arlNivel);
        $cantPersonas  = max(1, (int)($input['cantidad_personas'] ?? 1));

        // Inputs: en modo "Costo Hora" TODOS los campos son HORAS, no dias
        $horasDiurnas   = (int)($input['dias_diurnos']              ?? 0);
        $horasNocturnas = (int)($input['dias_nocturnos']            ?? 0);
        $horasDomDiu    = (int)($input['dominicales_diurnos']       ?? 0);
        $horasDomNoc    = (int)($input['dominicales_nocturnos']     ?? 0);
        $hedHoras       = (int)($input['horas_extra_diurnas']       ?? 0);
        $henHoras       = (int)($input['horas_extra_nocturnas']     ?? 0);
        $heddHoras      = (int)($input['horas_extra_dom_diurnas']   ?? 0);
        $hednHoras      = (int)($input['horas_extra_dom_nocturnas'] ?? 0);

        // PASO 2: Tarifas desde cargos_tabla_precios
        // La tabla almacena el precio por hora ya con SS + prestaciones + utilidad.
        $tp = DB::table('cargos_tabla_precios')->where('cargo_id', $cargo->id)->first();

        if ($tp) {
            $tarifaOrdinaria = (float)$tp->hora_ordinaria;
            $tarifaRN        = (float)$tp->recargo_nocturno;
            $tarifaDomDiu    = (float)$tp->hora_dominical;
            $tarifaHED       = (float)$tp->hora_extra_diurna;
            $tarifaHEN       = (float)$tp->hora_extra_nocturna;
            $tarifaHEDD      = (float)$tp->hora_extra_dominical_diurna;
            $tarifaHEDN      = (float)$tp->hora_extra_dominical_nocturna;
        } else {
            // Fallback cuando el cargo no tiene registro en la tabla
            $salHoraFb       = round($salBase / 240, 4);
            $tarifaOrdinaria = $salHoraFb;
            $tarifaRN        = round($salHoraFb * 0.35, 4);
            $tarifaDomDiu    = round($salHoraFb * 1.75, 4);
            $tarifaHED       = round($salHoraFb * 1.25, 4);
            $tarifaHEN       = round($salHoraFb * 1.75, 4);
            $tarifaHEDD      = round($salHoraFb * 2.00, 4);
            $tarifaHEDN      = round($salHoraFb * 2.50, 4);
        }

        // PASO 3: Costo total = horas x tarifa
        $costoOrdDiu  = round($horasDiurnas   * $tarifaOrdinaria);
        $costoOrdNoc  = round($horasNocturnas * ($tarifaOrdinaria + $tarifaRN));
        $costoDomDiu  = round($horasDomDiu    * $tarifaDomDiu);
        $costoDomNoc  = round($horasDomNoc    * ($tarifaDomDiu    + $tarifaRN));
        $costoHED     = round($hedHoras       * $tarifaHED);
        $costoHEN     = round($henHoras       * $tarifaHEN);
        $costoHEDD    = round($heddHoras      * $tarifaHEDD);
        $costoHEDN    = round($hednHoras      * $tarifaHEDN);

        $costoEmpresaMes   = $costoOrdDiu + $costoOrdNoc + $costoDomDiu + $costoDomNoc
                           + $costoHED + $costoHEN + $costoHEDD + $costoHEDN;
        $costoEmpresaTotal = $costoEmpresaMes * $cantPersonas;

        return [
            'cargo' => [
                'id'        => $cargo->id,
                'nombre'    => $cargo->nombre,
                'arl_nivel' => $arlNivel,
            ],
            'parametros' => [
                'año'            => $input['año'] ?? date('Y'),
                'smlv'           => 0,
                'aux_transporte' => 0,
            ],
            'devengados' => [
                'salario_ordinario'           => $costoOrdDiu,
                'recargo_nocturno'            => $costoOrdNoc,
                'dominicales_diurnos'         => $costoDomDiu,
                'dominicales_nocturnos'       => $costoDomNoc,
                'horas_extra_diurnas'         => $costoHED,
                'horas_extra_nocturnas'       => $costoHEN,
                'horas_extra_dom_diurnas'     => $costoHEDD,
                'horas_extra_dom_nocturnas'   => $costoHEDN,
                'aux_transporte_proporcional' => 0,
                'total_devengado'             => $costoEmpresaMes,
            ],
            'ibc'  => 0,
            'deducciones_empleado' => ['salud' => 0, 'pension' => 0, 'total' => 0],
            'neto_empleado'        => $costoEmpresaMes,
            'costo_empleador' => [
                'seguridad_social' => ['salud' => 0, 'pension' => 0, 'arl' => 0, 'subtotal' => 0],
                'parafiscales'     => ['sena' => 0, 'icbf' => 0, 'caja' => 0, 'subtotal' => 0],
                'provisiones'      => [
                    'prima' => 0, 'cesantias' => 0,
                    'intereses_cesantias' => 0, 'vacaciones' => 0, 'subtotal' => 0,
                ],
            ],
            'costo_empresa_mes'    => $costoEmpresaMes,
            'cantidad_personas'    => $cantPersonas,
            'costo_empresa_total'  => $costoEmpresaTotal,
            'es_exonerado_ley1607' => false,
            'arl_nivel'            => $arlNivel,
            'arl_porcentaje'       => $arlPorcentaje,
            'fuente_calculo'       => $tp ? 'cargos_tabla_precios' : 'salario_base',
        ];
    }

    private function getPorcentajeArl(int $nivel): float
    {
        return match($nivel) {
            1 => 0.5220,
            2 => 1.0440,
            3 => 2.4360,
            4 => 4.3500,
            5 => 6.9600,
            default => 0.5220,
        };
    }
}
