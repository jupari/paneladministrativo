<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'inv_productos';
    public $timestamps = false;
    protected $fillable = [
        'tipo_producto',
        'codigo',
        'nombre',
        'descripcion',
        'unidad_medida',
        'stock_minimo',
        'marca',
        'categoria',
        'subcategoria',
        'precio',
        'active'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function productosPropiedades()
    {
        return $this->hasMany(ProductoPropiedad::class, 'producto_id');
    }
}
