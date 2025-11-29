<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $pais_id
 * @property string $nombre
 * @property string $created_at
 * @property string $updated_at
 */
class Departamento extends Model
{
    /**
     * @var array
     */
    use HasFactory;
    protected $table='departamentos';
    public $timestamps=false;
    protected $fillable = ['pais_id', 'nombre', 'created_at', 'updated_at'];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class);
    }
}
