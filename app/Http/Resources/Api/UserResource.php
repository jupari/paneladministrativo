<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->getRoleNames()->first() ?? 'operator',
            'workshop_ids'=> $this->workshops()
                ->whereHas('companies', fn ($q) => $q->where('companies.id', $this->company_id))
                ->pluck('workshops.id')
                ->toArray(),
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),
            'avatar_url'  => null,
            'is_active'   => true,
        ];
    }
}
