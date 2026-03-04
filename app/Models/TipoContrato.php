<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $nombre
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 */
class TipoContrato extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tipos_contratos';
    public $timestamps=false;

    /**
     * @var array
     */
    protected $fillable = ['company_id','codigo','nombre', 'active', 'created_at', 'updated_at','tercerotipo_id'];

    public function Empleados(){
        return $this->hasMany(Empleado::class);
    }

}
