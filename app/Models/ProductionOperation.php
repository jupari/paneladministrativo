<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Empleado;

class ProductionOperation extends Model
{
    protected $fillable = [
        'workshop_id', 'production_order_id', 'activity_id',
        'workshop_operator_id', 'user_id', 'quantity',
        'registered_at', 'idempotency_key',
        'company_id', 'employee_id', 'order_operation_id',
        'notes', 'created_by', 'legacy_prod_worker_log_id',
        'work_date', 'shift', 'rejected_qty',
    ];

    protected $casts = ['registered_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(WorkshopOperator::class, 'workshop_operator_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'employee_id');
    }
}
