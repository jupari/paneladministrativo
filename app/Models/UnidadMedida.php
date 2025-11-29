<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table='unidades_medida';
    public $timestamps = false;
    protected $fillable=[
        'nombre',
        'sigla',
        'active',
        'created_at',
        'updated_at'
    ];
}
