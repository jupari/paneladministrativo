<?php

namespace App\Services\Nomina;

use App\Models\Empleado;
use App\Models\Nomina\NominaConcept;
use App\Models\Nomina\NominaNovelty;
use App\Models\Nomina\NominaPayRun;
use App\Models\Nomina\NominaPayRunLine;
use App\Models\Nomina\NominaPayRunParticipant;
use Illuminate\Support\Facades\DB;

class NominaDestajoFromSettlementsService
{
    /**
     * Recalcula/actualiza novedades LAB_DESTAJO para un periodo, desde prod_worker_settlements.
     * Opcional: si pasas pay_run_id, hace reset de líneas/participantes para forzar recálculo.
     */
    public function recalculate(int $companyId, string $periodStart, string $periodEnd, ?int $payRunId = null): array
    {
        $conceptId = NominaConcept::query()
            ->where('code', 'LAB_DESTAJO')
            ->value('id');

        if (!$conceptId) {
            throw new \RuntimeException('No existe el concepto LAB_DESTAJO.');
        }

        // 1) Traer settlements en el periodo (ajusta el campo fecha si tu tabla usa otro)
        $settlements = DB::table('prod_worker_settlements as s')
            ->where('s.company_id', $companyId)
            ->whereDate('s.created_at', '>=', $periodStart)
            ->whereDate('s.created_at', '<=', $periodEnd)
            ->whereIn('s.status', ['APPROVED', 'SYNCED_TO_NOMINA']) // ajusta a tus estados reales
            ->select([
                's.id',
                's.employee_id',
                's.order_id',
                's.order_operation_id',
                's.qty',
                's.rate',
                's.gross_amount',
                's.created_at',
            ])
            ->get();

        $created = 0;
        $updated = 0;
        $touchedEmployees = [];

        DB::transaction(function () use (
            $settlements, $companyId, $periodStart, $periodEnd, $conceptId,
            &$created, &$updated, &$touchedEmployees
        ) {
            foreach ($settlements as $s) {
                $sourceRef = 'PROD_SETTLEMENT:' . (int)$s->id;

                $amount = round((float)($s->gross_amount ?? 0), 2);
                $qty = (float)($s->qty ?? 1);

                // Si el settlement da 0, no crees novedad
                if ($amount <= 0) {
                    continue;
                }

                $payload = [
                    'company_id' => $companyId,
                    'participant_type' => Empleado::class,
                    'participant_id' => (int)$s->employee_id,
                    'link_type' => 'LABORAL',
                    'nomina_concept_id' => $conceptId,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'description' => "Destajo (No salarial) | Settlement#{$s->id} | Orden#{$s->order_id} | Op#{$s->order_operation_id}",
                    'status' => 'PENDING',
                    'meta' => [
                        'source' => 'PRODUCTION',
                        'settlement_id' => (int)$s->id,
                        'order_id' => (int)$s->order_id,
                        'order_operation_id' => (int)$s->order_operation_id,
                        'rate' => (float)($s->rate ?? 0),
                        'qty' => $qty,
                    ],
                ];

                // Si existe novedad por source_ref:
                $nov = NominaNovelty::query()
                    ->where('company_id', $companyId)
                    ->where('source_ref', $sourceRef)
                    ->first();

                if ($nov) {
                    // Si ya fue aplicada, NO la modifiques (histórico). Crea una nueva PENDING "ADJUST".
                    if ($nov->status === 'APPLIED') {
                        NominaNovelty::create($payload + [
                            'source_ref' => $sourceRef . ':ADJ:' . now()->format('YmdHis'),
                        ]);
                        $created++;
                    } else {
                        $nov->update($payload);
                        $updated++;
                    }
                } else {
                    NominaNovelty::create($payload + ['source_ref' => $sourceRef]);
                    $created++;
                }

                $touchedEmployees[(int)$s->employee_id] = true;
            }
        });

        // 2) Si hay payrun, hacer reset para forzar recálculo
        if ($payRunId) {
            $this->resetPayRunForEmployees($companyId, $payRunId, array_keys($touchedEmployees));
        }

        return ['created' => $created, 'updated' => $updated, 'employees' => count($touchedEmployees)];
    }

    private function resetPayRunForEmployees(int $companyId, int $payRunId, array $employeeIds): void
    {
        if (empty($employeeIds)) return;

        $payRun = NominaPayRun::query()
            ->where('company_id', $companyId)
            ->where('id', $payRunId)
            ->first();

        if (!$payRun) return;

        // solo si está en estados donde permites recalcular
        if (!in_array($payRun->status, ['DRAFT','CALCULATED'], true)) return;

        DB::transaction(function () use ($payRunId, $employeeIds) {

            // Borrar líneas de esos empleados
            NominaPayRunLine::query()
                ->where('pay_run_id', $payRunId)
                ->where('participant_type', Empleado::class)
                ->whereIn('participant_id', $employeeIds)
                ->delete();

            // Resetear totales en participant payrun
            NominaPayRunParticipant::query()
                ->where('pay_run_id', $payRunId)
                ->where('participant_type', Empleado::class)
                ->whereIn('participant_id', $employeeIds)
                ->update([
                    'status' => 'DRAFT',
                    'gross_total' => 0,
                    'deductions_total' => 0,
                    'net_total' => 0,
                ]);

            // Dejar el payrun en DRAFT para que el usuario dé Calcular nuevamente
            NominaPayRun::query()
                ->where('id', $payRunId)
                ->update(['status' => 'DRAFT']);
        });
    }
}
