# 📌 Resumen Ejecutivo - Módulo de Cotizaciones

**Para Directivos, Gerentes y Personas Clave**

---

## 🎯 ¿Qué es el Módulo de Cotizaciones?

El **módulo de cotizaciones** es el sistema que permite crear **presupuestos profesionales** para los clientes. Automatiza el cálculo de precios basándose en:

- ✅ Costo real de operación (salarios, beneficios)
- ✅ Margen de utilidad empresarial (31.5%)
- ✅ Tipo de servicio (ordinario, extra, nocturno, etc.)

**Resultado:** Cotizaciones precisas, competitivas y rentables.

---

## 🏗️ Estructura General

```
MÓDULO DE COTIZACIONES
│
├── CREAR COTIZACIÓN
│   ├── Seleccionar cliente
│   ├── Seleccionar sede
│   └── Especificar proyecto
│
├── AGREGAR PRODUCTOS/SERVICIOS
│   ├── Productos estándares (precio fijo)
│   │   ├── Materiales
│   │   ├── Herramientas
│   │   └── Consumibles
│   │
│   ├── Servicios por Horas/Días (cargos)
│   │   ├── Horas ordinarias
│   │   ├── Horas extras
│   │   ├── Horas nocturnas
│   │   ├── Dominicales
│   │   └── Combinaciones (Extra + Dominical)
│   │
│   └── Items personalizados
│
├── APLICAR DESCUENTOS Y CONCEPTOS
│   ├── Descuento global (%)
│   ├── Descuentos por línea
│   ├── Impuestos (IVA, etc.)
│   └── Bonificaciones
│
└── FINALIZAR Y ENTREGAR
    ├── Generar PDF
    ├── Enviar a cliente
    └── Solicitar autorización
```

---

## 💰 Cómo se Calculan los Precios (Flujo Simplificado)

### Paso 1: Configuración Base
```
Gerente de RH establece:
├── Salario base de cada cargo
├── Nivel de riesgo (ARL)
└── Prestaciones sociales (AFP, Salud, etc.)
```

### Paso 2: Sistema Calcula Costo Real
```
Sistema automático calcula:

Salario: $3,000,000

Más contribuciones:
├── Cesantías: $249,900
├── Prima: $249,900
├── Vacaciones: $125,100
├── Pensión: $360,000
├── Salud: $255,000
├── Riesgo: $72,000
├── Caja: $90,000
└── Otros: ...

Costo Total Mensual: $4,404,399
Costo por Día (26 días): $169,401
Costo por Hora (8 horas): $21,175
```

### Paso 3: Sistema Aplica Margen
```
Costo Hora: $21,175

Aplica utilidad 31.5%:
Precio Venta = $21,175 / 0.685 = $30,911

Este precio incluye:
✓ Costo real de operación
✓ Margen de ganancia
✓ Sostenibilidad empresarial
```

### Paso 4: Se Usan Diferentes Multiplicadores Según Tipo de Hora

```
Hora Ordinaria:       $30,911 (factor 1.0)
Recargo Nocturno:     $41,729 (factor 1.35 = +35%)
Extra Diurna:         $38,638 (factor 1.25 = +25%)
Extra Nocturna:       $54,094 (factor 1.75 = +75%)
Dominical:            $54,094 (factor 1.75 = +75%)
Extra Dom Diurna:     $61,821 (factor 2.0 = +100%)
Extra Dom Nocturna:   $77,277 (factor 2.5 = +150%)

Valor Día:           $247,285 (= Hora Ordinaria × 8)
```

---

## 📋 Tres Tipos de Cotización Comunes

### Tipo A: Venta de Productos

**Cliente:** Industria XYZ pide 500 tuercas de 1/2"

```
Producto: Tuerca 1/2"
Cantidad: 500
Precio Unitario: $500 (del catálogo)
Subtotal: $250,000

Descuento: 10%
Descuento Valor: -$25,000

TOTAL: $225,000
```

**Margen:** Definido por política de precios.

---

### Tipo B: Servicios de Personal

**Cliente:** Servicio de seguridad 40 horas/semana durante 4 semanas

```
Servicio: Vigilancia Privada
Cargo: Vigilante
Precio Hora: $28,500 (según tabla de precios)

Semana 1: 40h × $28,500 = $1,140,000
Semana 2: 40h × $28,500 = $1,140,000
Semana 3: 40h × $28,500 = $1,140,000
Semana 4: 40h × $28,500 = $1,140,000

SUBTOTAL: $4,560,000
Descuento: 5% (volumen)
TOTAL: $4,332,000
```

**Margen:** Incluido en el precio hora ($28,500)

---

### Tipo C: Proyecto Complejo (Mixto)

**Cliente:** Auditoría de 2 semanas

```
Recurso 1: Auditor Senior
├── 80 horas ordinarias: 80 × $45,000 = $3,600,000
└── 10 horas extras: 10 × $56,250 = $562,500

Recurso 2: Auditor Junior
├── 80 horas ordinarias: 80 × $28,000 = $2,240,000

Recurso 3: Software de Auditoría
└── Licencia 1 mes: 1 × $1,200,000 = $1,200,000

Materiales (reportes, etc.)
└── Kit completo: 1 × $300,000 = $300,000

SUBTOTAL PROYECTO: $7,902,500
Descuento especial cliente: 8%
IVA: 19%

TOTAL PROYECTO: $8,687,740
```

**Márgenes:** Variados según componente.

---

## 🔧 Elementos Técnicos Clave (Para Gerencia)

### 1. La "Tabla de Precios de Cargos"

```
¿Qué es?
    Una tabla que el sistema genera automáticamente con los precios
    de cada tipo de hora para cada cargo.

¿Cuándo se actualiza?
    Cuando:
    ├── Se cambia un salario (aumentos anuales)
    ├── Se cambia nivel de riesgo de un cargo
    ├── Se actualizan parámetros globales (SMLV, utilidad)
    └── Se modifican beneficios (cambios en contribuciones)

¿Quién la actualiza?
    Sistema automático bajo solicitud de administrador

¿Con qué frecuencia?
    Anualmente (mínimo) o cuando cambia ley laboral
```

### 2. Prioridades en Cálculo

El sistema respeta este orden:

```
PRIORIDAD 1 (Más importante)
    ↓
    Valores configurados en CADA CARGO
    (salario_base, arl_nivel)

PRIORIDAD 2 (Media)
    ↓
    Parámetros de nómina (cesantías, prima, etc.)

PRIORIDAD 3 (Menos importante)
    ↓
    Parámetros globales (SMLV, utilidad)
```

**Implicación:** Si un ejecutivo quiere cambiar el precio de un cargo, **debe cambiar el salario del cargo**, no la nómina general.

### 3. Cálculos Automáticos

El sistema calcula automáticamente:

```
✓ Precio de cada hora/día
✓ Descuentos en líneas
✓ Descuentos globales
✓ Impuestos
✓ Total de la cotización

El usuario NO hace cálculos manuales
```

---

## 📊 Impacto Empresarial

### Beneficios

| Beneficio | Impacto |
|-----------|---------|
| **Precisión de precios** | ±1% error (vs. ±10% con cálculos manuales) |
| **Cotizaciones rápidas** | 5 min vs. 1 hora manual |
| **Rentabilidad asegurada** | Margen 31.5% incluido automáticamente |
| **Consistencia** | Todos los ejecutivos usan mismos precios |
| **Auditoría** | Historial completo de cada cotización |
| **Cliente satisfecho** | Precios justos, cotización profesional |

### Riesgos Mitigados

| Riesgo | Mitigación |
|--------|-----------|
| Cotizar por debajo de costo | Sistema incluye margen automáticamente |
| Inconsistencia de precios | Un solo origen de verdad (tabla precios) |
| Errores de cálculo | Cálculos automáticos sin intervención manual |
| Pérdida de margen | Margen de 31.5% garantizado |

---

## 🚀 Flujo Típico de una Cotización

```
MAÑANA

08:30 - Cliente llama solicitando cotización
        Ejecutivo abre sistema y crea cotización

08:45 - Agrega productos y servicios
        Sistema calcula automáticamente precios
        (basado en tabla de precios actualizada)

09:00 - Aplica descuentos acordados
        Revisa total final

09:15 - Genera PDF profesional
        Envía a cliente por email

MEDIODÍA

12:00 - Cliente recibe cotización
        La revisa, aprueba

13:00 - Se genera Orden de Servicio
        Se inicia prestación del servicio


CICLO COMPLETO:
Desde solicitud hasta inicio de servicio: 5 horas
Costo manual estimado: 8-10 horas
Ahorro por agilidad: 50%+ en tiempo ejecutivo
```

---

## 💡 Decisiones Importantes que Afectan el Módulo

### A. Cambio de Utilidad (Margen)

```
Escenario: Junta Directiva reduce utilidad de 31.5% a 28%
           (por presión de competencia)

Impacto:   Todos los precios bajan automáticamente
           Cotizaciones futuras reflejan nuevo margen
           Cotizaciones pasadas NO cambian

Acción:    1. Actualizar configuración en .env
           2. Regenerar tabla de precios
           3. Nueva cotización usa 28%
```

### B. Aumento de Salarios (Inflación)

```
Escenario: Decreto ley aumenta SMLV a $1,400,000
           Empresa decide aumentar salarios 6%

Impacto:   Costo de operación aumenta
           Precios de cotización suben automáticamente

Acción:    1. Actualizar salarios en sistema
           2. Actualizar SMLV global
           3. Regenerar tabla de precios
           4. Nuevas cotizaciones reflejan aumento
```

### C. Cambio de Beneficios

```
Escenario: AFP negocia menor aporte de empleador

Impacto:   Costo de operación disminuye
           Margen mejora

Acción:    1. Actualizar % de AFP en parametrización
           2. Regenerar tabla de precios
           3. Nuevas cotizaciones usan nuevo margen
```

---

## 🎓 Capacitación Recomendada

### Ejecutivos de Ventas
- ✅ Cómo crear una cotización
- ✅ Cómo agregar productos y servicios
- ✅ Cómo aplicar descuentos
- ✅ Cómo generar PDF
- ⚠️ NO tocar configuración de precios

### Gerente de RH / Nomina
- ✅ Qué es la tabla de precios
- ✅ Cuándo regenerarla
- ✅ Cómo verificar que sea correcta
- ✅ Relación entre salarios → precios

### Gerente Administrativo / Financiero
- ✅ Flujo completo de cotización
- ✅ Verificación de márgenes
- ✅ Análisis de rentabilidad
- ✅ Auditoría de precios

### Administrador de Sistema
- ✅ Arquitectura completa del módulo
- ✅ Cómo regenerar tabla de precios
- ✅ Configuración de parámetros globales
- ✅ Troubleshooting

---

## 📈 Indicadores Clave a Monitorear

```
INDICADOR                          META        FRECUENCIA
────────────────────────────────────────────────────────
Tiempo promedio de cotización      < 15 min    Semanal
Precisión de precios              ±1%         Mensual
Margen promedio logrado           31.5%       Mensual
Tasa de aceptación de cotizaciones > 70%      Mensual
Variación de precios año anterior  ±10%       Anual
Número de cotizaciones/mes        > 50        Semanal
Valor promedio de cotización      $XXX        Mensual
```

---

## ❓ Preguntas Frecuentes Ejecutivas

**P: ¿Si cambiamos de utilidad, ¿se recalculan todas las cotizaciones viejas?**

R: No. Cada cotización guarda el margen que tenía cuando se creó. Esto es por auditoría. Solo las nuevas cotizaciones usan la utilidad nueva.

---

**P: ¿Cómo garantizamos que todos los ejecutivos cotizan a los mismos precios?**

R: Todos obtienen precios de una sola fuente: la "Tabla de Precios de Cargos". No hay variabilidad porque no hay cálculo manual.

---

**P: ¿Si un salario es erróneo, ¿cómo lo corregimos?**

R: 1. Editar el salario en el cargo
   2. Regenerar la tabla de precios
   3. Las nuevas cotizaciones usan el precio correcto
   4. Las viejas cotizaciones conservan el precio anterior (auditoría)

---

**P: ¿Podemos cotizar por debajo de costo?**

R: El sistema no lo permite. Incluye automáticamente el margen de 31.5%. Un ejecutivo podría forzar un descuento, pero aparecería en el historial.

---

**P: ¿Cuánto cuesta mantener el sistema?**

R: Inversión inicial en:
   - Configuración de parámetros (1 día)
   - Capacitación (4 horas)
   - Costo mensual: Regeneración tabla (5 minutos)

---

## 🎯 Conclusión

El módulo de cotizaciones es **automático, preciso y rentable**.

- ✅ Genera cotizaciones en minutos (no horas)
- ✅ Garantiza margen de ganancia
- ✅ Proporciona consistencia de precios
- ✅ Auditable y confiable

**Inversión inicial:** 1 día de configuración
**Retorno:** Ahorro de 50+ horas/mes en cálculos manuales

---

**Preguntas o aclaraciones:** Contactar a Desarrollo o Gerencia Administrativa

---

**Documento preparado:** Marzo 30, 2026
**Versión:** 1.0
**Siguiente revisión:** Trimestral o según cambios legales
