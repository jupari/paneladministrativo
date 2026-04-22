<?php

// app/Services/Contratos/TablaPreciosCargoService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TablaPreciosCargoService
{
    public function __construct(protected ParametroService $parametros) {}

    /**
     * Genera la tabla de precios por cargo basada en la parametrización y novedades.
     * Si $persistir es true, guarda los resultados en la tabla cargos_tabla_precios.
     *
     * @param bool $persistir
     * @return array Resultado con los cálculos por cargo.
     */
    public function generar(bool $persistir = true): array
    {
        $utilidad     = $this->parametros->getFloat('NOM_UTILIDAD_PCT', (float) config('app.utilidadPct', 0.315));
        $horasDiarias = $this->parametros->getInt('NOM_HORAS_DIARIAS', (int) config('app.horasDiarias', 8));
        $diasMes      = $this->parametros->getInt('NOM_DIAS_MES', 26);

        Log::debug('[TablaPreciosCargo] Inicio generar', [
            'persistir' => $persistir,
            'utilidad' => $utilidad,
            'horas_diarias' => $horasDiarias,
            'dias_mes' => $diasMes,
        ]);

        // Traer parametrización NOMINA + novedad_detalle + novedad (para totales)
        $rows = DB::table('parametrizacion as p')
            ->join('categorias as cat', 'cat.id', '=', 'p.categoria_id')
            ->join('cargos as c', 'c.id', '=', 'p.cargo_id')
            ->join('novedades_detalle as nd', 'nd.id', '=', 'p.novedad_detalle_id')
            ->join('novedades as n', 'n.id', '=', 'nd.novedad_id')
            ->where('p.active', 1)
            ->where('c.active', 1)
            ->whereRaw("UPPER(cat.nombre) = 'NOMINA'")
            ->select([
                'p.cargo_id',
                'c.nombre as cargo_nombre',
                'p.valor_porcentaje',
                'p.valor_admon',
                'p.valor_obra',
                'nd.nombre as detalle_nombre',
                'n.nombre as novedad_nombre',
                'n.total_admon',
                'n.total_operativo',
            ])
            ->get();

        Log::debug('[TablaPreciosCargo] Parametrizacion cargada', [
            'filas' => $rows->count(),
        ]);

        // Helpers
        $upper = fn($v) => mb_strtoupper(trim((string)$v));

        $toFloat = function ($v): float {
            if ($v === null) return 0.0;
            $s = trim((string)$v);
            if ($s === '') return 0.0;

            // soporta: "1.234.567,89" y "1234567.89"
            $s = str_replace([' ', "\t", "\n", "\r"], '', $s);

            // si viene con coma decimal
            if (str_contains($s, ',') && !str_contains($s, '.')) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } elseif (str_contains($s, ',') && str_contains($s, '.')) {
                // asume formato latino: miles '.' y decimal ','
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            }

            return (float)$s;
        };

        $pctNorm = function (float $raw): float {
            if ($raw == 0.0) return 0.0;
            return ($raw <= 1) ? $raw : ($raw / 100);
        };

        // Estructura por cargo
        $porCargo = [];

        foreach ($rows as $r) {
            $cargoId = (int)$r->cargo_id;

            if (!isset($porCargo[$cargoId])) {
                $porCargo[$cargoId] = [
                    'cargo_id' => $cargoId,
                    'cargo' => $r->cargo_nombre,

                    // B,C,D absolutos
                    'basico' => 0.0,
                    'aux_trans' => 0.0,
                    'auxilio' => 0.0,

                    // Porcentajes
                    'pct_cesantias' => 0.0,
                    'pct_int_cesantias' => 0.0,
                    'pct_prima' => 0.0,
                    'pct_vacaciones' => 0.0,
                    'pct_arp' => 0.0,
                    'pct_eps' => 0.0,
                    'pct_pension' => 0.0,
                    'pct_ccf' => 0.0,

                    // Totales por novedad (vp=0)
                    'viaticos_total' => 0.0,
                    'aliment_total' => 0.0,
                    'dotacion_total' => 0.0,
                    'examenes_total' => 0.0,

                    // auditoría por si algo no mapea
                    'otros_totales' => [],
                    'otros_porcentajes' => [],
                ];
            }

            $novedad = $upper($r->novedad_nombre);
            $detalle = $upper($r->detalle_nombre);

            $vpRaw = $toFloat($r->valor_porcentaje);

            // 1) DATOS BÁSICOS (NO porcentaje, NO totales, es valor directo)
            //    NOMINA / DATOS BÁSICOS - BASICO | AUX. DE TRANSPORTE | AUXILIO
            if ($novedad === 'DATOS BÁSICOS' || $novedad === 'DATOS BASICOS') {
                $valorAbs = $vpRaw;

                if ($detalle === 'BASICO' || $detalle === 'BÁSICO') {
                    $porCargo[$cargoId]['basico'] = $valorAbs;
                    Log::debug('[TablaPreciosCargo] DATOS BÁSICOS - BASICO', [
                        'cargo_id' => $cargoId,
                        'valor_abs' => $valorAbs,
                    ]);
                } elseif (str_contains($detalle, 'AUX') && str_contains($detalle, 'TRANSP')) {
                    $porCargo[$cargoId]['aux_trans'] = $valorAbs;
                    Log::debug('[TablaPreciosCargo] DATOS BÁSICOS - AUX. DE TRANSPORTE', [
                        'cargo_id' => $cargoId,
                        'valor_abs' => $valorAbs,
                    ]);
                } elseif ($detalle === 'AUXILIO') {
                    $porCargo[$cargoId]['auxilio'] = $valorAbs;
                    Log::debug('[TablaPreciosCargo] DATOS BÁSICOS - AUXILIO', [
                        'cargo_id' => $cargoId,
                        'valor_abs' => $valorAbs,
                    ]);
                }

                continue;
            }

            // 2) Regla general: si valor_porcentaje == 0 -> tomar totales de la novedad segun flags
            // 2a) Ítems de novedades de TOTALES FIJOS con valor absoluto (vpRaw > 0):
            //     DOTACIÓN, EXAMENES, VIATICOS y ALIMENTACION pueden parametrizarse con un
            //     valor monetario en valor_porcentaje por ítem (CAMISAS=70000, ALTURAS=65000…).
            //     Esos valores NO son porcentajes; se acumulan como absolutos en P/Q/N/O.
            $esNoveladTotalFijo = str_contains($novedad, 'DOTACI') || str_contains($novedad, 'DOTACION')
                || str_contains($novedad, 'EXAMEN')
                || str_contains($novedad, 'VIATIC')
                || str_contains($novedad, 'ALIMENT') || str_contains($novedad, 'ALIMENTACI');

            // 2) Novedades de totales fijos: usar el total pre-calculado (total_operativo)
            //    de la novedad en lugar de sumar los ítems individuales de valor_porcentaje.
            //    total_operativo es la fuente de verdad configurada en la novedad.
            if ($esNoveladTotalFijo) {
                if (str_contains($novedad, 'DOTACI') || str_contains($novedad, 'DOTACION')) {
                    $porCargo[$cargoId]['dotacion_total'] = $toFloat($r->total_operativo);
                } elseif (str_contains($novedad, 'EXAMEN')) {
                    $porCargo[$cargoId]['examenes_total'] = $toFloat($r->total_operativo);
                } elseif (str_contains($novedad, 'VIATIC')) {
                    $t = $toFloat($r->total_operativo);
                    if ($t > 0) $porCargo[$cargoId]['viaticos_total'] = $t;
                } elseif (str_contains($novedad, 'ALIMENT') || str_contains($novedad, 'ALIMENTACI')) {
                    $t = $toFloat($r->total_operativo);
                    if ($t > 0) $porCargo[$cargoId]['aliment_total'] = $t;
                }
                continue;
            }

            // 2b) Totales fijos con valor_porcentaje == 0 → usar total_admon/total_operativo
            //     de la novedad (valor agregado pre-calculado).
            if ($vpRaw == 0.0) {
                $total = 0.0;

                if ((int)$r->valor_admon === 1) {
                    $total += $toFloat($r->total_admon);
                }
                if ((int)$r->valor_obra === 1) {
                    $total += $toFloat($r->total_operativo);
                }

                // Mapear ese total al "concepto" correspondiente (por detalle o por novedad)
                // (ajusta estos contains si tus nombres exactos varían)
                if (str_contains($detalle, 'DOTACION') || $novedad === 'DOTACION') {
                    $porCargo[$cargoId]['dotacion_total'] = $total;
                } elseif (str_contains($detalle, 'EXAMEN') || $novedad === 'EXAMENES') {
                    $porCargo[$cargoId]['examenes_total'] = $total;
                } elseif (str_contains($detalle, 'VIATIC') || $novedad === 'VIATICOS') {
                    $porCargo[$cargoId]['viaticos_total'] = $total;
                } elseif (str_contains($detalle, 'ALIMENT') || $novedad === 'ALIMENTACION' || $novedad === 'ALIMENTACIÓN') {
                    $porCargo[$cargoId]['aliment_total'] = $total;
                } else {
                    $porCargo[$cargoId]['otros_totales'][] = [
                        'novedad' => $r->novedad_nombre,
                        'detalle' => $r->detalle_nombre,
                        'total' => $total,
                        'valor_admon' => (int)$r->valor_admon,
                        'valor_obra' => (int)$r->valor_obra,
                    ];
                }

                continue; // muy importante: no entrar a lógica de %
            }

            // 3) Si no es DATOS BÁSICOS y vp != 0 => es porcentaje
            $pct = $pctNorm($vpRaw);

            // Mapear porcentaje al campo correcto (según detalle / novedad)
            // (ajusta los contains si tus nombres exactos varían)
            if (str_contains($detalle, 'CESANT') && !str_contains($detalle, 'INT')) {
                $porCargo[$cargoId]['pct_cesantias'] = $pct;
            } elseif ((str_contains($detalle, 'INT') && str_contains($detalle, 'CESANT')) || str_contains($detalle, 'INTERES')) {
                $porCargo[$cargoId]['pct_int_cesantias'] = $pct;
            } elseif (str_contains($detalle, 'PRIMA')) {
                $porCargo[$cargoId]['pct_prima'] = $pct;
            } elseif (str_contains($detalle, 'VAC')) {
                $porCargo[$cargoId]['pct_vacaciones'] = $pct;
            } elseif (str_contains($detalle, 'ARP') || str_contains($detalle, 'ARL')) {
                $porCargo[$cargoId]['pct_arp'] = $pct;
            } elseif (str_contains($detalle, 'EPS')) {
                $porCargo[$cargoId]['pct_eps'] = $pct;
            } elseif (str_contains($detalle, 'PENSION') || str_contains($detalle, 'PENSIÓN')) {
                $porCargo[$cargoId]['pct_pension'] = $pct;
            } elseif (str_contains($detalle, 'CCF') || str_contains($detalle, 'CAJA')) {
                $porCargo[$cargoId]['pct_ccf'] = $pct;
            } else {
                $porCargo[$cargoId]['otros_porcentajes'][] = [
                    'novedad' => $r->novedad_nombre,
                    'detalle' => $r->detalle_nombre,
                    'pct' => $pct,
                ];
            }
        }

        // ── PRIORIDAD: campos configurados directamente en el cargo ──────────────
        // Fuente 1: cargos.salario_base   → overrides DATOS BÁSICOS - BASICO
        // Fuente 2: nom_parametros_globales.aux_transporte → overrides AUX. DE TRANSPORTE
        // Fuente 3: nom_arl_niveles[cargos.arl_nivel]     → siempre overrides pct_arp
        // Los demás conceptos (cesantías, prima, etc.) siguen desde parametrización.
        if (!empty($porCargo)) {
            $paramGlobal = \App\Models\NominaParametrosGlobal::paraAno((int)date('Y'));
            $arlNiveles  = \App\Models\NominaArlNivel::pluck('porcentaje', 'nivel'); // [1=>0.5220, ...]
            $cargosData  = \App\Models\Cargo::whereIn('id', array_keys($porCargo))
                ->select(['id', 'salario_base', 'arl_nivel'])
                ->get()
                ->keyBy('id');

            foreach ($porCargo as $cargoId => &$d) {
                $cargo = $cargosData[$cargoId] ?? null;
                if (!$cargo) continue;

                // B: salario_base del cargo tiene prioridad sobre DATOS BÁSICOS - BASICO
                if ($cargo->salario_base !== null) {
                    $d['basico'] = (float)$cargo->salario_base;

                    // C: aux_transporte global cuando salario_base está configurado
                    if ($paramGlobal) {
                        $aplica = ((float)$cargo->salario_base) <= ((float)$paramGlobal->smlv * 2);
                        $d['aux_trans'] = $aplica ? (float)$paramGlobal->aux_transporte : 0.0;
                        Log::debug('[TablaPreciosCargo] Aux. de transporte global', [
                            'cargo_id' => $cargoId,
                            'aplica' => $aplica,
                            'salario_base' => $cargo->salario_base,
                            'smlv' => $paramGlobal->smlv,
                            'aux_transporte' => $paramGlobal->aux_transporte,
                        ]);

                    }
                }

                // ARL: nivel configurado en el cargo siempre tiene prioridad sobre parametrización
                if (isset($arlNiveles[$cargo->arl_nivel])) {
                    $d['pct_arp'] = (float)$arlNiveles[$cargo->arl_nivel] / 100;
                }

                // EPS empleado (4%): usar tasa global cuando no está en la parametrización del cargo
                if ($d['pct_eps'] == 0.0) {
                    $d['pct_eps'] = $this->parametros->getFloat('NOM_PCT_SALUD_EMP', 0.04);
                }
            }
            unset($d); // romper referencia
        }

        // 4) Calcular igual que Excel + tabla precios
        $den = (1 - $utilidad);
        if ($den <= 0) $den = 1;

        // Factores de recargo/hora leídos desde la tabla elementos (configurables por empresa).
        // Valores por defecto según ley laboral colombiana si no están parametrizados.
        $factoresPrecio = [
            'hora_ordinaria'                => 1.0,
            // recargo_nocturno = solo el recargo adicional (35%), no el precio total de la hora (135%).
            // NOM_FACTOR_RN almacena el factor total (1.35); restamos 1 para obtener solo el delta.
            'recargo_nocturno'              => $this->parametros->getFloat('NOM_FACTOR_RN', 1.35) - 1.0,
            'hora_extra_diurna'             => $this->parametros->getFloat('NOM_FACTOR_HED',  1.25),
            'hora_extra_nocturna'           => $this->parametros->getFloat('NOM_FACTOR_HEN',  1.75),
            'hora_dominical'                => $this->parametros->getFloat('NOM_FACTOR_TDD',  1.75),
            'hora_extra_dominical_diurna'   => $this->parametros->getFloat('NOM_FACTOR_HEDD', 2.00),
            'hora_extra_dominical_nocturna' => $this->parametros->getFloat('NOM_FACTOR_HEDN', 2.50),
        ];

        $resultado = [];

        foreach ($porCargo as $cargoId => $d) {
            Log::debug('[TablaPreciosCargo] Calculo cargo', [
                'cargo_id' => $cargoId,
                'cargo' => $d['cargo'],
                'basico' => $d['basico'],
                'aux_trans' => $d['aux_trans'],
                'auxilio' => $d['auxilio'],
                'pct_cesantias' => $d['pct_cesantias'],
                'pct_int_cesantias' => $d['pct_int_cesantias'],
                'pct_prima' => $d['pct_prima'],
                'pct_vacaciones' => $d['pct_vacaciones'],
                'pct_arp' => $d['pct_arp'],
                'pct_eps' => $d['pct_eps'],
                'pct_pension' => $d['pct_pension'],
                'pct_ccf' => $d['pct_ccf'],
                'totales' => [
                    'viaticos_total' => $d['viaticos_total'],
                    'aliment_total' => $d['aliment_total'],
                    'dotacion_total' => $d['dotacion_total'],
                    'examenes_total' => $d['examenes_total'],
                ],
                'otros_totales' => $d['otros_totales'],
                'otros_porcentajes' => $d['otros_porcentajes'],
            ]);
            // B,C,D
            $B = round($d['basico'], 2);
            $C = round($d['aux_trans'], 2);
            $D = round($d['auxilio'], 2);

            // E
            $E = round($B + $C + $D, 2);

            // Bases como Excel
            $base_E_menos_D = max(0, $E - $D);

            // Porcentajes
            $F = round($base_E_menos_D * $d['pct_cesantias'], 2);
            $G = round($F * $d['pct_int_cesantias'], 2);
            $H = round($base_E_menos_D * $d['pct_prima'], 2);

            $I = round($B * $d['pct_vacaciones'], 2);
            $J = round($B * $d['pct_arp'], 2);
            $K = round($B * $d['pct_eps'], 2);
            $L = round($B * $d['pct_pension'], 2);
            $M = round($B * $d['pct_ccf'], 2);

            // Totales — con fallback a tarifas diarias parametrizadas (NOM_TRANSP_DIA / NOM_ALIMENT_DIA)
            $N = round($d['viaticos_total'] ?: ($this->parametros->getFloat('NOM_TRANSP_DIA',  0.0) * $diasMes), 2);
            $O = round($d['aliment_total']  ?: ($this->parametros->getFloat('NOM_ALIMENT_DIA', 0.0) * $diasMes), 2);
            $P = round($d['dotacion_total'], 2);
            $Q = round($d['examenes_total'], 2);

            // R = SUM(E..Q)
            $R = round($E + $F + $G + $H + $I + $J + $K + $L + $M + $N + $O + $P + $Q, 2);

            // S=R/26 y T=S/8
            $S = round($R / $diasMes, 2);
            $T = ($horasDiarias > 0) ? round($S / $horasDiarias, 4) : 0.0;

            $row = [
                'cargo_id' => $cargoId,
                'cargo' => $d['cargo'],

                // Auditoría estilo Excel
                'B_basico' => $B,
                'C_aux_trans' => $C,
                'D_auxilio' => $D,
                'E_total' => $E,
                'F_cesantias' => $F,
                'G_int_cesantias' => $G,
                'H_prima' => $H,
                'I_vacaciones' => $I,
                'J_arp' => $J,
                'K_eps' => $K,
                'L_pension' => $L,
                'M_ccf' => $M,
                'N_viaticos' => $N,
                'O_aliment' => $O,
                'P_dotacion' => $P,
                'Q_examenes' => $Q,
                'R_total_mes' => $R,
                'S_costo_dia' => $S,
                'T_costo_hora' => $T,

                'utilidad_pct' => $utilidad,
                'horas_diarias' => $horasDiarias,
                'dias_mes' => $diasMes,

                // Para depurar rápidamente si falta mapear algo
                'otros_totales' => $d['otros_totales'],
                'otros_porcentajes' => $d['otros_porcentajes'],
            ];

            Log::debug('[TablaPreciosCargo] Resultado cargo calculado', [
                'cargo_id' => $cargoId,
                'cargo' => $d['cargo'],
                'R_total_mes' => $R,
                'S_costo_dia' => $S,
                'T_costo_hora' => $T,
            ]);

            // Tabla de precios (A14:I22)
            foreach ($factoresPrecio as $k => $f) {
                $row[$k] = round(($T * $f) / $den, 2);
            }
            $row['valor_dia_ordinario'] = round($row['hora_ordinaria'] * $horasDiarias, 2);

            $resultado[] = $row;

            // Persistir (si existe la tabla cargos_tabla_precios)
            if ($persistir) {
                try {
                    DB::table('cargos_tabla_precios')->updateOrInsert(
                        ['cargo_id' => $cargoId],
                        [
                            'utilidad_pct' => $utilidad,
                            'horas_diarias' => $horasDiarias,
                            'base_costo_dia' => $S,
                            'base_costo_hora' => $T,

                            'hora_ordinaria' => $row['hora_ordinaria'],
                            'recargo_nocturno' => $row['recargo_nocturno'],
                            'hora_extra_diurna' => $row['hora_extra_diurna'],
                            'hora_extra_nocturna' => $row['hora_extra_nocturna'],
                            'hora_dominical' => $row['hora_dominical'],
                            'hora_extra_dominical_diurna' => $row['hora_extra_dominical_diurna'],
                            'hora_extra_dominical_nocturna' => $row['hora_extra_dominical_nocturna'],
                            'valor_dia_ordinario' => $row['valor_dia_ordinario'],

                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    Log::debug('[TablaPreciosCargo] Persistencia OK', [
                        'cargo_id' => $cargoId,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('[TablaPreciosCargo] Error persistiendo cargo', [
                        'cargo_id' => $cargoId,
                        'mensaje' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        }

        Log::debug('[TablaPreciosCargo] Proceso completado', [
            'cargos_procesados' => count($resultado),
            'persistido' => $persistir,
        ]);

        return $resultado;
    }
}








