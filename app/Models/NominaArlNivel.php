<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaArlNivel extends Model
{
    protected $table = 'nom_arl_niveles';
    protected $primaryKey = 'nivel';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['nivel', 'descripcion', 'porcentaje'];

    protected $casts = [
        'porcentaje' => 'float',
    ];
}
