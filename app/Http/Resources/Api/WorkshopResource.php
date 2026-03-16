<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'code'                  => $this->code,
            'address'               => $this->address,
            'coordinator_name'      => $this->coordinator_name,
            'coordinator_phone'     => $this->coordinator_phone,
            'status'                => $this->status,
            'active_orders_count'   => $this->active_orders_count,
            'total_operators_count' => $this->operators()->where('is_active', true)->count(),
            'operator_ids'          => $this->operator_ids,
            'last_sync_at'          => $this->last_sync_at?->toIso8601String(),
        ];
    }
}
