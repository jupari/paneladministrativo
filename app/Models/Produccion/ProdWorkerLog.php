<?php

namespace App\Models\Produccion;

use App\Models\ProductionOperation;

/**
 * Alias legacy: apunta a production_operations (tabla unificada).
 *
 * Mapeo de columnas:
 *   order_id     → production_order_id
 *   operation_id → activity_id
 *   qty          → quantity
 *   worked_at    → registered_at
 */
class ProdWorkerLog extends ProductionOperation
{
    // Hereda $table = 'production_operations' de ProductionOperation

    protected $fillable = [
        'company_id', 'production_order_id', 'order_operation_id',
        'activity_id', 'employee_id', 'quantity',
        'registered_at', 'notes', 'created_by',
        'workshop_id', 'workshop_operator_id', 'user_id',
        'idempotency_key', 'legacy_prod_worker_log_id',
    ];

    protected $casts = [
        'quantity'      => 'decimal:4',
        'registered_at' => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /* ── Accessors de compatibilidad legacy ───────────────── */

    protected $appends = ['order_id', 'operation_id', 'qty', 'worked_at'];
    protected $hidden  = ['production_order_id', 'activity_id', 'quantity', 'registered_at'];

    public function getOrderIdAttribute()
    {
        return $this->production_order_id;
    }

    public function setOrderIdAttribute($value): void
    {
        $this->attributes['production_order_id'] = $value;
    }

    public function getOperationIdAttribute()
    {
        return $this->activity_id;
    }

    public function setOperationIdAttribute($value): void
    {
        $this->attributes['activity_id'] = $value;
    }

    public function getQtyAttribute()
    {
        return $this->quantity;
    }

    public function setQtyAttribute($value): void
    {
        $this->attributes['quantity'] = $value;
    }

    public function getWorkedAtAttribute()
    {
        return $this->registered_at;
    }

    public function setWorkedAtAttribute($value): void
    {
        $this->attributes['registered_at'] = $value;
    }

    /* ── Relaciones ───────────────── */

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProdOrder::class, 'production_order_id');
    }

    public function settlement()
    {
        return $this->hasOne(ProdWorkerSettlement::class, 'order_operation_id', 'order_operation_id');
    }
}
