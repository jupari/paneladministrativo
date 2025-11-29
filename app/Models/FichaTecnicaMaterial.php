<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $fichatecnica_id
 * @property string $referencia_codigo
 * @property float $cantidad
 * @property string $prop_1
 * @property string $prop_2
 * @property string $created_at
 * @property string $updated_at
 * @property string $codigo
 * @property PrdFichasTecnica $prdFichasTecnica
 */
class FichaTecnicaMaterial extends Model
{
    /**
     * @var array
     */
    protected $table = 'prd_fichas_tecnicas_materiales';
    public $timestamps = false;
    protected $fillable = ['fichatecnica_id', 'referencia_codigo', 'cantidad', 'unidad_medida', 'prop_1', 'prop_2', 'created_at', 'updated_at', 'codigo'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prdFichasTecnica()
    {
        return $this->belongsTo('App\Models\PrdFichasTecnica', 'fichatecnica_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'referencia_codigo', 'codigo');
    }
}
