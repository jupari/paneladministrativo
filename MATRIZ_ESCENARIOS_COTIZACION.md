# 📊 Matriz de Escenarios - Cálculo de Precios en Cotizaciones

**Documento de Referencia Rápida**

---

## Escenarios por Tipo de Producto

### 1️⃣ Producto Estándar (Precio Fijo)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Productos o servicios con precio conocido y fijo |
| **Datos requeridos** | `cantidad`, `valor_unitario`, `descuento_porcentaje`, `descuento_valor` |
| **Fórmula** | `subtotal = cantidad × valor_unitario` |
| | `descuentos = descuento_valor + (subtotal × descuento_porcentaje / 100)` |
| | `valor_total = subtotal - descuentos + bono` |
| **Ejemplo** | 100 tuercas × $500 = $50,000 |
| **Dónde obtiene precio** | Manualmente o de tabla de productos |
| **Quién calcula** | `CotizacionProducto::boot()` automáticamente |
| **Tabla BD** | `ord_cotizacion_productos` |

---

### 2️⃣ Cargo - Horas Ordinarias

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Servicios en horario normal (8h diarias) |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'hora_ordinaria'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.hora_ordinaria` |
| **Fórmula obtención** | Ver TablaPreciosCargoService::generar() |
| | Precio = T / (1 - Utilidad%) donde T = (R/26)/8 |
| **Ejemplo** | 40h × $30,910.63 = $1,236,425.20 |
| **Descuentos** | Aplica igual que productos estándar |
| **Requisitos previos** | 1. Cargo debe tener salario_base |
| | 2. Debe existir parametrización de nómina |
| | 3. Tabla de precios debe estar generada |

---

### 3️⃣ Cargo - Recargo Nocturno (35%)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Trabajo entre 21:00 y 06:00 (incremento 35%) |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'recargo_nocturno'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.recargo_nocturno` |
| **Fórmula** | = T × (1 + 0.35) / (1 - Utilidad%) |
| **Ejemplo** | 10h × $41,729.35 = $417,293.50 |
| **Nota importante** | Se aplica además de hora ordinaria, no como reemplazo |

---

### 4️⃣ Cargo - Horas Extras Diurnas (25%)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Trabajo más allá de 8 horas en horario diurno |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'hora_extra_diurna'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.hora_extra_diurna` |
| **Fórmula** | = T × 1.25 / (1 - Utilidad%) |
| **Ejemplo** | 5h × $38,638.29 = $193,191.45 |
| **Cumple ley** | Sí, según código laboral colombiano |

---

### 5️⃣ Cargo - Horas Extras Nocturnas (75%)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Trabajo más allá de 8 horas en horario nocturno |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'hora_extra_nocturna'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.hora_extra_nocturna` |
| **Fórmula** | = T × 1.75 / (1 - Utilidad%) |
| **Ejemplo** | 3h × $54,093.61 = $162,280.83 |
| **Combinación** | Recargo nocturno 35% + Extra 25% = 75% total |

---

### 6️⃣ Cargo - Dominical (75%)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Trabajo en domingo en horario normal |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'hora_dominical'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.hora_dominical` |
| **Fórmula** | = T × 1.75 / (1 - Utilidad%) |
| **Ejemplo** | 8h × $54,093.61 = $432,748.88 |
| **Nota** | Domingo + Extra = 2.0 o 2.5 (ver abajo) |

---

### 7️⃣ Cargo - Extra Dominical Diurna (100%)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Horas extra en domingo durante el día |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'hora_extra_dominical_diurna'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.hora_extra_dominical_diurna` |
| **Fórmula** | = T × 2.0 / (1 - Utilidad%) |
| **Ejemplo** | 2h × $61,821.26 = $123,642.52 |
| **Ley** | Domingo (75%) + Extra (25%) = 100% |

---

### 8️⃣ Cargo - Extra Dominical Nocturna (150%)

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Horas extra en domingo en horario nocturno |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'hora_extra_dominical_nocturna'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.hora_extra_dominical_nocturna` |
| **Fórmula** | = T × 2.5 / (1 - Utilidad%) |
| **Ejemplo** | 1h × $77,276.58 = $77,276.58 |
| **Ley** | Domingo (75%) + Extra (25%) + Noche (35%) = 150% |

---

### 9️⃣ Cargo - Valor Día Ordinario

| Aspecto | Detalles |
|---------|----------|
| **Cuándo usar** | Cotizar por día completo (8 horas) en una línea |
| **Datos requeridos** | `cargo_id`, `tipo_costo = 'valor_dia_ordinario'`, `cantidad` |
| **Fuente de precio** | Tabla `cargos_tabla_precios.valor_dia_ordinario` |
| **Fórmula** | = (T × 1.0 / (1 - Util%)) × 8 = hora_ordinaria × 8 |
| **Ejemplo** | 5 días × $247,285.04 = $1,236,425.20 |
| **Ventaja** | Una línea en vez de 8 líneas de horas |
| **Equivalente** | = 8 × hora_ordinaria |

---

## Matriz de Decisión: ¿Cuál usar?

```
¿Es un producto conocido con precio fijo?
    ├─ SÍ → Usar PRODUCTO ESTÁNDAR (Escenario 1)
    └─ NO → ¿Es un servicio con cargo por hora/día?
        ├─ SÍ → Seleccionar tipo de hora:
        │   ├─ Horario normal 8h → HORA ORDINARIA (Escenario 2)
        │   ├─ Fuera de 6-21 → RECARGO NOCTURNO (Escenario 3)
        │   ├─ Más de 8h (día) → HORA EXTRA DIURNA (Escenario 4)
        │   ├─ Más de 8h (noche) → HORA EXTRA NOCTURNA (Escenario 5)
        │   ├─ Domingo → DOMINICAL (Escenario 6)
        │   ├─ Extra + Domingo (día) → EXTRA DOM DIURNA (Escenario 7)
        │   ├─ Extra + Domingo (noche) → EXTRA DOM NOCTURNA (Escenario 8)
        │   └─ Por día completo → VALOR DÍA (Escenario 9)
        └─ NO → Item propio personalizado
```

---

## Flujo de Generación de Precios

### A. Cuando se agrega un Cargo a Cotización

```
Usuario elige cargo → Sistema busca tabla_precios_cargos
                            ├─ ¿Existe?
                            │  ├─ SÍ → Obtener precio del tipo de costo
                            │  └─ NO → Regenerar tabla → Obtener precio
                            └─ Guardar en valor_unitario
                                    ↓
                            Usuario especifica cantidad
                                    ↓
                            Sistema calcula valor_total
                            = cantidad × valor_unitario - descuentos + bono
```

### B. Cuando se regenera Tabla de Precios

```
Usuario clickea "Generar Tabla de Precios" → TablaPreciosCargoService::generar()
                                                ├─ Leer parametrización nómina
                                                ├─ Aplicar prioridades:
                                                │  1. salario_base del cargo
                                                │  2. arl_nivel del cargo
                                                │  3. Valores parametrizados
                                                ├─ Calcular R (costo mes)
                                                ├─ Calcular S (costo día)
                                                ├─ Calcular T (costo hora)
                                                ├─ Aplicar utilidad (31.5%)
                                                ├─ Calcular factores horarios
                                                └─ Persistir en cargos_tabla_precios
                                                        ↓
                                                ✅ Tabla actualizada y lista
```

---

## Checklist: Antes de Cotizar

### ✅ Verificación de Cargos

- [ ] Todos los cargos tienen `salario_base` definido
- [ ] Todos los cargos tienen `arl_nivel` asignado
- [ ] Validar que salarios sean coherentes (no $0)

### ✅ Verificación de Parametrización

- [ ] Existen novedades de tipo NOMINA activas
- [ ] Las novedades tienen valores (% o montos)
- [ ] Conceptos mapeados: Cesantías, Prima, Vacaciones, ARP, EPS, Pensión, CCF

### ✅ Verificación de Parámetros Globales

- [ ] SMLV está actualizado
- [ ] aux_transporte tiene valor
- [ ] Utilidad % es correcta (usualmente 31.5%)

### ✅ Verificación de Tabla de Precios

- [ ] Tabla fue regenerada recientemente
- [ ] Número de cargos en tabla = Número de cargos activos
- [ ] Precios no están en $0

### ✅ En la Cotización

- [ ] Cliente seleccionado correctamente
- [ ] Sucursal asignada
- [ ] Productos/cargos tienen cantidades
- [ ] Descuentos reflejan acuerdos reales
- [ ] Conceptos (IVA, etc.) están aplicados

---

## Problemas Comunes y Soluciones Rápidas

### ❌ "No hay cargos disponibles"

| Causa | Solución |
|-------|----------|
| Tabla de precios no generada | Ir a Nómina → Parametrización → Generar Tabla |
| Cargos sin salario_base | Editar cada cargo y establecer salario_base |
| Parametrización no existe | Crear novedades de NOMINA con valores |

### ❌ "Precio está en $0"

| Causa | Solución |
|-------|----------|
| salario_base en $0 | Editar cargo: establecer salario_base realista |
| Utilidad muy alta (>80%) | Revisar config en .env: app.utilidadPct |
| Parametrización incompleta | Agregar valores a novedades faltantes |

### ❌ "Precio cambió de un día para otro"

| Causa | Solución |
|-------|----------|
| Se regeneró tabla de precios | Comportamiento normal - nuevas cotizaciones usan nuevos precios |
| Se modificó salario_base | Regenerar tabla de precios |
| Se modificó SMLV | Regenerar tabla de precios |

---

## Fórmulas de Referencia Rápida

```
═════════════════════════════════════════════════════════════
CÁLCULO DE TABLA DE PRECIOS
═════════════════════════════════════════════════════════════

B = salario_base
C = aux_transporte (si salario ≤ 2×SMLV)
D = auxilio
E = B + C + D

F = (E-D) × %cesantías
G = F × %int_cesantías
H = (E-D) × %prima
I = B × %vacaciones
J = B × %arp
K = B × %eps
L = B × %pensión
M = B × %ccf
N+O+P+Q = Viáticos+Alim+Dotación+Exámenes

R = E + F + G + H + I + J + K + L + M + N + O + P + Q
S = R / 26
T = S / 8

Precio_Hora_Ordinaria = T / (1 - Utilidad%)

Precio_Otro_Tipo = Precio_Hora_Ordinaria × Factor_Tipo


═════════════════════════════════════════════════════════════
CÁLCULO DE PRODUCTO EN COTIZACIÓN
═════════════════════════════════════════════════════════════

Subtotal = cantidad × valor_unitario
Descuentos = descuento_valor + (Subtotal × descuento_porcentaje/100)
Valor_Total = Subtotal - Descuentos + Bono


═════════════════════════════════════════════════════════════
CÁLCULO DE COTIZACIÓN TOTAL
═════════════════════════════════════════════════════════════

Subtotal_Cotización = SUM(valor_total de todos los productos)
Descuento_Global = Subtotal_Cotización × descuento_porcentaje
Subtotal_c/Desc = Subtotal - Descuento_Global

Impuestos = Subtotal_c/Desc × %iva
OtrosConceptos = ...

TOTAL = Subtotal_c/Desc + Impuestos + OtrosConceptos
```

---

## Tabla Rápida: Factores de Incremento por Tipo de Hora

| Tipo de Hora | Factor | Incremento | Cálculo |
|--------------|--------|-----------|---------|
| Ordinaria | 1.00 | 0% | T × 1.0 |
| Recargo Nocturno | 1.35 | 35% | T × 1.35 |
| Extra Diurna | 1.25 | 25% | T × 1.25 |
| Extra Nocturna | 1.75 | 75% | T × 1.75 |
| Dominical | 1.75 | 75% | T × 1.75 |
| Extra Dominical Diurna | 2.00 | 100% | T × 2.0 |
| Extra Dominical Nocturna | 2.50 | 150% | T × 2.5 |

---

## Configuración por Ambiente

### Desarrollo / Testing

```
app.utilidadPct = 0.31 (31%)
app.horasDiarias = 8
SMLV = 1,300,000
aux_transporte = 140,000
```

### Producción

```
app.utilidadPct = 0.315 (31.5%) ← Más común
app.horasDiarias = 8
SMLV = Actualizado anualmente
aux_transporte = Actualizado según ley
```

---

**Última actualización:** Marzo 30, 2026
**Versión:** 1.0
**Próxima revisión:** Cuando cambie ley laboral o política de utilidad
