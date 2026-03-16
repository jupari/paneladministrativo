<?php

namespace App\Services\Produccion;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopDevice;
use App\Models\WorkshopQrToken;
use Illuminate\Support\Str;

class WorkshopPairingService
{
    public function generateTokenForWorkshop(
        Workshop $workshop,
        int $companyId,
        ?int $createdByUserId = null,
        int $ttlMinutes = 5
    ): array {
        WorkshopQrToken::query()
            ->where('workshop_id', $workshop->id)
            ->where('company_id', $companyId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        do {
            $plainToken = 'ptk_' . Str::random(64);
            $tokenHash = hash('sha256', $plainToken);
        } while (WorkshopQrToken::query()->where('token_hash', $tokenHash)->exists());

        $token = WorkshopQrToken::query()->create([
            'workshop_id' => $workshop->id,
            'company_id' => $companyId,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes($ttlMinutes),
            'created_by_user_id' => $createdByUserId,
        ]);

        return [
            'token' => $token,
            'plain_token' => $plainToken,
            'qr_payload' => 'swt://pair?token=' . $plainToken,
        ];
    }

    public function pairDeviceWithToken(string $plainToken, array $deviceData, User $user): array
    {
        $tokenHash = hash('sha256', $plainToken);

        $token = WorkshopQrToken::query()
            ->with('workshop')
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$token) {
            return ['ok' => false, 'status' => 400, 'message' => 'Token de vinculación inválido.'];
        }

        if ($token->company_id !== (int) $user->company_id) {
            return ['ok' => false, 'status' => 403, 'message' => 'Sin acceso al taller de este token.'];
        }

        if ($token->used_at !== null) {
            return ['ok' => false, 'status' => 409, 'message' => 'Token de vinculación ya utilizado.'];
        }

        if ($token->expires_at !== null && $token->expires_at->isPast()) {
            return ['ok' => false, 'status' => 410, 'message' => 'Token de vinculación expirado.'];
        }

        $hasAccess = $user->workshops()
            ->where('workshops.id', $token->workshop_id)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $user->company_id))
            ->exists();

        if (!$hasAccess) {
            return ['ok' => false, 'status' => 403, 'message' => 'El usuario no tiene acceso al taller.'];
        }

        $device = WorkshopDevice::query()->firstOrNew([
            'workshop_id' => $token->workshop_id,
            'device_uuid' => $deviceData['device_uuid'],
        ]);

        $isNew = !$device->exists;

        $device->fill([
            'company_id' => $token->company_id,
            'device_name' => $deviceData['device_name'] ?? null,
            'platform' => $deviceData['platform'],
            'app_version' => $deviceData['app_version'] ?? null,
            'os_version' => $deviceData['os_version'] ?? null,
            'status' => 'active',
            'last_login_at' => now(),
            'registered_by_user_id' => $device->registered_by_user_id ?? $user->id,
            'revoked_by_user_id' => null,
            'revoked_at' => null,
        ]);

        $device->save();

        $token->update([
            'used_at' => now(),
            'used_by_device_id' => $device->id,
        ]);

        return [
            'ok' => true,
            'status' => $isNew ? 201 : 200,
            'workshop' => $token->workshop,
            'device' => $device,
        ];
    }

    public function updateDeviceStatus(WorkshopDevice $device, string $status, ?int $userId = null): WorkshopDevice
    {
        $payload = ['status' => $status];

        if ($status === 'revoked') {
            $payload['revoked_at'] = now();
            $payload['revoked_by_user_id'] = $userId;
        }

        if ($status === 'active') {
            $payload['revoked_at'] = null;
            $payload['revoked_by_user_id'] = null;
        }

        $device->update($payload);

        return $device->fresh();
    }
}
