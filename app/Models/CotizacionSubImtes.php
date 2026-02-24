<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $codigo
 * @property string $nombre
 * @property integer $unidad_medida_id
 * @property float $cantidad
 * @property integer $orden
 * @property string $observacion
 * @property string $created_at
 * @property string $updated_at
 */
class CotizacionSubImtes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    use HasFactory;
    protected $table = 'ord_cotizaciones_subitems';
    public $timestamps = true;

    protected $fillable = [
        'codigo',
        'nombre',
        'unidad_medida_id',
        'cantidad',
        'orden',
        'observacion',
        'cotizacion_item_id'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'cantidad' => 'decimal:2',
        'orden' => 'integer'
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function cotizacionItems()
    {
        return $this->hasMany(CotizacionItem::class, 'cotizacion_item_id');
    }
}
