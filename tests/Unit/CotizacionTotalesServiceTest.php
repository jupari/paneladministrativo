<?php

use App\Models\Concepto;
use App\Models\CotizacionConcepto;
use App\Services\CotizacionTotalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Se usa DatabaseTransactions (en lugar de RefreshDatabase) porque el esquema
// de este proyecto ya existe en la base de datos de pruebas configurada; solo
// necesitamos que los cambios se reviertan al finalizar el test.
uses(TestCase::class, DatabaseTransactions::class);

test('recalcularConceptos persiste el valor recalculado en cada concepto', function () {
    // `ord_cotizaciones_conceptos.cotizacion_id` tiene FK hacia `ord_cotizacion`.
    // recalcularConceptos() solo necesita el id numérico (no toca la cotización
    // en sí), así que se desactivan temporalmente las FK para no tener que
    // construir toda la cadena de dependencias (cotización, tercero, company...)
    // para esta prueba unitaria y aislada del método.
    Schema::disableForeignKeyConstraints();

    $cotizacionId = 999001;

    $conceptoDescuento = Concepto::create([
        'nombre' => 'Descuento test',
        'tipo' => 'DESCUENTO',
        'porcentaje_defecto' => 0,
        'active' => true,
    ]);

    $conceptoImpuesto = Concepto::create([
        'nombre' => 'IVA test',
        'tipo' => 'IMPUESTO',
        'porcentaje_defecto' => 0,
        'active' => true,
    ]);

    $conceptoRetencion = Concepto::create([
        'nombre' => 'Retención test',
        'tipo' => 'RETENCION',
        'porcentaje_defecto' => 0,
        'active' => true,
    ]);

    $ccDescuento = CotizacionConcepto::create([
        'cotizacion_id' => $cotizacionId,
        'concepto_id' => $conceptoDescuento->id,
        'porcentaje' => 10,
        'valor' => 0,
    ]);

    $ccImpuesto = CotizacionConcepto::create([
        'cotizacion_id' => $cotizacionId,
        'concepto_id' => $conceptoImpuesto->id,
        'porcentaje' => 19,
        'valor' => 0,
    ]);

    // Concepto de valor fijo, sin porcentaje: debe persistirse igual, sin cambiar.
    $ccRetencion = CotizacionConcepto::create([
        'cotizacion_id' => $cotizacionId,
        'concepto_id' => $conceptoRetencion->id,
        'porcentaje' => 0,
        'valor' => 5000,
    ]);

    Schema::enableForeignKeyConstraints();

    $service = app(CotizacionTotalesService::class);

    $reflection = new ReflectionMethod($service, 'recalcularConceptos');
    $reflection->setAccessible(true);

    $subtotalConUtilidad = 100000.0;
    $resultado = $reflection->invoke($service, $cotizacionId, $subtotalConUtilidad);

    // Descuento: 10% de 100000 = 10000
    expect($resultado['descuentos'])->toBe(10000.0);

    // Base gravable = 100000 - 10000 = 90000
    // Impuesto: 19% de 90000 = 17100
    expect($resultado['impuestos'])->toBe(17100.0);

    // Retención: valor fijo 5000 (sin porcentaje, no se recalcula desde base)
    expect($resultado['retenciones'])->toBe(5000.0);

    // El valor recalculado debe quedar persistido en cada fila (bug original: solo se acumulaba en memoria)
    expect((float) $ccDescuento->fresh()->valor)->toBe(10000.0);
    expect((float) $ccImpuesto->fresh()->valor)->toBe(17100.0);
    expect((float) $ccRetencion->fresh()->valor)->toBe(5000.0);
});
