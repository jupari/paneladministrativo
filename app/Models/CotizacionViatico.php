<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionViatico extends Model
{
    protected $table = 'ord_cotizacion_viaticos';

    protected $fillable = [
        'cotizacion_id',
        'concepto',
        'valor',
        'orden',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'orden' => 'integer',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }
}
