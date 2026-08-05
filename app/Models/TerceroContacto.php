<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombres
 * @property string $apellidos
 * @property string $correo
 * @property string $celular
 * @property string $telefono
 * @property string $ext
 * @property string $created_at
 * @property string $updated_at
 * @property int $user_id
 */
class TerceroContacto extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    use HasFactory;

    protected $table = 'terceros_contactos';

    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['tercero_id', 'nombres', 'apellidos', 'correo', 'celular', 'celular_indicativo', 'telefono', 'telefono_indicativo', 'ext', 'cargo', 'created_at', 'updated_at', 'user_id'];

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'tercero_id');
    }
}
