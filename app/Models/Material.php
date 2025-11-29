<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    protected $table = 'prd_materiales';
    public $timestamps = false;
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'unidad_medida',
        'active',
    ];
}
