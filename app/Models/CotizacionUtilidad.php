<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionUtilidad extends Model
{

    protected $table = 'ord_cotizaciones_utilidad';
    public $timestamps = false;

     /**
     * @var array
     */
    protected $fillable = [
        'cotizacion_id',
        'categoria_id',
        'item_propio_id',
        'cargo_id',
        'tipo',
        'valor',
        'valor_calculado',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_calculado' => 'decimal:2',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function itemPropio()
    {
        return $this->belongsTo(ItemPropio::class, 'item_propio_id');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }
    /**
     * Scope para filtrar por tipo de aplicación
     */
    public function scopePorCategoria($query)
    {
        return $query->where('aplica_a', 'categoria');
    }

    public function scopePorItemPropio($query)
    {
        return $query->where('aplica_a', 'item_propio');
    }

    /**
     * Obtener el nombre del elemento al que se aplica
     */
    public function getNombreElementoAttribute()
    {
        if ($this->aplica_a === 'categoria' && $this->categoria) {
            return $this->categoria->nombre;
        }

        if ($this->aplica_a === 'item_propio' && $this->itemPropio) {
            return $this->itemPropio->nombre;
        }

        return 'Sin especificar';
    }

    /**
     * Obtener descripción completa de la utilidad
     */
    public function getDescripcionAttribute()
    {
        $elemento = $this->getNombreElementoAttribute();
        $tipo = $this->tipo === 'porcentaje' ? '%' : '$';
        $valor = number_format($this->valor, 2);

        return "Utilidad {$tipo}{$valor} aplicada a {$elemento}";
    }
}
