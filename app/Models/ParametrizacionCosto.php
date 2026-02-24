<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametrizacionCosto extends Model
{
    use HasFactory;

    protected $table='parametrizacion_costos';
    public $timestamps = false;
    protected $fillable=[
        'categoria_id',
        'item',
        'item_nombre',
        'unidad_medida',
        'costo_dia',
        'active',
        'created_at',
        'updated_at',
        'costo_unitario',
    ];

    protected $casts = [
        'created_at' => 'datetime', // Esto asegura que Laravel trate el campo como un objeto Carbon.
        'updated_at' => 'datetime',
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida', 'sigla');
    }
}
