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
class TerceroTipo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

     use HasFactory;

     public $timestamps=false;
    protected $table = 'terceros_tipos';

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'active', 'created_at', 'updated_at'];

    public function terceros()
    {
        return $this->hasMany(Tercero::class, 'tercerotipo_id');
    }
}
