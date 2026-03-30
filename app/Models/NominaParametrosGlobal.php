<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaParametrosGlobal extends Model
{
    protected $table = 'nom_parametros_globales';

    protected $fillable = [
        'vigencia',
        'smlv',
        'aux_transporte',
        'uvt',
        'tope_exoneracion_ley1607',
        'active',
    ];

    protected $casts = [
        'smlv'           => 'float',
        'aux_transporte' => 'float',
        'uvt'            => 'float',
        'active'         => 'boolean',
    ];

    /**
     * Retorna los parámetros activos para el año dado.
     * Si no existe el año exacto, retorna el registro activo más reciente.
     */
    public static function paraAno(int $ano): self
    {
        return static::where('vigencia', $ano)->where('active', true)->firstOr(function () {
            return static::where('active', true)->orderByDesc('vigencia')->firstOrFail();
        });
    }
}
