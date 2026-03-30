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
class Permission extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table = 'permissions';
    protected $fillable = ['name', 'guard_name', 'description','created_at', 'updated_at'];

}
