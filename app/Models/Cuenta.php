<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table='cuentas';
    public $timestamps=false;
    protected $fillable =[
        'user_id',
        'estado_id',
        'usuario_dist',
        'nombre_cuenta',
        'password_cuenta',
        'fecha_asig'
    ];

    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_dist','id');
    }

    public function estado(){
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
