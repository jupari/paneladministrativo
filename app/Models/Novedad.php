<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    use HasFactory;

    protected $table='novedades';
    public $timestamps = false;
    protected $fillable=[
        'nombre',
        'active',
        'created_at',
        'updated_at'
    ];

    public function detalles()
    {
        return $this->hasMany(NovedadDetalle::class, 'novedad_id');
    }
}
