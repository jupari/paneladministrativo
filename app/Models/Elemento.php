<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Elemento extends Model
{
    use HasFactory;
    protected $table = 'elementos';
    public $timestamps = false;
    protected $fillable = [
        'codigo',
        'nombre',
        'valor',
        'valor_texto',
        'active',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function subElementos()
    {
        return $this->hasMany(SubElemento::class, 'elemento_id');
    }
}
