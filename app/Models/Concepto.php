<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $table = 'conceptos';

    protected $fillable = [
        'nombre',
        'tipo',
        'porcentaje_defecto',
        'active'
    ];

    protected $casts = [
        'porcentaje_defecto' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Relación con cotizaciones conceptos
     */
    public function cotizacionConceptos()
    {
        return $this->hasMany(CotizacionConcepto::class, 'concepto_id');
    }

    /**
     * Scope para conceptos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para conceptos por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
