<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $cotizacion_id
 * @property integer $producto_id
 * @property string $nombre
 * @property string $descripcion
 * @property string $codigo
 * @property string $unidad_medida
 * @property float $cantidad
 * @property float $valor_unitario
 * @property float $descuento_porcentaje
 * @property float $descuento_valor
 * @property float $valor_total
 * @property string $observaciones
 * @property integer $orden
 * @property boolean $active
 * @property integer $categoria_id
 * @property integer $cargo_id
 * @property float $costo_dia
 * @property float $costo_hora
 * @property float $costo_unitario
 * @property integer $dias_diurnos
 * @property integer $dias_nocturnos
 * @property integer $dias_remunerados_diurnos
 * @property integer $dias_remunerados_nocturnos
 * @property integer $dominicales_diurnos
 * @property integer $dominicales_nocturnos
 * @property integer $horas_diurnas
 * @property integer $horas_remuneradas
 * @property boolean $incluir_dominicales
 * @property string $tipo_costo
 */
class CotizacionProducto extends Model
{
    use HasFactory;

    protected $table = 'ord_cotizacion_productos';

    protected $fillable = [
        'cotizacion_id',
        'cotizacion_item_id',
        'cotizacion_subitem_id',
        'item_propio_id',
        'parametrizacion_id',//cargo id Categoria=>Nomina, Seguridad Social, Parafiscales, Prestaciones Sociales
        'producto_id',
        'nombre',
        'descripcion',
        'codigo',
        'unidad_medida',
        'cantidad',
        'valor_unitario',
        'descuento_porcentaje',
        'descuento_valor',
        'valor_total',
        'observaciones',
        'orden',
        'active',
        'categoria_id',
        'cargo_id',
        'costo_dia',
        'costo_hora',
        'costo_unitario',
        'dias_diurnos',
        'dias_nocturnos',
        'dias_remunerados_diurnos',
        'dias_remunerados_nocturnos',
        'dominicales_diurnos',
        'dominicales_nocturnos',
        'horas_diurnas',
        'horas_remuneradas',
        'incluir_dominicales',
        'tipo_costo'
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_valor' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'orden' => 'integer',
        'active' => 'boolean',
        'categoria_id' => 'integer',
        'cargo_id' => 'integer',
        'costo_dia' => 'float',
        'costo_hora' => 'float',
        'costo_unitario' => 'float',
        'dias_diurnos' => 'integer',
        'dias_nocturnos' => 'integer',
        'dias_remunerados_diurnos' => 'integer',
        'dias_remunerados_nocturnos' => 'integer',
        'dominicales_diurnos' => 'integer',
        'dominicales_nocturnos' => 'integer',
        'horas_diurnas' => 'integer',
        'horas_remuneradas' => 'integer',
        'incluir_dominicales' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Relación con producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function cotizacionItem()
    {
        return $this->belongsTo(CotizacionItem::class, 'cotizacion_item_id');
    }

    public function cotizacionSubItem()
    {
        return $this->belongsTo(CotizacionSubImtes::class, 'cotizacion_subitem_id');
    }

    public function itemPropio()
    {
        return $this->belongsTo(ItemPropio::class, 'item_propio_id');
    }

    public function parametrizacion()
    {
        return $this->belongsTo(Parametrizacion::class, 'parametrizacion_id');
    }

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para ordenar por orden
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc');
    }

    /**
     * Calcular valor total automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $subtotal = $model->cantidad * $model->valor_unitario;
            $descuento = $model->descuento_valor + ($subtotal * ($model->descuento_porcentaje / 100));
            $model->valor_total = $subtotal - $descuento;
        });
    }
}
