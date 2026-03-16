<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Models\WorkshopDevice;
use App\Services\Produccion\WorkshopPairingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkshopPairingAdminController extends Controller
{
    public function __construct(private readonly WorkshopPairingService $pairingService)
    {
    }

    /**
     * POST /admin.produccion.workshops/{workshopId}/pairing-qr
     */
    public function generatePairingQr(int $workshopId, Request $request): JsonResponse
    {
        $companyId = (int) session('company_id');

        $workshop = Workshop::query()
            ->where('id', $workshopId)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->firstOrFail();

        $ttlMinutes = max(1, min(30, (int) $request->input('ttl_minutes', 5)));

        $result = $this->pairingService->generateTokenForWorkshop(
            $workshop,
            $companyId,
            auth()->id(),
            $ttlMinutes
        );

        return response()->json([
            'data' => [
                'workshop_id' => $workshop->id,
                'pairing_token' => $result['plain_token'],
                'expires_at' => $result['token']->expires_at?->toIso8601String(),
                'qr_payload' => $result['qr_payload'],
            ],
        ]);
    }

    /**
     * GET /admin.produccion.workshops/{workshopId}/devices
     */
    public function devices(int $workshopId): JsonResponse
    {
        $companyId = (int) session('company_id');

        $devices = WorkshopDevice::query()
            ->where('workshop_id', $workshopId)
            ->where('company_id', $companyId)
            ->orderByDesc('id')
            ->get([
                'id', 'workshop_id', 'device_uuid', 'device_name', 'platform',
                'app_version', 'os_version', 'status',
                'last_login_at', 'last_sync_at', 'revoked_at', 'created_at',
            ]);

        return response()->json(['data' => $devices]);
    }

    /**
     * PATCH /admin.produccion.workshops/{workshopId}/devices/{deviceId}/status
     */
    public function updateDeviceStatus(int $workshopId, int $deviceId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:active,blocked,revoked',
        ]);

        $companyId = (int) session('company_id');

        $device = WorkshopDevice::query()
            ->where('id', $deviceId)
            ->where('workshop_id', $workshopId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $updated = $this->pairingService->updateDeviceStatus($device, $data['status'], auth()->id());

        return response()->json([
            'data' => [
                'id' => $updated->id,
                'status' => $updated->status,
                'revoked_at' => $updated->revoked_at?->toIso8601String(),
            ],
        ]);
    }
}
