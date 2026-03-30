# 📚 Documentación del Módulo de Cotizaciones

**Centro de Recursos - Empieza Aquí**

---

## 🎯 Elige tu Rol y Comienza a Leer

### 👔 Soy Directivo / Gerente General
**Tiempo de lectura:** 15 minutos

👉 **Lee:** [RESUMEN_EJECUTIVO_COTIZACIONES.md](./RESUMEN_EJECUTIVO_COTIZACIONES.md)

**Aprenderás:**
- ¿Qué es el módulo de cotizaciones?
- Cómo se calculan los precios (sin tecnicismos)
- Impacto empresarial y beneficios
- Indicadores clave a monitorear
- Respuestas a preguntas de negocio

---

### 💼 Soy Ejecutivo de Ventas / Comercial
**Tiempo de lectura:** 30 minutos

👉 **Lee en orden:**

1. **[MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)** - Sección "Flujo General"
   - Cómo crear una cotización
   - Cómo agregar productos y servicios
   - Cómo calcular totales

2. **[MATRIZ_ESCENARIOS_COTIZACION.md](./MATRIZ_ESCENARIOS_COTIZACION.md)** - Sección "Escenarios Comunes"
   - Diferentes tipos de cotizaciones
   - Cuándo usar cada tipo de precio
   - Ejemplos prácticos

3. **[MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)** - Sección "Troubleshooting"
   - Qué hacer si algo no funciona
   - Respuestas rápidas a problemas

**Guarda como favorito:** [MATRIZ_ESCENARIOS_COTIZACION.md](./MATRIZ_ESCENARIOS_COTIZACION.md)
(Consúltalo cada vez que necesites elegir un tipo de hora)

---

### 👨‍💼 Soy Gerente de RH / Nómina
**Tiempo de lectura:** 45 minutos

👉 **Lee en orden:**

1. **[MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)** - Sección "Cálculo de Precios de Cargos"
   - De dónde vienen los precios
   - Cómo se generan las tablas
   - Sistema de prioridades

2. **[GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md)** - Sección "Fórmulas de Cálculo"
   - Matemática detrás de los precios
   - Cada componente explicado
   - Prioridades en detalle

3. **[MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)** - Sección "Troubleshooting"
   - P3: Precios de horas extras están mal
   - P4: No se puede calcular valor total

**Tu responsabilidad clave:**
- ✅ Mantener salarios_base actualizados
- ✅ Mantener arl_nivel correcto
- ✅ Regenerar tabla de precios cuando hay cambios

---

### 👨‍💻 Soy Desarrollador / Programador
**Tiempo de lectura:** 2 horas

👉 **Lee en orden:**

1. **[GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md)** (Completo)
   - Flujo técnico completo
   - Código fuente comentado
   - Fórmulas paso a paso
   - Ejemplos de cálculo reales

2. **[MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)** - Sección "Cambios Recientes y Mejoras"
   - Campo "bono" agregado
   - Nueva tabla CotizacionLista
   - Auditoría de cambios

3. **[MATRIZ_ESCENARIOS_COTIZACION.md](./MATRIZ_ESCENARIOS_COTIZACION.md)** - Sección "Checklist"
   - Qué revisar antes de cambiar código

**Archivos clave a estudiar:**
```
app/Services/TablaPreciosCargoService.php     ← Lógica principal
app/Models/CotizacionProducto.php            ← Auto-cálculo
app/Services/CotizacionProductoService.php   ← Integración
app/Services/CotizacionService.php           ← Orquestación
```

**Testing:**
Asegúrate de ejecutar los ejemplos en [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md) - Sección "Testing y Validación"

---

### 👨‍💼 Soy Administrador del Sistema / DevOps
**Tiempo de lectura:** 30 minutos

👉 **Lee:**

1. **[MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)** - Sección "Parámetros Globales"
   - Cómo actualizar SMLV
   - Cómo cambiar utilidad %
   - Cómo regenerar tablas

2. **[GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md)** - Sección "Testing y Validación"
   - Cómo verificar que precios sean correctos
   - Debugging de un cargo específico

**Tu responsabilidad clave:**
- ✅ Regenerar tabla de precios anualmente (después de aumentos)
- ✅ Monitorear tabla de precios (¿está vacía? ¿tiene valores realistas?)
- ✅ Responder consultas sobre por qué cambió un precio

---

## 🗂️ Descripción de Documentos

### 1. README_COTIZACIONES.md (Este archivo)
**Propósito:** Guía de navegación - saber por dónde empezar

**Cuándo leerlo:**
- Primer contacto con el módulo
- Necesitas saber qué documento leer según tu rol

**Contenido:**
- Rutas de aprendizaje por rol
- Tabla de contenidos cruzada
- Glosario de términos

---

### 2. RESUMEN_EJECUTIVO_COTIZACIONES.md
**Propósito:** Visión ejecutiva sin tecnicismos

**Cuándo leerlo:**
- Eres gerente o directivo
- Necesitas entender ROI del módulo
- Necesitas datos para reuniones

**Contenido:**
- Beneficios empresariales
- Flujo simplificado
- Indicadores clave
- Preguntas frecuentes ejecutivas

---

### 3. MANUAL_COTIZACIONES.md
**Propósito:** Manual completo de uso y referencia

**Cuándo leerlo:**
- Primera vez usando el módulo
- Necesitas resolver un problema
- Necesitas documentación completa

**Contenido:**
- Cómo usar cada función
- Cálculo paso a paso de precios
- Todos los escenarios
- Troubleshooting extenso
- Referencias de endpoints

---

### 4. GUIA_TECNICA_PRECIOS_CARGOS.md
**Propósito:** Guía técnica para desarrolladores

**Cuándo leerlo:**
- Necesitas entender cómo funciona el código
- Necesitas modificar fórmulas de cálculo
- Necesitas debuggear problemas

**Contenido:**
- Código fuente comentado
- Flujos técnicos completos
- Fórmulas paso a paso
- Casos edge y cuidados
- Fuentes y referencias

---

### 5. MATRIZ_ESCENARIOS_COTIZACION.md
**Propósito:** Referencia rápida - no es para leer completo

**Cuándo consultarlo:**
- Estás creando una cotización (¿cuál tipo de hora usar?)
- Necesitas verificar una fórmula
- Necesitas checklist antes de cotizar
- Necesitas tabla de factores

**Contenido:**
- Matriz de decisión (diagrama)
- Tabla rápida de 9 escenarios
- Checklist pre-cotización
- Tabla de factores de incremento
- Troubleshooting ultra-rápido

---

## 🔄 Relación Entre Documentos

```
                    README_COTIZACIONES.md
                    (Estás aquí - índice)
                             │
              ┌──────────────┼──────────────┬─────────────┐
              │              │              │             │
              ▼              ▼              ▼             ▼
        EJECUTIVOS      EJECUTIVOS        RH/          DEVELOPERS
        (15 min)        COMERCIALES      NOMINA         (2 horas)
                        (30 min)         (45 min)
              │              │              │             │
              ▼              ▼              ▼             ▼
        RESUMEN_        MANUAL +        GUIA_         GUIA_
        EJECUTIVO       MATRIZ          TECNICA       TECNICA


                    Lectura común:
                    MANUAL_COTIZACIONES.md
                    (Punto de referencia para todos)
```

---

## 📚 Tabla Cruzada de Contenidos

| Tema | Dónde encontrarlo |
|------|-------------------|
| Cómo crear cotización | MANUAL - Flujo General |
| Cómo agregar productos | MANUAL - Flujo General |
| Tipos de horas (9 escenarios) | MATRIZ - Escenarios |
| Fórmula de cálculo completa | GUIA TECNICA - Paso a Paso |
| Cómo regenerar tabla precios | MANUAL - Cambios Recientes |
| Beneficios para negocio | RESUMEN EJECUTIVO |
| Código fuente comentado | GUIA TECNICA - Código |
| Ejemplos con números reales | GUIA TECNICA - Ejemplos |
| Checklist pre-cotización | MATRIZ - Checklist |
| Troubleshooting | MANUAL - Troubleshooting |
| Tabla de factores horarios | MATRIZ - Tabla Rápida |
| Indicadores a monitorear | RESUMEN EJECUTIVO |
| Configuración por ambiente | MATRIZ - Configuración |

---

## 🚀 Primeros Pasos Rápidos

### Si tienes 5 minutos
1. Lee [RESUMEN_EJECUTIVO_COTIZACIONES.md](./RESUMEN_EJECUTIVO_COTIZACIONES.md) - "¿Qué es el Módulo?"
2. Mira los "Tres tipos de cotización comunes"

### Si tienes 20 minutos
1. Lee [RESUMEN_EJECUTIVO_COTIZACIONES.md](./RESUMEN_EJECUTIVO_COTIZACIONES.md)
2. Lee [MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md) - "Flujo General" + "Escenario 2"

### Si tienes 1 hora
1. Lee [RESUMEN_EJECUTIVO_COTIZACIONES.md](./RESUMEN_EJECUTIVO_COTIZACIONES.md)
2. Lee [MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md) - Completo, skip Troubleshooting

### Si tienes 2 horas (Desarrollador)
1. Lee [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md)
2. Estudia el código en `TablaPreciosCargoService.php`
3. Ejecuta el ejemplo práctico (Sección 4)

---

## 🎓 Glosario de Términos

| Término | Definición | Dónde leer |
|---------|-----------|-----------|
| **Tabla de Precios** | Tabla generada con precios de cada tipo de hora para cada cargo | MANUAL - Cálculo de Precios |
| **Costo Hora (T)** | Costo real sin margen, calculado como (R/26)/8 | GUIA TECNICA - Fórmulas |
| **Utilidad** | Margen de ganancia (típicamente 31.5%) | MANUAL - Cambios Recientes |
| **Factor de Hora** | Multiplicador según tipo (1.0, 1.35, 1.75, etc.) | MATRIZ - Tabla de Factores |
| **Cotización** | Presupuesto profesional entregado a cliente | RESUMEN EJECUTIVO - Introducción |
| **Producto** | Bien tangible con precio fijo | MATRIZ - Escenario 1 |
| **Cargo** | Servicio de personal con precio por hora/día | MATRIZ - Escenarios 2-9 |
| **SMLV** | Salario Mínimo Legal Vigente | MANUAL - Prioridades |
| **ARL/ARP** | Seguro de Riesgos Laborales | GUIA TECNICA - Componentes |
| **Parametrización** | Configuración de conceptos de nómina | MANUAL - Origen de Tabla Precios |
| **Descuento** | Reducción de precio (% o valor fijo) | MATRIZ - Escenario 1 |
| **Concepto** | Línea de impuesto, descuento o bonificación | MANUAL - Flujo General |
| **Bono** | Bonificación adicional al precio | MANUAL - Campo Bono |

---

## ❓ Preguntas de Navegación

**P: No sé cuál documento leer**
R: Usa la sección "Elige tu Rol y Comienza a Leer" arriba ↑

**P: Necesito saber un precio específico ¿dónde miro?**
R: [MATRIZ_ESCENARIOS_COTIZACION.md](./MATRIZ_ESCENARIOS_COTIZACION.md) - Tabla de Factores

**P: El precio está mal ¿cómo lo arreglo?**
R: [MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md) - Troubleshooting P3

**P: Soy nuevo ¿por dónde empiezo?**
R: Lee primero tu rol en "Elige tu Rol" de este documento

**P: Necesito código comentado**
R: [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md) - Sección 2

**P: Necesito un ejemplo con números**
R: [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md) - Sección 4

---

## 📞 ¿Necesitas Ayuda?

### Problema técnico
- Contacta a: **Equipo de Desarrollo**
- Referencia: [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md)

### Problema de precio
- Contacta a: **Gerente RH/Nómina**
- Referencia: [MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md) - Troubleshooting

### Pregunta de negocio
- Contacta a: **Gerencia General/Comercial**
- Referencia: [RESUMEN_EJECUTIVO_COTIZACIONES.md](./RESUMEN_EJECUTIVO_COTIZACIONES.md)

### No encuentras respuesta
- Busca en: **Todos los documentos** usando Ctrl+F
- Si aún no encuentras, crea un **issue en GitHub** o contacta a desarrollo

---

## 📅 Versiones y Actualizaciones

| Documento | Versión | Actualizado |
|-----------|---------|-------------|
| README | 1.0 | Marzo 30, 2026 |
| RESUMEN EJECUTIVO | 1.0 | Marzo 30, 2026 |
| MANUAL | 1.0 | Marzo 30, 2026 |
| GUIA TECNICA | 1.0 | Marzo 30, 2026 |
| MATRIZ | 1.0 | Marzo 30, 2026 |

**Próxima actualización:** Junio 30, 2026 (o antes si hay cambios)

---

## ✅ Checklist: ¿Estoy Listo?

### Para Ejecutivos Comerciales
- [ ] He leído [RESUMEN_EJECUTIVO_COTIZACIONES.md](./RESUMEN_EJECUTIVO_COTIZACIONES.md)
- [ ] He leído "Flujo General" en [MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)
- [ ] Tengo guardado [MATRIZ_ESCENARIOS_COTIZACION.md](./MATRIZ_ESCENARIOS_COTIZACION.md) como favorito
- [ ] He creado mi primera cotización sin errores
- [ ] He resuelto al menos un problema usando Troubleshooting

### Para Gerentes RH
- [ ] He leído "Cálculo de Precios" en [MANUAL_COTIZACIONES.md](./MANUAL_COTIZACIONES.md)
- [ ] He leído "Fórmulas" en [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md)
- [ ] He regenerado tabla de precios exitosamente
- [ ] He verificado que precios sean realistas (vs. salarios)

### Para Desarrolladores
- [ ] He leído [GUIA_TECNICA_PRECIOS_CARGOS.md](./GUIA_TECNICA_PRECIOS_CARGOS.md) completo
- [ ] He estudiado `TablaPreciosCargoService.php`
- [ ] He ejecutado el ejemplo práctico (Ejemplo 1)
- [ ] He debuggeado un precio en BD
- [ ] Entiendo la diferencia entre prioridades de cálculo

---

**¡Bienvenido al módulo de cotizaciones!**

**Próximo paso:** Elige tu rol arriba y comienza a leer 👆

---

**Documento:** README_COTIZACIONES.md
**Versión:** 1.0
**Fecha:** Marzo 30, 2026
**Última revisión:** Marzo 30, 2026
