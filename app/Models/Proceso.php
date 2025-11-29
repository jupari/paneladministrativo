<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proceso extends Model
{
    use HasFactory;
    protected $table = 'prd_procesos';
    protected $fillable = ['codigo','nombre', 'descripcion', 'active'];
    public $timestamps = false;

    public function procesosDet()
    {
        return $this->hasMany(ProcesoDet::class, 'proceso_id');
    }
}
