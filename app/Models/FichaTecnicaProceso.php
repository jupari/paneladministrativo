<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $fichatecnica_id
 * @property string $proceso
 * @property float $costo
 * @property string $observacion
 * @property string $created_at
 * @property string $updated_at
 * @property string $codigo
 * @property PrdFichasTecnica $prdFichasTecnica
 */
class FichaTecnicaProceso extends Model
{
    /**
     * @var array
     */
    protected $table = 'prd_fichas_tecnicas_procesos';
    public $timestamps = false;
    protected $fillable = ['fichatecnica_id', 'proceso_id', 'costo', 'observacion', 'created_at', 'updated_at', 'codigo','codigo_proceso'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prdFichasTecnica()
    {
        return $this->belongsTo('App\Models\PrdFichasTecnica', 'fichatecnica_id');
    }
}
