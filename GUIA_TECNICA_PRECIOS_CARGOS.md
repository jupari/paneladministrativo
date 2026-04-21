# 🔧 Guía Técnica: Cálculo de Precios de Cargos

**Documento Técnico para Desarrolladores**

---

## 1. Flujo Técnico Completo

```
┌─────────────────────────────────────────────────────────────────┐
│ USUARIO CREA COTIZACIÓN Y AGREGA CARGO (Ej: Técnico Senior)    │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
        ┌───────────────────────────────┐
        │ CotizacionProductoController  │
        │     @store()                   │
        │  - Validar datos              │
        │  - Guardar producto           │
        │  - Obtener precio de cargo    │
        └────────────┬──────────────────┘
                     │
                     ▼
        ┌───────────────────────────────┐
        │ CotizacionProductoService     │
        │   agregarProducto()           │
        │  - Buscar tabla_precios_cargo │
        │  - Calcular valor_unitario    │
        │  - Guardar en BD              │
        └────────────┬──────────────────┘
                     │
                     ▼
        ┌──────────────────────────────┐
        │ TablaPreciosCargoService     │
        │  generar()                    │
        │ (si tabla no existe)          │
        │ ├─ Leer parametrización      │
        │ ├─ Aplicar prioridades       │
        │ ├─ Calcular fórmulas         │
        │ └─ Persistir en BD           │
        └────────────┬─────────────────┘
                     │
                     ▼
        ┌──────────────────────────────┐
        │ Tabla: cargos_tabla_precios  │
        │  Contiene: hora_ordinaria,   │
        │            recargo_nocturno, │
        │            extras, etc.      │
        └────────────┬─────────────────┘
                     │
                     ▼
        ┌──────────────────────────────┐
        │ CotizacionProducto guardado  │
        │  valor_unitario = precio OK  │
        │  valor_total calculado auto  │
        └──────────────────────────────┘
```

---

## 2. Código de Generación de Tabla de Precios

**Archivo:** `app/Services/TablaPreciosCargoService.php`

### 2.1 Estructura Principal

```php
public function generar(bool $persistir = true): array
{
    // 1. OBTENER CONFIGURACIÓN
    $utilidad = (float) config('app.utilidadPct', 0.315);      // 31.5%
    $horasDiarias = (int) config('app.horasDiarias', 8);       // 8 horas
    $diasMes = 26;                                              // 26 días/mes

    // 2. TRAER DATOS DE PARAMETRIZACIÓN
    $rows = DB::table('parametrizacion as p')
        ->join('cargos as c', 'c.id', '=', 'p.cargo_id')
        ->join('novedades_detalle as nd', 'nd.id', '=', 'p.novedad_detalle_id')
        ->join('novedades as n', 'n.id', '=', 'nd.novedad_id')
        ->where('p.active', 1)
        ->where(DB::raw("UPPER(cat.nombre)"), '=', 'NOMINA')
        ->select([...])
        ->get();

    // 3. PROCESAR POR CARGO
    $porCargo = [];
    foreach ($rows as $r) {
        // ... lógica de agrupación por cargo ...
    }

    // 4. APLICAR PRIORIDADES
    // - salario_base del cargo sobrescribe DATOS BÁSICOS
    // - arl_nivel del cargo sobrescribe % ARL
    // - aux_transporte global aplica si salario ≤ 2×SMLV

    // 5. CALCULAR FACTORES DE PRECIO
    foreach ($porCargo as $cargoId => $d) {
        // Calcular B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q
        // Calcular R, S, T
        // Aplicar utilidad
        // Persistir si $persistir = true
    }

    return $resultado;
}
```

### 2.2 Mapeo de Conceptos (Parte Crítica)

```php
// DATOS BÁSICOS - Valores Absolutos
if ($novedad === 'DATOS BÁSICOS') {
    $valorAbs = $toFloat($r->valor_porcentaje);

    if ($detalle === 'BASICO') {
        $porCargo[$cargoId]['basico'] = $valorAbs;
    } else if (str_contains($detalle, 'AUX') && str_contains($detalle, 'TRANSP')) {
        $porCargo[$cargoId]['aux_trans'] = $valorAbs;
    } else if ($detalle === 'AUXILIO') {
        $porCargo[$cargoId]['auxilio'] = $valorAbs;
    }
    continue;
}

// TOTALES FIJOS - valor_porcentaje = 0, usar total_admon + total_operativo
if ($vpRaw == 0.0) {
    $total = 0.0;

    if ((int)$r->valor_admon === 1) {
        $total += $toFloat($r->total_admon);
    }
    if ((int)$r->valor_obra === 1) {
        $total += $toFloat($r->total_operativo);
    }

    // Mapear según nombre
    if (str_contains($detalle, 'DOTACION')) {
        $porCargo[$cargoId]['dotacion_total'] = $total;
    } elseif (str_contains($detalle, 'EXAMEN')) {
        $porCargo[$cargoId]['examenes_total'] = $total;
    } elseif (str_contains($detalle, 'VIATIC')) {
        $porCargo[$cargoId]['viaticos_total'] = $total;
    } elseif (str_contains($detalle, 'ALIMENT')) {
        $porCargo[$cargoId]['aliment_total'] = $total;
    }
    continue; // No procesar como porcentaje
}

// PORCENTAJES - valor_porcentaje > 0
$pct = $pctNorm($vpRaw); // Normalizar: si > 1, dividir entre 100

if (str_contains($detalle, 'CESANT') && !str_contains($detalle, 'INT')) {
    $porCargo[$cargoId]['pct_cesantias'] = $pct;
} elseif (str_contains($detalle, 'PRIMA')) {
    $porCargo[$cargoId]['pct_prima'] = $pct;
} elseif (str_contains($detalle, 'VAC')) {
    $porCargo[$cargoId]['pct_vacaciones'] = $pct;
} elseif (str_contains($detalle, 'ARP') || str_contains($detalle, 'ARL')) {
    $porCargo[$cargoId]['pct_arp'] = $pct;
} elseif (str_contains($detalle, 'EPS')) {
    $porCargo[$cargoId]['pct_eps'] = $pct;
} elseif (str_contains($detalle, 'PENSION')) {
    $porCargo[$cargoId]['pct_pension'] = $pct;
} elseif (str_contains($detalle, 'CCF') || str_contains($detalle, 'CAJA')) {
    $porCargo[$cargoId]['pct_ccf'] = $pct;
}
```

### 2.3 Aplicación de Prioridades

```php
// PRIORIDAD 1: Campos en el Cargo (sobrescriben parametrización)
$cargosData = \App\Models\Cargo::whereIn('id', array_keys($porCargo))
    ->select(['id', 'salario_base', 'arl_nivel'])
    ->get()
    ->keyBy('id');

foreach ($porCargo as $cargoId => &$d) {
    $cargo = $cargosData[$cargoId] ?? null;
    if (!$cargo) continue;

    // B: Usar salario_base del cargo si existe
    if ($cargo->salario_base !== null) {
        $d['basico'] = (float)$cargo->salario_base;

        // C: Aplicar aux_transporte global si salario ≤ 2×SMLV
        if ($paramGlobal) {
            $aplica = ((float)$cargo->salario_base) <= ((float)$paramGlobal->smlv * 2);
            $d['aux_trans'] = $aplica ? (float)$paramGlobal->aux_transporte : 0.0;
        }
    }

    // ARL: Usar nivel del cargo (SIEMPRE sobrescribe)
    if (isset($arlNiveles[$cargo->arl_nivel])) {
        $d['pct_arp'] = (float)$arlNiveles[$cargo->arl_nivel] / 100;
    }
}
```

### 2.4 Fórmulas de Cálculo

```php
// Denominador para aplicar utilidad
$den = (1 - $utilidad);  // Ejemplo: 1 - 0.315 = 0.685

foreach ($porCargo as $cargoId => $d) {
    // ═══════════════════════════════════════
    // COMPONENTES DE SALARIO
    // ═══════════════════════════════════════
    $B = round($d['basico'], 2);
    $C = round($d['aux_trans'], 2);
    $D = round($d['auxilio'], 2);

    // Base para totales
    $E = round($B + $C + $D, 2);
    $base_E_menos_D = max(0, $E - $D);

    // ═══════════════════════════════════════
    // CONCEPTOS PORCENTUALES
    // ═══════════════════════════════════════
    $F = round($base_E_menos_D * $d['pct_cesantias'], 2);        // Cesantías
    $G = round($F * $d['pct_int_cesantias'], 2);                 // Int. Cesantías
    $H = round($base_E_menos_D * $d['pct_prima'], 2);            // Prima

    $I = round($B * $d['pct_vacaciones'], 2);                    // Vacaciones
    $J = round($B * $d['pct_arp'], 2);                           // ARP
    $K = round($B * $d['pct_eps'], 2);                           // EPS
    $L = round($B * $d['pct_pension'], 2);                       // Pensión
    $M = round($B * $d['pct_ccf'], 2);                           // CCF

    // ═══════════════════════════════════════
    // TOTALES FIJOS
    // ═══════════════════════════════════════
    $N = round($d['viaticos_total'], 2);
    $O = round($d['aliment_total'], 2);
    $P = round($d['dotacion_total'], 2);
    $Q = round($d['examenes_total'], 2);

    // ═══════════════════════════════════════
    // COSTO TOTAL PERÍODO
    // ═══════════════════════════════════════
    $R = round($E + $F + $G + $H + $I + $J + $K + $L + $M + $N + $O + $P + $Q, 2);

    // ═══════════════════════════════════════
    // COSTO DÍA Y HORA
    // ═══════════════════════════════════════
    $S = round($R / $diasMes, 2);                                // Costo día
    $T = ($horasDiarias > 0) ? round($S / $horasDiarias, 4) : 0; // Costo hora base

    // ═══════════════════════════════════════
    // TABLA DE FACTORES DE HORAS
    // ═══════════════════════════════════════
    $factoresPrecio = [
        'hora_ordinaria' => 1.0,
        'recargo_nocturno' => 1.35,
        'hora_extra_diurna' => 1.25,
        'hora_extra_nocturna' => 1.75,
        'hora_dominical' => 1.75,
        'hora_extra_dominical_diurna' => 2.0,
        'hora_extra_dominical_nocturna' => 2.5,
    ];

    $row = [
        'B_basico' => $B,
        'C_aux_trans' => $C,
        'D_auxilio' => $D,
        'E_total' => $E,
        'F_cesantias' => $F,
        'G_int_cesantias' => $G,
        'H_prima' => $H,
        'I_vacaciones' => $I,
        'J_arp' => $J,
        'K_eps' => $K,
        'L_pension' => $L,
        'M_ccf' => $M,
        'N_viaticos' => $N,
        'O_aliment' => $O,
        'P_dotacion' => $P,
        'Q_examenes' => $Q,
        'R_total_mes' => $R,
        'S_costo_dia' => $S,
        'T_costo_hora' => $T,
    ];

    // ═══════════════════════════════════════
    // APLICAR UTILIDAD A FACTORES
    // ═══════════════════════════════════════
    foreach ($factoresPrecio as $k => $f) {
        // Fórmula: (T × Factor) / (1 - Utilidad%)
        $row[$k] = round(($T * $f) / $den, 2);
    }

    // Valor día ordinario = precio_hora_ordinaria × 8
    $row['valor_dia_ordinario'] = round($row['hora_ordinaria'] * $horasDiarias, 2);

    // Persistir en BD
    if ($persistir) {
        DB::table('cargos_tabla_precios')->updateOrInsert(
            ['cargo_id' => $cargoId],
            [
                'hora_ordinaria' => $row['hora_ordinaria'],
                'recargo_nocturno' => $row['recargo_nocturno'],
                'hora_extra_diurna' => $row['hora_extra_diurna'],
                // ... etc
                'updated_at' => now(),
            ]
        );
    }
}
```

---

## 3. Uso en Cotizaciones

### 3.1 Cuando se agrega un Cargo a la Cotización

**Archivo:** `app/Http/Controllers/Cotizar/CotizacionProductoController.php`

```php
public function store(CotizacionProductoRequest $request): JsonResponse
{
    try {
        // 1. Guardar producto
        $producto = $this->cotizacionProductoService->agregarProducto(
            $request->validated()
        );

        // 2. Actualizar totales automáticamente
        $this->cotizacionProductoService->actualizarTotalesAutomaticamente(
            $producto->cotizacion_id
        );

        return response()->json([
            'success' => true,
            'data' => $producto->load('producto'),
        ], 201);

    } catch (Exception $e) {
        Log::error("Error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al agregar producto: ' . $e->getMessage()
        ], 500);
    }
}
```

### 3.2 Service que obtiene el Precio

**Archivo:** `app/Services/CotizacionProductoService.php` (lógica interna)

```php
// Pseudocódigo de la lógica
public function agregarProducto(array $datos)
{
    // Si es un cargo (categoria_id o cargo_id)
    if (!empty($datos['cargo_id'])) {

        // Obtener tabla de precios para este cargo
        $tablaPrecio = DB::table('cargos_tabla_precios')
            ->where('cargo_id', $datos['cargo_id'])
            ->first();

        if (!$tablaPrecio) {
            // Regenerar tabla de precios
            $service = app(TablaPreciosCargoService::class);
            $service->generar(true);

            // Reintentar
            $tablaPrecio = DB::table('cargos_tabla_precios')
                ->where('cargo_id', $datos['cargo_id'])
                ->first();
        }

        // Obtener tipo de costo (hora_ordinaria, hora_extra_diurna, valor_dia_ordinario, etc.)
        $tipoCosto = $datos['tipo_costo'] ?? 'hora_ordinaria';

        // Establecer valor_unitario desde tabla de precios
        $datos['valor_unitario'] = (float)$tablaPrecio->{$tipoCosto};
    }

    // Guardar producto (valor_total se calcula automáticamente en boot())
    return CotizacionProducto::create($datos);
}
```

### 3.3 Cálculo Automático de valor_total

**Archivo:** `app/Models/CotizacionProducto.php`

```php
protected static function boot()
{
    parent::boot();

    static::saving(function ($model) {
        // Subtotal sin descuentos
        $subtotal = $model->cantidad * $model->valor_unitario;

        // Descuentos (valor absoluto + porcentaje)
        $descuento = $model->descuento_valor +
                     ($subtotal * ($model->descuento_porcentaje / 100));

        // Total = Subtotal - Descuentos + Bono
        $model->valor_total = round(
            ($subtotal - $descuento) + ($model->bono ?? 0),
            2
        );
    });
}
```

---

## 4. Ejemplos Prácticos de Cálculo

### Ejemplo 1: Técnico Senior - Hora Ordinaria

**Inputs:**
```
Cargo: "Técnico Senior"
salario_base: 3,000,000
arl_nivel: 3
Utilidad: 31.5%
SMLV: 1,300,000

Parametrización:
- Cesantías: 8.33%
- Prima: 8.33%
- Vacaciones: 4.17%
- ARP Nivel 3: 2.40%
- EPS: 8.5%
- Pensión: 12%
- CCF: 3%
```

**Cálculo Paso a Paso:**

```
PASO 1: Asignar valores base
B = 3,000,000 (salario_base - prioridad 1)
C = 0 (3,000,000 > 2×1,300,000, no aplica aux)
D = 0
E = 3,000,000

PASO 2: Calcular componentes
Base % = E - D = 3,000,000

F = 3,000,000 × 0.0833 = 249,900
G = 249,900 × 0.01 = 2,499
H = 3,000,000 × 0.0833 = 249,900
I = 3,000,000 × 0.0417 = 125,100
J = 3,000,000 × 0.024 = 72,000
K = 3,000,000 × 0.085 = 255,000
L = 3,000,000 × 0.12 = 360,000
M = 3,000,000 × 0.03 = 90,000

N,O,P,Q = 0

PASO 3: Costo período
R = 3,000,000 + 249,900 + 2,499 + 249,900 + 125,100 + 72,000 + 255,000 + 360,000 + 90,000
  = 4,404,399 (mes)

S = 4,404,399 / 26 = 169,400.73 (día)
T = 169,400.73 / 8 = 21,175.09 (hora sin utilidad)

PASO 4: Aplicar utilidad (31.5%)
Denominador = 1 - 0.315 = 0.685

Precio_Hora_Ordinaria = 21,175.09 / 0.685 = 30,910.63

PASO 5: Factores adicionales
Recargo Nocturno = 30,910.63 × (1 + 0.35) = 41,729.35
Extra Diurna = 30,910.63 × 1.25 = 38,638.29
Extra Nocturna = 30,910.63 × 1.75 = 54,093.61
Dominical = 30,910.63 × 1.75 = 54,093.61
Extra Dom Diurna = 30,910.63 × 2.0 = 61,821.26
Extra Dom Nocturna = 30,910.63 × 2.5 = 77,276.58

Valor Día = 30,910.63 × 8 = 247,285.04

RESULTADO:
Tabla de Precios para "Técnico Senior":
├── Hora Ordinaria: $30,910.63
├── Recargo Nocturno: $41,729.35
├── Extra Diurna: $38,638.29
├── Extra Nocturna: $54,093.61
├── Dominical: $54,093.61
├── Extra Dom Diurna: $61,821.26
├── Extra Dom Nocturna: $77,276.58
└── Valor Día Ordinario: $247,285.04
```

### Ejemplo 2: Usar el Precio en Cotización

**Escenario:** Cotizar 40 horas ordinarias del Técnico Senior

```php
// En CotizacionProductoController@store
$request->validated() = [
    'cotizacion_id' => 15,
    'cargo_id' => 5,              // Técnico Senior
    'tipo_costo' => 'hora_ordinaria',
    'cantidad' => 40,
    'descuento_porcentaje' => 5,  // 5% de descuento
];

// CotizacionProductoService::agregarProducto()
$tablaPrecio = DB::table('cargos_tabla_precios')
    ->where('cargo_id', 5)
    ->first();
// Retorna: {'hora_ordinaria': 30910.63, ...}

$datos['valor_unitario'] = 30910.63;
$datos['cantidad'] = 40;
$datos['descuento_porcentaje'] = 5;

// Guardar y CotizacionProducto::boot() calcula:
$subtotal = 40 × 30,910.63 = 1,236,425.20
$descuento = (1,236,425.20 × 0.05) = 61,821.26
$valor_total = 1,236,425.20 - 61,821.26 = 1,174,603.94

// Resultado guardado en BD:
CotizacionProducto {
    cantidad: 40,
    valor_unitario: 30910.63,
    descuento_porcentaje: 5,
    descuento_valor: 0,
    valor_total: 1174603.94,
    bono: null
}
```

---

## 5. Testing y Validación

### 5.1 Cómo Verificar que los Precios son Correctos

**Endpoint para verificar:**
```
GET /admin/contratos/tabla-precios-cargos
```

**Tabla de referencia en BD:**
```sql
SELECT cargo_id,
       CONCAT(c.nombre) as cargo,
       hora_ordinaria,
       hora_extra_diurna,
       valor_dia_ordinario,
       updated_at
FROM cargos_tabla_precios
JOIN cargos c ON c.id = cargo_id
ORDER BY c.nombre;
```

### 5.2 Debug de un Cargo Específico

**Pasos:**

1. **Verificar salario_base del cargo:**
```sql
SELECT id, nombre, salario_base, arl_nivel
FROM cargos
WHERE nombre LIKE '%Técnico Senior%';
```

2. **Verificar parametrización:**
```sql
SELECT p.*, n.nombre as novedad, nd.nombre as detalle
FROM parametrizacion p
JOIN novedades_detalle nd ON nd.id = p.novedad_detalle_id
JOIN novedades n ON n.id = nd.novedad_id
WHERE p.cargo_id = 5 AND n.nombre LIKE '%NOMINA%'
ORDER BY n.nombre, nd.nombre;
```

3. **Verificar tabla de precios generada:**
```sql
SELECT * FROM cargos_tabla_precios WHERE cargo_id = 5;
```

4. **Comparar con cálculo manual:**
   - Ver sección 4 de este documento (Ejemplos Prácticos)
   - Verificar con calculadora o Excel

---

## 6. Casos Edge y Cuidados

### 6.1 Cargo sin salario_base

**Comportamiento:**
- Usa valor de parametrización "DATOS BÁSICOS - BASICO"
- Si tampoco existe, usa $0
- **Resultado:** Precios en $0 o inconsistentes

**Solución:**
```
Siempre establecer salario_base en cada cargo
```

### 6.2 Aux. Transporte Condicional

**Regla:**
- Solo aplica si: `salario_base ≤ 2 × SMLV`

**Ejemplos:**
```
Si SMLV = 1,300,000:

Cargo A: salario = 2,000,000
  ≤ 2 × 1,300,000 (2,600,000) ✓ → Aplica aux_trans

Cargo B: salario = 3,000,000
  > 2 × 1,300,000 (2,600,000) ✗ → NO aplica aux_trans
```

### 6.3 ARL Siempre Sobrescribe

**Importante:**
- El `arl_nivel` del cargo SIEMPRE sobrescribe el % parametrizado
- Cambiar el nivel significa regenerar tabla de precios

**Código:**
```php
if (isset($arlNiveles[$cargo->arl_nivel])) {
    $d['pct_arp'] = (float)$arlNiveles[$cargo->arl_nivel] / 100;
    // Esto sobrescribe lo que vino de parametrización
}
```

### 6.4 Manejo de Decimales

**El servicio usa `round(..., 2)` en múltiples puntos:**

```php
// Costo hora base (4 decimales para precisión)
$T = round($S / $horasDiarias, 4);

// Precios finales (2 decimales)
$row[$k] = round(($T * $f) / $den, 2);
```

**Cuidado:** Pequeñas variaciones pueden acumularse en cálculos

---

## 7. Fuentes y Referencias

**Archivos Clave:**
- `app/Services/TablaPreciosCargoService.php` - Lógica principal
- `app/Models/CotizacionProducto.php` - Cálculo automático
- `app/Services/CotizacionProductoService.php` - Integración
- `resources/views/cotizar/cotizaciones/documento.blade.php` - UI
- `public/assets/js/contratos/parametrizacion/tablaPreciosCargo.js` - Frontend

**Tablas de BD Importantes:**
```
✓ cargos                    - Master de cargos
✓ cargos_tabla_precios      - Precios calculados
✓ parametrizacion           - Parametrización de costos
✓ novedades                 - Conceptos de nómina
✓ novedades_detalle         - Detalles de novedades
✓ nom_parametros_globales   - Config global
✓ nom_arl_niveles           - Niveles de ARL
✓ ord_cotizacion_productos  - Productos en cotización
✓ ord_cotizacion            - Header de cotización
```

---

**Última actualización:** Marzo 30, 2026
**Versión:** 1.0
