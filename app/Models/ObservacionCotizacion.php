<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ObservacionCotizacion extends Model
{
    use HasFactory;

    protected $table = 'ord_cotizaciones_observaciones';

    protected $fillable = [
        'cotizacion_id',
        'observacion_id',
        'active'
    ];

    protected $casts = [
        'cotizacion_id' => 'integer',
        'observacion_id' => 'integer',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relación con la cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id', 'id');
    }

    /**
     * Relación con la observación
     */
    public function observacion()
    {
        return $this->belongsTo(Observacion::class, 'observacion_id', 'id');
    }

    /**
     * Scope para filtrar por cotización
     */
    public function scopePorCotizacion($query, $cotizacionId)
    {
        return $query->where('cotizacion_id', $cotizacionId);
    }

    /**
     * Scope para observaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Scope para observaciones con su texto
     */
    public function scopeConObservacion($query)
    {
        return $query->with(['observacion' => function($q) {
            $q->where('active', 1);
        }]);
    }
}
