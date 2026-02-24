<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $cotizacion_id
 * @property string $tiempo_entrega
 * @property string $lugar_obra
 * @property string $duracion_oferta
 * @property string $garantia
 * @property string $forma_pago
 * @property string $created_at
 * @property string $updated_at
 */
class CotizacionCondicionComercial extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ord_cotizaciones_condiciones_comerciales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cotizacion_id',
        'tiempo_entrega',
        'lugar_obra',
        'duracion_oferta',
        'garantia',
        'forma_pago'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'cotizacion_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id', 'id');
    }

    /**
     * Scope para filtrar por cotización
     */
    public function scopePorCotizacion($query, $cotizacionId)
    {
        return $query->where('cotizacion_id', $cotizacionId);
    }

    /**
     * Mutators para limpiar datos
     */
    public function setTiempoEntregaAttribute($value)
    {
        $this->attributes['tiempo_entrega'] = $value ? trim($value) : null;
    }

    public function setLugarObraAttribute($value)
    {
        $this->attributes['lugar_obra'] = $value ? trim($value) : null;
    }

    public function setDuracionOfertaAttribute($value)
    {
        $this->attributes['duracion_oferta'] = $value ? trim($value) : null;
    }

    public function setGarantiaAttribute($value)
    {
        $this->attributes['garantia'] = $value ? trim($value) : null;
    }

    public function setFormaPagoAttribute($value)
    {
        $this->attributes['forma_pago'] = $value ? trim($value) : null;
    }
}
