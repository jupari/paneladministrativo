<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityResource;
use App\Http\Resources\Api\WorkshopOperatorResource;
use App\Http\Resources\Api\DamageTypeResource;
use App\Http\Traits\ApiResponses;
use App\Models\Activity;
use App\Models\WorkshopOperator;
use App\Models\DamageType;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    use ApiResponses;

    /**
     * GET /api/v1/catalog/activities
     */
    public function activities(): JsonResponse
    {
        $activities = Activity::where('is_active', true)->orderBy('name')->get();

        return $this->successResponse(ActivityResource::collection($activities));
    }

    /**
     * GET /api/v1/catalog/operators
     * Devuelve todos los operarios activos (sin filtrar por taller, útil para catálogo global).
     */
    public function operators(): JsonResponse
    {
        $operators = WorkshopOperator::where('is_active', true)->orderBy('name')->get();

        return $this->successResponse(WorkshopOperatorResource::collection($operators));
    }

    /**
     * GET /api/v1/catalog/damage-types
     */
    public function damageTypes(): JsonResponse
    {
        $types = DamageType::where('is_active', true)->orderBy('name')->get();

        return $this->successResponse(DamageTypeResource::collection($types));
    }
}
