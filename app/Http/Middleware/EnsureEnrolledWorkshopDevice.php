<?php

namespace App\Http\Middleware;

use App\Models\DamagedGarment;
use App\Models\DamageEvidence;
use App\Models\ProductionOrder;
use App\Models\WorkshopDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolledWorkshopDevice
{
    public function handle(Request $request, Closure $next, string $scope = 'workshop'): Response
    {
        $deviceUuid = $this->resolveDeviceUuid($request);

        if (!$deviceUuid) {
            return response()->json([
                'message' => 'Dispositivo no identificado. Debes enrolar el dispositivo para continuar.',
                'code' => 'DEVICE_UUID_REQUIRED',
            ], 403);
        }

        $workshopIds = $this->resolveWorkshopIds($request, $scope);
        if (empty($workshopIds)) {
            return response()->json([
                'message' => 'No se pudo determinar el taller para validar enrolamiento.',
                'code' => 'WORKSHOP_CONTEXT_REQUIRED',
            ], 400);
        }

        $user = $request->user();

        $enrolledWorkshopIds = WorkshopDevice::query()
            ->where('company_id', $user->company_id)
            ->where('device_uuid', $deviceUuid)
            ->where('status', 'active')
            ->whereIn('workshop_id', $workshopIds)
            ->pluck('workshop_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missing = array_diff($workshopIds, $enrolledWorkshopIds);
        if (!empty($missing)) {
            // Distinguir entre "nunca enrolado" y "enrolado pero revocado/bloqueado"
            $isRevoked = WorkshopDevice::query()
                ->where('company_id', $user->company_id)
                ->where('device_uuid', $deviceUuid)
                ->whereIn('workshop_id', $workshopIds)
                ->whereIn('status', ['blocked', 'revoked'])
                ->exists();

            if ($isRevoked) {
                return response()->json([
                    'message' => 'Dispositivo revocado o inactivo. Contacta a tu administrador para reactivar el acceso.',
                    'code' => 'DEVICE_REVOKED',
                ], 403);
            }

            return response()->json([
                'message' => 'Dispositivo no enrolado en el taller. Debes vincular el dispositivo para continuar.',
                'code' => 'DEVICE_NOT_ENROLLED',
            ], 403);
        }

        return $next($request);
    }

    private function resolveDeviceUuid(Request $request): ?string
    {
        $headerValue = $request->header('X-Device-UUID');
        if (is_string($headerValue) && trim($headerValue) !== '') {
            return trim($headerValue);
        }

        $inputValue = $request->input('device_uuid');
        if (is_string($inputValue) && trim($inputValue) !== '') {
            return trim($inputValue);
        }

        return null;
    }

    private function resolveWorkshopIds(Request $request, string $scope): array
    {
        $ids = match ($scope) {
            'workshop' => $this->workshopIdsFromWorkshopRoute($request),
            'order' => $this->workshopIdsFromOrderRoute($request),
            'operations-bulk' => $this->workshopIdsFromBulk($request->input('operations', [])),
            'damaged-bulk' => $this->workshopIdsFromBulk($request->input('damaged_garments', [])),
            'evidence-store' => $this->workshopIdsFromEvidenceStore($request),
            'evidence-route' => $this->workshopIdsFromEvidenceRoute($request),
            default => [],
        };

        return array_values(array_unique(array_filter($ids, fn ($id) => (int) $id > 0)));
    }

    private function workshopIdsFromWorkshopRoute(Request $request): array
    {
        $workshopId = (int) ($request->route('workshop') ?? $request->route('workshopId') ?? 0);

        return $workshopId > 0 ? [$workshopId] : [];
    }

    private function workshopIdsFromOrderRoute(Request $request): array
    {
        $orderId = (int) ($request->route('orderId') ?? $request->route('order') ?? 0);
        if ($orderId <= 0) {
            return [];
        }

        $workshopId = (int) ProductionOrder::query()->whereKey($orderId)->value('workshop_id');

        return $workshopId > 0 ? [$workshopId] : [];
    }

    private function workshopIdsFromBulk(array $rows): array
    {
        return collect($rows)
            ->pluck('workshop_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function workshopIdsFromEvidenceStore(Request $request): array
    {
        $damagedGarmentId = (int) $request->input('damaged_garment_id');
        if ($damagedGarmentId <= 0) {
            return [];
        }

        $workshopId = (int) DamagedGarment::query()->whereKey($damagedGarmentId)->value('workshop_id');

        return $workshopId > 0 ? [$workshopId] : [];
    }

    private function workshopIdsFromEvidenceRoute(Request $request): array
    {
        $evidenceId = (int) $request->route('id');
        if ($evidenceId <= 0) {
            return [];
        }

        $workshopId = (int) DamageEvidence::query()
            ->join('damaged_garments', 'damaged_garments.id', '=', 'damage_evidences.damaged_garment_id')
            ->where('damage_evidences.id', $evidenceId)
            ->value('damaged_garments.workshop_id');

        return $workshopId > 0 ? [$workshopId] : [];
    }
}
