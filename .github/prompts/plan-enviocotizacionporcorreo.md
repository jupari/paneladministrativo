## Plan: Envío de cotización por correo + aprobación/rechazo del cliente + cuadro de control

El sistema actualmente no tiene infraestructura de email ni rutas públicas. Se necesita crear el flujo completo: enviar cotización → cliente recibe email con PDF adjunto + link → cliente aprueba/rechaza desde una página pública → el estado se actualiza automáticamente → el vendedor lo ve en un cuadro de control.

---

### Fase 1 — Migración y modelo

1. Crear migración para agregar a `ord_cotizacion`:
   - `token_aprobacion` (string 64, nullable, unique) — token para la URL pública
   - `token_expira_en` (datetime, nullable) — expiración del token
   - `motivo_rechazo` (text, nullable) — razón del rechazo por parte del cliente
   - `respondido_por` (string 200, nullable) — nombre/email de quien respondió
2. Agregar campos al `$fillable` y `$casts` del modelo `Cotizacion`

> *Nota: `fecha_envio` y `fecha_respuesta` ya existen en la tabla.*

---

### Fase 2 — Extraer servicio PDF (prerequisito)

3. Crear `app/Services/CotizacionPdfService.php` extrayendo `buildPdf()`, `resolverLogoBase64()` y `cargarCotizacionParaPdf()` del controller actual — para reutilizar desde el Mailable y el controller (*depende de paso 1*)
4. Refactorizar `CotizacionController::generatePdf()` y `previewPdf()` para usar el nuevo servicio (*depende de paso 3*)

---

### Fase 3 — Mailable y template de email

5. Crear `app/Mail/CotizacionEnviada.php` que recibe la cotización, genera y adjunta el PDF, y pasa datos al template (*depende de paso 3*)
6. Crear `resources/views/emails/cotizacion-enviada.blade.php`:
   - Logo de la empresa, resumen (número, proyecto, total)
   - Botón CTA: "Ver y responder cotización" → URL pública con token
   - Texto de vigencia según `fecha_vencimiento`
   - Diseño profesional responsive (*paralelo con paso 5*)

---

### Fase 4 — Página pública de aprobación/rechazo

7. Crear `app/Http/Controllers/Public/CotizacionRespuestaController.php`:
   - `mostrar($token)` — valida token + expiración, muestra cotización en modo lectura
   - `responder(Request $request, $token)` — procesa aprobación/rechazo, actualiza `estado_id`, `fecha_respuesta`, `respondido_por`, `motivo_rechazo`
8. Crear `resources/views/public/cotizacion-respuesta.blade.php` — vista standalone (sin layout admin): datos del cliente, productos, totales, observaciones, condiciones comerciales, botones Aprobar (verde) / Rechazar (rojo + textarea motivo obligatorio). Muestra "Ya respondida" o "Expirada" según corresponda (*paralelo con paso 7*)
9. Crear `resources/views/public/cotizacion-respondida.blade.php` — confirmación de respuesta
10. Agregar rutas públicas en web.php (sin middleware `auth`, con throttle):
    - `GET /cotizacion/{token}` → `mostrar`
    - `POST /cotizacion/{token}/responder` → `responder`

---

### Fase 5 — Envío desde el controller + botón UI

11. Agregar método `enviarPorCorreo($id)` en `CotizacionController`: genera token con `Str::random(64)`, cambia estado a "Enviado", guarda `fecha_envio`, envía el Mailable al `terceroContacto->correo ?? tercero->correo` (*depende de pasos 5 y 10*)
12. Agregar ruta `POST admin.cotizaciones.enviar/{id}` en admin.php
13. En documento.blade.php: agregar botón "Enviar por correo" (ícono sobre) junto a Vista Previa/Descargar PDF
14. En documento-coordinator.js: agregar función `enviarCotizacion()` — confirma con SweetAlert mostrando email destino, POST a la ruta, actualiza badge de estado (*paralelo con paso 13*)

---

### Fase 6 — Cuadro de control (Dashboard)

15. Actualizar `CotizacionService::obtenerEstadisticas()`: conteos y sumas por cada estado + cotizaciones enviadas pendientes de respuesta + respondidas últimos 7 días (*depende de paso 1*)
16. Descomentar y actualizar `CotizacionController@index` para pasar estadísticas a la vista
17. Actualizar index.blade.php: agregar cards de resumen arriba del DataTable — una por estado con color, conteo y valor. Click en card filtra DataTable
18. Agregar columnas `fecha_envio` y `fecha_respuesta` al DataTable del index

---

### Archivos a crear

| Archivo | Propósito |
|---------|-----------|
| `database/migrations/xxxx_add_token_aprobacion_to_ord_cotizacion.php` | Nuevos campos de token y rechazo |
| `app/Services/CotizacionPdfService.php` | Lógica PDF reutilizable |
| `app/Mail/CotizacionEnviada.php` | Mailable con PDF adjunto |
| `resources/views/emails/cotizacion-enviada.blade.php` | Template del email |
| `app/Http/Controllers/Public/CotizacionRespuestaController.php` | Página pública aprobación/rechazo |
| `resources/views/public/cotizacion-respuesta.blade.php` | Vista lectura + botones respuesta |
| `resources/views/public/cotizacion-respondida.blade.php` | Confirmación de respuesta |

### Archivos a modificar

| Archivo | Cambio |
|---------|--------|
| Cotizacion.php | `$fillable`, `$casts` |
| CotizacionController.php | `enviarPorCorreo()`, refactor PDF → service |
| CotizacionService.php | `obtenerEstadisticas()` ampliado |
| admin.php | Ruta de envío |
| web.php | Rutas públicas token |
| index.blade.php | Cards de stats + columnas DataTable |
| documento.blade.php | Botón enviar |
| documento-coordinator.js | Función `enviarCotizacion()` |

---

### Verificación

1. Enviar cotización → email llega con PDF adjunto + link funcional
2. Abrir link → cotización visible en modo lectura con botones Aprobar/Rechazar
3. Aprobar → estado cambia a "Aprobado", `fecha_respuesta` se registra
4. Rechazar → pide motivo obligatorio, estado cambia a "Rechazado"
5. Link ya usado → muestra "Esta cotización ya fue respondida"
6. Link expirado → muestra "Esta cotización ha expirado"
7. Dashboard → muestra conteos correctos por estado, click filtra DataTable

### Decisiones

- **Token simple** (`Str::random(64)`) en DB en vez de signed URLs — permite expiración personalizada y persistencia
- **PDF adjunto** al email (no solo el link) — el cliente tiene copia offline
- **Página pública sin login** — protegida por token único + rate limiting (`throttle:6,1`)
- **Motivo de rechazo obligatorio** cuando el cliente rechaza
- **Expiración**: se usa `fecha_vencimiento` de la cotización si existe, sino 30 días desde envío
- **SMTP**: el usuario debe configurar `MAIL_*` en .env (actualmente Mailpit); se valida antes de enviar

### Consideraciones

1. **Notificación al vendedor** cuando el cliente responde — recomiendo incluirlo como segundo Mailable sencillo dentro de este mismo scope
2. **Reenvío**: ¿permitir reenviar? Genera nuevo token e invalida el anterior. Recomiendo sí, con confirmación SweetAlert
3. **Configuración SMTP**: ¿Mostrar un warning si el mail no está configurado en producción? Recomiendo validar en `enviarPorCorreo()` y retornar error descriptivo

¿Apruebas el plan o hay ajustes que quieras hacer?
