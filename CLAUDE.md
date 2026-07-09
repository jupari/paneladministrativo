# CLAUDE.md

Contexto del proyecto para Claude Code. Léelo antes de trabajar en cualquier tarea.

## Qué es

Panel administrativo **multiempresa** en **Laravel 10 / PHP 8.1**. Gestiona
cotizaciones, nóminas, inventario, producción, contratos y terceros para varias
empresas (`Company` / `CompanyBranch`). El dominio está en **español**: los
modelos, controladores y rutas usan nombres como `Cotizacion`, `Nomina`,
`Empleado`, `Cargo`, `Producto`.

## Stack

- **Backend:** Laravel 10, PHP ^8.1
- **Auth/permisos:** Laravel Breeze + Sanctum + **Spatie laravel-permission** (roles y permisos)
- **UI:** AdminLTE 3 (`jeroennoten/laravel-adminlte`), Blade + Alpine.js + Tailwind, bundling con **Vite**
- **Tablas:** `yajra/laravel-datatables`
- **Exportes/documentos:** dompdf (PDF), PhpWord (Word), PhpSpreadsheet (Excel), Zamzar
- **Correo:** Microsoft Graph / OAuth2 (integración Outlook)
- **Tests:** Pest 2 + PHPUnit, factories con Faker
- **Estilo:** Laravel Pint

## Arquitectura

Flujo de capas — **respétalo al añadir código nuevo**:

```
Route → Controller → Service → Repository → Model
```

- **Controllers** (`app/Http/Controllers/`): finos, orquestan. Agrupados por módulo
  (`Admin/`, `Cotizar/`, `Nomina/`, `Inventario/`, `Produccion/`, `Contratos/`,
  `Terceros/`, `Api/`). La lógica de negocio NO va aquí.
- **Services** (`app/Services/`): lógica de negocio. Ej: `CotizacionService`,
  `CotizacionTotalesService`, `CotizacionPdfService`, `EmpleadoService`. Cálculos
  (totales, precios, nómina) viven aquí.
- **Repositories** (`app/Repositories/`): acceso a datos cuando se necesita
  abstraer consultas (`UserRepository`, `ProgramaRepository`).
- **Requests** (`app/Http/Requests/`): validación de entrada (Form Requests).
- **Policies** (`app/Policies/`): autorización por modelo.
- **Models** (`app/Models/`): ~84 modelos Eloquent.

## Convenciones

- Nombres de dominio en **español** (no traducir `Cotizacion` a `Quote`).
- Validación en **Form Requests**, no inline en el controlador.
- Lógica de cálculo y negocio en **Services**, nunca en Blade ni en el controlador.
- Autorización con **Policies** + permisos de Spatie; comprobar permisos, no roles hardcodeados.
- Formato de moneda: pesos colombianos (ver commits recientes sobre formato de pesos).
- Sigue el estilo del código vecino; formatea con Pint antes de commitear.

## Comandos

```bash
# Tests (Pest)
php artisan test                 # o ./vendor/bin/pest
php artisan test --filter=Nombre # un solo test

# Formato
./vendor/bin/pint                # aplica estilo
./vendor/bin/pint --test         # solo verifica

# Frontend
npm run dev                      # Vite en desarrollo
npm run build                    # build de producción

# App
php artisan serve
php artisan migrate
php artisan tinker
```

Docker disponible: `docker-compose.yml` (ver `README_DOCKER.md`).

## Módulos principales

- **Cotizaciones** (`Cotizar/`, `CotizacionProductosController`): el más complejo.
  Cálculo de totales, productos, cargos, condiciones comerciales, PDF y envío por
  correo. Ver `MANUAL_COTIZACIONES.md`, `GUIA_TECNICA_PRECIOS_CARGOS.md` y
  `MATRIZ_ESCENARIOS_COTIZACION.md`.
- **Nómina** (`Nomina/`): cálculo de salarios, recargos, novedades. Ver
  `NOMINA_GUIA_FUNCIONAMIENTO.md`.
- **Multiempresa:** ver `MULTI_EMPRESA_GUIA.md` y `COMPANY_CRUD_COMPLETO.md`.

## Rutas

`routes/web.php`, `routes/admin.php` (panel), `routes/api.php` (Sanctum),
`routes/auth.php` (Breeze).

## ⚠️ Reglas críticas de base de datos

- **NUNCA regeneres todas las migraciones** ni corras `migrate:fresh` sin confirmación explícita del usuario. Esto borra TODOS los datos.
- **Solo crea nuevas migraciones** para cambios de schema. No toques migraciones existentes.
- **Los tests Pest DEBEN usar SQLite en memoria** (:`memory:`) en `phpunit.xml`, NUNCA la BD real de `.env`.
- Si descubres que los tests usan RefreshDatabase contra MySQL/BD real, detenerse inmediatamente y avisar al usuario antes de correr nada.

## Notas

- Antes de un fix, reproduce el bug y localiza la capa correcta (normalmente un Service).
- Hay planes de trabajo previos en `.github/prompts/` que sirven de referencia de estilo.
- No commitees ni pushees salvo que se pida explícitamente.
