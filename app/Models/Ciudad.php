<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombre
 * @property int $departamento_id
 * @property int $pais_id
 * @property string $created_at
 * @property string $updated_at
 * @property bool $active
 */
class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'ciudades';

    protected $fillable = ['departamento_id', 'nombre', 'pais_id', 'active'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }
}
