<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;
    protected $table = 'inv_movimientos';
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'num_doc',
        'tipo',
        'observacion',
        'doc_ref',
        'created_at',
        'updated_at'
    ];


    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
