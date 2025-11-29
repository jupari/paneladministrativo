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
 */
class TipoIdentificacion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

     use HasFactory;
    protected $table = 'tipo_identificacion';
    public $timestamps=false;

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'active', 'created_at', 'updated_at'];
}
