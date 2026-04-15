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
class CargoTablaPrecio extends Model
{
    /**
     * @var array
     */

     use HasFactory;

     protected $table='cargos_tabla_precios';
     public $timestamps=false;

    protected $fillable = [
            'id' ,
            'cargo_id' ,
            'utilidad_pct' ,
            'horas_diarias' ,
            'base_costo_dia' ,
            'base_costo_hora'  ,
            'hora_ordinaria'  ,
            'recargo_nocturno'  ,
            'hora_extra_diurna'  ,
            'hora_extra_nocturna'  ,
            'hora_dominical' ,
            'hora_extra_dominical_diurna' ,
            'hora_extra_dominical_nocturna',
            'valor_dia_ordinario',
            'created_at' ,
            'updated_at' ,
    ];

    protected $casts = [
        'created_at' => 'datetime', // Esto asegura que Laravel trate el campo como un objeto Carbon.
        'updated_at' => 'datetime',
    ];
}
