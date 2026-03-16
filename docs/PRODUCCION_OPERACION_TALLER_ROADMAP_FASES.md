# PRODUCCIÓN > OPERACIÓN-TALLER
## Roadmap técnico por fases (sin regresión de app móvil)

Fecha: 2026-03-15
Proyecto web: `paneladministrativo`
Proyecto móvil: `app-movil/Sysworks/appmovil`

---

## 1) Objetivo funcional

Implementar en la aplicación web:
1. Creación y administración de talleres (tabla existente `workshops`).
2. Generación de QR por taller para vincular dispositivos móviles.
3. Registro de uno o varios dispositivos por taller.

Manteniendo estable lo ya desarrollado en móvil:
- No romper login actual.
- No romper sync actual de operaciones y daños.
- No cambiar payload de endpoints ya consumidos por la app.

---

## 2) Estado actual validado

### Backend (sí existe)
- Modelo y base de talleres: `workshops`, `user_workshops`, `company_workshops`, `workshop_operators`.
- API móvil para talleres: listado/detalle/operarios.
- Middleware de acceso por taller.

### Backend (no existe)
- CRUD web/admin de talleres.
- Registro de dispositivos por taller.
- Flujo QR de vinculación taller-dispositivo.

### Móvil (sí existe)
- Escáner QR para preselección de actividad (`swt://op/{id}`), no para pairing taller-dispositivo.

### Brecha crítica de seguridad
- Endpoints bulk validan existencia de IDs, pero deben endurecer validación de pertenencia (usuario-taller-orden-dispositivo).

---

## 3) Principios de implementación

1. **Compatibilidad retroactiva**: cambios aditivos.
2. **Feature flag**: activar pairing por empresa/taller gradualmente.
3. **One-time token** para QR con expiración corta.
4. **Auditoría** de altas/bajas de dispositivos.
5. **No bloqueo de operación** durante rollout inicial.

---

## 4) Fase 0 — Preparación y no-regresión

### Entregables
- Matriz de compatibilidad de endpoints actuales.
- Checklist de regresión mínima móvil.
- Definición final del formato QR.

### Criterios de aceptación
- Todos los endpoints actuales siguen respondiendo igual (status/payload).
- App móvil actual continúa operando sin update obligatorio.

---

## 5) Fase 1 — Modelo de datos (DB)

## 5.1 Nueva tabla: `workshop_devices`

Campos sugeridos:
- `id` (PK)
- `workshop_id` (FK -> workshops)
- `company_id` (FK -> companies)
- `device_uuid` (string 120)  // identificador técnico del dispositivo/app
- `device_name` (string 120, nullable)
- `platform` (enum: android, ios)
- `app_version` (string 30, nullable)
- `os_version` (string 30, nullable)
- `status` (enum: active, blocked, revoked)
- `last_login_at` (timestamp nullable)
- `last_sync_at` (timestamp nullable)
- `registered_by_user_id` (FK -> users nullable)
- `revoked_by_user_id` (FK -> users nullable)
- `revoked_at` (timestamp nullable)
- `created_at`, `updated_at`

Índices/constraints:
- unique (`workshop_id`, `device_uuid`)
- index (`company_id`, `status`)
- index (`workshop_id`, `status`)

## 5.2 Nueva tabla: `workshop_qr_tokens`

Campos sugeridos:
- `id` (PK)
- `workshop_id` (FK)
- `company_id` (FK)
- `token_hash` (string 255, unique) // nunca guardar token en claro
- `expires_at` (timestamp)
- `used_at` (timestamp nullable)
- `used_by_device_id` (FK workshop_devices nullable)
- `created_by_user_id` (FK users)
- `created_at`, `updated_at`

Índices:
- unique (`token_hash`)
- index (`workshop_id`, `expires_at`)

### Criterios de aceptación
- Migraciones aplican y hacen rollback correctamente.
- Constraints evitan duplicidad de dispositivo en mismo taller.

---

## 6) Fase 2 — API de pairing QR (backend)

Base: `/api/v1`

## 6.1 Web/Admin API (protegida por sesión web)

### `POST /admin/produccion/workshops/{id}/pairing-qr`
Genera token temporal para QR.

Response ejemplo:
```json
{
  "data": {
    "workshop_id": 12,
    "pairing_token": "ptk_xxx...",
    "expires_at": "2026-03-15T15:30:00Z",
    "qr_payload": "swt://pair?token=ptk_xxx..."
  }
}
```

Reglas:
- TTL recomendado: 5 minutos.
- Invalidar token anterior activo del mismo taller al generar uno nuevo.

## 6.2 Mobile API (auth:sanctum)

### `POST /workshops/pair`
Body:
```json
{
  "pairing_token": "ptk_xxx...",
  "device_uuid": "a1b2c3d4",
  "device_name": "Samsung A15",
  "platform": "android",
  "app_version": "1.0.0",
  "os_version": "14"
}
```

Response:
```json
{
  "data": {
    "paired": true,
    "workshop_id": 12,
    "device_status": "active"
  }
}
```

Reglas:
- Token válido, no expirado, no usado.
- Taller debe pertenecer a la compañía del usuario autenticado.
- Si ya existe (`workshop_id`,`device_uuid`) en `active`, responder idempotente.

### `GET /workshops/{workshopId}/devices`
Lista dispositivos del taller (web y móvil supervisor opcional).

### `PATCH /workshops/{workshopId}/devices/{deviceId}/status`
Permite `blocked` o `revoked` desde web.

### Códigos de error clave
- 400 token inválido
- 410 token expirado
- 409 token ya usado
- 403 sin acceso a compañía/taller

### Criterios de aceptación
- Pairing exitoso crea/actualiza registro en `workshop_devices`.
- Token queda marcado como usado y no se puede reutilizar.

---

## 7) Fase 3 — Módulo web (operación-taller)

## 7.1 CRUD talleres (web)
Rutas sugeridas (admin):
- `GET /admin/produccion/workshops`
- `POST /admin/produccion/workshops`
- `PUT /admin/produccion/workshops/{id}`
- `PATCH /admin/produccion/workshops/{id}/status`
- `GET /admin/produccion/workshops/{id}`

## 7.2 Vista detalle de taller
Secciones:
1. Datos del taller.
2. Botón “Generar QR de vinculación”.
3. Tabla de dispositivos vinculados (estado, versión app, último sync).
4. Acciones: bloquear/revocar/reactivar.

### Criterios de aceptación
- Un taller se crea y queda disponible para asignación.
- Se genera QR y se visualiza expiración en pantalla.
- Se listan múltiples dispositivos por taller.

---

## 8) Fase 4 — Ajuste móvil mínimo (sin ruptura)

## 8.1 Nuevo flujo opcional
- Pantalla “Vincular dispositivo” (solo si feature flag activo).
- Escanear QR `swt://pair?token=...`.
- Llamar `POST /workshops/pair`.

## 8.2 Compatibilidad
- Mantener scanner actual para actividades (`swt://op/{id}`).
- Si no hay pairing habilitado, flujo actual sigue igual.

### Criterios de aceptación
- Build actual funciona sin cambios en backend legacy.
- Con feature flag ON, el dispositivo se vincula correctamente.

---

## 9) Fase 5 — Endurecimiento de seguridad y auditoría

## 9.1 Endurecer endpoints de escritura
Aplicar validaciones de pertenencia en:
- `operations/bulk`
- `damaged-garments/bulk`

Validar que:
1. `workshop_id` pertenezca al usuario/compañía.
2. `order_id` pertenezca al `workshop_id`.
3. `operator_id` pertenezca al `workshop_id`.
4. `device_uuid` esté vinculado y activo (cuando se active enforcement).

## 9.2 Auditoría
Log de eventos:
- token generado
- token usado
- dispositivo revocado/bloqueado

### Criterios de aceptación
- Peticiones inconsistentes son rechazadas con 403/422.
- Eventos críticos quedan trazables.

---

## 10) Plan de despliegue recomendado

1. Deploy Fase 1 + 2 (sin enforcement estricto).
2. Deploy Fase 3 (web admin).
3. Publicar versión móvil con pairing opcional.
4. Activar feature flag por empresa piloto.
5. Activar enforcement de dispositivo en bulk por etapas.

---

## 11) Backlog técnico (orden sugerido)

1. Migraciones: `workshop_devices`, `workshop_qr_tokens`.
2. Modelos Eloquent + relaciones en `Workshop`.
3. Servicios de dominio:
   - `WorkshopPairingService`
   - `WorkshopDeviceService`
4. Controladores:
   - Admin Workshops CRUD
   - API Pairing Controller
5. Policies/Middleware de compañía y taller.
6. Vistas Blade módulo web talleres.
7. Integración móvil de pantalla pairing.
8. Pruebas feature/integration.

---

## 12) Casos de prueba mínimos

1. Crear taller en web y asociarlo a compañía.
2. Generar QR de vinculación.
3. Escanear QR desde dispositivo A y vincular.
4. Vincular dispositivo B al mismo taller.
5. Bloquear dispositivo A y validar rechazo en escritura.
6. Mantener operativo dispositivo B.
7. Verificar que app actual (sin pairing) sigue listando talleres/órdenes.

---

## 13) Definiciones de “hecho” por fase

- **Fase 1**: tablas y constraints en producción sin errores.
- **Fase 2**: API pairing estable + test de token one-time.
- **Fase 3**: CRUD web talleres + gestión de dispositivos usable.
- **Fase 4**: móvil vincula sin romper scanner de actividad.
- **Fase 5**: enforcement de seguridad activo y auditado.

---

## 14) Nota de compatibilidad final

Este roadmap está diseñado para no dañar la app móvil existente: 
- no elimina rutas existentes,
- no altera contratos actuales,
- introduce capacidades nuevas de manera aditiva y controlada.
