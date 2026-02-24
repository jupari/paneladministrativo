<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $concepto_id
 * @property float $porcentaje
 * @property float $valor
 * @property integer $cotizacion_id
 * @property string $updated_at
 * @property string $created_at
 */
class CotizacionConcepto extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ord_cotizaciones_conceptos';

    /**
     * @var array
     */
    protected $fillable = ['concepto_id', 'porcentaje', 'valor', 'cotizacion_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'porcentaje' => 'decimal:2',
        'valor' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con la cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Relación con el concepto
     */
    public function concepto()
    {
        return $this->belongsTo(Concepto::class, 'concepto_id');
    }
}
