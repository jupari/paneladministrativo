<?php

namespace App\Models\Produccion;

use App\Models\ProductionOrder;

/**
 * Alias legacy: apunta a production_orders (tabla unificada).
 *
 * Los campos legacy se mapean así:
 *   code         → order_code
 *   objective_qty → total_units
 *   end_date     → deadline
 *
 * Para consultas raw sigue usando los nombres reales de columna
 * de production_orders. Este modelo existe solo para que el código
 * admin que usa ProdOrder::where(...) siga funcionando.
 */
class ProdOrder extends ProductionOrder
{
    // Hereda $table = 'production_orders' de ProductionOrder

    protected $fillable = [
        'company_id', 'order_code', 'product_id', 'total_units',
        'start_date', 'deadline', 'status', 'notes', 'created_by',
        'workshop_id', 'garment_type', 'garment_reference', 'color',
        'completed_units', 'cost_per_unit', 'completed_at',
        'legacy_prod_order_id',
    ];

    /**
     * Exponer los nombres legacy en JSON para que el front admin
     * reciba los mismos campos que antes (code, objective_qty, end_date).
     */
    protected $appends = ['code', 'objective_qty', 'end_date'];

    /**
     * Ocultar las columnas reales que ya se exponen vía appends.
     * Solo afecta a ProdOrder; ProductionOrder (API) no hereda esto.
     */
    protected $hidden = ['order_code', 'total_units', 'deadline'];

    /* ── Accessors de compatibilidad legacy ───────────────── */

    public function getCodeAttribute(): ?string
    {
        return $this->order_code;
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['order_code'] = $value;
    }

    public function getObjectiveQtyAttribute()
    {
        return $this->total_units;
    }

    public function setObjectiveQtyAttribute($value): void
    {
        $this->attributes['total_units'] = (int) $value;
    }

    public function getEndDateAttribute()
    {
        return $this->deadline;
    }

    public function setEndDateAttribute($value): void
    {
        $this->attributes['deadline'] = $value;
    }
}
