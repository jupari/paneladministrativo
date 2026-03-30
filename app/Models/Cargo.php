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
class Cargo extends Model
{
    /**
     * @var array
     */

     use HasFactory;

     protected $table='cargos';
     public $timestamps=false;

    protected $fillable = [
        'nombre',
        'active',
        'salario_base',
        'arl_nivel',
        'aplica_exoneracion_ley1607',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
        'salario_base'                => 'float',
        'aplica_exoneracion_ley1607'  => 'boolean',
    ];
}
