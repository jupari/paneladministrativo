<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NovedadDetalle extends Model
{
    use HasFactory;

    protected $table='novedades_detalle';
    public $timestamps = false;
    protected $fillable=[
        'novedad_id',
        'nombre',
        'valor_admon',
        'valor_operativo',
        'created_at',
        'updated_at'
    ];

    public function novedad()
    {
        return $this->belongsTo(Novedad::class, 'novedad_id');
    }

}
