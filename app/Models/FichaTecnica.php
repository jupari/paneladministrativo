<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $codigo
 * @property string $nombre
 * @property string $coleccion
 * @property string $fecha
 * @property string $observacion
 * @property string $created_at
 * @property string $updated_at
 */
class FichaTecnica extends Model
{
    /**
     * @var array
     */

    protected $table = 'prd_fichas_tecnicas';
    public $timestamps = false;
    protected $fillable = ['codigo', 'nombre', 'coleccion', 'fecha', 'observacion','codigo_barras','codigo_producto_terminado' ,'created_at', 'updated_at', 'estado_ficha_tecnica_id' ];

    public function estadoFichaTecnica()
    {
        return $this->belongsTo(EstadoFichaTecnica::class, 'estado_ficha_tecnica_id');
    }

    public function estado(){
        return $this->belongsTo(EstadoFichaTecnica::class, 'estado_ficha_tecnica_id');
    }

    public function bocetos()
    {
        return $this->hasMany(FichaTecnicaBoceto::class, 'fichatecnica_id');
    }
}
