const opcionesNovedad = {}
let tablaNovedades = null
let primeraCarga = firstTime

const SAVE_NOVEDADES_URL ='/admin/admin.parametrizacion.storenovedades' // guarda {parametrizacion:[]}

const categoriasNovedades = Array.isArray(categorias)
    ? categorias.filter(c => c.costos === 0)
    : []
// const categoriasCostos = Array.isArray(categorias)
//     ? categorias.filter(c => c.costos === 1)
//     : [];

const columnasCategoriasNovedades = [
    {
        title: 'Categoría',
        field: 'categoria_id',
        editor: 'list',
        editorParams: {
            values: categoriasNovedades.reduce((acc, c) => {
                acc[c.id] = c.nombre // Tabulator mostrará el nombre pero guardará el id
                return acc
            }, {}),
            autocomplete: true,
            listOnEmpty: true
        },
        formatter: cell => {
            const id = cell.getValue()
            const cat = categoriasNovedades.find(c => c.id == id)
            return cat ? cat.nombre : ''
        },
        cellEdited: onCategoriaChangeNovedades // <- Detectar cambio
    }
]

const columnasGenerales = [
    {
        title: 'Cargo',
        field: 'cargo_id',
        editor: 'list',
        editorParams: { values: cargos },
        formatter: cell => cargos[cell.getValue()] || ''
    },
    {
        title: 'Novedad',
        field: 'novedad_detalle_id',
        editor: 'list',
        editorParams: { values: opcionesNovedad },
        formatter: cell => opcionesNovedad[cell.getValue()] || ''
    }
]

const columnasFinanzas = [
    {
        title: 'Valor %/$',
        field: 'valor_porcentaje',
        editor: 'input',
        hozAlign: 'right'
    },
    {
        title: 'Valor Admon',
        field: 'valor_admon',
        hozAlign: 'right',
        editor: 'input',
        editorParams: {
            elementAttributes: {
                inputmode: 'decimal',
                pattern: '[0-9.]*'
            }
        },
        validator: ['numeric'],
        formatter: cell => {
            const v = toNumber(cell.getValue())
            return Number.isFinite(v) ? fmtMiles.format(v) : ''
        }
    },
    {
        title: 'Valor Obra',
        field: 'valor_obra',
        hozAlign: 'right',
        editor: 'input',
        editorParams: {
            elementAttributes: {
                inputmode: 'decimal',
                pattern: '[0-9.]*'
            }
        },
        validator: ['numeric'],
        formatter: cell => {
            const v = toNumber(cell.getValue())
            return Number.isFinite(v) ? fmtMiles.format(v) : ''
        }
    }
]

// Formateador reutilizable (miles con punto y decimales con coma)
const fmtMiles = new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})

const toNumber = v => {
    if (v === null || v === undefined || v === '') return NaN
    if (typeof v === 'number') return v
    const n = Number(String(v).replace(',', '.'))
    return Number.isFinite(n) ? n : NaN
}

const unidadesMedida = Array.isArray(unidades)
    ? unidades.map(u => ({ id: u.id, nombre: u.nombre }))
    : []

const unidadesNombres = unidadesMedida.map(u => u.nombre)

const getUnidadNombreById = id =>
    unidadesMedida.find(u => u.id == id)?.nombre || ''

const columnaAcciones = {
    title: 'Acciones',
    formatter: () => `
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-success btn-guardar-fila-novedad"><i class="fas fa-save"></i></button>
            <button class="btn btn-sm btn-danger btn-eliminar-fila-novedad"><i class="fas fa-trash"></i></button>
        </div>
    `,
    hozAlign: 'center',
    width: 100,
    cellClick: async function (e, cell) {
        const row = cell.getRow()
        const el = e.target

        if (!el) return

        if (el.closest('.btn-guardar-fila-novedad')) {
            await saveDataNovedades(row)
        } else if (el.closest('.btn-eliminar-fila-novedad')) {
            row.delete()
        }
    }
}

// Si quieres poder cambiar X y recalcular todas las filas:
function setValorX (nuevoX, table) {
    valorX = toNumber(nuevoX) || 0
    const data = table.getData()
    const recalculado = data.map(r => {
        const d = toNumber(r.costo_dia)
        return {
            id: r.id, // o la PK de tu fila
            costo_hora:
                Number.isFinite(d) && valorX ? +(d / valorX).toFixed(2) : null
        }
    })
    table.updateData(recalculado)
}

tablaNovedades?.on('dataChanged', rows => {
    const ids = new Set(),
        dup = []
    ;(rows || []).forEach(r => {
        if (ids.has(r.__rid)) dup.push(r.__rid)
        ids.add(r.__rid)
    })
    if (dup.length) console.warn('Duplicados __rid:', dup)
})

//crea un id local único
const genRidN = () =>
    'r' + Math.random().toString(36).slice(2) + Date.now().toString(36)
// crea un id local estable para cada fila
const withRowIdN = (arr = []) =>
    arr.map((r, i) => ({
        __rid:
            r.__rid ??
            r.id ??
            `${r.item}-${r.unidad_medida}-${i}-${Date.now()}`, // algo único y estable
        ...r
    }))

// helpers de conteo
const getInitialCount = () =>
    Array.isArray(parametrizacion) ? parametrizacion.length : 0
const getGridCount = () =>
    tablaNovedades ? tablaNovedades.getData().length : 0

async function CargarNovedades (primeraCargan) {
    if (!primeraCargan) {
        const base = getInitialCount()
        const grid = getGridCount()

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

    if (window.tablaNovedades) tablaNovedades.destroy()

    const data = Array.isArray(parametrizacion)
        ? withRowIdN(parametrizacion)
        : []

    tablaNovedades = new Tabulator('#tabla-parametrizacion', {
        index: '__rid',
        height: '400px',
        data,
        layout: 'fitColumns',
        columns: [
            ...columnasCategoriasNovedades,
            ...columnasGenerales,
            ...columnasFinanzas,
            columnaAcciones
        ],
        placeholder: 'No hay ninguna parametrización...'
    })
}

function onCategoriaChangeNovedades (cell) {
    const categoriaId = cell.getValue()
    let nuevasColumnasCategoriasNovedades = [...columnasCategoriasNovedades]
    nuevasColumnasCategoriasNovedades.push(
        ...columnasGenerales,
        ...columnasFinanzas
    )
    nuevasColumnasCategoriasNovedades.push(columnaAcciones)
    tablaNovedades.setColumns(nuevasColumnasCategoriasNovedades)
}

function agregarFilaNovedades () {
    tablaNovedades.addRow(
        {
            __rid: genRidN(), // id local único
            categoria_id: null,
            cargo_id: null,
            novedad_detalle_id: null,
            valor_porcentaje: '',
            valor_admon: '',
            valor_obra: ''
        },
        false
    ) // true = insertar al inicio
}

async function saveDataNovedades (row = null) {
    const isSingle = !!row

    const rows = (() => {
        if (!isSingle) {
            return tablaNovedades.getData()
        }
        // row puede ser RowComponent o un objeto literal de datos
        if (row && typeof row.getData === 'function') return [row.getData()]
        return [row] // ya es objeto de datos
    })()


    // 3) Limpiar campos efímeros (no enviar __rid ni columnas de acción)
    const payload = rows.map(({ __rid, acciones, ...r }) => r)

    // 4) Validación básica
    const invalida = payload.find(r => !r?.categoria_id)
    if (invalida) {
        await Swal.fire(
            'Atención',
            'Hay filas sin categoría o ítem.',
            'warning'
        )
        return false
    }

    // 5) Enviar al backend (siempre como array)
    try {
        const res = await fetch(SAVE_NOVEDADES_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ parametrizacion: payload })
        })

        if (!res.ok) {
            const errText = await res.text().catch(() => '')
            throw new Error(errText || 'No se pudo guardar')
        }

        // (Opcional) usar la respuesta si necesitas actualizar la fila
        // const dataResp = await res.json().catch(() => ({}));
        //actulizamos la variable parametrizacion
        parametrizacion = tablaNovedades.getData();

        await Swal.fire(
            'OK',
            isSingle ? 'Fila guardada correctamente' : 'Guardado correctamente',
            'success'
        )

        return true
    } catch (err) {
        console.error(err)
        await Swal.fire('Error', err.message || 'No se pudo guardar', 'error')
        return false
    }
}

function abrirModalCrearItemPropio (cell) {
    // Mostrar modal Bootstrap o prompt
    Swal.fire({
        title: 'Crear nuevo item propio',
        html:
            `<input id="nuevo_nombre" class="swal2-input" placeholder="Nombre">` +
            `<input id="nuevo_codigo" class="swal2-input" placeholder="Código">` +
            `<select id="unidad_medida" class="swal2-select">${Object.entries(
                unidades
            )
                .map(
                    ([id, nombre]) => `<option value="${id}">${nombre}</option>`
                )
                .join('')}</select>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return {
                nombre: document.getElementById('nuevo_nombre').value,
                codigo: document.getElementById('nuevo_codigo').value,
                unidadmedida_id: document.getElementById('unidad_medida').value
            }
        }
    }).then(result => {
        if (result.value) {
            $.ajax({
                url: '/admin/admin.itempropio.store',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                    'X-HTTP-Method-Override': 'POST'
                },
                method: 'POST',
                data: result.value,
                success: function (res) {
                    itemsPropios[res.id] = res.nombre

                    // Reasignar nuevo ID al cell
                    cell.setValue(res.id)
                    toastr.success('Item propio creado exitosamente')
                },
                error: function () {
                    toastr.error('No se pudo crear el item propio')
                }
            })
        } else {
            cell.setValue(null) // Canceló creación
        }
    })
}

//costos hora cuando se consulta lo guardado
function recomputeCostoHora (row) {
    const data = row.getData()
    const d = toNumber(data.costo_dia)
    const x = valorX // si luego depende de unidad_medida, cámbialo aquí
    const h = Number.isFinite(d) && x ? +(d / x).toFixed(2) : null
    row.update({ costo_hora: h })
}

// y si cambias X:
function setValorX (nuevoX) {
    valorX = parseFloat(nuevoX) || 0
    table.getRows().forEach(recomputeCostoHora)
}
