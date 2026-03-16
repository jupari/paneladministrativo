<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopQrToken extends Model
{
    protected $fillable = [
        'workshop_id',
        'company_id',
        'token_hash',
        'expires_at',
        'used_at',
        'used_by_device_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function usedByDevice(): BelongsTo
    {
        return $this->belongsTo(WorkshopDevice::class, 'used_by_device_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getIsUsedAttribute(): bool
    {
        return $this->used_at !== null;
    }
}
