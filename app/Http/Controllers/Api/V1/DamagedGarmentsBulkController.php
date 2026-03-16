<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Models\DamagedGarment;
use App\Models\ProductionOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DamagedGarmentsBulkController extends Controller
{
    use ApiResponses;

    /**
     * POST /api/v1/damaged-garments/bulk
     *
     * Acepta los campos tal como los envía la app Flutter:
     *   order_id → production_order_id
     *   idempotency_key es opcional (se genera si no viene)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'damaged_garments'                         => 'required|array|min:1|max:500',
            'damaged_garments.*.workshop_id'           => 'required|integer|exists:workshops,id',
            'damaged_garments.*.order_id'              => 'required|integer|exists:production_orders,id',
            'damaged_garments.*.damage_type_id'        => 'required|integer|exists:damage_types,id',
            'damaged_garments.*.quantity'              => 'required|integer|min:1',
            'damaged_garments.*.notes'                 => 'nullable|string|max:500',
            'damaged_garments.*.registered_at'         => 'required|date',
            'damaged_garments.*.idempotency_key'       => 'nullable|string|max:100',
        ]);

        $userId = $request->user()->id;
        $now    = now();

        // Deduplicar por idempotency_key si viene
        $incomingKeys = collect($request->damaged_garments)
            ->pluck('idempotency_key')
            ->filter();

        $existingKeys = $incomingKeys->isNotEmpty()
            ? DamagedGarment::whereIn('idempotency_key', $incomingKeys)
                ->pluck('idempotency_key')
                ->flip()
            : collect();

        $toInsert = collect($request->damaged_garments)
            ->reject(fn ($item) =>
                !empty($item['idempotency_key']) && $existingKeys->has($item['idempotency_key'])
            )
            ->map(fn ($item) => [
                'workshop_id'          => $item['workshop_id'],
                'production_order_id'  => $item['order_id'],
                'damage_type_id'       => $item['damage_type_id'],
                'user_id'              => $userId,
                'quantity'             => $item['quantity'],
                'notes'                => $item['notes'] ?? null,
                'registered_at'        => $item['registered_at'],
                'idempotency_key'      => $item['idempotency_key'] ?? Str::uuid()->toString(),
                'created_at'           => $now,
                'updated_at'           => $now,
            ])
            ->values()
            ->all();

        $synced = 0;
        if (!empty($toInsert)) {
            DB::transaction(function () use ($toInsert, &$synced) {
                foreach (array_chunk($toInsert, 100) as $chunk) {
                    DamagedGarment::insert($chunk);
                    $synced += count($chunk);
                }

                // Recalcular completed_units restando los daños
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
