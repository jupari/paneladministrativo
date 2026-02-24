<?php

namespace App\Services\Produccion;

use App\Models\Empleado;
use App\Models\Nomina\NominaConcept;
use App\Models\Nomina\NominaNovelty;
use App\Models\Produccion\ProdOrder;
use App\Models\Produccion\ProdWorkerSettlement;
use Illuminate\Support\Facades\DB;

class ProductionPayrollIntegrationService
{
    /**
     * Envía la liquidación (destajo) a nómina como Novedades PENDING.
     *
     * Reglas:
     * - Operarios = Empleados
     * - Concepto nómina = LAB_DESTAJO (NO SALARIAL)
     * - Crea novedades por settlement (1 por empleado+operación) para auditabilidad
     * - Evita duplicados: si el settlement ya fue sincronizado, no vuelve a crear
     *
     * @return int Cantidad de novedades creadas
     */
    public function syncSettlementsToNomina(
        int $orderId,
        string $periodStart,
        string $periodEnd,
        int $companyId,
        int $userId
    ): int {
        // 1) Validar orden pertenece a la company
        $order = ProdOrder::query()
            ->where('company_id', $companyId)
            ->findOrFail($orderId);

        // 2) Concepto nómina
        $conceptId = NominaConcept::query()
            ->where('code', 'LAB_DESTAJO')
            ->value('id');

        if (!$conceptId) {
            throw new \RuntimeException("No existe el concepto de nómina LAB_DESTAJO.");
        }

        // 3) Traer liquidaciones pendientes de envío
        $settlements = ProdWorkerSettlement::query()
            ->where('company_id', $companyId)
            ->where('order_id', $order->id)
            ->whereIn('status', ['APPROVED', 'DRAFT'])
            ->get();

        if ($settlements->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use (
            $settlements,
            $companyId,
            $userId,
            $conceptId,
            $periodStart,
            $periodEnd,
            $order
        ) {
            $created = 0;

            foreach ($settlements as $s) {
                // Seguridad extra
                if ((int)$s->company_id !== (int)$companyId) {
                    continue;
                }

                // Si ya se sincronizó antes, no duplicar
                if ($s->status === 'SYNCED_TO_NOMINA') {
                    continue;
                }

                // Evitar crear duplicado si por alguna razón ya existe una novedad idéntica
                // (esto te protege si alguien cambia el status manualmente)
                $exists = NominaNovelty::query()
                    ->where('company_id', $companyId)
                    ->where('participant_type', Empleado::class)
                    ->where('participant_id', $s->employee_id)
                    ->where('nomina_concept_id', $conceptId)
                    ->whereDate('period_start', $periodStart)
                    ->whereDate('period_end', $periodEnd)
                    ->where('status', 'PENDING')
                    ->where('description', 'like', "%Orden#{$order->id}%Op#{$s->order_operation_id}%")
                    ->exists();

                if ($exists) {
                    // Marcar settlement sincronizado para no reintentar
                    $s->update(['status' => 'SYNCED_TO_NOMINA']);
                    continue;
                }

                $qty = (float)($s->qty ?? 0);
                $amount = (float)($s->gross_amount ?? 0);

                if ($amount <= 0) {
                    // No tiene sentido enviar novedad con 0
                    $s->update(['status' => 'SYNCED_TO_NOMINA']);
                    continue;
                }

                $notes = sprintf(
                    'Destajo (No salarial) | Orden#%d | Op#%d | Qty: %s | Rate: %s',
                    $order->id,
                    (int)$s->order_operation_id,
                    rtrim(rtrim(number_format($qty, 4, '.', ''), '0'), '.'),
                    number_format((float)($s->rate ?? 0), 2, '.', '')
                );

                // NominaNovelty::create([
                //     'company_id' => $companyId,
                //     'participant_type' => Empleado::class,
                //     'participant_id' => (int)$s->employee_id,
                //     'link_type' => 'CONTRATISTA',
                //     'nomina_concept_id' => $conceptId,
                //     'period_start' => $periodStart,
                //     'period_end' => $periodEnd,
                //     'quantity' => $qty,
                //     'amount' => round($amount, 2),
                //     'description' => $notes,
                //     'status' => 'PENDING',
                //     'created_by' => $userId, // si tu tabla no tiene este campo, elimínalo
                //     'meta' => [
                //         'source' => 'PRODUCTION',
                //         'order_id' => (int)$order->id,
                //         'order_operation_id' => (int)$s->order_operation_id,
                //         'settlement_id' => (int)$s->id, // clave para recalcular exacto
                //         'rate' => (float)($s->rate ?? 0),
                //         'qty' => (float)($s->qty ?? 0),
                //     ],
                // ]);

                $sourceRef = 'PROD_SETTLEMENT:' . (int)$s->id;

                NominaNovelty::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'source_ref' => $sourceRef,          // <- CLAVE ÚNICA POR ORIGEN
                ],
                [
                    'participant_type' => Empleado::class,
                    'participant_id' => (int)$s->employee_id,
                    'link_type' => 'LABORAL',
                    'nomina_concept_id' => $conceptId,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'quantity' => (float)($s->qty ?? 1),
                    'amount' => round((float)($s->gross_amount ?? 0), 2),
                    'description' => $notes,
                    'status' => 'PENDING',
                    'meta' => [
                        'source' => 'PRODUCTION',
                        'settlement_id' => (int)$s->id,
                        'order_id' => (int)$s->order_id,
                        'order_operation_id' => (int)$s->order_operation_id,
                        'rate' => (float)($s->rate ?? 0),
                        'qty' => (float)($s->qty ?? 0),
                    ],
                ]);

                $s->update(['status' => 'SYNCED_TO_NOMINA']);
                $created++;
            }

            return $created;
        });
    }
}
