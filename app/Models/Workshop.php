<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workshop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'address',
        'coordinator_name', 'coordinator_phone', 'status', 'last_sync_at',
        'company_workshops'
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    /** Compañías a las que pertenece este taller (multi-compañía) */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_workshops');
    }

    public function operators(): HasMany
    {
        return $this->hasMany(WorkshopOperator::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_workshops');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(WorkshopDevice::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(WorkshopQrToken::class);
    }

    /** Cantidad de órdenes activas (in_progress) */
    public function getActiveOrdersCountAttribute(): int
    {
        return $this->productionOrders()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }

    /** IDs de los operarios de este taller */
    public function getOperatorIdsAttribute(): array
    {
        return $this->operators()->where('is_active', true)->pluck('id')->toArray();
    }
}
