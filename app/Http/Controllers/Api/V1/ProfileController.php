<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Http\Traits\ApiResponses;
use App\Models\DamagedGarment;
use App\Models\ProductionOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponses;

    /**
     * GET /api/v1/profile
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()));
    }

    /**
     * GET /api/v1/profile/account-status
     */
    public function accountStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id'           => $user->id,
            'email'        => $user->email,
            'role'         => $user->getRoleNames()->first() ?? 'operator',
            'is_active'    => true,
            'workshop_count' => $user->workshops()->count(),
        ]);
    }

    /**
     * GET /api/v1/profile/sync-status
     * Conteo de operaciones y daños registrados por el usuario en los últimos 7 días.
     */
    public function syncStatus(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $since  = now()->subDays(7);

        $operations = ProductionOperation::where('user_id', $userId)
            ->where('registered_at', '>=', $since)
            ->count();

        $damages = DamagedGarment::where('user_id', $userId)
            ->where('registered_at', '>=', $since)
            ->count();

        return $this->successResponse([
            'operations_last_7_days'      => $operations,
            'damaged_garments_last_7_days' => $damages,
            'since'                        => $since->toIso8601String(),
        ]);
    }
}
