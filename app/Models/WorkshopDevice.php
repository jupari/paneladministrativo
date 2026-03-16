<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopDevice extends Model
{
    protected $fillable = [
        'workshop_id',
        'company_id',
        'device_uuid',
        'device_name',
        'platform',
        'app_version',
        'os_version',
        'status',
        'last_login_at',
        'last_sync_at',
        'registered_by_user_id',
        'revoked_by_user_id',
        'revoked_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function qrTokensUsed(): HasMany
    {
        return $this->hasMany(WorkshopQrToken::class, 'used_by_device_id');
    }
}
