<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps a ProductionOperation with its related Activity data,
 * used by GET /api/v1/production-orders/{id}/activities.
 */
class OrderOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'activity_id'  => $this->activity_id,
            'activity'     => new ActivityResource($this->whenLoaded('activity')),
            'operator_id'  => $this->workshop_operator_id,
            'operator'     => new WorkshopOperatorResource($this->whenLoaded('operator')),
            'quantity'     => $this->quantity,
            'registered_at'=> $this->registered_at?->toIso8601String(),
        ];
    }
}
