<?php

namespace App\Services\Nomina;

use App\Models\Empleado;
use App\Models\Nomina\NominaConcept;
use App\Models\Nomina\NominaNovelty;
use Illuminate\Support\Facades\DB;

class NominaDestajoNoveltyRecalculateService
{
    /**
     * Recalcula novedades LAB_DESTAJO para un periodo:
     * - Reconstruye amount/qty desde prod_worker_settlements (o tu fuente real)
     * - Deja status = PENDING
     * - Si existe novedad previa PENDING del mismo settlement, la actualiza
     * - Si estaba APPLIED, NO la toca (para no romper histórico). En ese caso crea una nueva PENDING.
     */
    public function recalculate(int $companyId, string $periodStart, string $periodEnd, int $userId): array
    {
        $conceptId = NominaConcept::where('code', 'LAB_DESTAJO')->value('id');
        if (!$conceptId) {
            throw new \RuntimeException('No existe el concepto LAB_DESTAJO.');
        }

        // Fuente recomendada: settlements aprobados o sincronizados
        // Ajusta nombres de tabla/columnas según tu implementación real.
        $rows = DB::table('prod_worker_settlements as s')
            ->join('prod_orders as o', 'o.id', '=', 's.order_id')
            ->where('s.company_id', $companyId)
            ->whereBetween(DB::raw('DATE(s.work_date)'), [$periodStart, $periodEnd]) // si no tienes work_date en settlements, usa created_at o log_date
            ->whereIn('s.status', ['APPROVED','SYNCED_TO_NOMINA']) // ajusta
            ->select([
                's.id as settlement_id',
                's.employee_id',
                's.order_id',
                's.order_operation_id',
                's.qty',
                's.rate',
                DB::raw('ROUND(s.gross_amount,2) as amount'),
            ])
            ->get();

        $updated = 0;
        $created = 0;

        DB::transaction(function () use ($rows, $companyId, $periodStart, $periodEnd, $conceptId, &$updated, &$created) {
            foreach ($rows as $r) {

                $payload = [
                    'company_id' => $companyId,
                    'participant_type' => Empleado::class,
                    'participant_id' => (int)$r->employee_id,
                    'link_type' => 'LABORAL',
                    'nomina_concept_id' => $conceptId,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'quantity' => (float)($r->qty ?? 0),
                    'amount' => (float)($r->amount ?? 0),
                    'description' => "Destajo (No salarial) | Settlement#{$r->settlement_id} | Orden#{$r->order_id} | Op#{$r->order_operation_id}",
                    'status' => 'PENDING',
                    'meta' => [
                        'source' => 'PRODUCTION',
                        'settlement_id' => (int)$r->settlement_id,
                        'order_id' => (int)$r->order_id,
                        'order_operation_id' => (int)$r->order_operation_id,
                        'rate' => (float)($r->rate ?? 0),
                        'qty' => (float)($r->qty ?? 0),
                    ],
                ];

                // 1) Si ya existe PENDING para ese settlement en ese periodo -> update
                $existingPending = NominaNovelty::query()
                    ->where('company_id', $companyId)
                    ->where('nomina_concept_id', $conceptId)
                    ->where('status', 'PENDING')
                    ->where('participant_type', Empleado::class)
                    ->where('participant_id', (int)$r->employee_id)
                    ->whereDate('period_start', $periodStart)
                    ->whereDate('period_end', $periodEnd)
                    ->whereRaw("JSON_EXTRACT(meta,'$.settlement_id') = ?", [(int)$r->settlement_id])
                    ->first();

                if ($existingPending) {
                    $existingPending->update($payload);
                    $updated++;
                    continue;
                }

                // 2) Si existe APPLIED (ya usado en nómina) -> NO tocar: crear una nueva PENDING
                $existsApplied = NominaNovelty::query()
                    ->where('company_id', $companyId)
                    ->where('nomina_concept_id', $conceptId)
                    ->where('status', 'APPLIED')
                    ->where('participant_type', Empleado::class)
                    ->where('participant_id', (int)$r->employee_id)
                    ->whereDate('period_start', $periodStart)
                    ->whereDate('period_end', $periodEnd)
                    ->whereRaw("JSON_EXTRACT(meta,'$.settlement_id') = ?", [(int)$r->settlement_id])
                    ->exists();

                if ($existsApplied) {
                    NominaNovelty::create($payload);
                    $created++;
                    continue;
                }

                // 3) No existe -> create
                NominaNovelty::create($payload);
                $created++;
            }
        });

        return ['created' => $created, 'updated' => $updated];
    }
}
