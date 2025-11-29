<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $identificacion
 * @property string $nombre_completo
 * @property string $created_at
 * @property string $updated_at
 */
class Vendedor extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table = 'vendedores';
    protected $fillable = ['identificacion', 'nombre_completo', 'created_at','active', 'updated_at'];
}
