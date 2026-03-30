# 📋 Manual de Funcionamiento del Módulo de Cotizaciones

**Versión:** 1.0
**Fecha:** Marzo 2026
**Estado:** En Producción

---

## 📑 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Flujo General del Módulo](#flujo-general-del-módulo)
3. [Cálculo de Precios de Cargos](#cálculo-de-precios-de-cargos)
4. [Escenarios de Cotización](#escenarios-de-cotización)
5. [Cambios Recientes y Mejoras](#cambios-recientes-y-mejoras)
6. [Troubleshooting](#troubleshooting)

---

## Introducción

El módulo de **Cotizaciones** es el componente central para la creación y gestión de presupuestos de servicios. Permite:

- ✅ Crear cotizaciones para clientes
- ✅ Agregar productos y servicios (incluyendo cargos por hora/día)
- ✅ Aplicar descuentos y conceptos (impuestos)
- ✅ Generar versiones de cotizaciones
- ✅ Generar PDFs profesionales
- ✅ Administrar autorizaciones

**Ubicación en la aplicación:** `/admin/cotizaciones`

---

## Flujo General del Módulo

### 1. Estructura de Datos

```
Cotización (ord_cotizacion)
    ├── Cliente (Tercero)
    ├── Sucursal Cliente (TerceroSucursal)
    ├── Contacto (TerceroContacto)
    ├── Productos (ord_cotizacion_productos) ← AQUÍ SE APLICA EL CÁLCULO DE PRECIOS
    ├── Items (ord_cotizaciones_items)
    │   └── SubItems (ord_cotizaciones_subitems)
    ├── Conceptos (ord_cotizacion_conceptos) ← Impuestos/Descuentos
    ├── Observaciones (ord_cotizacion_observaciones)
    ├── Condiciones Comerciales (ord_cotizacion_condiciones_comerciales)
    └── Estado (estado_cotizacion)
```

### 2. Ciclo de Vida de una Cotización

```
┌─────────────────────────────────────────────────────────────┐
│                    CREAR COTIZACIÓN                          │
│  1. Seleccionar cliente y sucursal                           │
│  2. Especificar proyecto y observaciones                     │
│  3. Generar número de documento automático (COT-XXXXXX)     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                   AGREGAR PRODUCTOS                          │
│  1. Productos estándares (con precio fijo)                  │
│  2. Cargos (con cálculo automático de horas/días)          │
│  3. Ítems propios (personalizados)                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              APLICAR DESCUENTOS Y CONCEPTOS                  │
│  1. Descuentos globales (%)                                  │
│  2. Descuentos por item (% o valor fijo)                    │
│  3. Impuestos/Conceptos (Iva, Bolsa, etc.)                 │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                GUARDAR Y FINALIZAR                           │
│  1. Guardar cotización (Estado: Borrador)                   │
│  2. Generar PDF para enviar                                  │
│  3. Crear versión (si se modifica más tarde)                │
│  4. Autorizar (opcional - requiere aprobación)              │
└─────────────────────────────────────────────────────────────┘
```

---

## Cálculo de Precios de Cargos

Este es el **corazón** del módulo. Los cargos (servicios por horas/días) se calculan automáticamente basándose en una **Tabla de Precios de Cargos** que se genera desde la parametrización.

### 3.1 Origen de la Tabla de Precios

La tabla de precios se genera en **Nómina → Parametrización de Costos** y se basa en:

```
Parametrización de Costos (parametrizacion)
    ↓
    Fuentes de datos:
    ├── Cargos (cargos)
    │   ├── salario_base (sobrescribe DATOS BÁSICOS)
    │   └── arl_nivel (sobrescribe % ARL parametrizado)
    │
    ├── Novedades de Nómina (novedades)
    │   ├── Datos Básicos (valor_porcentaje = 0, valor absoluto)
    │   │   ├── Básico (salario base)
    │   │   ├── Aux. de Transporte (si aplica)
    │   │   └── Auxilio
    │   │
    │   ├── Conceptos Porcentuales (valor_porcentaje > 0)
    │   │   ├── Cesantías (8.33%)
    │   │   ├── Interés Cesantías (1%)
    │   │   ├── Prima (8.33%)
    │   │   ├── Vacaciones (4.17%)
    │   │   ├── ARP (según nivel)
    │   │   ├── EPS (porcentaje empresa)
    │   │   ├── Pensión (porcentaje empresa)
    │   │   └── CCF (porcentaje empresa)
    │   │
    │   └── Totales Fijos (valor_porcentaje = 0, pero con total_admon/total_operativo)
    │       ├── Viáticos
    │       ├── Alimentación
    │       ├── Dotación
    │       └── Exámenes
    │
    └── Parámetros Globales (nom_parametros_globales)
        ├── SMLV (Salario Mínimo Legal Vigente)
        ├── aux_transporte (monto fijo si salario ≤ 2 SMLV)
        └── Utilidad % (para cálculo de precio venta)
```

### 3.2 Fórmula de Cálculo de la Tabla de Precios

La tabla se genera usando el service `TablaPreciosCargoService::generar()`:

#### **Paso 1: Recolectar datos del cargo**

Para cada cargo, se extrae:
- `B`: Básico (salario_base del cargo)
- `C`: Aux. Transporte (si salario ≤ 2 SMLV, aplica monto global)
- `D`: Auxilio
- `E = B + C + D`: Total nomina base

#### **Paso 2: Calcular conceptos**

```
Base para porcentajes = E - D (sin auxilio)

Conceptos Porcentuales:
F = (E - D) × % Cesantías
G = F × % Interés Cesantías
H = (E - D) × % Prima
I = B × % Vacaciones
J = B × % ARP
K = B × % EPS
L = B × % Pensión
M = B × % CCF

Totales Fijos:
N = Total Viáticos (si está parametrizado)
O = Total Alimentación
P = Total Dotación
Q = Total Exámenes
```

#### **Paso 3: Calcular costo período**

```
R = E + F + G + H + I + J + K + L + M + N + O + P + Q
    (TOTAL COSTO MENSUAL)

S = R / 26  (COSTO POR DÍA, considerando 26 días/mes)
T = S / 8   (COSTO POR HORA, considerando 8 horas/día)
```

#### **Paso 4: Aplicar factores horarios y utilidad**

```
Factores de Horas:
- Hora Ordinaria: T × 1.0
- Recargo Nocturno: T × 1.35
- Hora Extra Diurna: T × 1.25
- Hora Extra Nocturna: T × 1.75
- Dominical: T × 1.75
- Extra Dominical Diurna: T × 2.0
- Extra Dominical Nocturna: T × 2.5

Aplicar Utilidad:
Precio Venta = Precio / (1 - Utilidad%)

Ejemplo: Si Utilidad = 31.5%
  Precio Venta = T × Factor / (1 - 0.315)
               = T × Factor / 0.685
```

#### **Paso 5: Calcular valor día ordinario**

```
Valor Día = Hora Ordinaria × 8 horas
```

### 3.3 Prioridades en el Cálculo

⚠️ **IMPORTANTE**: Hay un sistema de prioridades para valores:

```
PRIORIDAD 1 (Mayor): Campos configurados en el Cargo
  ├── cargos.salario_base → Sobrescribe DATOS BÁSICOS - BASICO
  ├── cargos.arl_nivel → Siempre sobrescribe % ARL parametrizado
  └── Si salario_base está configurado:
      └── Aux. Transporte global (nom_parametros_globales.aux_transporte)
           ↳ Aplica solo si salario_base ≤ 2 × SMLV

PRIORIDAD 2 (Media): Parametrización de Nómina
  └── Valores definidos en la parametrización (novedades)

PRIORIDAD 3 (Menor): Valores por defecto
  └── Si no hay configuración, usar cero
```

### 3.4 Ejemplo de Cálculo Completo

**Datos del Cargo "Técnico Senior":**
- salario_base: $3,000,000
- arl_nivel: 3
- SMLV: $1,300,000
- aux_transporte global: $140,000
- utilidad: 31.5%

**Parametrización:**
- Cesantías: 8.33%
- Prima: 8.33%
- Vacaciones: 4.17%
- ARP nivel 3: 2.40% (sobrescribe parametrización)
- EPS: 8.5%
- Pensión: 12%
- CCF: 3%

**Cálculo:**

```
B (Básico) = 3,000,000
C (Aux. Trans) = 0  (porque 3,000,000 > 2 × 1,300,000)
D (Auxilio) = 0
E = 3,000,000 + 0 + 0 = 3,000,000

Base para % = E - D = 3,000,000

F (Cesantías) = 3,000,000 × 0.0833 = 249,900
G (Int. Cesantías) = 249,900 × 0.01 = 2,499
H (Prima) = 3,000,000 × 0.0833 = 249,900
I (Vacaciones) = 3,000,000 × 0.0417 = 125,100
J (ARP) = 3,000,000 × 0.0240 = 72,000
K (EPS) = 3,000,000 × 0.085 = 255,000
L (Pensión) = 3,000,000 × 0.12 = 360,000
M (CCF) = 3,000,000 × 0.03 = 90,000

N, O, P, Q = 0 (sin totales fijos)

R = 3,000,000 + 249,900 + 2,499 + 249,900 + 125,100 + 72,000 + 255,000 + 360,000 + 90,000
  = 4,404,399 (COSTO MENSUAL)

S = 4,404,399 / 26 = 169,400.73 (COSTO DÍA)
T = 169,400.73 / 8 = 21,175.09 (COSTO HORA ORDINARIA)

Aplicando utilidad 31.5%:
Precio Hora = 21,175.09 / 0.685 = 30,910.63

Factores:
- Hora Ordinaria: 30,910.63 × 1.0 = 30,910.63
- Recargo Nocturno: 30,910.63 × 1.35 = 41,729.35
- Hora Extra Diurna: 30,910.63 × 1.25 = 38,638.29
- ... y así con los demás

Valor Día Ordinario = 30,910.63 × 8 = 247,285.04
```

---

## Escenarios de Cotización

### 4.1 Escenario 1: Cotización de Producto Estándar

**Caso:** Cliente solicita 100 unidades de un producto con precio fijo.

**Proceso:**
1. Crear cotización
2. Agregar producto: "Tuerca de 1/2 pulgada"
   - Cantidad: 100
   - Valor unitario: $500
   - Descuento: 5%
3. Guardar y calcular totales automáticamente

**Cálculo:**
```
Subtotal = 100 × 500 = $50,000
Descuento = 50,000 × 0.05 = $2,500
Total Producto = 50,000 - 2,500 = $47,500

Cotización Total = $47,500
```

---

### 4.2 Escenario 2: Cotización de Servicio (Horas de Cargo)

**Caso:** Cliente solicita 40 horas de trabajo de un "Técnico Senior"

**Proceso:**
1. Crear cotización
2. Agregar producto tipo "Cargo"
   - Cargo: "Técnico Senior"
   - Tipo de costo: "Hora Ordinaria"
   - Cantidad: 40
3. El sistema automáticamente:
   - Busca en tabla_precios_cargos el cargo
   - Obtiene: Precio Hora Ordinaria = $30,910.63
   - Calcula: 40 × 30,910.63 = $1,236,425.20

**Resultado:**
```
Subtotal = 40 h × $30,910.63/h = $1,236,425.20
Total = $1,236,425.20
```

---

### 4.3 Escenario 3: Cotización Mixta con Descuentos

**Caso:** Cliente solicita productos + servicios + aplica descuento por volumen

**Proceso:**
1. Agregar 5 productos estándares: $100,000
2. Agregar 20 horas de "Técnico Senior": $618,212.60
3. Aplicar descuento global: 10%
4. Aplicar IVA: 19%

**Cálculo:**
```
Subtotal = 100,000 + 618,212.60 = $718,212.60

Descuento Global = 718,212.60 × 0.10 = $71,821.26
Subtotal c/ Desc. = 718,212.60 - 71,821.26 = $646,391.34

IVA (Concepto) = 646,391.34 × 0.19 = $122,814.35

TOTAL FINAL = 646,391.34 + 122,814.35 = $769,205.69
```

---

### 4.4 Escenario 4: Variación de Precios por Tipos de Horas

**Caso:** Cotización que incluye diferentes tipos de horas (ordinarias, extras, dominicales)

**Datos:**
- Cargo: Técnico Senior (como antes)
- 20 horas ordinarias
- 10 horas extra diurna
- 5 horas dominical

**Tabla de Precios para este cargo:**
```
Hora Ordinaria: $30,910.63
Hora Extra Diurna: $38,638.29
Dominical: $54,093.61
```

**Cálculo:**
```
Subtotal = (20 × 30,910.63) + (10 × 38,638.29) + (5 × 54,093.61)
         = 618,212.60 + 386,382.90 + 270,468.05
         = $1,275,063.55

Total = $1,275,063.55
```

---

### 4.5 Escenario 5: Cotización por Días (no horas)

**Caso:** Cliente solicita 10 días de servicio de "Obrero General"

**Proceso:**
1. Seleccionar cargo: "Obrero General"
2. Tipo de costo: "Valor Día Ordinario"
3. Cantidad: 10 días

**El sistema busca en tabla_precios_cargos:**
```
Valor Día Ordinario para "Obrero General" = $45,250.00
```

**Cálculo:**
```
Subtotal = 10 × 45,250.00 = $452,500.00
Total = $452,500.00
```

---

## Cambios Recientes y Mejoras

### 5.1 Campo "Bono" en CotizacionProducto

**Cambio:** Agregación del campo `bono` en la tabla `ord_cotizacion_productos`

**Propósito:** Permitir agregar un bono/bonificación adicional al valor total de cada producto.

**Impacto:**
```
Formula actualizada:
valor_total = (cantidad × valor_unitario - descuento_valor - (subtotal × descuento_porcentaje/100)) + bono

Ejemplo:
- Cantidad: 10
- Valor unitario: 1,000
- Descuento: 5%
- Bono: +500

Cálculo:
  Subtotal = 10 × 1,000 = 10,000
  Descuento = 10,000 × 0.05 = 500
  Valor Total = (10,000 - 500) + 500 = 10,000
```

**Dónde se aplica:**
- En la clase `CotizacionProducto.php` mediante el método `boot()` (saving event)
- Automáticamente se calcula cuando se guarda un producto

---

### 5.2 Integración con Lista de Precios (CotizacionLista)

**Nueva Funcionalidad:** Tabla `ord_cotizaciones_listas` para administrar listas de precios

**Propósito:** Mantener histórico de precios utilizados en cotizaciones

**Estructura:**
```
ord_cotizaciones_listas
├── id
├── cotizacion_id → referencia a la cotización
├── producto_id → referencia al producto
├── precio_base → precio sin descuentos
├── descuento_aplicado → monto o % descontado
├── precio_final → precio después de descuento
└── vigencia → rango de fechas

```

---

### 5.3 Auditoría de Cambios en Cargos

**Mejora:** Ahora se registran cambios en `salario_base` y `arl_nivel` de cargos

**Impacto:**
- Cuando se modifica un cargo, la tabla de precios se recalcula automáticamente
- Las cotizaciones existentes **NO se recalculan** (conservan sus precios originales)
- Las nuevas cotizaciones usarán los precios actualizados

---

## Troubleshooting

### P1: La tabla de precios está vacía

**Síntomas:**
- No aparecen opciones de cargos al crear cotización
- Mensaje: "No hay cargos disponibles"

**Causas y soluciones:**

1. **La tabla no se ha generado**
   ```
   Solución: Ir a Nómina → Parametrización → Tabla de Precios
   Click en "Generar Tabla de Precios"
   ```

2. **No hay parametrización de nómina**
   ```
   Solución: Ir a Nómina → Parametrización de Costos
   Verificar que existan novedades de NOMINA con valores
   ```

3. **Cargos sin salario_base**
   ```
   Solución: En Contratos → Cargos, editar cada cargo y establecer
   un salario_base
   ```

---

### P2: Los precios de cargos cambiaron, ¿por qué?

**Causa:** Se modificó la parametrización o los valores en los cargos

**Comportamiento esperado:**
- ✅ Cotizaciones nuevas usan precios actuales
- ✅ Cotizaciones existentes conservan sus precios originales
- ❌ NO se recalculan automáticamente

**Solución:** Si necesita recalcular una cotización:
1. Eliminar todos los productos cotizados
2. Volver a agregar los productos (usarán precios nuevos)
3. O duplicar la cotización (crea una versión nueva)

---

### P3: El precio de una hora extra está mal calculado

**Verificación paso a paso:**

1. **Confirmar salario_base del cargo**
   ```
   Contratos → Cargos → [Editar] → Buscar "salario_base"
   ```

2. **Confirmar parametrización de nómina**
   ```
   Nómina → Parametrización → Buscar el cargo
   Verificar todos los porcentajes y totales
   ```

3. **Confirmar configuración global**
   ```
   Nómina → Parámetros Globales
   - SMLV: [debe tener valor]
   - Aux. Transporte: [debe tener valor]
   - Utilidad %: [usualmente 31.5%]
   ```

4. **Regenerar tabla de precios**
   ```
   Nómina → Parametrización → Tabla de Precios
   Click en "Regenerar"
   ```

---

### P4: No se puede calcular el valor_total automáticamente

**Causa probable:** El modelo CotizacionProducto no está guardándose correctamente

**Solución:**

1. Verificar que se incluyan TODOS estos campos:
   - `cantidad`
   - `valor_unitario`
   - `descuento_porcentaje`
   - `descuento_valor`

2. El campo `valor_total` se calcula automáticamente en `boot()`:
   ```php
   static::saving(function ($model) {
       $subtotal = $model->cantidad * $model->valor_unitario;
       $descuento = $model->descuento_valor +
                    ($subtotal * ($model->descuento_porcentaje / 100));
       $model->valor_total = round(
           ($subtotal - $descuento) + ($model->bono ?? 0),
           2
       );
   });
   ```

---

### P5: El descuento global no se está aplicando

**Verificación:**

1. ¿El descuento se agregó como concepto?
   ```
   En la cotización → Agregar Concepto → Seleccionar tipo "Descuento"
   ```

2. ¿Se guardó con los valores correctos?
   ```
   Cotización → Ver detalles → Conceptos
   Verificar que aparezca el descuento en la lista
   ```

3. **Manual update de totales:**
   ```
   Ir a: /admin/cotizaciones/{id}/recalcular
   (Este endpoint puede no existir - usar duplicar en su lugar)
   ```

---

## Notas Importantes

### ⚠️ Advertencias

1. **Los precios en cotizaciones existentes NO cambian automáticamente**
   - Esto es intencional para mantener auditoría
   - Si necesita nuevos precios, cree una nueva cotización o use "Duplicar"

2. **La tabla de precios se regenera cada vez que se solicita**
   - No se cachea, siempre usa datos actuales
   - Operación rápida (< 1 segundo generalmente)

3. **El cálculo de Aux. Transporte es condicional**
   ```
   - Solo aplica si: salario_base ≤ 2 × SMLV
   - Usa valor de: nom_parametros_globales.aux_transporte
   ```

4. **Los porcentajes de ARL siempre usan el nivel del cargo**
   - Aunque esté parametrizado, se sobrescribe con `arl_nivel`
   - Para cambiar, editar el cargo, no la parametrización

---

### 📚 Referencia de Endpoints Principales

**Controllers:**
- `CotizacionController` → CRUD de cotizaciones
- `CotizacionProductoController` → Agregar/editar productos
- `CotizacionItemController` → Gestión de ítems y subitems

**Services:**
- `CotizacionService` → Lógica general de cotizaciones
- `CotizacionProductoService` → Cálculos de productos
- `CotizacionTotalesService` → Recalcular totales
- `TablaPreciosCargoService` → Generar tabla de precios

**URLs útiles:**
```
GET  /admin/cotizaciones                    → Listar cotizaciones
POST /admin/cotizaciones                    → Crear cotización
GET  /admin/cotizaciones/{id}               → Ver cotización
PUT  /admin/cotizaciones/{id}               → Actualizar cotización
DEL  /admin/cotizaciones/{id}               → Eliminar (marcar como anulada)
GET  /admin/cotizaciones/{id}/pdf           → Descargar PDF
GET  /admin/cotizaciones/{id}/preview-pdf   → Ver PDF en navegador
POST /admin/cotizaciones/{id}/duplicate      → Duplicar con nueva versión
```

---

## Conclusión

El módulo de cotizaciones es robusto y automático. Los precios de cargos se calculan de forma inteligente basándose en la parametrización de nómina, pero **siempre respeta las prioridades**: Cargo > Parametrización > Parámetros Globales.

Para cambios en precios, simplemente **regenere la tabla de precios** y cree nuevas cotizaciones.

**¿Preguntas o problemas?** Revise la sección de [Troubleshooting](#troubleshooting) o contacte al equipo de desarrollo.

---

**Última actualización:** Marzo 30, 2026
