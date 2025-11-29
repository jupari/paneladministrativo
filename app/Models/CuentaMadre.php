<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaMadre extends Model
{
    use HasFactory;

    protected $table='cuentas_madres';
    public $timestamps = false;
    protected $fillable=[
        'email',
        'password',
        'nombre',
        'usuario_dist',
        'cm_asociada',
        'cta_ppal',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'usuario_dist');
    }

    public function account(){
        return $this->belongsTo(Account::class, 'email','email');
    }

}
