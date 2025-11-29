<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $nombre
 * @property string $created_at
 * @property string $updated_at
 */
class Pais extends Model
{
    use HasFactory;
    protected $table = 'paises';
    public $timestamps=false;
    protected $fillable = ['nombre', 'created_at', 'updated_at'];

    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }

 }
