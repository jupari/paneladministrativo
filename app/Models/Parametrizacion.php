<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $nombre
 * @property integer $active
 * @property string $created_at
 * @property string $updated_at
 */
class Parametrizacion extends Model
{
     use HasFactory;
     protected $table='parametrizacion';
     public $timestamps=false;

    protected $fillable = [
        'categoria_id',
        'cargo_id',
        'novedad_detalle_id',
        'valor_porcentaje',
        'valor_admon',
        'valor_obra',
        'created_at',
        'updated_at',
        'active',
    ];

    protected $casts = [
        'created_at' => 'datetime', // Esto asegura que Laravel trate el campo como un objeto Carbon.
        'updated_at' => 'datetime',
    ];
}







