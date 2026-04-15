## Plan: Validaciones Nómina y Novedades en Cotizaciones

Aplicar 4 cambios al módulo de cotizaciones: (1) bloquear categorías mutuamente excluyentes con Nómina, (2) validar máximo 7 horas en Tiempo Ordinario, (3) validar máximo 2 horas en Horas Extra, y (4) agregar sección de novedades operativas por trabajador/cargo.

---

### Fase 1: Bloqueo de categorías al seleccionar Nómina

**Archivo:** `public/assets/js/cotizar/documento.js` — función `validarSeleccionCategorias()` (~L2437)

1. Modificar `validarSeleccionCategorias()` para detectar si la checkbox seleccionada tiene `data-tipo="nomina"`
2. Si Nómina está marcada → deshabilitar TODAS las demás `.categoria-checkbox` (disabled + opacidad reducida)
3. Si se desmarca Nómina → rehabilitar todas
4. Inverso: si hay categoría estándar seleccionada → deshabilitar la checkbox de Nómina
5. Ajustar el checkbox `selectAllCategorias` para excluir Nómina del "seleccionar todos"

---

### Fase 2: Validación Tiempo Ordinario — Máximo 7 horas

**Archivo:** `public/assets/js/cotizar/documento.js` — dentro de `abrirModalNominaConfig()` (~L10385)

1. Crear función `validarTiempoOrdinario(idx)` que sume los 4 campos (`nominaDiasDiurnos`, `nominaDiasNocturnos`, `nominaDominicales`, `nominaDomNocturnos`) y si > 7 → mostrar alerta "El tiempo máximo en horas de Tiempo Ordinario es 7", revertir valor, borde rojo
2. Actualizar los 4 inputs: cambiar `max="30"` → `max="7"`, agregar llamada a `validarTiempoOrdinario(idx)` en `oninput`/`onchange`
3. Cambiar el label "máx. 30 d/mes" → "máx. 7 horas"
4. Bloquear `finalizarNominaConfig()` si la validación falla

---

### Fase 3: Validación Horas Extra — Máximo 2 horas

**Archivo:** `public/assets/js/cotizar/documento.js` — dentro de `abrirModalNominaConfig()` (~L10416) y sección Costo Día (~L10530)

**Sección Costo Hora:**
1. Crear función `validarHorasExtra(idx)` que sume los 4 campos (`nominaHED`, `nominaHEN`, `nominaHEDD`, `nominaHEDN`) y si > 2 → alerta "El máximo de horas extras es 2", revertir, borde rojo
2. Agregar `max="2"` y llamada a `validarHorasExtra(idx)` en cada input de Horas Extra
3. Actualizar header para incluir "máx. 2 h"

**Sección Costo Día (Turno):**
4. Crear función `validarHorasExtraTurno(idx)` que valide `nominaHEDxDia + nominaHENxDia ≤ 2`
5. Agregar validación cruzada en `oninput`/`onchange` de ambos inputs
6. Mensaje al usuario: "El valor máximo son 2 horas extras"

---

### Fase 4: Sección de Novedades por Trabajador

**Archivos:** `public/assets/js/cotizar/documento.js` (frontend), `app/Http/Controllers/Cotizar/CotizacionProductoController.php` (backend ya existe)

**Infraestructura existente que se reutiliza:**
- Endpoint `obtenerNovedadesGrupoCotiza()` — ya filtra `grupo_cotiza = 1` y carga detalles con `valor_operativo`
- Caché JS `_cacheNovedadesOperativas` — ya implementado
- Modelo `CotizacionLista` — ya persiste `novedad_detalle_id`, `cantidad`, `valor`, `subtotal`
- Backend `guardarSalariosCotizacion()` (~L514) — ya guarda novedades del request si vienen en `configuracionCosto.novedades`

**Pasos:**
1. En `abrirModalNominaConfig()`, después de la sección "Bono adicional" (~L10580), agregar HTML de sección "Novedades Operativas" con contenedor `tablaNovedadesNomina_${idx}` por cada cargo
2. Crear `renderizarNovedadesEnNomina(idx, novedades)` — tabla con columnas: Novedad/Detalle | Valor Unit. | Cantidad (input numérico) | Subtotal
3. Crear `recalcularNovedadNomina(idx, detalleId, valorUnitario)` — calcula subtotal de fila, actualiza celda, llama `recalcularTotalNovedadesNomina(idx)` que suma todos los subtotales
4. Modificar `calcularLiquidacionNomina(idx)` para sumar el total de novedades al display del costo empresa/mes
5. Modificar `finalizarNominaConfig()` para recopilar novedades con cantidad > 0 en `configuracionCosto.novedades[]` con formato `{ novedad_detalle_id, valor, cantidad, subtotal }`
6. Verificar compatibilidad del formato con lo que espera el backend en `guardarSalariosCotizacion()` (~L514)

---

### Archivos a modificar

- `public/assets/js/cotizar/documento.js` — Todas las fases (archivo principal)
- `app/Http/Controllers/Cotizar/CotizacionProductoController.php` — Solo si el formato de novedades requiere ajuste (verificar)

---

### Verificación

1. **Categorías:** Seleccionar Nómina → demás deshabilitadas. Desmarcar → se rehabilitan. Seleccionar estándar → Nómina deshabilitada.
2. **Tiempo Ordinario:** Suma de 4 campos > 7 → alerta + revert. Exactamente 7 → sin error.
3. **Horas Extra (Costo Hora):** Suma de 4 campos > 2 → alerta "El máximo de horas extras es 2".
4. **Horas Extra (Costo Día/Turno):** HEDxDia + HENxDia > 2 → alerta "El valor máximo son 2 horas extras".
5. **Novedades:** Aparecen las novedades con `grupo_cotiza=1`, inputs de cantidad funcionales, subtotales calculados, total reflejado en costo empresa/mes.
6. **Persistencia:** Producto nómina guardado → `ord_cotizaciones_listas` con `novedad_detalle_id`, `cantidad`, `valor`, `subtotal` correctos.

---

### Decisiones

- Validación de horas solo en frontend (JS); el backend ya tiene restricciones `max` en `CotizacionProductoRequest`.
- Novedades reutilizan toda la infraestructura existente (caché, endpoint, modelo `CotizacionLista`).
- Bloqueo de categorías es **bidireccional**: Nómina bloquea estándar y estándar bloquea Nómina.
