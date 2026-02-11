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
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'departamentos';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pais_id', 'nombre'];

    /**
     * Get the país that owns the departamento.
     */
    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    /**
     * Get the ciudades for the departamento.
     */
    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'departamento_id');
    }
}
