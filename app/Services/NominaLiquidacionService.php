<?php

namespace App\Services;

use App\Models\Cargo;
use App\Models\NominaParametrosGlobal;

/**
 * Motor de Liquidación de Nómina para Cotizaciones.
 *
 * Calcula el costo empresa mensual de un cargo con las novedades estimadas,
 * aplicando la legislación laboral colombiana vigente (CST + Ley 1607).
 *
 * Porcentajes de Seguridad Social (Decreto 723/2013 y normas concordantes):
 *   Salud empleado:    4.00%   Salud empleador:   8.50% (exonerable Ley 1607)
 *   Pensión empleado:  4.00%   Pensión empleador: 12.00%
 *   SENA empleador:    2.00%   (exonerable Ley 1607)
 *   ICBF empleador:    3.00%   (exonerable Ley 1607)
 *   Caja Compensación: 4.00%
 *
 * Factores CST (Art. 168, 179, 180):
 *   Recargo nocturno (RN):               +35%  → ×1.35 sobre salario día
 *   Hora extra diurna (HED):             ×1.25 sobre salario hora
 *   Hora extra nocturna (HEN):           ×1.75 sobre salario hora
 *   Trabajo dominical/festivo diurno:    ×1.75 sobre salario día
 *   Trabajo dominical/festivo nocturno:  ×2.10 sobre salario día
 *   HED dominical (HEDD):                ×2.00 sobre salario hora
 *   HEN dominical (HEDN):                ×2.50 sobre salario hora
 */
class NominaLiquidacionService
{
    // ── Seguridad Social ─────────────────────────────────────────────────
    const PCT_SALUD_EMPLEADO    = 0.04;
    const PCT_PENSION_EMPLEADO  = 0.04;
    const PCT_SALUD_EMPLEADOR   = 0.085;
    const PCT_PENSION_EMPLEADOR = 0.12;
    const PCT_SENA              = 0.02;
    const PCT_ICBF              = 0.03;
    const PCT_CAJA              = 0.04;

    // ── Prestaciones Sociales ────────────────────────────────────────────
    const PCT_PRIMA             = 1 / 12;    // 8.333%
    const PCT_CESANTIAS         = 1 / 12;    // 8.333%
    const PCT_INT_CESANTIAS     = 0.12 / 12; // 1% mensual sobre cesantías
    const PCT_VACACIONES        = 15 / 360;  // 4.167%

    // ── Factores CST ────────────────────────────────────────────────────
    const FACTOR_RN             = 1.35;
    const FACTOR_HED            = 1.25;
    const FACTOR_HEN            = 1.75;
    const FACTOR_TDD            = 1.75;
    const FACTOR_TDN            = 2.10;
    const FACTOR_HEDD           = 2.00;
    const FACTOR_HEDN           = 2.50;

    /**
     * Calcula la liquidación de nómina para un cargo con novedades estimadas.
     *
     * @param array $input {
     *   cargo_id:                  int,
     *   año:                       int,
     *   cantidad_personas:         int,
     *   dias_diurnos:              int,
     *   dias_nocturnos:            int  (default 0),
     *   dominicales_diurnos:       int  (default 0),
     *   dominicales_nocturnos:     int  (default 0),
     *   horas_extra_diurnas:       int  (default 0),
     *   horas_extra_nocturnas:     int  (default 0),
     *   horas_extra_dom_diurnas:   int  (default 0),
     *   horas_extra_dom_nocturnas: int  (default 0),
     * }
     * @return array OutputLiquidacion
     */
    public function calcular(array $input): array
    {
        // ── PASO 1: RESOLUCIÓN DE PARÁMETROS ────────────────────────────
        $params  = NominaParametrosGlobal::paraAno((int)$input['año']);
        $cargo   = Cargo::findOrFail((int)$input['cargo_id']);

        $salBase = $cargo->salario_base !== null
            ? (float)$cargo->salario_base
            : (float)$params->smlv;

        $salHora = round($salBase / 240, 4);  // 30 días × 8 horas
        $salDia  = round($salBase / 30, 4);

        $arlNivel      = (int)($cargo->arl_nivel ?? 1);
        $arlPorcentaje = $this->getPorcentajeArl($arlNivel);

        $cantPersonas = max(1, (int)($input['cantidad_personas'] ?? 1));

        // Novedades (default 0)
        $diasDiurnos          = (int)($input['dias_diurnos']              ?? 0);
        $diasNocturnos        = (int)($input['dias_nocturnos']            ?? 0);
        $domDiurnos           = (int)($input['dominicales_diurnos']       ?? 0);
        $domNocturnos         = (int)($input['dominicales_nocturnos']     ?? 0);
        $hedHoras             = (int)($input['horas_extra_diurnas']       ?? 0);
        $henHoras             = (int)($input['horas_extra_nocturnas']     ?? 0);
        $heddHoras            = (int)($input['horas_extra_dom_diurnas']   ?? 0);
        $hednHoras            = (int)($input['horas_extra_dom_nocturnas'] ?? 0);

        // ── PASO 2: DEVENGADOS ───────────────────────────────────────────
        $devOrdinario     = $diasDiurnos * $salDia;
        $devRN            = $diasNocturnos * $salDia * self::FACTOR_RN;
        $devDomDiurno     = $domDiurnos * $salDia * self::FACTOR_TDD;
        $devDomNocturno   = $domNocturnos * $salDia * self::FACTOR_TDN;
        $devHED           = $hedHoras * $salHora * self::FACTOR_HED;
        $devHEN           = $henHoras * $salHora * self::FACTOR_HEN;
        $devHEDD          = $heddHoras * $salHora * self::FACTOR_HEDD;
        $devHEDN          = $hednHoras * $salHora * self::FACTOR_HEDN;

        $devSalarial = $devOrdinario + $devRN + $devDomDiurno + $devDomNocturno
                     + $devHED + $devHEN + $devHEDD + $devHEDN;

        $diasLaborados  = $diasDiurnos + $diasNocturnos + $domDiurnos + $domNocturnos;
        $diasLaborados  = max($diasLaborados, 1); // evitar división por 0

        $auxTransporte      = ($salBase <= $params->smlv * 2) ? (float)$params->aux_transporte : 0.0;
        $auxTransporteProp  = round($auxTransporte * ($diasLaborados / 30));

        $totalDevengado = round($devSalarial + $auxTransporteProp);

        // ── PASO 3: IBC ──────────────────────────────────────────────────
        // El auxilio de transporte NO forma parte del IBC
        $ibc = max(
            min(round($devSalarial), $params->smlv * 25),
            (float)$params->smlv
        );

        // ── PASO 4: EXONERACIÓN LEY 1607 ────────────────────────────────
        $aplica = $cargo->aplica_exoneracion_ley1607 ?? true;
        $esExonerado = (bool)$aplica
                    && ($salBase < $params->smlv * $params->tope_exoneracion_ley1607);

        // ── PASO 5: DEDUCCIONES EMPLEADO ────────────────────────────────
        $dedSaludEmp   = round($ibc * self::PCT_SALUD_EMPLEADO);
        $dedPensionEmp = round($ibc * self::PCT_PENSION_EMPLEADO);
        $totalDedEmp   = $dedSaludEmp + $dedPensionEmp;

        $netoEmpleado = $totalDevengado - $totalDedEmp;

        // ── PASO 6: SEGURIDAD SOCIAL EMPLEADOR ──────────────────────────
        $costoSaludEmp   = $esExonerado ? 0 : round($ibc * self::PCT_SALUD_EMPLEADOR);
        $costoPensionEmp = round($ibc * self::PCT_PENSION_EMPLEADOR);
        $costoArl        = round($ibc * $arlPorcentaje / 100);
        $subtotalSS      = $costoSaludEmp + $costoPensionEmp + $costoArl;

        // ── PASO 7: PARAFISCALES ────────────────────────────────────────
        $costoSena   = $esExonerado ? 0 : round($ibc * self::PCT_SENA);
        $costoIcbf   = $esExonerado ? 0 : round($ibc * self::PCT_ICBF);
        $costoCaja   = round($ibc * self::PCT_CAJA);
        $subtotalPara = $costoSena + $costoIcbf + $costoCaja;

        // ── PASO 8: PRESTACIONES SOCIALES ───────────────────────────────
        // Base cesantías y prima incluye aux. transporte (Art. 249 CST)
        $basePrestaciones = round($devSalarial + $auxTransporteProp);
        // Base vacaciones: solo componente salarial ordinario (sin recargos)
        $baseVacaciones   = round($salDia * $diasLaborados);

        $provPrima        = round($basePrestaciones * self::PCT_PRIMA);
        $provCesantias    = round($basePrestaciones * self::PCT_CESANTIAS);
        $provIntCesantias = round($provCesantias * self::PCT_INT_CESANTIAS);
        $provVacaciones   = round($baseVacaciones * self::PCT_VACACIONES);
        $subtotalProv     = $provPrima + $provCesantias + $provIntCesantias + $provVacaciones;

        // ── PASO 9: COSTO EMPRESA ────────────────────────────────────────
        $costoEmpresaMes   = round($totalDevengado + $subtotalSS + $subtotalPara + $subtotalProv);
        $costoEmpresaTotal = $costoEmpresaMes * $cantPersonas;

        return [
            'cargo' => [
                'id'        => $cargo->id,
                'nombre'    => $cargo->nombre,
                'arl_nivel' => $arlNivel,
            ],
            'parametros' => [
                'año'            => $input['año'],
                'smlv'           => $params->smlv,
                'aux_transporte' => $params->aux_transporte,
            ],
            'devengados' => [
                'salario_ordinario'          => round($devOrdinario),
                'recargo_nocturno'           => round($devRN),
                'dominicales_diurnos'        => round($devDomDiurno),
                'dominicales_nocturnos'      => round($devDomNocturno),
                'horas_extra_diurnas'        => round($devHED),
                'horas_extra_nocturnas'      => round($devHEN),
                'horas_extra_dom_diurnas'    => round($devHEDD),
                'horas_extra_dom_nocturnas'  => round($devHEDN),
                'aux_transporte_proporcional'=> $auxTransporteProp,
                'total_devengado'            => $totalDevengado,
            ],
            'ibc' => $ibc,
            'deducciones_empleado' => [
                'salud'   => $dedSaludEmp,
                'pension' => $dedPensionEmp,
                'total'   => $totalDedEmp,
            ],
            'neto_empleado' => $netoEmpleado,
            'costo_empleador' => [
                'seguridad_social' => [
                    'salud'   => $costoSaludEmp,
                    'pension' => $costoPensionEmp,
                    'arl'     => $costoArl,
                    'subtotal'=> $subtotalSS,
                ],
                'parafiscales' => [
                    'sena'    => $costoSena,
                    'icbf'    => $costoIcbf,
                    'caja'    => $costoCaja,
                    'subtotal'=> $subtotalPara,
                ],
                'provisiones' => [
                    'prima'              => $provPrima,
                    'cesantias'          => $provCesantias,
                    'intereses_cesantias'=> $provIntCesantias,
                    'vacaciones'         => $provVacaciones,
                    'subtotal'           => $subtotalProv,
                ],
            ],
            'costo_empresa_mes'        => $costoEmpresaMes,
            'cantidad_personas'        => $cantPersonas,
            'costo_empresa_total'      => $costoEmpresaTotal,
            'es_exonerado_ley1607'     => $esExonerado,
            'arl_nivel'                => $arlNivel,
            'arl_porcentaje'           => $arlPorcentaje,
        ];
    }

    /**
     * Retorna el porcentaje ARL según el nivel (1–5).
     */
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
