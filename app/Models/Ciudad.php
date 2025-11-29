<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $nombre
 * @property integer $departamento_id
 * @property integer $pais_id
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $active
 */
class Ciudad extends Model
{
    use HasFactory;
    protected $table = 'ciudades';
    protected $fillable = ['departamento_id', 'nombre', 'pais_id','active'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }
}
