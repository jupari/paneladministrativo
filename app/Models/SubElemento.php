<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubElemento extends Model
{
    use HasFactory;
    protected $table = 'sub_elementos';
    public $timestamps = false;
    protected $fillable = [
        'codigo',
        'nombre',
        'codigo_padre',
        'valor',
        'contiene_prop',
        'active',
        'elemento_id',
    ];

    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }
}
