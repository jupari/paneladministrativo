<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $observacion_id
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 */
class CotizacionObservacion extends Model
{
    use HasFactory;
    protected $table = 'ord_cotizaciones_observaciones';

    protected $fillable = ['cotizacion_id','observacion_id', 'active', 'created_at', 'updated_at'];

    public function observacion()
    {
        return $this->belongsTo(OrdObservacion::class, 'observacion_id');
    }
}
