<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'sigla',
        'sigla',
        'active'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Scope para obtener solo unidades activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Accessor para obtener simbolo o sigla como fallback
     */
    public function getSimboloAttribute($value)
    {
        return $value ?: $this->sigla;
    }
}
