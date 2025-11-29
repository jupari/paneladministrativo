<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPropio extends Model
{
    use HasFactory;

    protected $table='items_propios';
    public $timestamps = false;
    protected $fillable=[
        'categoria_id',
        'nombre',
        'codigo',
        'active',
        'unidad_medida',
        'created_at',
        'updated_at',
        'orden'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida', 'sigla');
    }
}
