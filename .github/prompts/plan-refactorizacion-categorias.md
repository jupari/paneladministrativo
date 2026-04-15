## Plan Detallado: Refactor Configuración de Productos No-Nómina

### Diagnóstico del Estado Actual

**Problema principal**: La pantalla de configuración de costos para productos no-nómina (maquinaria, insumos, etc.) muestra campos diseñados para nómina que no aplican: días remunerados diurnos/nocturnos, dominicales diurno/nocturno, turnos inicio/fin, horas normales/extras. Esto confunde al usuario y complejiza una operación que debería ser simple.

**Tabla `parametrizacion_costos`** tiene 3 campos de precio:
- `costo_dia` — costo por día
- `costo_unitario` — costo por unidad
- `costo_hora` — **no existe como columna**; se calcula como `costo_dia / 8`

**Modelo `CotizacionProducto`** tiene la fórmula en `boot()`:
```
valor_total = (cantidad × valor_unitario - descuento) + bono
```

---

### Cambios Propuestos (5 bloques)

---

#### BLOQUE 1 — Simplificar UI de configuración (No-Nómina)

**Archivo**: documento.js

**Función `generarTarjetasItemsCostos()`** (~línea 3350):
- Detectar `esNomina` como ya se hace.
- Para items **no-nómina**, generar una tarjeta simplificada con solo:
  1. **Tipo de costo** (radio: Unitario / Hora / Día) — ya existe
  2. **Unidad de medida** — precargada desde `parametrizacion_costos.unidad_medida`
  3. **Cantidad** — cantidad de unidades/horas/días
  4. **Valor sugerido** — precargado desde `parametrizacion_costos` según tipo seleccionado, editable
  5. **Precio Total** (readonly) = Cantidad × Valor

- **Eliminar para no-nómina**:
  - Tipo de día (normal/festivo)
  - Días diurnos / Días nocturnos
  - Días remunerados diurnos / nocturnos
  - Sección dominicales (checkbox + campos)
  - Turno hora inicial / hora final
  - Horas normales / horas extras (readonly)

**Función `generarCamposConfiguracion()`** (~línea 3505):
- Crear nueva función `generarCamposConfiguracionSimple(itemId)` para no-nómina con solo: unidad de medida, cantidad, valor del costo (según tipo), precio total.
- Mantener `generarCamposConfiguracion()` existente solo para nómina.
- En `generarTarjetasItemsCostos()`, llamar la función correcta según `esNomina`.

---

#### BLOQUE 2 — Precargar valores sugeridos desde `parametrizacion_costos`

**Archivo**: documento.js

**Función `cambiarTipoCostoVisual()`** (~línea 3798):
- Para no-nómina: al seleccionar tipo de costo, precargar inmediatamente el valor sugerido en el campo de costo.
- Mostrar badge "Valor sugerido" junto al campo para que el usuario sepa que puede editarlo.
- Precargar también `unidad_medida` en el campo correspondiente.

**Backend `obtenerValoresPorDefecto()`** (CotizacionProductoController.php):
- Ya funciona correctamente para `tipo_item = 'propio'` → busca en `parametrizacion_costos` por código.
- Agregar al response los 3 valores disponibles de una vez: `costo_unitario`, `costo_dia`, `costo_hora` (calculado), para que el frontend no necesite hacer una petición por cada cambio de tipo.

```php
// Retornar los 3 costos disponibles
$valores['costos_disponibles'] = [
    'unitario' => (float) ($parametrizacionCosto->costo_unitario ?? 0),
    'dia'      => (float) ($parametrizacionCosto->costo_dia ?? 0),
    'hora'     => $parametrizacionCosto->costo_dia > 0 
                  ? round($parametrizacionCosto->costo_dia / 8, 2) : 0,
];
```

**Frontend**: Cachear `costos_disponibles` en el item al primer load, y usarlos al cambiar tipo de costo sin peticiones adicionales.

---

#### BLOQUE 3 — Simplificar cálculo de precio (No-Nómina)

**Archivo**: documento.js

**Función `actualizarPrecioVisual()`** (~línea 4115):
- Para no-nómina, la fórmula es siempre: `precio = costoBase × cantidad`
  - **Unitario**: `costo_unitario × cantidad`
  - **Hora**: `costo_hora × cantidad_horas`
  - **Día**: `costo_dia × cantidad_dias`
- Eliminar la complejidad de días remunerados + dominicales + horas extras × factor 1.5 para items no-nómina.

**Backend** CotizacionProductoService.php `calcularValorPorConfiguracion()`:
- Para no-nómina, simplificar: `valor_unitario = costo × 1` (el costo ya viene calculado).
- La `cantidad` ya se guarda en `CotizacionProducto.cantidad`.
- `valor_total` se calcula por el `boot()` del modelo: `cantidad × valor_unitario - descuento`.

---

#### BLOQUE 4 — Simplificar validación en `finalizarConfiguracionCostos()`

**Archivo**: documento.js

- Para no-nómina, validar solo:
  1. Tipo de costo seleccionado
  2. Unidad de medida ingresada
  3. Cantidad > 0
  4. Valor del costo > 0
  5. Precio total > 0

- Eliminar validaciones de días remunerados y horas que no aplican a no-nómina.

- En `configuracionCosto` del payload, para no-nómina enviar solo los campos relevantes (no los 12+ campos de días/horas/dominicales que quedan en `null`).

---

#### BLOQUE 5 — Integración con totales generales

**Ya implementado** en sesiones anteriores — solo verificar:

- `CotizacionTotalesService::recalcular()` ya suma `ord_cotizacion_productos.valor_total` de TODOS los productos (nómina y no-nómina).
- `CotizacionProducto::boot()` calcula `valor_total = (cantidad × valor_unitario - descuento) + bono`.
- Al agregar/actualizar un producto no-nómina, se dispara `actualizarTotalesAutomaticamente()` → delega a `CotizacionTotalesService`.
- **No requiere cambios backend adicionales** salvo lo mencionado en Bloque 2.

---

### Resumen de Archivos a Modificar

| Archivo | Cambio | Bloque |
|---------|--------|--------|
| documento.js | Nueva `generarCamposConfiguracionSimple()` | 1 |
| documento.js | Bifurcar en `generarTarjetasItemsCostos()` | 1 |
| documento.js | Simplificar `actualizarPrecioVisual()` para no-nómina | 3 |
| documento.js | Simplificar `finalizarConfiguracionCostos()` validación | 4 |
| documento.js | Cache de `costos_disponibles` en `cambiarTipoCostoVisual()` | 2 |
| CotizacionProductoController.php | Retornar 3 costos en `obtenerValoresPorDefecto()` | 2 |

---

### Flujo UX Simplificado (No-Nómina)

```
1. Seleccionar categoría (ej: Maquinaria)
2. Seleccionar items del listado
3. Modal de configuración muestra por cada item:
   ┌─────────────────────────────────────────┐
   │ 🔧 RETROEXCAVADORA                      │
   │ Código: MAQ-001 • Maquinaria            │
   ├─────────────────────────────────────────┤
   │ Tipo de costo:  ○ Unitario  ○ Hora  ● Día│
   │                                         │
   │ Unidad de medida: [DIA]  ← precargado   │
   │ Cantidad:         [5]                    │
   │ Costo por día:    [$150,000] ← sugerido  │
   │                   (Valor sugerido ✓)     │
   │                                         │
   │ 💰 Precio Total:  $750,000.00           │
   └─────────────────────────────────────────┘
```

Comparado con lo actual (12+ campos con días diurnos, nocturnos, remunerados, dominicales, turnos, etc.), el refactor reduce a **4 campos editables**: tipo de costo, unidad de medida, cantidad, valor del costo.
