## Plan: Corregir Cálculo de Totales en Cotizaciones

Los totales no suman todos los valores porque hay **3 problemas críticos**: un endpoint que retorna datos hardcodeados, dos servicios de cálculo con lógica inconsistente, y una tabla completa (`ord_cotizaciones_listas`) cuyos valores no se incluyen en ningún cálculo.

---

### Problemas Detectados

| # | Problema | Ubicación | Impacto |
|---|---------|-----------|---------|
| P1 | `obtenerTotales()` retorna **datos hardcodeados** (1250.75, 62.54, etc.) | CotizacionProductoController.php | Frontend `cotizacion-productos.js` siempre muestra valores falsos |
| P2 | `actualizarTotalesCotizacion()` solo suma `valor_total`, **ignora utilidades, conceptos, impuestos y retenciones** | CotizacionProductoController.php | Total = subtotal bruto sin ningún concepto |
| P3 | Dos servicios con lógica diferente | CotizacionProductoService.php vs CotizacionTotalesService.php | Resultados inconsistentes según qué operación disparó el cálculo |
| P4 | `ord_cotizaciones_listas` **nunca se incluye** en cálculos | CotizacionLista.php | Valores de lista perdidos en totales |
| P5 | `CotizacionTotalesService` **no maneja retenciones** | CotizacionTotalesService.php | Retenciones se ignoran cuando se recalcula desde utilidades |

---

### Steps

#### Fase 1: Unificar servicio de cálculo (Backend)

1. **Agregar retenciones a `CotizacionTotalesService::recalcularConceptos()`** — Agregar tercera pasada para tipos `RETENCION/RETENTION/RET/RETE`, retornar `['descuentos', 'impuestos', 'retenciones']` y ajustar fórmula final a: `total = subtotalConUtilidad - descuentos + impuestos - retenciones`
   - **Archivo**: CotizacionTotalesService.php

2. **Incluir items de `ord_cotizaciones_listas` en el subtotal base** — Consultar `CotizacionLista::where('cotizacion_id', ...)` y sumar sus `subtotal` al base (si no están ya reflejados en `valor_total` del producto vinculado)
   - **Archivo**: CotizacionTotalesService.php

3. **Hacer que `recalcular()` retorne estructura detallada** — Agregar desglose de productos, utilidades, conceptos y cálculo final (similar a lo que ya tiene `CotizacionProductoService`)
   - **Archivo**: CotizacionTotalesService.php

#### Fase 2: Corregir endpoints y eliminar código muerto (Backend)

4. **Reemplazar `obtenerTotales()` hardcodeado** — Que delegue a `CotizacionTotalesService::recalcular()` *(depende de paso 1-3)*
   - **Archivo**: CotizacionProductoController.php

5. **Reemplazar `actualizarTotalesCotizacion()` privado** — Que delegue a `CotizacionTotalesService::recalcular()` *(depende de paso 1-3)*
   - **Archivo**: CotizacionProductoController.php

6. **Redirigir `CotizacionProductoService::actualizarTotalesAutomaticamente()`** — Que llame a `CotizacionTotalesService::recalcular()` en vez de su propio método *(depende de paso 1-3)*
   - **Archivo**: CotizacionProductoService.php

7. **Actualizar `obtenerTotalesCotizacion()` en controller** — Que también use `CotizacionTotalesService` *(depende de paso 1-3)*
   - **Archivo**: CotizacionProductoController.php

#### Fase 3: Agregar trigger desde conceptos (Backend)

8. **Agregar recálculo automático en `CotizacionConceptoController`** — Llamar a `CotizacionTotalesService::recalcular()` después de `store()`, `update()` y `destroy()` *(paralelo con pasos 4-7)*
   - **Archivo**: CotizacionConceptoController.php

#### Fase 4: Verificar Frontend (JS)

9. **Ajustar response keys en `cotizacion-productos.js`** — El JS espera `data.descuento_total` pero el servicio retorna `data.descuento`. Alinear keys *(depende de paso 4)*
   - **Archivo**: cotizacion-productos.js

10. **Verificar `documento.js` y `sticky-summary.js`** — Confirmar que los hidden fields `#subtotal`, `#descuento`, `#total_impuesto`, `#total` se actualicen correctamente con la nueva estructura
    - **Archivos**: documento.js, sticky-summary.js

---

### Verificación

1. Crear cotización con productos de múltiples categorías + utilidades + conceptos (descuento, impuesto, retención) → verificar que total refleje TODOS los componentes
2. Revisar logs Laravel después de cada CRUD para confirmar servicio unificado ejecutándose
3. Agregar/eliminar concepto y verificar actualización de totales sin recargar página
4. Eliminar producto y confirmar que utilidades y conceptos se recalculan
5. Comparar totales antes/después en cotización existente con datos reales

---

### Decisión clave pendiente

**¿Los items de `ord_cotizaciones_listas` ya están reflejados en el `valor_total` del producto vinculado vía `cotizacion_producto_id`?** Si sí, no se deben sumar de nuevo. Si son desglose adicional, sí se incluyen. → Recomiendo verificar datos reales en BD antes de implementar el paso 2.
