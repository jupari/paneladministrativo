# 📚 Guía de Funcionamiento del Sistema de Nómina

Esta guía explica paso a paso cómo funciona el motor de cálculo de nómina en el sistema.

---

## 📑 Tabla de Contenidos

1. [Arquitectura General](#arquitectura-general)
2. [Conceptos Clave](#conceptos-clave)
3. [Flujo Completo de Nómina](#flujo-completo-de-nómina)
4. [Configuración Inicial](#configuración-inicial)
5. [Proceso de Cálculo Detallado](#proceso-de-cálculo-detallado)
6. [Fórmulas y Cálculos](#fórmulas-y-cálculos)
7. [Casos de Uso](#casos-de-uso)

---

## 🏗️ Arquitectura General

### Componentes del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    SISTEMA DE NÓMINA                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. MAESTROS (Configuración)                                │
│     ├── NominaConcept (Conceptos: salarios, deducciones)   │
│     └── NominaConceptRule (Reglas: tasas, porcentajes)     │
│                                                              │
│  2. OPERACIÓN (Procesos)                                    │
│     ├── NominaPayRun (Periodos de nómina)                  │
│     ├── NominaPayRunParticipant (Empleados incluidos)      │
│     └── NominaNovelty (Novedades: extras, descuentos)      │
│                                                              │
│  3. RESULTADOS (Liquidación)                                │
│     └── NominaPayRunLine (Líneas calculadas por concepto)  │
│                                                              │
│  4. MOTOR (Lógica de negocio)                               │
│     ├── NominaEngineService (Calcula nómina)               │
│     └── NominaPayRunService (Gestiona periodos)            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Conceptos Clave

### 1. **Concepto de Nómina** (`NominaConcept`)

Es cada elemento que compone la nómina (salario, horas extra, deducciones, etc.)

**Propiedades importantes:**

- **`code`**: Código único (ej: `LAB_BASICO`, `LAB_DED_SALUD_EMP`)
- **`name`**: Nombre descriptivo
- **`kind`**: Tipo de concepto
  - `DEVENGADO` - Dinero que se paga al empleado
  - `DEDUCCION` - Dinero que se descuenta
  - `APORTE_PATRONAL` - Costos para la empresa (no va en desprendible)
  - `INFORMATIVO` - Solo informativo
- **`tax_nature`**: Naturaleza tributaria
  - `SALARIAL` - Cuenta para IBC (base cotización)
  - `NO_SALARIAL` - No cuenta para IBC
- **`priority`**: Orden de cálculo (menor = primero)

**Ejemplo:**
```
code: LAB_BASICO
name: Salario Básico
kind: DEVENGADO
tax_nature: SALARIAL
priority: 10
```

---

### 2. **Regla de Concepto** (`NominaConceptRule`)

Define **cómo se calcula** un concepto (tasas, porcentajes, fórmulas)

**Propiedades:**

- **`nomina_concept_id`**: A qué concepto aplica
- **`parameters`** (JSON): Parámetros de cálculo
  - `{"rate": 0.04}` → 4%
  - `{"formula": "salario * dias / 30"}`
- **`valid_from` / `valid_to`**: Vigencia temporal

**Ejemplo:**
```json
{
  "nomina_concept_id": 5,  // LAB_DED_SALUD_EMP
  "parameters": {
    "rate": 0.04  // 4% sobre el IBC
  },
  "valid_from": "2024-01-01",
  "valid_to": null
}
```

---

### 3. **Periodo de Nómina** (`NominaPayRun`)

Representa una corrida de nómina (quincenal, mensual, etc.)

**Ciclo de estados:**

```
DRAFT → CALCULATED → APPROVED → PAID → CLOSED
```

- **DRAFT**: Borrador, en construcción
- **CALCULATED**: Ya se calculó la liquidación
- **APPROVED**: Aprobado por dirección
- **PAID**: Ya se pagó
- **CLOSED**: Cerrado, no modificable

---

### 4. **Participante** (`NominaPayRunParticipant`)

Representa a una persona incluida en un periodo de nómina

**Tipos de vínculo:**

- **LABORAL**: Empleado con contrato laboral
- **CONTRATISTA**: Prestador de servicios (honorarios)

**Campos calculados:**

- `gross_total` - Total devengado
- `deductions_total` - Total deducciones
- `net_total` - Neto a pagar (devengado - deducciones)

---

### 5. **Novedad** (`NominaNovelty`)

Evento que modifica la nómina de una persona en un periodo

**Ejemplos:**
- Horas extra trabajadas
- Incapacidades
- Bonificaciones
- Descuentos por préstamos
- Licencias no remuneradas

**Estados:**
- `PENDING` - Pendiente de aplicar
- `APPLIED` - Ya fue aplicada en el cálculo
- `REJECTED` - Rechazada

---

### 6. **Línea de Nómina** (`NominaPayRunLine`)

Cada concepto calculado para cada persona

**Ejemplo de líneas para Juan Pérez:**

| Concepto | Cantidad | Base | Tasa | Monto | Dirección |
|----------|----------|------|------|-------|-----------|
| Salario básico | 15 días | 2,500,000 | 1 | 1,250,000 | ADD |
| Horas extra | 10 hrs | 83,333 | 1.25 | 104,167 | ADD |
| Salud empleado | 1 | 1,354,167 | 0.04 | 54,167 | SUB |
| Pensión empleado | 1 | 1,354,167 | 0.04 | 54,167 | SUB |

---

## 🔄 Flujo Completo de Nómina

### **Paso 1: Configuración Inicial** (Una sola vez)

```
1. Crear conceptos de nómina
   ├── Devengos laborales (salario, extras, primas)
   ├── Deducciones laborales (salud, pensión)
   ├── Aportes patronales (salud, pensión, ARL)
   ├── Conceptos para contratistas (honorarios, retenciones)
   └── Conceptos informativos

2. Crear reglas de cálculo
   ├── Definir tasas de salud (4% empleado, 8.5% empleador)
   ├── Definir tasas de pensión (4% empleado, 12% empleador)
   ├── Definir retenciones para contratistas
   └── Configurar vigencias temporales
```

---

### **Paso 2: Registro de Novedades** (Durante el periodo)

```
1. Registrar eventos que afectan la nómina
   ├── Horas extra trabajadas
   ├── Incapacidades médicas
   ├── Bonificaciones especiales
   ├── Descuentos por préstamos
   └── Licencias no remuneradas

2. Cada novedad especifica:
   ├── A quién aplica (empleado/contratista)
   ├── Qué concepto modifica
   ├── Cantidad y monto
   ├── Periodo de aplicación
   └── Estado: PENDING
```

---

### **Paso 3: Crear Periodo de Nómina** (Al inicio del proceso)

```
1. Definir periodo
   ├── Fechas: inicio y fin del periodo
   ├── Tipo: Laboral / Contratistas / Mixto
   ├── Fecha de pago
   └── Estado: DRAFT

2. Incluir participantes
   ├── Seleccionar empleados activos
   ├── Seleccionar contratistas con honorarios
   └── Crear NominaPayRunParticipant por cada uno
```

---

### **Paso 4: Calcular Nómina** (El momento clave)

```
1. Ejecutar NominaEngineService::calculate()
   
2. El motor procesa:
   ├── Carga conceptos y reglas vigentes
   ├── Por cada participante:
   │   ├── Borra cálculos previos (recálculo)
   │   ├── Carga novedades pendientes
   │   ├── Calcula según tipo de vínculo
   │   ├── Genera líneas de nómina
   │   └── Calcula totales
   └── Marca periodo como CALCULATED

3. Marca novedades como APPLIED
```

---

### **Paso 5: Aprobación y Pago** (Después del cálculo)

```
1. Supervisor revisa liquidación
2. Aprueba periodo → Estado: APPROVED
3. Contabilidad procesa pagos
4. Marca como pagado → Estado: PAID
5. Cierra periodo → Estado: CLOSED
```

---

## 📝 Configuración Inicial

### **1. Conceptos Básicos Necesarios**

#### **Para EMPLEADOS LABORALES:**

| Código | Nombre | Tipo | Naturaleza | Prioridad |
|--------|--------|------|------------|-----------|
| `LAB_BASICO` | Salario Básico | DEVENGADO | SALARIAL | 10 |
| `LAB_HORAS_EXTRA` | Horas Extra | DEVENGADO | SALARIAL | 20 |
| `LAB_AUXILIO_TRANSPORTE` | Aux. Transporte | DEVENGADO | NO_SALARIAL | 30 |
| `LAB_DED_SALUD_EMP` | Salud Empleado | DEDUCCION | NO_SALARIAL | 100 |
| `LAB_DED_PENSION_EMP` | Pensión Empleado | DEDUCCION | NO_SALARIAL | 101 |
| `LAB_AP_SALUD_PAT` | Salud Empleador | APORTE_PATRONAL | NO_SALARIAL | 200 |
| `LAB_AP_PENSION_PAT` | Pensión Empleador | APORTE_PATRONAL | NO_SALARIAL | 201 |

#### **Para CONTRATISTAS:**

| Código | Nombre | Tipo | Naturaleza | Prioridad |
|--------|--------|------|------------|-----------|
| `CON_HONORARIOS` | Honorarios | DEVENGADO | NO_SALARIAL | 10 |
| `CON_DED_RETEFUENTE` | Retención Fuente | DEDUCCION | NO_SALARIAL | 100 |
| `CON_DED_RETEICA` | Retención ICA | DEDUCCION | NO_SALARIAL | 101 |

---

### **2. Reglas de Cálculo por Concepto**

#### **Salud Empleado (4%)**
```json
{
  "nomina_concept_id": [ID del concepto LAB_DED_SALUD_EMP],
  "parameters": {"rate": 0.04},
  "valid_from": "2024-01-01",
  "valid_to": null,
  "description": "Aporte salud empleado - 4% sobre IBC"
}
```

#### **Pensión Empleado (4%)**
```json
{
  "nomina_concept_id": [ID del concepto LAB_DED_PENSION_EMP],
  "parameters": {"rate": 0.04},
  "valid_from": "2024-01-01",
  "valid_to": null
}
```

#### **Salud Empleador (8.5%)**
```json
{
  "nomina_concept_id": [ID del concepto LAB_AP_SALUD_PAT],
  "parameters": {"rate": 0.085},
  "valid_from": "2024-01-01",
  "valid_to": null
}
```

#### **Pensión Empleador (12%)**
```json
{
  "nomina_concept_id": [ID del concepto LAB_AP_PENSION_PAT],
  "parameters": {"rate": 0.12},
  "valid_from": "2024-01-01",
  "valid_to": null
}
```

#### **Retención Fuente Contratistas (11%)**
```json
{
  "nomina_concept_id": [ID del concepto CON_DED_RETEFUENTE],
  "parameters": {"rate": 0.11},
  "valid_from": "2024-01-01",
  "valid_to": null
}
```

---

## ⚙️ Proceso de Cálculo Detallado

### **Fase 1: Preparación**

```php
// 1. Validar estado del periodo
if (estado != 'DRAFT' && estado != 'CALCULATED') {
    ERROR: "No se puede calcular"
}

// 2. Cargar participantes
participantes = NominaPayRunParticipant
    WHERE pay_run_id = [periodo]

if (participantes.isEmpty()) {
    ERROR: "No hay participantes"
}

// 3. Cargar conceptos activos
conceptos = NominaConcept
    WHERE is_active = 1
    ORDER BY priority

// 4. Cargar reglas vigentes
reglas = NominaConceptRule
    WHERE (valid_from <= periodo_end OR valid_from IS NULL)
    AND (valid_to >= periodo_start OR valid_to IS NULL)
```

---

### **Fase 2: Cálculo por Participante**

#### **Para LABORAL:**

```
1. Limpiar cálculos previos
   DELETE NominaPayRunLine WHERE participante

2. Cargar novedades pendientes
   novedades = NominaNovelty
       WHERE participante
       AND status = 'PENDING'
       AND periodo se cruza

3. Calcular SALARIO BÁSICO
   concepto = LAB_BASICO
   salario_empleado = Empleado.salario
   monto = salario_empleado / 2  (quincena)
   → Crear línea: ADD

4. Aplicar NOVEDADES
   Por cada novedad:
       tipo = (concepto.kind == 'DEDUCCION') ? SUB : ADD
       → Crear línea con dirección correspondiente

5. Calcular IBC (Ingreso Base de Cotización)
   IBC = SUMA de líneas ADD con tax_nature = 'SALARIAL'
   
   Ejemplo:
   - Salario básico: $1,250,000 (SALARIAL) ✓
   - Horas extra: $150,000 (SALARIAL) ✓
   - Aux. transporte: $80,000 (NO_SALARIAL) ✗
   IBC = $1,400,000

6. Calcular DEDUCCIONES sobre IBC
   - Salud empleado: IBC × 4% = $56,000 → SUB
   - Pensión empleado: IBC × 4% = $56,000 → SUB

7. Calcular APORTES PATRONALES
   - Salud empleador: IBC × 8.5% = $119,000 → ADD
   - Pensión empleador: IBC × 12% = $168,000 → ADD

8. Persistir líneas en BD
   NominaPayRunLine::create() por cada línea

9. Calcular totales
   devengado = SUMA(líneas WHERE direction = 'ADD')
   deducciones = SUMA(líneas WHERE direction = 'SUB')
   neto = devengado - deducciones

10. Actualizar participante
    status = 'CALCULATED'
    gross_total = devengado
    deductions_total = deducciones
    net_total = neto

11. Marcar novedades como APPLIED
    UPDATE novedades SET status = 'APPLIED'
```

#### **Para CONTRATISTA:**

```
1. Limpiar cálculos previos

2. Cargar novedades pendientes

3. Calcular HONORARIOS
   honorarios = SUMA(novedades WHERE concepto = CON_HONORARIOS)
   → Crear línea: ADD

4. Aplicar otras NOVEDADES
   (Igual que laboral)

5. Calcular RETENCIONES sobre honorarios
   - Retención fuente: honorarios × 11% → SUB
   - Retención ICA: honorarios × 0.966% → SUB

6. Persistir y calcular totales
   (Igual que laboral)
```

---

### **Fase 3: Finalización**

```
1. Marcar periodo como CALCULATED
   UPDATE NominaPayRun SET status = 'CALCULATED'

2. Commit transacción
   (Todo se hace en una sola transacción)
```

---

## 📐 Fórmulas y Cálculos

### **1. Salario Quincenal**

```
Salario Básico Quincenal = Salario Mensual / 2
```

**Ejemplo:**
```
Salario mensual: $2,500,000
Salario quincenal: $2,500,000 / 2 = $1,250,000
```

---

### **2. Ingreso Base de Cotización (IBC)**

```
IBC = SUMA(Conceptos con tax_nature = 'SALARIAL' y direction = 'ADD')
```

**Ejemplo:**
```
+ Salario básico:      $1,250,000 (SALARIAL)
+ Horas extra:           $150,000 (SALARIAL)
+ Aux. transporte:        $80,000 (NO_SALARIAL) ← NO se suma
────────────────────────────────────────────────
  IBC =                $1,400,000
```

---

### **3. Deducciones de Seguridad Social**

```
Salud Empleado = IBC × 4%
Pensión Empleado = IBC × 4%
```

**Ejemplo:**
```
IBC: $1,400,000
Salud: $1,400,000 × 0.04 = $56,000
Pensión: $1,400,000 × 0.04 = $56,000
```

---

### **4. Aportes Patronales**

```
Salud Empleador = IBC × 8.5%
Pensión Empleador = IBC × 12%
```

**Ejemplo:**
```
IBC: $1,400,000
Salud patrón: $1,400,000 × 0.085 = $119,000
Pensión patrón: $1,400,000 × 0.12 = $168,000
```

---

### **5. Retenciones Contratistas**

```
Retención Fuente = Honorarios × 11%
Retención ICA = Honorarios × 0.966%
```

**Ejemplo:**
```
Honorarios: $5,000,000
Rete Fuente: $5,000,000 × 0.11 = $550,000
Rete ICA: $5,000,000 × 0.00966 = $48,300
```

---

### **6. Neto a Pagar**

```
Neto = Total Devengado - Total Deducciones
```

**Ejemplo completo empleado:**
```
DEVENGOS:
+ Salario básico:        $1,250,000
+ Horas extra:             $150,000
+ Aux. transporte:          $80,000
────────────────────────────────────
  Total devengado:       $1,480,000

DEDUCCIONES:
- Salud (4%):               $56,000
- Pensión (4%):             $56,000
- Préstamo:                $100,000
────────────────────────────────────
  Total deducciones:       $212,000

════════════════════════════════════
  NETO A PAGAR:          $1,268,000
════════════════════════════════════
```

---

## 💼 Casos de Uso

### **Caso 1: Empleado con Salario Básico Simple**

**Datos:**
- Empleado: María López
- Salario mensual: $1,500,000
- Periodo: Quincena 1-15 febrero

**Proceso:**
```
1. Salario básico = $1,500,000 / 2 = $750,000
2. IBC = $750,000 (no hay otros conceptos salariales)
3. Salud = $750,000 × 4% = $30,000
4. Pensión = $750,000 × 4% = $30,000
5. Neto = $750,000 - $60,000 = $690,000
```

---

### **Caso 2: Empleado con Horas Extra**

**Datos:**
- Empleado: Carlos Ruiz
- Salario mensual: $2,000,000
- Horas extra: $200,000
- Periodo: Quincena 1-15 febrero

**Proceso:**
```
1. Salario básico = $2,000,000 / 2 = $1,000,000
2. Horas extra (novedad) = $200,000
3. IBC = $1,000,000 + $200,000 = $1,200,000
4. Salud = $1,200,000 × 4% = $48,000
5. Pensión = $1,200,000 × 4% = $48,000
6. Devengado = $1,200,000
7. Deducciones = $96,000
8. Neto = $1,104,000
```

---

### **Caso 3: Empleado con Auxilio de Transporte**

**Datos:**
- Empleado: Ana Torres
- Salario mensual: $1,800,000
- Aux. transporte: $80,000
- Periodo: Quincena 1-15 febrero

**Proceso:**
```
1. Salario básico = $1,800,000 / 2 = $900,000
2. Aux. transporte = $80,000 (NO_SALARIAL)
3. IBC = $900,000 (transporte NO cuenta)
4. Salud = $900,000 × 4% = $36,000
5. Pensión = $900,000 × 4% = $36,000
6. Devengado = $900,000 + $80,000 = $980,000
7. Deducciones = $72,000
8. Neto = $908,000
```

---

### **Caso 4: Empleado con Préstamo**

**Datos:**
- Empleado: Luis Gómez
- Salario mensual: $2,500,000
- Descuento préstamo: $200,000
- Periodo: Quincena 1-15 febrero

**Proceso:**
```
1. Salario básico = $2,500,000 / 2 = $1,250,000
2. IBC = $1,250,000
3. Salud = $1,250,000 × 4% = $50,000
4. Pensión = $1,250,000 × 4% = $50,000
5. Préstamo (novedad) = $200,000
6. Devengado = $1,250,000
7. Deducciones = $50,000 + $50,000 + $200,000 = $300,000
8. Neto = $950,000
```

---

### **Caso 5: Contratista Simple**

**Datos:**
- Contratista: Pedro Sánchez
- Honorarios: $4,000,000
- Periodo: Mes de febrero

**Proceso:**
```
1. Honorarios (novedad) = $4,000,000
2. Rete Fuente = $4,000,000 × 11% = $440,000
3. Rete ICA = $4,000,000 × 0.966% = $38,640
4. Devengado = $4,000,000
5. Deducciones = $478,640
6. Neto = $3,521,360
```

---

## ⚠️ Puntos Críticos

### **1. Conceptos con `tax_nature` Correcta**

❌ **Error común:**
```
Aux. Transporte con tax_nature = 'SALARIAL'
→ Aumenta IBC incorrectamente
→ Deducciones más altas
```

✅ **Correcto:**
```
Aux. Transporte con tax_nature = 'NO_SALARIAL'
→ No afecta IBC
→ Deducciones correctas
```

---

### **2. Orden de Cálculo (`priority`)**

El sistema calcula en orden de prioridad:

```
10-99:   Devengos base
100-199: Deducciones
200-299: Aportes patronales
```

Esto asegura que:
1. Primero se calculan los devengos
2. Se calcula el IBC
3. Luego las deducciones sobre el IBC

---

### **3. Vigencia de Reglas**

Las tasas pueden cambiar en el tiempo:

```
Salud empleado antes 2024:
{"rate": 0.04, "valid_from": "2020-01-01", "valid_to": "2023-12-31"}

Salud empleado desde 2024:
{"rate": 0.045, "valid_from": "2024-01-01", "valid_to": null}
```

El sistema automáticamente usa la regla vigente según las fechas del periodo.

---

### **4. Novedades Duplicadas**

⚠️ Si recalculas un periodo:
- Las líneas anteriores se borran
- Las novedades `APPLIED` NO se vuelven a aplicar

✅ Para recalcular con las mismas novedades:
1. Cambiar novedades a `PENDING`
2. Ejecutar el cálculo nuevamente

---

## 🔍 Verificación de Resultados

### **Validar un Cálculo**

```sql
-- 1. Ver líneas del participante
SELECT 
    nc.name as concepto,
    npl.quantity,
    npl.base_amount,
    npl.rate,
    npl.amount,
    npl.direction
FROM nomina_pay_run_lines npl
JOIN nomina_concepts nc ON nc.id = npl.nomina_concept_id
WHERE npl.pay_run_id = [ID_PERIODO]
  AND npl.participant_id = [ID_EMPLEADO]
ORDER BY nc.priority;

-- 2. Ver totales del participante
SELECT 
    gross_total as devengado,
    deductions_total as deducciones,
    net_total as neto
FROM nomina_pay_run_participants
WHERE pay_run_id = [ID_PERIODO]
  AND participant_id = [ID_EMPLEADO];

-- 3. Verificar IBC manualmente
SELECT SUM(amount) as ibc_calculado
FROM nomina_pay_run_lines npl
JOIN nomina_concepts nc ON nc.id = npl.nomina_concept_id
WHERE npl.pay_run_id = [ID_PERIODO]
  AND npl.participant_id = [ID_EMPLEADO]
  AND npl.direction = 'ADD'
  AND nc.tax_nature = 'SALARIAL';
```

---

## 📞 Soporte y Extensión

### **Para Agregar un Nuevo Concepto:**

1. Crear el concepto en `nomina_concepts`
2. Si es porcentual, crear regla en `nomina_concept_rules`
3. Modificar `NominaEngineService` para incluir su cálculo
4. Probar con un periodo de prueba

### **Para Modificar una Tasa:**

1. **Opción A (Nueva vigencia):**
   - Cerrar regla actual con `valid_to`
   - Crear nueva regla con nueva tasa desde `valid_from`

2. **Opción B (Actualizar):**
   - Actualizar `parameters` en la regla existente
   - Solo si aún no se usó en periodos cerrados

---

## 📖 Glosario

- **IBC**: Ingreso Base de Cotización - Base para calcular seguridad social
- **Devengado**: Dinero que gana el empleado
- **Deducción**: Dinero que se descuenta al empleado
- **Aporte Patronal**: Costo que asume la empresa (no descuenta al empleado)
- **Novedad**: Evento que modifica la nómina estándar
- **PayRun**: Periodo de nómina (quincena, mes, etc.)
- **Tax Nature**: Naturaleza tributaria (salarial o no salarial)

---

**Última actualización:** Febrero 2026
**Versión del documento:** 1.0
