<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    use HasFactory;

    protected $table = 'ord_observaciones';

    protected $fillable = [
        'texto',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con las cotizaciones que tienen esta observación
     */
    public function cotizaciones()
    {
        return $this->belongsToMany(
            Cotizacion::class,
            'ord_cotizaciones_observaciones',
            'observacion_id',
            'cotizacion_id'
        )->withTimestamps();
    }

    /**
     * Scope para observaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Mutator para el texto
     */
    public function setTextoAttribute($value)
    {
        $this->attributes['texto'] = trim($value);
    }

    /**
     * Accessor para el texto formateado
     */
    public function getTextoFormateadoAttribute()
    {
        return trim($this->texto);
    }
}
