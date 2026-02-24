<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table='categorias';
    public $timestamps = false;
    protected $fillable=[
        'nombre',
        'active',
        'costos',
        'created_at',
        'updated_at'
    ];

    public function itemsPropios()
    {
        return $this->hasMany(ItemPropio::class, 'categoria_id');
    }

    public function cotizacionProductos()
    {
        return $this->hasMany(CotizacionProducto::class, 'categoria_id');
    }
}
