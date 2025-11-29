<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPropiedad extends Model
{
    use HasFactory;
    protected $table = 'inv_productos_propiedades';
    public $timestamps = false;
    protected $fillable = ['producto_id','codigo','propiedad1', 'propiedad2'];

    public function productosPropiedades()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
