# Integracion n8n para liquidacion de destajo

## Variables de entorno Laravel

Agrega estas variables al `.env`:

```env
QUEUE_CONNECTION=database
N8N_WEBHOOK_URL=https://tu-n8n/webhook/production-settlement-synced
N8N_WEBHOOK_TOKEN=tu_token_bearer
N8N_SIGNING_SECRET=tu_secreto_hmac
N8N_TIMEOUT_SECONDS=10
```

## Migrar y ejecutar

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

Si ya tienes tabla `jobs`, omite `php artisan queue:table`.

## Scheduler

El comando `integrations:n8n-outbox` ya quedo programado en `app/Console/Kernel.php` para correr cada minuto.

Debes tener el scheduler activo:

```bash
php artisan schedule:work
```

## Flujo n8n

1. Importa `docs/n8n/production-settlement-synced.workflow.json`.
2. Activa el workflow.
3. Usa la URL de webhook de produccion en `N8N_WEBHOOK_URL`.
4. Si usas firma HMAC, valida header `X-Signature` en n8n.
