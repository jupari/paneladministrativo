<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductionOrderResource;
use App\Http\Resources\Api\OrderOperationResource;
use App\Http\Traits\ApiResponses;
use App\Models\ProductionOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionOrdersController extends Controller
{
    use ApiResponses;

    /** Transiciones de estado permitidas */
    private const TRANSITIONS = [
        'pending'     => ['in_progress', 'cancelled'],
        'in_progress' => ['completed',   'cancelled'],
        'completed'   => [],
        'cancelled'   => [],
        // Compatibilidad con valores uppercase legacy
        'DRAFT'       => ['IN_PROGRESS', 'CANCELLED'],
        'IN_PROGRESS' => ['CLOSED',      'CANCELLED'],
        'CLOSED'      => [],
        'CANCELLED'   => [],
    ];

    /**
     * GET /api/v1/workshops/{workshopId}/orders
     */
    public function index(int $workshopId): JsonResponse
    {
        $orders = ProductionOrder::with('requiredActivities')
            ->where('workshop_id', $workshopId)
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse(ProductionOrderResource::collection($orders));
    }

    /**
     * GET /api/v1/production-orders/{orderId}
     */
    public function show(int $orderId): JsonResponse
    {
        $order = ProductionOrder::with('requiredActivities')->findOrFail($orderId);

        return $this->successResponse(new ProductionOrderResource($order));
    }

    /**
     * GET /api/v1/production-orders/{orderId}/activities
     * Lista las operaciones (ProductionOperation) registradas en esta orden.
     */
    public function activities(int $orderId): JsonResponse
    {
        ProductionOrder::findOrFail($orderId); // 404 si no existe

        $operations = \App\Models\ProductionOperation::with(['activity', 'operator'])
            ->where('production_order_id', $orderId)
            ->orderByDesc('registered_at')
            ->get();

        return $this->successResponse(OrderOperationResource::collection($operations));
    }

    /**
     * PATCH /api/v1/production-orders/{orderId}/status
     * Body: { "status": "in_progress" }
     */
    public function updateStatus(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
        ]);

        $order   = ProductionOrder::findOrFail($orderId);
        $current = $order->status;
        $next    = $request->status;

        $allowed = self::TRANSITIONS[$current] ?? [];
        if (!in_array($next, $allowed, true)) {
            return $this->errorResponse(
                "Transición inválida de '{$current}' a '{$next}'.",
                422
            );
        }

        $order->status = $next;
        if ($next === 'completed') {
            $order->completed_at = now();
        }
        $order->save();

        return $this->successResponse(new ProductionOrderResource($order));
    }
}
