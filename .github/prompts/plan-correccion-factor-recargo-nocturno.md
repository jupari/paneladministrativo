## Plan: Corrección Factor `recargo_nocturno` en Tabla de Precios por Cargo

**TL;DR:** Un solo error numérico en el factor del `recargo_nocturno` dentro de `TablaPreciosCargoService.php` causaba que la columna "Recargo nocturno" de la tabla mostrara únicamente el valor del recargo adicional (35%) en lugar del precio total de la hora nocturna (135%). Esta era la discrepancia con el rango A14:I22 de la hoja "Tabla" del archivo `Calculo Nominas.xlsx`.

---

## Diagnóstico

El array `$factoresPrecio` en `app/Services/TablaPreciosCargoService.php` tenía:

| Campo | Factor incorrecto | Factor correcto | Diferencia |
|---|---|---|---|
| `recargo_nocturno` | **0.35 ❌** | **1.35 ✅** | El 0.35 es solo el recargo adicional; la hora nocturna tiene un precio **total** de T × 1.35, no T × 0.35 |
| `hora_extra_diurna` | 1.25 ✅ | 1.25 | — |
| `hora_extra_nocturna` | 1.75 ✅ | 1.75 | — |
| `hora_dominical` | 1.75 ✅ | 1.75 | — |
| `hora_extra_dominical_diurna` | 2.0 ✅ | 2.0 | — |
| `hora_extra_dominical_nocturna` | 2.5 ✅ | 2.5 | — |

**Impacto numérico con datos reales (cargo AYUDANTE de la pantalla):**
- Valor incorrecto: `T(8,591.39) × 0.35 / 0.685 = **4,422.04**`
- Valor correcto: `T(8,591.39) × 1.35 / 0.685 = **17,056.43**` = `hora_ordinaria × 1.35 = 12,634.39 × 1.35`

**Evidencia en 4 fuentes del mismo proyecto:**
1. `MANUAL_COTIZACIONES.md` — "Recargo Nocturno: T × **1.35**"
2. `MATRIZ_ESCENARIOS_COTIZACION.md` — `| Recargo Nocturno | 1.35 | 35% | T × 1.35 |`
3. `GUIA_TECNICA_PRECIOS_CARGOS.md` (ejemplo de cálculo) — `× (1 + 0.35) = × 1.35`
4. `public/assets/js/cotizar/documento.js` línea 11477 — `"Recargo nocturno (×1.35)"` (el frontend ya sabía el factor correcto)

---

## Implementación realizada

### Cambio 1 — Corrección del factor en el servicio
**Archivo:** `app/Services/TablaPreciosCargoService.php`

```php
// ANTES (incorrecto):
'recargo_nocturno' => 0.35,

// DESPUÉS (correcto):
'recargo_nocturno' => 1.35,
```

### Cambio 2 — Corrección del ejemplo de código en la guía técnica
**Archivo:** `GUIA_TECNICA_PRECIOS_CARGOS.md`

El bloque PHP de ejemplo en la guía también tenía `0.35` copiado del código buggeado. Corregido a `1.35` para que el ejemplo de código sea coherente con el ejemplo de cálculo dentro del mismo documento.

---

## Correcciones adicionales implementadas (P2)

### Problema 2 — Solo 1 cargo procesado (regresión)
**Causa:** El servicio solo procesaba cargos que tuvieran filas en `parametrizacion` con categoría NOMINA. Los demás cargos (SOLDADOR, PAILERO, TUBERO, AYUDANTE, SISO, RESIDENTE) tienen `salario_base` en la tabla `cargos` pero sin parametrización NOMINA.

**Solución:** Bloque *seed* insertado después del `foreach ($rows as $r)` que carga todos los `Cargo::where('active',1)->whereNotNull('salario_base')` que no están ya en `$porCargo`, heredando los porcentajes del primer cargo parametrizado. Sus valores absolutos (basico, aux_trans) los completa el bloque de prioridades.

```php
// app/Services/TablaPreciosCargoService.php — después del foreach de $rows
if (!empty($porCargo)) {
    $templatePcts = reset($porCargo);
    $cargosConSalario = \App\Models\Cargo::where('active', 1)
        ->whereNotNull('salario_base')
        ->whereNotIn('id', array_keys($porCargo))
        ->select(['id', 'nombre'])
        ->get();
    foreach ($cargosConSalario as $cargoSin) {
        $porCargo[$cargoSin->id] = [
            'cargo_id' => $cargoSin->id,
            'cargo'    => $cargoSin->nombre,
            'basico'   => 0.0, 'aux_trans' => 0.0, 'auxilio' => 0.0,
            // hereda todos los pct_* del template
            ...
        ];
    }
}
```

### Problema 3 — EPS (K) siempre 0
**Causa:** EPS no tiene fila en `parametrizacion` para ningún cargo. El campo `pct_eps` quedaba en 0.0 y por tanto K=0 en la suma R.

**Solución:** Fallback al parámetro `NOM_PCT_SALUD_EMPR=0.09` (9%) leído desde la tabla `elementos`, aplicado dentro del bloque de prioridades:
```php
if ($d['pct_eps'] == 0.0) {
    $d['pct_eps'] = $this->parametros->getFloat('NOM_PCT_SALUD_EMPR', 0.09);
}
```

### Problema 4 — Factores hardcodeados
**Causa:** `$factoresPrecio` tenía los multiplicadores escritos directamente en el código; cambiarlos requería deploy.

**Solución:** Se leen de la tabla `elementos` con fallback al valor legal colombiano:
```php
$factoresPrecio = [
    'hora_ordinaria'                => 1.0,
    'recargo_nocturno'              => $this->parametros->getFloat('NOM_FACTOR_RN',   1.35),
    'hora_extra_diurna'             => $this->parametros->getFloat('NOM_FACTOR_HED',  1.25),
    'hora_extra_nocturna'           => $this->parametros->getFloat('NOM_FACTOR_HEN',  1.75),
    'hora_dominical'                => $this->parametros->getFloat('NOM_FACTOR_TDD',  1.75),
    'hora_extra_dominical_diurna'   => $this->parametros->getFloat('NOM_FACTOR_HEDD', 2.00),
    'hora_extra_dominical_nocturna' => $this->parametros->getFloat('NOM_FACTOR_HEDN', 2.50),
];
```

### Resultado final verificado (7 cargos procesados)
| Cargo | Salario base | S (costo/día) | T (costo/hora) | H. Ordinaria |
|---|---|---|---|---|
| AYUDANTE | 1.300.000 | 82.721,54 | 10.340,19 | 15.206,17 |
| ING. SUPERVISOR | 2.000.000 | 173.413,51 | 21.676,69 | 31.877,48 |
| PAILERO | 1.700.000 | 105.389,48 | 13.173,69 | 19.373,07 |
| RESIDENTE DE OBRA | 200.000 | 20.384,71 | 2.548,09 | 3.747,19 |
| SISO | 1.700.000 | 105.389,48 | 13.173,69 | 19.373,07 |
| SOLDADOR | 2.000.000 | 122.390,43 | 15.298,80 | 22.498,24 |
| TUBERO | 2.000.000 | 122.390,43 | 15.298,80 | 22.498,24 |

---

## Paso manual post-deploy

Después de desplegar el cambio, regenerar la tabla en BD:

1. Ingresar a **Nómina → Parametrización → Tabla de precios por cargo**
2. Hacer clic en **"Generar / Recalcular"**
3. Esto actualiza la tabla `cargos_tabla_precios` con los valores corregidos

O via Artisan:
```bash
php artisan tabla-precios-cargo:generar
```

---

## Verificación

```sql
-- recargo_nocturno debe ser igual a ROUND(hora_ordinaria * 1.35, 2)
SELECT
    c.nombre,
    ctp.hora_ordinaria,
    ctp.recargo_nocturno,
    ROUND(ctp.hora_ordinaria * 1.35, 2) AS esperado,
    ABS(ctp.recargo_nocturno - ROUND(ctp.hora_ordinaria * 1.35, 2)) AS diferencia
FROM cargos_tabla_precios ctp
JOIN cargos c ON c.id = ctp.cargo_id;
```

Todos los valores de `diferencia` deben ser 0.00 o diferencia de centavos por redondeo.

---

## Scope y decisiones

- **Solo se cambió 1 número** en el servicio — no se refactorizó nada más
- **Los demás factores (extra_diurna, extra_nocturna, dominical, etc.) ya eran correctos**
- **Cotizaciones históricas no se tocan** — `ord_cotizacion_productos.valor_unitario` ya guardados quedan intactos; solo cotizaciones nuevas post-regeneración usarán el precio corregido
- **Fuera de scope:** revisar si cotizaciones existentes con precios erróneos deben recotizarse (decisión de negocio, no técnica)

---

**Fecha de implementación:** 2026-04-20
**Rama:** `bugfix/cotizacion`
**Archivos modificados:**
- `app/Services/TablaPreciosCargoService.php` — 1 línea
- `GUIA_TECNICA_PRECIOS_CARGOS.md` — 1 línea (consistencia documental)
