<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property integer $id
 * @property string $num_documento
 * @property string $tipo
 * @property string $proyecto
 * @property string $Autorizacion
 * @property string $doc_origen
 * @property integer $version
 * @property integer $tercero_id
 * @property integer $tercero_sucursal_id
 * @property integer $tercero_contacto_id
 * @property string $observacion
 * @property float $subtotal
 * @property float $descuento
 * @property float $total
 * @property float $total_impuesto
 * @property integer $estado_id
 */
class Cotizacion extends Model
{

    use HasFactory;
    protected $table = 'ord_cotizacion';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = [
        'company_id',
        'num_documento',
        'fecha',
        'tipo',
        'proyecto',
        'autorizacion_id',
        'doc_origen',
        'version',
        'tercero_id',
        'tercero_sucursal_id',
        'tercero_contacto_id',
        'observacion',
        'subtotal',
        'descuento',
        'total',
        'total_impuesto',
        'estado_id',
        'user_id',
        'vendedor_id',
        'fecha_vencimiento',
        'fecha_envio',
        'fecha_respuesta'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'total_impuesto' => 'decimal:2',
        'fecha' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_envio' => 'datetime',
        'fecha_respuesta' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function autorizacion()
    {
        return $this->belongsTo(Autorizar::class, 'autorizacion_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'tercero_id');
    }

    public function terceroSucursal()
    {
        return $this->belongsTo(TerceroSucursal::class, 'tercero_sucursal_id');
    }

    public function terceroContacto()
    {
        return $this->belongsTo(TerceroContacto::class, 'tercero_contacto_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCotizacion::class, 'estado_id');
    }

    public function conceptos()
    {
        return $this->hasMany(CotizacionConcepto::class, 'cotizacion_id');
    }

    public function items()
    {
        return $this->hasMany(CotizacionItem::class, 'cotizacion_id');
    }

    public function observaciones()
    {
        return $this->hasMany(CotizacionObservacion::class, 'cotizacion_id');
    }

    public function condicionesComerciales()
    {
        return $this->hasMany(CotizacionCondicionComercial::class, 'cotizacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function productos()
    {
        return $this->hasMany(CotizacionProducto::class, 'cotizacion_id');
    }

    public function utilidades()
    {
        return $this->hasMany(CotizacionUtilidad::class, 'cotizacion_id');
    }


    /**
     * Obtener productos activos ordenados
     */
    public function productosActivos()
    {
        return $this->hasMany(CotizacionProducto::class, 'cotizacion_id')
            ->where('active', true)
            ->orderBy('orden', 'asc');
    }

    /**
     * Calcular totales de la cotización
     */
    public function calcularTotales()
    {
        $subtotal = $this->productos()->where('active', true)->sum('valor_total');
        $descuento = $this->descuento;
        $total_conceptos = $this->conceptos()->sum('valor');

        $this->subtotal = $subtotal;
        $this->total_impuesto = $total_conceptos;
        $this->total = $subtotal - $descuento + $total_conceptos;

        return $this;
    }

    /**
     * Recalcular totales y guardar
     */
    public function recalcularTotales()
    {
        $this->calcularTotales();
        $this->save();
        return $this;
    }

    /**
     * Agregar producto a la cotización
     */
    public function agregarProducto(array $datosProducto)
    {
        // Establecer orden automático si no se proporciona
        if (!isset($datosProducto['orden']) || !$datosProducto['orden']) {
            $datosProducto['orden'] = $this->obtenerSiguienteOrdenProducto();
        }

        $producto = $this->productos()->create($datosProducto);
        $this->recalcularTotales();

        return $producto;
    }

    /**
     * Obtener el siguiente número de orden para productos
     */
    public function obtenerSiguienteOrdenProducto()
    {
        return $this->productos()->max('orden') + 1;
    }

    /**
     * Obtener total de productos activos
     */
    public function getTotalProductosActivosAttribute()
    {
        return $this->productos()->where('active', true)->count();
    }

    /**
     * Obtener cantidad total de productos
     */
    public function getCantidadTotalProductosAttribute()
    {
        return $this->productos()->where('active', true)->sum('cantidad');
    }

    /**
     * Obtener valor total de productos sin descuentos
     */
    public function getSubtotalProductosBrutoAttribute()
    {
        return $this->productos()->where('active', true)->sum(DB::raw('cantidad * valor_unitario'));
    }

    /**
     * Obtener total de descuentos en productos
     */
    public function getTotalDescuentosProductosAttribute()
    {
        return $this->productos()->where('active', true)->sum('descuento_valor');
    }

    /**
     * Verificar si la cotización tiene productos
     */
    public function tieneProductos()
    {
        return $this->productos()->where('active', true)->exists();
    }

    /**
     * Duplicar productos a otra cotización
     */
    public function duplicarProductosA(Cotizacion $cotizacionDestino)
    {
        $productos = $this->productos()->where('active', true)->get();

        foreach ($productos as $producto) {
            $nuevoProducto = $producto->replicate();
            $nuevoProducto->cotizacion_id = $cotizacionDestino->id;
            $nuevoProducto->save();
        }

        $cotizacionDestino->recalcularTotales();

        return $productos->count();
    }

    /**
     * Obtener productos agrupados por categoría
     */
    public function productosAgrupadosPorCategoria()
    {
        return $this->productos()
            ->where('active', true)
            ->leftJoin('inv_productos', 'ord_cotizacion_productos.producto_id', '=', 'inv_productos.id')
            ->selectRaw('
                COALESCE(inv_productos.categoria, "Sin Categoría") as categoria,
                COUNT(*) as cantidad_items,
                SUM(ord_cotizacion_productos.cantidad) as cantidad_total,
                SUM(ord_cotizacion_productos.valor_total) as valor_total
            ')
            ->groupBy('categoria')
            ->orderBy('valor_total', 'desc')
            ->get();
    }

    /**
     * Aplicar descuento global a todos los productos
     */
    public function aplicarDescuentoGlobalProductos(float $porcentajeDescuento)
    {
        $productos = $this->productos()->where('active', true)->get();

        foreach ($productos as $producto) {
            $subtotal = $producto->cantidad * $producto->valor_unitario;
            $descuento = ($subtotal * $porcentajeDescuento) / 100;

            $producto->update([
                'descuento_porcentaje' => $porcentajeDescuento,
                'descuento_valor' => $descuento
            ]);
        }

        $this->recalcularTotales();

        return $productos->count();
    }


}
