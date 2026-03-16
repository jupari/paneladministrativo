<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Models\Workshop;
use App\Models\WorkshopDevice;
use App\Services\Produccion\WorkshopPairingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkshopPairingController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly WorkshopPairingService $pairingService)
    {
    }

    /**
     * POST /api/v1/workshops/pair
     */
    public function pair(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pairing_token' => 'required|string|max:255',
            'device_uuid' => 'required|string|max:120',
            'device_name' => 'nullable|string|max:120',
            'platform' => 'required|string|in:android,ios',
            'app_version' => 'nullable|string|max:30',
            'os_version' => 'nullable|string|max:30',
        ]);

        $result = $this->pairingService->pairDeviceWithToken(
            $data['pairing_token'],
            $data,
            $request->user()
        );

        if (!($result['ok'] ?? false)) {
            return $this->errorResponse($result['message'], $result['status']);
        }

        /** @var Workshop $workshop */
        $workshop = $result['workshop'];
        /** @var WorkshopDevice $device */
        $device = $result['device'];

        return $this->successResponse([
            'paired' => true,
            'workshop_id' => $workshop->id,
            'device_id' => $device->id,
            'device_status' => $device->status,
            'expires_at' => null,
        ], '', $result['status']);
    }

    /**
     * GET /api/v1/workshops/{workshopId}/devices
     */
    public function indexDevices(int $workshopId, Request $request): JsonResponse
    {
        $devices = WorkshopDevice::query()
            ->where('workshop_id', $workshopId)
            ->where('company_id', $request->user()->company_id)
            ->orderByDesc('id')
            ->get([
                'id', 'workshop_id', 'device_uuid', 'device_name', 'platform',
                'app_version', 'os_version', 'status',
                'last_login_at', 'last_sync_at', 'created_at',
            ]);

        return $this->successResponse($devices);
    }

    /**
     * PATCH /api/v1/workshops/{workshopId}/devices/{deviceId}/status
     */
    public function updateDeviceStatus(int $workshopId, int $deviceId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:active,blocked,revoked',
        ]);

        $device = WorkshopDevice::query()
            ->where('id', $deviceId)
            ->where('workshop_id', $workshopId)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        $updated = $this->pairingService->updateDeviceStatus($device, $data['status'], $request->user()->id);

        return $this->successResponse([
            'id' => $updated->id,
            'workshop_id' => $updated->workshop_id,
            'status' => $updated->status,
            'revoked_at' => $updated->revoked_at?->toIso8601String(),
        ]);
    }
}
