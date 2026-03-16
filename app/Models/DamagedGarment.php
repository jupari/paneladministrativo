<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DamagedGarment extends Model
{
    protected $fillable = [
        'workshop_id', 'production_order_id', 'damage_type_id',
        'user_id', 'quantity', 'notes', 'registered_at', 'idempotency_key',
    ];

    protected $casts = ['registered_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function damageType(): BelongsTo
    {
        return $this->belongsTo(DamageType::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(DamageEvidence::class);
    }
}
