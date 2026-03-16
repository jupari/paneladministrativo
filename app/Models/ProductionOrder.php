<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProductionOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'workshop_id', 'order_code', 'garment_type', 'garment_reference',
        'color', 'total_units', 'completed_units', 'cost_per_unit',
        'status', 'start_date', 'deadline', 'completed_at',
        'product_id', 'notes', 'created_by', 'legacy_prod_order_id',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'deadline'      => 'date',
        'completed_at'  => 'datetime',
        'cost_per_unit' => 'decimal:2',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(ProductionOperation::class);
    }

    public function damagedGarments(): HasMany
    {
        return $this->hasMany(DamagedGarment::class);
    }

    /** Actividades requeridas para completar una unidad de esta orden. */
    public function requiredActivities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'production_order_activities')
                    ->withPivot('position')
                    ->orderByPivot('position');
    }

    /**
     * Recalcula completed_units basándose en las actividades requeridas.
     *
     * completed_units = unidades buenas (que pasaron por todas las etapas).
     * Las prendas dañadas se rastrean aparte (damaged_garments) y NO se restan
     * de completed_units porque el operador ya las excluye al registrar
     * solo las unidades buenas por actividad.
     *
     * Ejemplo: 150 pedidas, 145 buenas por actividad, 5 dañadas
     *   → completed_units = MIN(145,145,145) = 145
     *   → pending = 150 - 145 - 5 = 0
     */
    //ayuda a que se actualice el campo completed_units de la orden cada vez que se registran operaciones o prendas dañadas
    public function recalculateCompletedUnits(): void
    {
        $requiredActivityIds = $this->requiredActivities()->pluck('activities.id');

        if ($requiredActivityIds->isNotEmpty()) {
            // MIN de las cantidades sumadas por cada actividad requerida
            $minPerActivity = DB::table('production_operations')
                ->select('activity_id', DB::raw('SUM(quantity) as total'))
                ->where('production_order_id', $this->id)
                ->whereIn('activity_id', $requiredActivityIds)
                ->groupBy('activity_id')
                ->pluck('total', 'activity_id');

            // Si falta alguna actividad requerida, su total es 0
            $quantities = $requiredActivityIds->map(fn ($id) => (int) ($minPerActivity[$id] ?? 0));
            $completed = $quantities->min() ?? 0;
        } else {
            // Sin actividades requeridas: suma total (compatibilidad)
            $completed = (int) $this->operations()->sum('quantity');
        }

        $this->update(['completed_units' => $completed]);
    }
}
