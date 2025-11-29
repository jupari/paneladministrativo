<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;
    protected $table = 'inv_saldos';
    public $timestamps = false;
    protected $fillable = [
        'producto_id',
        'codigo_producto',
        'talla',
        'color',
        'bodega_id',
        'saldo',
        'ultimo_costo'
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $exists = self::where('producto_id', $model->producto_id)
    //             ->where('bodega_id', $model->bodega_id)
    //             ->exists();
    //         if ($exists) {
    //             throw new \Exception('Ya existe un saldo para este producto y bodega.');
    //         }
    //     });
    // }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }
}
