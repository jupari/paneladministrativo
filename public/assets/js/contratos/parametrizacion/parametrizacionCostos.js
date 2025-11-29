// ======================= Datos inyectados desde Laravel =======================
// Asegúrate de pasar estas variables desde tu controlador:
// $categorias: [{id, nombre, ...}]
// $unidades:   [{sigla, nombre}]  (NO uses 'sigla as id')
// $itemsPropios: [{codigo, nombre, categoria_id, unidad_medida}]
// $parametrizacioncostos: dataset inicial de la tabla (opcional)
// =========================== Config de Endpoints =============================
const ITEMS_STORE_URL = 'admin.items-propios' // crea ítem
const SAVE_COSTOS_URL = 'admin.parametrizacion.storecostos' // guarda {tablaCostos:[]}

// =============================== Helpers ====================================
const CSRF = document.querySelector('meta[name="csrf-token"]').content

const upperEs = v => {
    if (v == null) return v
    return String(v).normalize('NFC').trim().toUpperCase()
}

const unidadesArr = Object.entries(unidades).map(([sigla, nombre]) => ({
    sigla,
    nombre
}))

// ============================ Catálogos JS ===================================
const categoriasMap = (Array.isArray(categorias) ? categorias : [])
  .filter(c => Number(c.costos) > 0)
  .reduce((acc, c) => {
    acc[c.id] = c.nombre;
    return acc;
  }, {});


// Para editor list en unidad: usamos SIGLA como value y mostramos “SIGLA - Nombre”
const unidadOptions = unidadesArr.map(({ sigla, nombre }) => ({
    label: `${sigla} - ${nombre}`,
    value: sigla
}))
const unidadesMap = unidadesArr.reduce(
    (a, { sigla, nombre }) => ((a[sigla] = nombre), a),
    {}
)

// Items por código (incluye categoria_id para filtrar)
const itemOptions = Object.fromEntries(
    itemsPropios.map(i => [
        i.codigo,
        {
            nombre: i.nombre,
            codigo: i.codigo,
            unidad_medida: i.unidad_medida, // aquí guardas la SIGLA o nombre según tu modelo
            categoria_id: i.categoria_id
        }
    ])
)

//Índice para filtrar editor por categoría
const itemsByCat = itemsPropios.reduce((acc, i) => {
    ;(acc[i.categoria_id] ??= []).push(i.codigo)
    return acc
}, {})


// ============================ Cálculo derivado ===============================
let valorX = cantHorasDiarias // divisor (ej. 8 horas por día)

function setValorX (nuevo) {
    valorX = toNumber(nuevo) || 0
    // Forzamos re-evaluación de mutators sin loops:
    if (tablaCostos) tablaCostos.replaceData(tablaCostos.getData())
}

// ============================ Columnas Tabulator =============================
let _updatingItemCell = false // bandera anti-reentrada

const columnasCategoriasCostos = [
    {
        title: 'Categoría',
        field: 'categoria_id',
        editor: 'list',
        editorParams: {
            values: categoriasMap,
            autocomplete: true,
            listOnEmpty: true
        },
        formatter: cell => categoriasMap[cell.getValue()] || '',
        cellEdited: cell => {
            cell.getRow().update({
                item: '',
                item_nombre: '',
                unidad_medida: ''
            })
        }
    }
]

const columnasMaquinaria = [
    {
        title: 'Item Propio',
        field: 'item', // guarda CÓDIGO
        editor: 'list',
        editorParams: cell => {
            const catId = cell.getRow().getData().categoria_id
            const opciones = itemsByCat[catId] ?? []
            return { values: opciones, autocomplete: true, listOnEmpty: true }
        },
        // Mostrar nombre si existe catálogo; si no, el código
        formatter: cell =>
            itemOptions[cell.getValue()]?.codigo || cell.getValue(),

        cellEdited: async function (cell) {
            if (_updatingItemCell) return
            const row = cell.getRow()
            const value = cell.getValue() // código elegido/ingresado
            const catId = row.getData().categoria_id
            const item = itemOptions[value]

            // Si existe en el catálogo: sincroniza dependientes y termina
            if (item.nombre != 'Ingresar Nuevo') {
                _updatingItemCell = true
                try {
                    row.update({
                        item_nombre: item.nombre,
                        unidad_medida: item.unidad_medida || ''
                    })
                } finally {
                    _updatingItemCell = false
                }
                return
            }

            // Si no existe, validar categoría y ofrecer crear
            if (!catId) {
                Swal.fire(
                    'Atención',
                    'Seleccione una categoría antes de crear el ítem.',
                    'warning'
                )
                _updatingItemCell = true
                try {
                    row.update({ item: '' })
                } finally {
                    _updatingItemCell = false
                }
                return
            }

            const result = await Swal.fire({
                title: 'Crear nuevo Ítem.',
                html:
                    `<input id="new_codigo" class="swal2-input" placeholder="Código" value="${
                        value ?? ''
                    }" style="text-transform:uppercase;">` +
                    `<input id="new_nombre" class="swal2-input" placeholder="Nombre" style="text-transform:uppercase;">` +
                    `<select id="unidad_medida" class="swal2-select">
            ${unidadesArr
                .map(
                    u =>
                        `<option value="${u.sigla}">${u.sigla} - ${u.nombre}</option>`
                )
                .join('')}
          </select>`,
                showCancelButton: true,
                confirmButtonText: 'Crear',
                preConfirm: () => ({
                    codigo: upperEs(
                        document.getElementById('new_codigo').value
                    ),
                    nombre: upperEs(
                        document.getElementById('new_nombre').value
                    ),
                    unidad_medida:
                        document.getElementById('unidad_medida').value, // SIGLA
                    categoria_id: Number(catId)
                })
            })

            if (!result.value) {
                _updatingItemCell = true
                try {
                    row.update({ item: '' })
                } finally {
                    _updatingItemCell = false
                }
                return
            }

            const nuevo = result.value

            // Guardar en backend
            try {
                const response = await fetch(ITEMS_STORE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        codigo: nuevo.codigo,
                        nombre: nuevo.nombre,
                        categoria_id: nuevo.categoria_id,
                        unidad_medida: nuevo.unidad_medida // SIGLA
                    })
                })

                if (!response.ok) throw new Error('No se pudo guardar el ítem')

                const saved = await response.json()

                // Actualiza catálogos en memoria
                itemOptions[saved.codigo] = {
                    nombre: saved.nombre,
                    unidad_medida: saved.unidad_medida, // SIGLA
                    categoria_id: saved.categoria_id
                }
                ;(itemsByCat[saved.categoria_id] ??= []).push(saved.codigo)

                // Actualiza la fila (sin cell.setValue para evitar recursión)
                _updatingItemCell = true
                try {
                    row.update({
                        item: saved.codigo,
                        item_nombre: saved.nombre,
                        unidad_medida: saved.unidad_medida
                    })
                } finally {
                    _updatingItemCell = false
                }

                Swal.fire(
                    'Item creado',
                    'El nuevo ítem fue guardado exitosamente.',
                    'success'
                )
            } catch (err) {
                console.error(err)
                Swal.fire('Error', 'No se pudo guardar el ítem.', 'error')
                _updatingItemCell = true
                try {
                    row.update({ item: '' })
                } finally {
                    _updatingItemCell = false
                }
            }
        }
    },

    // Nombre editable en mayúsculas
    {
        title: 'Nombre',
        field: 'item_nombre',
        editor: 'input',
        editorParams: {
            elementAttributes: { style: 'text-transform:uppercase;' }
        },
        mutatorEdit: value => upperEs(value)
    },

    // Unidad por SIGLA (value = sigla)
    {
        title: 'Unidad',
        field: 'unidad_medida', // guarda SIGLA
        editor: 'list',
        editorParams: {
            values: unidadOptions,
            autocomplete: true,
            listOnEmpty: true
        },
        formatter: cell => {
            const sigla = cell.getValue()
            return sigla ? sigla : ''
        }
    },

    // COSTO DÍA (origen)
    {
        title: 'Costo Día',
        field: 'costo_dia',
        hozAlign: 'right',
        headerHozAlign: 'right',
        editor: 'input',
        editorParams: {
            elementAttributes: {
                inputmode: 'decimal',
                pattern: '[0-9.]*',
                style: 'text-align:right;'
            }
        },
        validator: ['numeric'],
        // mutatorEdit: (value,data) => {
        // //   const n = toNumber(value);
        // //   return Number.isFinite(n) ? +(n.toFixed(2)) : null;
        //     const costoDia = toNumber(value);
        //     const row = value.getRow();
        //     if (costoDia === null || !valorX) {
        //         row.update({ costo_hora: null });
        //         return;
        //     }
        //     const costoHora = +(costoDia / valorX).toFixed(2);
        //     row.update({ costo_hora: costoHora });
        // },
        cellEdited: cell => {
            const costoDia = toNumber(cell.getValue())
            const row = cell.getRow()
            if (costoDia === null || !valorX) {
                row.update({ costo_hora: null })
                return
            }
            const costoHora = +(costoDia / valorX).toFixed(2)
            row.update({ costo_hora: costoHora })
        },
        formatter: cell => {
            const v = toNumber(cell.getValue())
            return Number.isFinite(v) ? fmtMiles.format(v) : ''
        }
    },

    // COSTO HORA (derivado por mutator, sin row.update)
    {
        title: `Costo Hora (auto)`,
        field: 'costo_hora',
        hozAlign: 'right',
        headerHozAlign: 'right',
        editor: false,
        mutator: (value, data) => {
            const d = toNumber(data.costo_dia)
            const x = toNumber(valorX)
            if (!Number.isFinite(d) || !Number.isFinite(x) || x <= 0)
                return null
            return +(d / x).toFixed(2)
        },
        formatter: cell => {
            const v = toNumber(cell.getValue())
            return Number.isFinite(v) ? fmtMiles.format(v) : ''
        }
    }
]

// Acciones de fila (opcional)
const columnaAccionesCostos = {
    title: 'Acciones',
    field: 'acciones',
    headerSort: false,
    width: 140,
    formatter: () =>
        `<div class="d-flex gap-1">
       <button class="btn btn-sm btn-success btn-guardar-fila-costo"><i class="fas fa-save"></i></button>
       <button class="btn btn-sm btn-danger btn-eliminar-fila-costo"><i class="fas fa-trash"></i></button>
     </div>`,
    hozAlign: 'center',
    width: 80,
    cellClick: async (e, cell) => {
        const row = cell.getRow()
        const el = e.target

        if (!el) return

        if (el.closest('.btn-guardar-fila-costo')) {
            await saveDataCostos(row)
        } else if (el.closest('.btn-eliminar-fila-costo')) {
            row.delete()
        }
    }
}

//============================Helper==========================
// helpers de conteo
const getInitialCountCostos = () =>
    Array.isArray(initialData) ? initialData.length : 0
const getGridCountCostos = () =>
    tablaCostos ? tablaCostos.getData().length : 0
// =========================== Inicialización tabla ============================
let tablaCostos

async function CargarCostos (primeraCargac) {
    if (!primeraCargac) {
        const base = getInitialCountCostos()
        const grid = getGridCountCostos()

        if (base !== grid) {
            const diff = grid - base
            const detalle =
                diff > 0
                    ? `Tienes ${diff} fila(s) nuevas sin guardar.`
                    : `Se eliminaron ${Math.abs(diff)} fila(s) sin guardar.`

            const res = await Swal.fire({
                title: 'Cambios sin guardar',
                html: `${detalle}<br>¿Quieres recargar y perder los cambios?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, recargar',
                cancelButtonText: 'No, cancelar',
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,
                allowEscapeKey: true
            })

            if (res.dismiss === 'cancel' || res.dismiss) return

            if (res.value !== true) return // por si acaso
        }
    }

    if (tablaCostos) tablaCostos.destroy()

    const data = Array.isArray(initialData) ? withRowId(initialData) : []

    tablaCostos = new Tabulator('#tabla-parametrizacion-costos', {
        index: '__rid', // ← clave de fila
        height: '520px',
        data, // ← ya normalizado con __rid
        layout: 'fitColumns',
        columns: [
            ...columnasCategoriasCostos,
            ...columnasMaquinaria,
            columnaAccionesCostos
        ],
        placeholder: 'No hay ninguna parametrización...'
    })
}

// ============================== Guardado =====================================
async function guardarFila (row) {
    // Si usas guardado por fila en otro endpoint, implementa aquí.
    // Este ejemplo guarda todo junto con el botón "Guardar todo".
    const d = row.getData()
}

/**
 * Guarda costos: toda la grilla o una sola fila.
 * @param {RowComponent|Object|null} row - Si viene un RowComponent (Tabulator) o un objeto de fila, guarda solo esa fila; si es null, guarda toda la grilla.
 * @returns {Promise<boolean>} true si guardó, false si abortó/validación falla.
 */
async function saveDataCostos(row = null) {
  // 1) Determinar el alcance
  const isSingle = !!row;
  // 2) Normalizar filas a un array de objetos
  const rows = (() => {
    if (!isSingle) {
        return tablaCostos.getData();

    }
    // row puede ser RowComponent o un objeto literal de datos
    if (row && typeof row.getData === 'function') return [row.getData()];
    return [row]; // ya es objeto de datos
  })();

  // 3) Limpiar campos efímeros (no enviar __rid ni columnas de acción)
  const payload = rows.map(({ __rid, acciones, ...r }) => r);


  // 4) Validación básica
  const invalida = payload.find(r => !r?.categoria_id || !r?.item);
  if (invalida) {
    await Swal.fire('Atención', 'Hay filas sin categoría o ítem.', 'warning');
    return false;
  }

  // 5) Enviar al backend (siempre como array)
  try {
    const res = await fetch(SAVE_COSTOS_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({ tablaCostos: payload }),
    });

    if (!res.ok) {
      const errText = await res.text().catch(() => '');
      throw new Error(errText || 'No se pudo guardar');
    }

    // (Opcional) usar la respuesta si necesitas actualizar la fila
    // const dataResp = await res.json().catch(() => ({}));

    initialData = tablaCostos.getData(); // actualizar variable global al guardar

    await Swal.fire(
      'OK',
      isSingle ? 'Fila guardada correctamente' : 'Guardado correctamente',
      'success'
    );

    return true;
  } catch (err) {
    console.error(err);
    await Swal.fire('Error', err.message || 'No se pudo guardar', 'error');
    return false;
  }
}


async function recargarDatos (nuevos) {
    tablaCostos.replaceData(withRowId(nuevos || []))
}

//detecta duplicados
tablaCostos?.on('dataChanged', rows => {
    const ids = new Set(),
        dup = []
    ;(rows || []).forEach(r => {
        if (ids.has(r.__rid)) dup.push(r.__rid)
        ids.add(r.__rid)
    })
    if (dup.length) console.warn('Duplicados __rid:', dup)
})

// =============================== UI Events ===================================
const genRid = () =>
    'r' + Math.random().toString(36).slice(2) + Date.now().toString(36)
// crea un id local estable para cada fila
const withRowId = (arr = []) =>
    arr.map((r, i) => ({
        __rid:
            r.__rid ??
            r.id ??
            `${r.item}-${r.unidad_medida}-${i}-${Date.now()}`, // algo único y estable
        ...r
    }))

document.addEventListener('DOMContentLoaded', async () => {
    // Toast
    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: false,
        progressBar: false,
        positionClass: 'toast-bottom-right',
        preventDuplicates: false,
        onclick: null,
        showDuration: '300',
        hideDuration: '1000',
        timeOut: '5000',
        extendedTimeOut: '1000',
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    }

    //Logica para el select de novedades
    novedadesCombo.forEach(item => {
        opcionesNovedad[item.id] = item.nombre
    })

    document.getElementById('btn-nuevo').addEventListener('click', () => {
        tablaCostos.addRow(
            {
                __rid: genRid(), // id local único
                categoria_id: categorias[0]?.id ?? null,
                item: '',
                item_nombre: '',
                unidad_medida: unidades[0]?.sigla ?? '',
                costo_dia: null,
                costo_hora: null,
                active: 1
            },
            false
        )
    })

    //carga de la datatable
    await CargarNovedades(primeraCarga)
    await CargarCostos(primeraCarga)
})
