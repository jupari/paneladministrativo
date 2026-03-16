<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkshopResource;
use App\Http\Resources\Api\WorkshopOperatorResource;
use App\Http\Traits\ApiResponses;
use App\Models\Workshop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkshopsController extends Controller
{
    use ApiResponses;

    /**
     * GET /api/v1/workshops
     * Lista solo los talleres asignados al usuario autenticado
     * y que pertenecen a su compañía.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $workshops = $request->user()
            ->workshops()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->with(['operators' => fn ($q) => $q->where('is_active', true)])
            ->get();

        return $this->successResponse(WorkshopResource::collection($workshops));
    }

    /**
     * GET /api/v1/workshops/{workshopId}
     * Detalle de un taller (acceso ya validado por EnsureWorkshopAccess).
     */
    public function show(int $workshopId): JsonResponse
    {
        $workshop = Workshop::with(['operators' => fn ($q) => $q->where('is_active', true)])
            ->findOrFail($workshopId);

        return $this->successResponse(new WorkshopResource($workshop));
    }

    /**
     * GET /api/v1/workshops/{workshopId}/operators
     * Operarios activos del taller.
     */
    public function operators(int $workshopId): JsonResponse
    {
        $operators = Workshop::findOrFail($workshopId)
            ->operators()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse(WorkshopOperatorResource::collection($operators));
    }
}
