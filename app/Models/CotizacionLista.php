<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotizacionLista extends Model
{
    use HasFactory;

    protected $table = 'ord_cotizaciones_listas';
    public $timestamps = true;

    protected $fillable = [
        'cotizacion_id',
        'cotizacion_producto_id',
        'novedad_detalle_id',
        'valor',
        'cantidad',
        'subtotal',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'valor'    => 'decimal:2',
        'cantidad' => 'decimal:3',
        'subtotal' => 'decimal:2',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function cotizacionProducto()
    {
        return $this->belongsTo(CotizacionProducto::class, 'cotizacion_producto_id');
    }

    public function novedadDetalle()
    {
        return $this->belongsTo(NovedadDetalle::class, 'novedad_detalle_id');
    }
}
