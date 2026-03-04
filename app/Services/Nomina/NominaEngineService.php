<?php

namespace App\Services\Nomina;

use App\Models\Empleado;
use App\Models\Nomina\NominaConcept;
use App\Models\Nomina\NominaConceptRule;
use App\Models\Nomina\NominaNovelty;
use App\Models\Nomina\NominaPayRun;
use App\Models\Nomina\NominaPayRunLine;
use App\Models\Nomina\NominaPayRunParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NominaEngineService
{
    // public function calculate(NominaPayRun $payRun): void
    // {
    //     if (!in_array($payRun->status, ['DRAFT','CALCULATED'], true)) {
    //         throw new \RuntimeException("No se puede calcular en estado {$payRun->status}");
    //     }

    //     DB::transaction(function () use ($payRun) {
    //         $participants = NominaPayRunParticipant::where('pay_run_id', $payRun->id)->get();
    //         if ($participants->isEmpty()) throw new \RuntimeException('No hay participantes incluidos.');

    //         $concepts = NominaConcept::where('is_active', 1)->orderBy('priority')->get()->keyBy('code');

    //         $rules = NominaConceptRule::query()
    //             ->where(function ($q) use ($payRun) {
    //                 $q->whereNull('valid_from')->orWhereDate('valid_from','<=',$payRun->period_end);
    //             })
    //             ->where(function ($q) use ($payRun) {
    //                 $q->whereNull('valid_to')->orWhereDate('valid_to','>=',$payRun->period_start);
    //             })
    //             ->get()
    //             ->groupBy('nomina_concept_id');

    //         foreach ($participants as $p) {
    //             // borrar líneas previas
    //             NominaPayRunLine::where('pay_run_id',$payRun->id)
    //                 ->where('participant_type',$p->participant_type)
    //                 ->where('participant_id',$p->participant_id)
    //                 ->delete();

    //             $novelties = NominaNovelty::where('participant_type',$p->participant_type)
    //                 ->where('participant_id',$p->participant_id)
    //                 ->where('status','PENDING')
    //                 ->whereDate('period_start','<=',$payRun->period_end)
    //                 ->whereDate('period_end','>=',$payRun->period_start)
    //                 ->get();

    //             $lines = [];

    //             if ($p->link_type === 'LABORAL') {
    //                 $empleado = Empleado::findOrFail($p->participant_id);

    //                 // Starter: básico quincenal = salario / 2 (si tu salario es mensual)
    //                 $basicoConcept = $concepts->get('LAB_BASICO');
    //                 if ($basicoConcept && $empleado->salario) {
    //                     $amount = ((float)$empleado->salario) / 2.0;
    //                     $lines[] = $this->line($payRun, $p, $basicoConcept->id, 15, (float)$empleado->salario/30, 1, $amount, 'ADD', 'ENGINE', 'Básico quincenal');
    //                 }

    //                 // Novedades como líneas (manual)
    //                 foreach ($novelties as $n) {
    //                     $c = NominaConcept::find($n->nomina_concept_id);
    //                     $direction = ($c && $c->kind === 'DEDUCCION') ? 'SUB' : 'ADD';
    //                     $lines[] = $this->line($payRun, $p, $n->nomina_concept_id, (float)($n->quantity ?? 1), 0, 0, (float)($n->amount ?? 0), $direction, 'NOVELTY', $n->description);
    //                 }

    //                 // IBC starter: suma ADD salariales
    //                 $ibc = 0.0;
    //                 foreach ($lines as $l) {
    //                     if ($l['direction'] !== 'ADD') continue;
    //                     $c = NominaConcept::find($l['nomina_concept_id']);
    //                     if ($c && $c->tax_nature === 'SALARIAL') $ibc += (float)$l['amount'];
    //                 }

    //                 // Salud/pensión empleado (tasa desde rules)
    //                 $lines = array_merge($lines, $this->percentLine($payRun, $p, $concepts->get('LAB_DED_SALUD_EMP'), $rules, $ibc, 'SUB'));
    //                 $lines = array_merge($lines, $this->percentLine($payRun, $p, $concepts->get('LAB_DED_PENSION_EMP'), $rules, $ibc, 'SUB'));

    //                 // Aportes empleador (si los manejas)
    //                 $lines = array_merge($lines, $this->percentLine($payRun, $p, $concepts->get('LAB_AP_SALUD_PAT'), $rules, $ibc, 'ADD'));
    //                 $lines = array_merge($lines, $this->percentLine($payRun, $p, $concepts->get('LAB_AP_PENSION_PAT'), $rules, $ibc, 'ADD'));
    //             }

    //             if ($p->link_type === 'CONTRATISTA') {
    //                 // Starter: honorarios deben venir por novedad o por “meta” del tercero (si luego lo guardas)
    //                 $honConcept = $concepts->get('CON_HONORARIOS');
    //                 $hon = (float)$novelties->where('nomina_concept_id', optional($honConcept)->id)->sum(fn($x)=>(float)($x->amount ?? 0));

    //                 if ($honConcept && $hon > 0) {
    //                     $lines[] = $this->line($payRun, $p, $honConcept->id, 1, $hon, 1, $hon, 'ADD', 'ENGINE', 'Honorarios');
    //                 }

    //                 foreach ($novelties as $n) {
    //                     if ($honConcept && $n->nomina_concept_id == $honConcept->id) continue;
    //                     $c = NominaConcept::find($n->nomina_concept_id);
    //                     $direction = ($c && $c->kind === 'DEDUCCION') ? 'SUB' : 'ADD';
    //                     $lines[] = $this->line($payRun, $p, $n->nomina_concept_id, (float)($n->quantity ?? 1), 0, 0, (float)($n->amount ?? 0), $direction, 'NOVELTY', $n->description);
    //                 }

    //                 // Retenciones sobre honorarios
    //                 $lines = array_merge($lines, $this->percentLine($payRun, $p, $concepts->get('CON_DED_RETEFUENTE'), $rules, $hon, 'SUB'));
    //                 $lines = array_merge($lines, $this->percentLine($payRun, $p, $concepts->get('CON_DED_RETEICA'), $rules, $hon, 'SUB'));
    //             }

    //             // Persistir líneas
    //             foreach ($lines as $l) NominaPayRunLine::create($l);

    //             // Totales
    //             $gross = collect($lines)->where('direction','ADD')->sum('amount');
    //             $ded = collect($lines)->where('direction','SUB')->sum('amount');
    //             $net = $gross - $ded;

    //             $p->update([
    //                 'status' => 'CALCULATED',
    //                 'gross_total' => round($gross,2),
    //                 'deductions_total' => round($ded,2),
    //                 'net_total' => round($net,2),
    //             ]);

    //             // marcar novedades aplicadas
    //             if ($novelties->isNotEmpty()) {
    //                 NominaNovelty::whereIn('id',$novelties->pluck('id'))->update(['status'=>'APPLIED']);
    //             }
    //         }

    //         $payRun->update(['status'=>'CALCULATED']);
    //     });
    // }

    public function calculate(NominaPayRun $payRun): void
    {
        if (!in_array($payRun->status, ['DRAFT','CALCULATED'], true)) {
            throw new \RuntimeException("No se puede calcular en estado {$payRun->status}");
        }

        DB::transaction(function () use ($payRun) {

            $participants = NominaPayRunParticipant::where('pay_run_id', $payRun->id)->get();
            if ($participants->isEmpty()) {
                throw new \RuntimeException('No hay participantes incluidos.');
            }

            // ✅ Cache de conceptos para evitar N+1
            $concepts = NominaConcept::where('is_active', 1)->orderBy('priority')->get();
            $conceptsByCode = $concepts->keyBy('code');
            $conceptsById   = $concepts->keyBy('id');

            // ✅ Reglas vigentes
            $rules = NominaConceptRule::query()
                ->where(function ($q) use ($payRun) {
                    $q->whereNull('valid_from')->orWhereDate('valid_from','<=',$payRun->period_end);
                })
                ->where(function ($q) use ($payRun) {
                    $q->whereNull('valid_to')->orWhereDate('valid_to','>=',$payRun->period_start);
                })
                ->get()
                ->groupBy('nomina_concept_id');

                Log::info('Reglas cargadas para cálculo', [
                    'pay_run_id' => $payRun->id,
                    'rules_count' => $rules->count(),
                ]);

            foreach ($participants as $p) {

                // borrar líneas previas
                NominaPayRunLine::where('pay_run_id', $payRun->id)
                    ->where('participant_type', $p->participant_type)
                    ->where('participant_id', $p->participant_id)
                    ->delete();

                // novedades pendientes en el rango
                $noveltiesQuery = NominaNovelty::where('participant_type', $p->participant_type)
                    ->where('participant_id', $p->participant_id)
                    ->where('status', 'PENDING')
                    ->whereDate('period_start','<=',$payRun->period_end)
                    ->whereDate('period_end','>=',$payRun->period_start);

                // ✅ Si ya agregaste company_id a nomina_novelties (recomendado)
                $noveltiesQuery->where('company_id', $payRun->company_id);

                $novelties = $noveltiesQuery->get();

                $lines = [];

                // =========================================================
                // LABORAL
                // =========================================================
                if ($p->link_type === 'LABORAL') {

                    $empleado = Empleado::findOrFail($p->participant_id);

                    // ✅ Básico quincenal consistente con line(): qty=15, base=salario/30, rate=1, amount=qty*base
                    $basicoConcept = $conceptsByCode->get('LAB_BASICO');
                    $salarioMensual = (float) ($empleado->salario ?? 0);

                    if ($basicoConcept && $salarioMensual > 0) {
                        $dayRate = $salarioMensual / 30.0;
                        $days = 15.0;
                        $amount = $days * $dayRate;

                        $lines[] = $this->line(
                            $payRun,
                            $p,
                            $basicoConcept->id,
                            $days,
                            $dayRate,
                            1,
                            $amount,
                            'ADD',
                            'ENGINE',
                            'Básico quincenal'
                        );
                    }

                    // ✅ Novedades como líneas (manual) - sin N+1 + mejora qty/amount
                    foreach ($novelties as $n) {
                        $c = $conceptsById->get((int)$n->nomina_concept_id);

                        $direction = ($c && $c->kind === 'DEDUCCION') ? 'SUB' : 'ADD';

                        $qty  = (float) ($n->quantity ?? 1);
                        $amt  = (float) ($n->amount ?? 0);

                        // Si viene quantity y no viene amount, y el concepto tiene lógica por cantidad,
                        // puedes calcular basado en base_amount si algún día lo manejas.
                        // Por ahora: si amount viene en 0 y qty>0, se mantiene 0 (evita inventar).
                        // Si quieres calcular qty * base, tendrías que tener base en la novedad o en reglas.

                        $lines[] = $this->line(
                            $payRun,
                            $p,
                            (int)$n->nomina_concept_id,
                            $qty,
                            0,
                            0,
                            $amt,
                            $direction,
                            'NOVELTY',
                            $n->description
                        );
                    }

                    // ✅ IBC starter: suma ADD salariales (sin consultar DB)
                    $ibc = 0.0;
                    foreach ($lines as $l) {
                        if (($l['direction'] ?? null) !== 'ADD') continue;

                        $c = $conceptsById->get((int)$l['nomina_concept_id']);
                        if ($c && $c->tax_nature === 'SALARIAL') {
                            $ibc += (float) $l['amount'];
                        }
                    }
                    $ibc = round($ibc, 2);

                    // ✅ Salud/pensión empleado (tasa desde rules)
                    $lines = array_merge($lines, $this->percentLine($payRun, $p, $conceptsByCode->get('LAB_DED_SALUD_EMP'), $rules, $ibc, 'SUB'));
                    $lines = array_merge($lines, $this->percentLine($payRun, $p, $conceptsByCode->get('LAB_DED_PENSION_EMP'), $rules, $ibc, 'SUB'));

                    // ✅ Aportes empleador
                    $lines = array_merge($lines, $this->percentLine($payRun, $p, $conceptsByCode->get('LAB_AP_SALUD_PAT'), $rules, $ibc, 'ADD'));
                    $lines = array_merge($lines, $this->percentLine($payRun, $p, $conceptsByCode->get('LAB_AP_PENSION_PAT'), $rules, $ibc, 'ADD'));
                }

                // =========================================================
                // CONTRATISTA
                // =========================================================
                if ($p->link_type === 'CONTRATISTA') {

                    $honConcept = $conceptsByCode->get('CON_HONORARIOS');
                    $honConceptId = $honConcept?->id;
                    $destajoConcept = $conceptsByCode->get('LAB_DESTAJO');
                    Log::info('Calculando para contratista', [
                        'participant_id' => $p->participant_id,
                        'hon_concept_exists' => $honConcept ? 'YES' : 'NO',
                        'destajo_concept_exists' => $destajoConcept ? json_encode($destajoConcept) : 'NO',
                    ]);
                    // ✅ Pago por destajo para contratistas (concepto LAB_DESTAJO definido como DEVENGADO no salarial)
                    if ($destajoConcept) {
                        $destajoNov = $novelties->where('nomina_concept_id', $destajoConcept->id);
                        $destajoAmount = (float) $destajoNov->sum(fn($n) => (float) ($n->amount ?? 0));
                        Log::info('Cálculo de destajo', [
                            'destajo_concept_id' => $destajoConcept->id,
                            'destajo_novelties_count' => $destajoNov->count(),
                            'destajo_amount' => $destajoAmount,
                        ]);
                        if ($destajoAmount > 0) {
                            $lines[] = $this->line(
                                $payRun,
                                $p,
                                $destajoConcept->id,
                                1,
                                $destajoAmount,
                                1,
                                $destajoAmount,
                                'ADD',
                                'NOVELTY',
                                'Pago por destajo'
                            );
                        }

                        // Evita duplicar las mismas novedades en el bucle general
                        $novelties = $novelties->reject(fn($n) => (int)$n->nomina_concept_id === (int)$destajoConcept->id)->values();
                    }

                    // Honorarios deben venir por novedad (suma)
                    $hon = 0.0;
                    if ($honConceptId) {
                        $hon = (float) $novelties
                            ->where('nomina_concept_id', $honConceptId)
                            ->sum(fn($x) => (float)($x->amount ?? 0));
                    }
                    $hon = round($hon, 2);

                    if ($honConcept && $hon > 0) {
                        $lines[] = $this->line(
                            $payRun,
                            $p,
                            $honConcept->id,
                            1,
                            $hon,
                            1,
                            $hon,
                            'ADD',
                            'ENGINE',
                            'Honorarios'
                        );
                    }

                    // otras novedades (excluye honorarios si ya se pusieron)
                    foreach ($novelties as $n) {
                        if ($honConceptId && (int)$n->nomina_concept_id === (int)$honConceptId) continue;

                        $c = $conceptsById->get((int)$n->nomina_concept_id);
                        $direction = ($c && $c->kind === 'DEDUCCION') ? 'SUB' : 'ADD';

                        $lines[] = $this->line(
                            $payRun,
                            $p,
                            (int)$n->nomina_concept_id,
                            (float)($n->quantity ?? 1),
                            0,
                            0,
                            (float)($n->amount ?? 0),
                            $direction,
                            'NOVELTY',
                            $n->description
                        );
                    }

                    // Retenciones sobre honorarios
                    $lines = array_merge($lines, $this->percentLine($payRun, $p, $conceptsByCode->get('CON_DED_RETEFUENTE'), $rules, $hon, 'SUB'));
                    $lines = array_merge($lines, $this->percentLine($payRun, $p, $conceptsByCode->get('CON_DED_RETEICA'), $rules, $hon, 'SUB'));

                    Log::info('Lineas', [
                        'lines_count' => count($lines),
                    ]);
                }

                // Persistir líneas
                foreach ($lines as $l) {
                    Log::info('Persistiendo línea de nómina', [
                        'pay_run_id' => $l['pay_run_id'],
                        'participant_type' => $l['participant_type'],
                        'participant_id' => $l['participant_id'],
                        'concept_id' => $l['nomina_concept_id'],
                        'amount' => $l['amount'],
                        'direction' => $l['direction'],
                        'source' => $l['source'],
                    ]);
                    NominaPayRunLine::create($l);
                }

                // ✅ Totales
                $gross = (float) collect($lines)->where('direction','ADD')->sum('amount');
                $ded   = (float) collect($lines)->where('direction','SUB')->sum('amount');
                $net   = $gross - $ded;

                // ✅ Employer total (solo conceptos kind=APORTE)
                $employer = 0.0;
                foreach ($lines as $l) {
                    if (($l['direction'] ?? null) !== 'ADD') continue;

                    $c = $conceptsById->get((int)$l['nomina_concept_id']);
                    if ($c && $c->kind === 'APORTE') {
                        $employer += (float)$l['amount'];
                    }
                }

                $updateData = [
                    'status' => 'CALCULATED',
                    'gross_total' => round($gross,2),
                    'deductions_total' => round($ded,2),
                    'net_total' => round($net,2),
                ];

                // Solo si tu tabla tiene employer_total
                if (Schema::hasColumn('nomina_pay_run_participants', 'employer_total')) {
                    $updateData['employer_total'] = round($employer, 2);
                }

                $p->update($updateData);

                // marcar novedades aplicadas
                if ($novelties->isNotEmpty()) {
                    NominaNovelty::whereIn('id',$novelties->pluck('id'))->update(['status'=>'APPLIED']);
                }
            }

            $payRun->update(['status'=>'CALCULATED']);
        });
    }

    private function line(NominaPayRun $payRun, NominaPayRunParticipant $p, int $conceptId, float $qty, float $base, float $rate, float $amount, string $direction, string $source, ?string $notes): array
    {
        return [
            'pay_run_id' => $payRun->id,
            'participant_type' => $p->participant_type,
            'participant_id' => $p->participant_id,
            'link_type' => $p->link_type,
            'nomina_concept_id' => $conceptId,
            'quantity' => $qty,
            'base_amount' => $base,
            'rate' => $rate,
            'amount' => round($amount,2),
            'direction' => $direction,
            'source' => $source,
            'notes' => $notes,
            'meta' => null,
        ];
    }

    private function percentLine(NominaPayRun $payRun, NominaPayRunParticipant $p, ?NominaConcept $concept, $rulesGrouped, float $base, string $direction): array
    {
        if (!$concept || $base <= 0) return [];

        $ruleList = $rulesGrouped->get($concept->id, collect());
        $rate = (float) data_get($ruleList->first()?->parameters, 'rate', 0);
        if ($rate <= 0) return [];

        $amount = $base * $rate;
        return [
            $this->line($payRun, $p, $concept->id, 1, $base, $rate, $amount, $direction, 'ENGINE', $concept->name)
        ];
    }


}
