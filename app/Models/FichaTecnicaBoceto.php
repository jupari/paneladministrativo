<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $fichatecnica_id
 * @property string $nombre
 * @property string $archivo
 * @property string $created_at
 * @property string $updated_at
 * @property string $codigo
 * @property PrdFichasTecnica $prdFichasTecnica
 */
class FichaTecnicaBoceto extends Model
{
    /**
     * @var array
     */
    protected $table = 'prd_fichas_tecnicas_bocetos';
    public $timestamps = false;
    protected $fillable = ['fichatecnica_id', 'nombre', 'archivo', 'created_at', 'updated_at', 'codigo', 'observacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prdFichasTecnica()
    {
        return $this->belongsTo('App\Models\PrdFichasTecnica', 'fichatecnica_id');
    }
}
