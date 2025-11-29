<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $plantilla
 * @property string $archivo
 * @property string $campos
 * @property string $created_at
 * @property string $updated_at
 */
class Plantilla extends Model
{
    /**
     * @var array
     */

    use HasFactory;

    protected $table='plantillas';
    public $timestamps=false;

    protected $fillable = ['plantilla', 'archivo', 'nombre_archivo','campos','active','created_at', 'updated_at'];
}
