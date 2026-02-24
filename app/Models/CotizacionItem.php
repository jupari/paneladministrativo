<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $nombre
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 * @property integer $cotizacion_id
 * @property integer $subitem_id
 * @property float $cantidad
 * @property float $valor_unitario
 * @property float $valor_total
 * @property string $observaciones
 * @property integer $orden
 */
class CotizacionItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    use HasFactory;
    protected $table = 'ord_cotizaciones_items';
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'nombre',
        'active',
        'cotizacion_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'orden' => 'integer'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }
    public function subitems()
    {
        return $this->hasMany(CotizacionSubImtes::class, 'cotizacion_item_id');
    }
}
