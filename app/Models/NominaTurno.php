<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaTurno extends Model
{
    protected $table = 'nom_turnos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_ordinaria',
        'es_dominical_festivo',
        'max_horas_ordinarias',
        'tiene_extras_diurnas',
        'tiene_extras_nocturnas',
        'max_horas_extras',
        'active',
    ];

    protected $casts = [
        'es_dominical_festivo'   => 'boolean',
        'tiene_extras_diurnas'   => 'boolean',
        'tiene_extras_nocturnas' => 'boolean',
        'active'                 => 'boolean',
    ];
}
