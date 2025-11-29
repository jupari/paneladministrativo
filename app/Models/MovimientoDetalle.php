<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoDetalle extends Model
{
    use HasFactory;
    protected $table = 'inv_movimientos_detalles';
    public $timestamps = false;
    protected $fillable = [
        'movimiento_id',
        'num_doc',
        'producto_id',
        'codigo_producto',
        'talla',
        'color',
        'bodega_id',
        'tipo',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'referencia',
        'usuario_id',
        'created_at',
        'updated_at'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function propiedades()
    {
        return $this->hasMany(ProductoPropiedad::class, 'producto_id', 'producto_id');

    }
}
