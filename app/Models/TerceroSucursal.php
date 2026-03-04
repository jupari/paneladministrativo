<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $tercero_id
 * @property integer $ciudad_id
 * @property integer $vendedor_id
 * @property string $nombre_sucursal
 * @property string $telefono
 * @property string $celular
 * @property string $correo
 * @property string $direccion
 * @property string $persona_contacto
 * @property string $created_at
 * @property string $updated_at
 */
class TerceroSucursal extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

     use HasFactory;
     protected $table = 'terceros_sucursales';
     public $timestamps=false;

    /**
     * @var array
     */
    protected $fillable = ['tercero_id', 'ciudad_id', 'vendedor_id', 'nombre_sucursal', 'telefono', 'celular', 'correo', 'direccion', 'persona_contacto', 'created_at', 'updated_at'];

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'tercero_id');
    }

    public function ciudades(){
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function vendedores(){
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
}
