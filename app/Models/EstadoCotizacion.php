<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $nombre
 * @property boolean $active
 */
class EstadoCotizacion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    use HasFactory;
    protected $table = 'estados_cotizacion';

    /**
     * @var array
     */
    protected $fillable = ['estado', 'descripcion', 'color', 'active', 'orden'];
}
