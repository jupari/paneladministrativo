<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Models\ProductionOperation;
use App\Models\ProductionOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperationsBulkController extends Controller
{
    use ApiResponses;

    /**
     * POST /api/v1/operations/bulk
     *
     * Acepta los campos tal como los envía la app Flutter:
     *   order_id     → production_order_id
     *   operator_id  → workshop_operator_id
     *   idempotency_key es opcional (se genera si no viene)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'operations'                => 'required|array|min:1|max:500',
            'operations.*.workshop_id'  => 'required|integer|exists:workshops,id',
            'operations.*.order_id'     => 'required|integer|exists:production_orders,id',
            'operations.*.activity_id'  => 'required|integer|exists:activities,id',
            'operations.*.operator_id'  => 'required|integer|exists:workshop_operators,id',
            'operations.*.quantity'     => 'required|integer|min:1',
            'operations.*.registered_at'=> 'required|date',
            'operations.*.idempotency_key' => 'nullable|string|max:100',
        ]);

        $userId = $request->user()->id;
        $now    = now();

        // Deduplicar por idempotency_key si viene
        $incomingKeys = collect($request->operations)
            ->pluck('idempotency_key')
            ->filter();

        $existingKeys = $incomingKeys->isNotEmpty()
            ? ProductionOperation::whereIn('idempotency_key', $incomingKeys)
                ->pluck('idempotency_key')
                ->flip()
            : collect();

        $toInsert = collect($request->operations)
            ->reject(fn ($op) =>
                !empty($op['idempotency_key']) && $existingKeys->has($op['idempotency_key'])
            )
            ->map(fn ($op) => [
                'workshop_id'          => $op['workshop_id'],
                'production_order_id'  => $op['order_id'],
                'activity_id'          => $op['activity_id'],
                'workshop_operator_id' => $op['operator_id'],
                'user_id'              => $userId,
                'quantity'             => $op['quantity'],
                'registered_at'        => $op['registered_at'],
                'idempotency_key'      => $op['idempotency_key'] ?? Str::uuid()->toString(),
                'created_at'           => $now,
                'updated_at'           => $now,
            ])
            ->values()
            ->all();

        $synced = 0;
        if (!empty($toInsert)) {
            DB::transaction(function () use ($toInsert, &$synced) {
                foreach (array_chunk($toInsert, 100) as $chunk) {
                    ProductionOperation::insert($chunk);
                    $synced += count($chunk);
                }

                // Recalcular completed_units para cada orden afectada
                $affectedOrderIds = collect($toInsert)
                    ->pluck('production_order_id')
                    ->unique();

                foreach ($affectedOrderIds as $orderId) {
                    ProductionOrder::find($orderId)?->recalculateCompletedUnits();
                }
            });
        }

        return response()->json([
            'synced' => $synced,
            'failed' => 0,
        ]);
    }
}
