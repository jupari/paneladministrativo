<?php

namespace App\Services\Produccion;

use App\Models\Empleado;
use App\Models\Nomina\NominaConcept;
use App\Models\Nomina\NominaNovelty;
use App\Models\Nomina\NominaPayRun;
use App\Models\Nomina\NominaPayRunParticipant;
use App\Models\Produccion\ProdOrder;
use App\Models\Produccion\ProdWorkerSettlement;
use App\Models\Tercero;
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
    // public function syncSettlementsToNomina(
    //     int $orderId,
    //     string $periodStart,
    //     string $periodEnd,
    //     int $companyId,
    //     int $userId
    // ): int {
    //     // 1) Validar orden pertenece a la company
    //     $order = ProdOrder::query()
    //         ->where('company_id', $companyId)
    //         ->findOrFail($orderId);

    //     // 2) Concepto nómina
    //     $conceptId = NominaConcept::query()
    //         ->where('code', 'LAB_DESTAJO')
    //         ->value('id');

    //     if (!$conceptId) {
    //         throw new \RuntimeException("No existe el concepto de nómina LAB_DESTAJO.");
    //     }

    //     // 3) Traer liquidaciones pendientes de envío
    //     $settlements = ProdWorkerSettlement::query()
    //         ->where('company_id', $companyId)
    //         ->where('order_id', $order->id)
    //         ->whereIn('status', ['APPROVED', 'DRAFT'])
    //         ->get();

    //     if ($settlements->isEmpty()) {
    //         return 0;
    //     }

    //     return DB::transaction(function () use (
    //         $settlements,
    //         $companyId,
    //         $userId,
    //         $conceptId,
    //         $periodStart,
    //         $periodEnd,
    //         $order
    //     ) {
    //         $created = 0;

    //         foreach ($settlements as $s) {
    //             // Seguridad extra
    //             if ((int)$s->company_id !== (int)$companyId) {
    //                 continue;
    //             }

    //             // Si ya se sincronizó antes, no duplicar
    //             if ($s->status === 'SYNCED_TO_NOMINA') {
    //                 continue;
    //             }

    //             // Evitar crear duplicado si por alguna razón ya existe una novedad idéntica
    //             // (esto te protege si alguien cambia el status manualmente)
    //             $exists = NominaNovelty::query()
    //                 ->where('company_id', $companyId)
    //                 ->where('participant_type', Empleado::class)
    //                 ->where('participant_id', $s->employee_id)
    //                 ->where('nomina_concept_id', $conceptId)
    //                 ->whereDate('period_start', $periodStart)
    //                 ->whereDate('period_end', $periodEnd)
    //                 ->where('status', 'PENDING')
    //                 ->where('description', 'like', "%Orden#{$order->id}%Op#{$s->order_operation_id}%")
    //                 ->exists();

    //             if ($exists) {
    //                 // Marcar settlement sincronizado para no reintentar
    //                 $s->update(['status' => 'SYNCED_TO_NOMINA']);
    //                 continue;
    //             }

    //             $qty = (float)($s->qty ?? 0);
    //             $amount = (float)($s->gross_amount ?? 0);

    //             if ($amount <= 0) {
    //                 // No tiene sentido enviar novedad con 0
    //                 $s->update(['status' => 'SYNCED_TO_NOMINA']);
    //                 continue;
    //             }

    //             $notes = sprintf(
    //                 'Destajo (No salarial) | Orden#%d | Op#%d | Qty: %s | Rate: %s',
    //                 $order->id,
    //                 (int)$s->order_operation_id,
    //                 rtrim(rtrim(number_format($qty, 4, '.', ''), '0'), '.'),
    //                 number_format((float)($s->rate ?? 0), 2, '.', '')
    //             );

    //             // NominaNovelty::create([
    //             //     'company_id' => $companyId,
    //             //     'participant_type' => Empleado::class,
    //             //     'participant_id' => (int)$s->employee_id,
    //             //     'link_type' => 'CONTRATISTA',
    //             //     'nomina_concept_id' => $conceptId,
    //             //     'period_start' => $periodStart,
    //             //     'period_end' => $periodEnd,
    //             //     'quantity' => $qty,
    //             //     'amount' => round($amount, 2),
    //             //     'description' => $notes,
    //             //     'status' => 'PENDING',
    //             //     'created_by' => $userId, // si tu tabla no tiene este campo, elimínalo
    //             //     'meta' => [
    //             //         'source' => 'PRODUCTION',
    //             //         'order_id' => (int)$order->id,
    //             //         'order_operation_id' => (int)$s->order_operation_id,
    //             //         'settlement_id' => (int)$s->id, // clave para recalcular exacto
    //             //         'rate' => (float)($s->rate ?? 0),
    //             //         'qty' => (float)($s->qty ?? 0),
    //             //     ],
    //             // ]);

    //             $sourceRef = 'PROD_SETTLEMENT:' . (int)$s->id;

    //             NominaNovelty::updateOrCreate(
    //             [
    //                 'company_id' => $companyId,
    //                 'source_ref' => $sourceRef,          // <- CLAVE ÚNICA POR ORIGEN
    //             ],
    //             [
    //                 'participant_type' => Empleado::class,
    //                 'participant_id' => (int)$s->employee_id,
    //                 'link_type' => 'LABORAL',
    //                 'nomina_concept_id' => $conceptId,
    //                 'period_start' => $periodStart,
    //                 'period_end' => $periodEnd,
    //                 'quantity' => (float)($s->qty ?? 1),
    //                 'amount' => round((float)($s->gross_amount ?? 0), 2),
    //                 'description' => $notes,
    //                 'status' => 'PENDING',
    //                 'meta' => [
    //                     'source' => 'PRODUCTION',
    //                     'settlement_id' => (int)$s->id,
    //                     'order_id' => (int)$s->order_id,
    //                     'order_operation_id' => (int)$s->order_operation_id,
    //                     'rate' => (float)($s->rate ?? 0),
    //                     'qty' => (float)($s->qty ?? 0),
    //                 ],
    //             ]);

    //             $s->update(['status' => 'SYNCED_TO_NOMINA']);
    //             $created++;
    //         }

    //         return $created;
    //     });
    // }

    public function syncSettlementsToNomina(
    int $orderId,
    string $periodStart,
    string $periodEnd,
    int $companyId,
    int $userId
        ): int
    {

        // 1) Validar orden pertenece a la company
        $order = ProdOrder::query()
            ->where('company_id', $companyId)
            ->findOrFail($orderId);

        // 2) Concepto nómina
        $conceptId = NominaConcept::query()
            ->where('code', 'LAB_DESTAJO')
            ->where('is_active', 1)
            ->value('id');

        if (!$conceptId) {
            throw new \RuntimeException("No existe el concepto de nómina LAB_DESTAJO.");
        }

        // 3) Encontrar el payrun que corresponde al periodo (o créalo si tú ya lo haces en otro lado)
        $payRun = \App\Models\Nomina\NominaPayRun::query()
            ->where('company_id', $companyId)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();

        if (!$payRun) {
            throw new \RuntimeException("No existe un PayRun para el periodo {$periodStart} - {$periodEnd}. Crea el periodo primero.");
        }

        // 4) Traer liquidaciones pendientes de envío
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
            $payRun,
            $order
        ) {
            $created = 0;

            // 5) Forzar recalcular si ya estaba calculado
            if ($payRun->status === 'CALCULATED') {
                $payRun->update(['status' => 'DRAFT']);
            }

            foreach ($settlements as $s) {
                if ((int)$s->company_id !== (int)$companyId) {
                    continue;
                }

                // Si ya se sincronizó antes, no duplicar
                if ($s->status === 'SYNCED_TO_NOMINA') {
                    continue;
                }

                $employeeId = (int)($s->employee_id ?? 0);
                $qty = (float)($s->qty ?? 1);
                $amount = (float)($s->gross_amount ?? 0);

                if ($employeeId <= 0) {
                    // no hay empleado, no se puede enviar
                    $s->update(['status' => 'SYNCED_TO_NOMINA']);
                    continue;
                }

                if ($amount <= 0) {
                    // No tiene sentido enviar novedad con 0
                    $s->update(['status' => 'SYNCED_TO_NOMINA']);
                    continue;
                }

                // 6) Asegurar participant del payrun (esto es lo que te faltaba)
                NominaPayRunParticipant::updateOrCreate(
                    [
                        'pay_run_id' => $payRun->id,
                        'participant_type' => Tercero::class,
                        'participant_id' => $employeeId,
                    ],
                    [
                        'company_id' => $companyId,
                        'link_type' => 'CONTRATISTA', // o 'LABORAL' según corresponda
                        'status' => 'PENDING',
                        'gross_total' => 0,
                        'deductions_total' => 0,
                        'net_total' => 0,
                    ]
                );

                $notes = sprintf(
                    'Destajo (No salarial) | Orden#%d | Op#%d | Qty: %s | Rate: %s',
                    (int)$order->id,
                    (int)($s->order_operation_id ?? 0),
                    rtrim(rtrim(number_format($qty, 4, '.', ''), '0'), '.'),
                    number_format((float)($s->rate ?? 0), 2, '.', '')
                );

                // Clave idempotente por settlement
                $sourceRef = 'PROD_SETTLEMENT:' . (int)$s->id;

                // 7) Crear/actualizar novedad ligada al payrun (esto es clave)
                NominaNovelty::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'source_ref' => $sourceRef,
                    ],
                    [
                        'pay_run_id' => $payRun->id,
                        'participant_type' => Empleado::class,
                        'participant_id' => $employeeId,
                        'link_type' => 'LABORAL',
                        'nomina_concept_id' => $conceptId,
                        'period_start' => $payRun->period_start,
                        'period_end' => $payRun->period_end,
                        'quantity' => $qty,
                        'amount' => round($amount, 2),
                        'description' => $notes,
                        'status' => 'PENDING',
                        'created_by' => $userId, // si tu tabla lo tiene; si no, elimínalo
                        'meta' => [
                            'source' => 'PRODUCTION',
                            'settlement_id' => (int)$s->id,
                            'order_id' => (int)$s->order_id,
                            'order_operation_id' => (int)($s->order_operation_id ?? 0),
                            'rate' => (float)($s->rate ?? 0),
                            'qty' => $qty,
                        ],
                    ]
                );

                // 8) Marcar settlement sincronizado
                $s->update([
                    'status' => 'SYNCED_TO_NOMINA',
                    'sent_to_nomina_at' => now(),
                    'nomina_pay_run_id' => $payRun->id,
                ]);

                $created++;
            }

            return $created;
        });
    }

    //no se usa este método, lo dejé preparado por si quieres hacer la integración directo desde el periodo de nómina en vez de un comando/artisan separado
    public function sendToNomina(NominaPayRun $payRun, array $settlementIds): void
    {
        $companyId = (int) session('company_id');

        if ((int)$payRun->company_id !== $companyId) {
            throw new \RuntimeException('El periodo de nómina no pertenece a la empresa.');
        }

        $concept = NominaConcept::query()
            ->where('code', 'LAB_DESTAJO')
            ->where('is_active', 1)
            ->first();

        if (!$concept) {
            throw new \RuntimeException('No existe el concepto activo LAB_DESTAJO.');
        }

        DB::transaction(function () use ($payRun, $settlementIds, $concept, $companyId) {

            // Forzar recalcular si ya estaba calculada
            if ($payRun->status === 'CALCULATED') {
                $payRun->update(['status' => 'DRAFT']);
            }

            $settlements = \App\Models\Produccion\ProdWorkerSettlement::query()
                ->whereIn('id', $settlementIds)
                ->where('company_id', $companyId)
                ->get();

            if ($settlements->isEmpty()) {
                throw new \RuntimeException('No hay liquidaciones válidas para enviar.');
            }

            foreach ($settlements as $s) {
                $empleadoId = (int) ($s->empleado_id ?? 0);
                $valor = (float) ($s->gross_amount ?? 0);

                if ($empleadoId <= 0) continue;
                if ($valor <= 0) continue;

                // 1) Asegurar participante en el payrun
                NominaPayRunParticipant::updateOrCreate(
                    [
                        'pay_run_id' => $payRun->id,
                        'participant_type' => Empleado::class,
                        'participant_id' => $empleadoId,
                    ],
                    [
                        'company_id' => $companyId,
                        'link_type' => 'LABORAL',
                        'status' => 'DRAFT',
                        'gross_total' => 0,
                        'deductions_total' => 0,
                        'net_total' => 0,
                    ]
                );

                // 2) Crear/actualizar novedad (idempotente por source_ref)
                // source_ref evita duplicar si "Enviar a Nómina" se presiona 2 veces.
                NominaNovelty::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'pay_run_id' => $payRun->id,
                        'source_ref' => 'DESTAJO_SETTLEMENT_'.$s->id,
                    ],
                    [
                        'participant_type' => Empleado::class,
                        'participant_id' => $empleadoId,
                        'nomina_concept_id' => $concept->id,
                        'status' => 'PENDING',
                        'period_start' => $payRun->period_start,
                        'period_end' => $payRun->period_end,
                        'quantity' => 1,
                        'amount' => $valor,
                        'description' => 'Pago por destajo (Liquidación #'.$s->id.')',
                        'meta' => json_encode([
                            'settlement_id' => $s->id,
                            // si tienes otros campos útiles, agrégalos aquí:
                            // 'production_order_id' => $s->production_order_id ?? null,
                            // 'product_id' => $s->product_id ?? null,
                        ], JSON_UNESCAPED_UNICODE),
                    ]
                );

                // 3) Marcar liquidación como enviada (ajusta campos si difieren)
                $s->update([
                    'status' => 'SENT_TO_NOMINA',
                    'sent_to_nomina_at' => now(),
                    'nomina_pay_run_id' => $payRun->id,
                ]);
            }
        });
    }
}
