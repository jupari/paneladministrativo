## Plan: Alinear PDF de Cotizaciones con totales del sistema

El PDF actualmente recalcula los totales inline con IVA fijo del 19%, ignorando descuentos, viáticos, retenciones y utilidades que sí procesa el sistema backend. La solución es hacer que el PDF lea directamente los valores ya calculados y guardados en DB, y exponer los conceptos reales (nombre, valor) en lugar de lógica hardcodeada.

---

**Pasos**

**Fase 1 — Controlador**

1. En `generatePdf()` y `previewPdf()` (líneas 477 y 563), agregar a la carga eager las relaciones faltantes:
   - `conceptos.concepto` → para IVA, descuentos, retenciones con nombre y valor real
   - `viaticos` → para el renglón de viáticos
   - `condicionesComerciales` → para el footer dinámico
   - `productos.cotizacionItem` y `productos.cotizacionSubItem` → ya se usan en la vista pero no se precargan (actualmente solo viene `items` que es distinto)

2. Extraer la lógica de carga del logo a un método privado `resolverLogoBase64(Cotizacion $cotizacion)` para eliminar la duplicación entre ambos métodos *(paralelo con paso 1)*.

**Fase 2 — Plantilla PDF principal** (cotizacion.blade.php)

3. En la columna **VR TOTAL** de cada producto, cambiar `$producto->cantidad * $producto->valor_unitario` → `$producto->valor_total` (ya incluye descuentos por producto).

4. Cambiar la variable `$subtotal` que se acumula en el loop — mantenerla solo para la columna de subtotal por ítem. Para los totales finales, usar directamente `$cotizacion->subtotal` (que incluye utilidades + novedades).

5. Reemplazar el bloque de **TOTALES** hardcodeado (SUBTOTAL / IVA 19% / TOTAL) por una tabla dinámica:
   - Fila **SUBTOTAL**: `$cotizacion->subtotal`
   - Filas **CONCEPTOS** (loop sobre `$cotizacion->conceptos`): mostrar cada concepto con su nombre (`$cotizacionConcepto->concepto->nombre`) y su valor almacenado (`$cotizacionConcepto->valor`). Agrupar visualmente descuentos (rojo/negativo), impuestos (normal), retenciones (negativo).
   - Fila **VIÁTICOS** (si `$cotizacion->viaticos > 0`): `$cotizacion->viaticos`
   - Fila **TOTAL**: `$cotizacion->total` (negrita, color resaltado)
   - Mantener mismo estilo de tabla existente (gris/blanco, bordes, alineación derecha).

**Fase 3 — Partials del PDF**

6. En header.blade.php línea 27: cambiar `<strong>VERSIÓN:</strong> 1` → `<strong>VERSIÓN:</strong> {{ $cotizacion->version ?? 1 }}`

7. En footer.blade.php: reemplazar las condiciones comerciales hardcodeadas (TIEMPO DE ENTREGA, SITIO DE ENTREGA, etc.) por un loop sobre `$cotizacion->condicionesComerciales`. Si no hay condiciones guardadas, mostrar texto genérico como fallback.

---

**Archivos a modificar**

- CotizacionController.php — métodos `generatePdf()` y `previewPdf()` (líneas 477–650)
- cotizacion.blade.php — toda la sección de productos y totales
- header.blade.php — versión (línea 27)
- footer.blade.php — condiciones comerciales

---

**Verificación**

1. Generar PDF de una cotización que tenga descuentos configurados → verificar que aparecen como filas negativas en los totales.
2. Generar PDF de una cotización con IVA distinto al 19% → verificar que el porcentaje real aparece, no el hardcodeado.
3. Generar PDF de una cotización con viáticos → verificar que aparece la fila de viáticos.
4. Verificar que el TOTAL del PDF coincide exactamente con `$cotizacion->total` en DB.
5. Revisar cotización sin condiciones comerciales → footer muestra texto de fallback sin error.
6. Verificar versión en header refleja el campo `version` de la cotización.

---

**Decisiones**

- Los totales del PDF se leen **directamente de DB** (`subtotal`, `descuento`, `total_impuesto`, `viaticos`, `total`) en lugar de recalcular — garantiza consistencia con lo que ve el usuario en la web.
- Se usa `$producto->valor_total` (campo almacenado) por línea, no cantidad×precio — ya incluye descuentos por ítem.
- Se mantiene el diseño gráfico existente: mismos colores gris/blanco, fuente DejaVu Sans, estructura de tablas — solo se añaden filas al bloque de totales.
- Queda fuera del alcance: modificar las plantillas legacy `pdf.blade.php`, `pdf-exacto.blade.php`, `pdf-simple.blade.php` (no se usan).

---

¿Confirmas este plan o hay algo que ajustar (por ejemplo si el footer debe seguir con texto fijo o debe ser dinámico)?
