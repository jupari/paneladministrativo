<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoDet extends Model
{
    use HasFactory;
    protected $table = 'prd_procesos_det';
    protected $fillable = ['proceso_id', 'actividad', 'descripcion', 'tiempo', 'costo', 'active'];
    public $timestamps = false;

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }
}
