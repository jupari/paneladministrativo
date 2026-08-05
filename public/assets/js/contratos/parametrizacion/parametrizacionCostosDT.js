// ======================= Parametrización de Costos (DataTable) =======================
// Reemplaza la versión anterior basada en Tabulator (parametrizacionCostos.js),
// siguiendo el mismo patrón usado para Novedades (parametrizacionDT.js):
// helpers renderSelect/renderInput (definidos en parametrizacionDT.js, cargado antes)
// y edición inline por celda mediante la clase .dt-editable.
//
// Variables globales esperadas (inyectadas desde el blade):
// categorias: [{id, nombre, costos}], unidades: {sigla: nombre},
// itemsPropios: [{codigo, nombre, categoria_id, unidad_medida}],
// initialData: dataset inicial de la tabla, cantHorasDiarias: divisor costo_dia/costo_hora

// =========================== Config de Endpoints =============================
const ITEMS_STORE_URL_DT = '/admin/admin.items-propios';
const SAVE_COSTOS_URL_DT = '/admin/admin.parametrizacion.storecostos';
const DELETE_COSTO_URL_DT = '/admin/admin.parametrizacion.deletecosto';
const NUEVO_ITEM_VALUE = '__nuevo__';

const CSRF_COSTOS = document.querySelector('meta[name="csrf-token"]')?.content || '';

const upperEsCostos = v => (v == null ? v : String(v).normalize('NFC').trim().toUpperCase());

// ============================ Catálogos JS ===================================
const categoriasCostosArr = (Array.isArray(categorias) ? categorias : [])
    .filter(c => Number(c.costos) > 0)
    .map(c => ({ id: c.id, nombre: c.nombre }));

const esCategoriaConCostoUnitarioDT = (categoriaId) => {
    if (!categoriaId) return false;
    const categoria = categorias.find(c => c.id == categoriaId);
    if (!categoria) return false;
    const nombre = categoria.nombre.toLowerCase();
    return nombre.includes('tarifa') || nombre.includes('otros');
};

const unidadesArrDT = Object.entries(unidades).map(([sigla, nombre]) => ({ sigla, nombre }));
const unidadOptionsDT = unidadesArrDT.map(({ sigla, nombre }) => ({ id: sigla, nombre: `${sigla} - ${nombre}` }));

let itemOptionsDT = Object.fromEntries(
    (itemsPropios || []).map(i => [i.codigo, {
        nombre: i.nombre,
        codigo: i.codigo,
        unidad_medida: i.unidad_medida,
        categoria_id: i.categoria_id
    }])
);

let itemsByCatDT = (itemsPropios || []).reduce((acc, i) => {
    (acc[i.categoria_id] ??= []).push(i.codigo);
    return acc;
}, {});

let valorXCostosDT = cantHorasDiarias;

// ============================ Render de celdas ================================
function renderItemSelectDT(rowData, selected) {
    const codigos = itemsByCatDT[rowData.categoria_id] || [];
    const opciones = codigos.map(cod => ({
        id: cod,
        nombre: `${cod} - ${itemOptionsDT[cod]?.nombre || ''}`
    }));
    // Datos históricos: el código de la fila puede no estar en el catálogo de
    // items_propios (se cargó libremente antes de que existiera ese catálogo).
    // Si no está entre las opciones, se agrega igual para no perder el valor.
    if (selected && !codigos.includes(selected)) {
        opciones.unshift({
            id: selected,
            nombre: itemOptionsDT[selected] ? `${selected} - ${itemOptionsDT[selected].nombre}` : selected
        });
    }
    opciones.push({ id: NUEVO_ITEM_VALUE, nombre: '➕ Crear nuevo ítem…' });
    return renderSelect(opciones, selected, 'item');
}

// ==================== Visibilidad dinámica de Costo Unitario ==================
function actualizarVisibilidadCostoUnitarioDT() {
    if (!window.tablaCostosDT) return;
    const datos = window.tablaCostosDT.rows().data().toArray();
    const necesita = datos.some(fila => esCategoriaConCostoUnitarioDT(fila.categoria_id));
    window.tablaCostosDT.column('costo_unitario:name').visible(necesita);
}

// =========================== Lectura de una fila (para guardar) ===============
function leerFilaCostosDT(rowIdx) {
    const rowApi = window.tablaCostosDT.row(rowIdx);
    const rowData = rowApi.data();
    const $tr = $(rowApi.node());

    const idxCat = window.tablaCostosDT.column('categoria_id:name').index();
    const idxItem = window.tablaCostosDT.column('item:name').index();
    const idxNombre = window.tablaCostosDT.column('item_nombre:name').index();
    const idxUnidad = window.tablaCostosDT.column('unidad_medida:name').index();
    const idxDia = window.tablaCostosDT.column('costo_dia:name').index();
    const idxUnitario = window.tablaCostosDT.column('costo_unitario:name').index();

    const selVal = idx => $tr.find('td').eq(idx).find('select').val();
    const inpVal = idx => $tr.find('td').eq(idx).find('input').val();

    return {
        id: rowData.id ?? null,
        categoria_id: selVal(idxCat),
        item: selVal(idxItem),
        item_nombre: inpVal(idxNombre),
        unidad_medida: selVal(idxUnidad),
        costo_dia: inpVal(idxDia),
        costo_hora: rowData.costo_hora,
        costo_unitario: inpVal(idxUnitario),
        active: rowData.active ?? 1
    };
}

// ============================ Cambios dependientes =============================
function resetItemDependientesDT(row, dataIndex, newCatId) {
    const idxItem = window.tablaCostosDT.column('item:name').index();
    const idxNombre = window.tablaCostosDT.column('item_nombre:name').index();
    const idxUnidad = window.tablaCostosDT.column('unidad_medida:name').index();

    window.tablaCostosDT.cell(dataIndex, idxItem).data('');
    window.tablaCostosDT.cell(dataIndex, idxNombre).data('');
    window.tablaCostosDT.cell(dataIndex, idxUnidad).data('');

    const $tds = $(row).find('td');
    $tds.eq(idxItem).html(renderItemSelectDT({ categoria_id: newCatId }, ''));
    $tds.eq(idxNombre).find('input').val('');
    $tds.eq(idxUnidad).find('select').val('');

    actualizarVisibilidadCostoUnitarioDT();
}

function recomputeCostoHoraDT(row, dataIndex, costoDiaVal) {
    const idxHora = window.tablaCostosDT.column('costo_hora:name').index();
    const costoDia = toNumber(costoDiaVal);
    const costoHora = (Number.isFinite(costoDia) && valorXCostosDT) ? +(costoDia / valorXCostosDT).toFixed(2) : null;
    window.tablaCostosDT.cell(dataIndex, idxHora).data(costoHora);

    const txt = Number.isFinite(costoHora) ? fmtMiles.format(costoHora) : '';
    $(row).find('td').eq(idxHora).html(`<span class="costo-hora-auto">${txt}</span>`);
}

// Abre el flujo de "crear ítem nuevo" (Swal + POST a items-propios) y actualiza
// los catálogos en memoria. Devuelve el ítem creado, o null si se canceló/falló.
// Reutilizada tanto por la edición inline (columna Ítem) como por el modal de
// "Nuevo Registro" (costosModal.js).
async function crearNuevoItemPropioDT(categoriaId) {
    if (!categoriaId) {
        await Swal.fire('Atención', 'Seleccione una categoría antes de crear el ítem.', 'warning');
        return null;
    }

    const result = await Swal.fire({
        title: 'Crear nuevo Ítem.',
        html:
            '<input id="new_codigo" class="swal2-input" placeholder="Código" style="text-transform:uppercase;">' +
            '<input id="new_nombre" class="swal2-input" placeholder="Nombre" style="text-transform:uppercase;">' +
            `<select id="new_unidad" class="swal2-select">${unidadesArrDT.map(u => `<option value="${u.sigla}">${u.sigla} - ${u.nombre}</option>`).join('')}</select>`,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        preConfirm: () => {
            const codigoEl = document.getElementById('new_codigo');
            const nombreEl = document.getElementById('new_nombre');
            const unidadEl = document.getElementById('new_unidad');
            if (!codigoEl.value || !nombreEl.value) {
                Swal.showValidationMessage('Código y nombre son obligatorios');
                return false;
            }
            return {
                codigo: upperEsCostos(codigoEl.value),
                nombre: upperEsCostos(nombreEl.value),
                unidad_medida: unidadEl.value,
                categoria_id: Number(categoriaId)
            };
        }
    });

    if (!result.value) return null;

    try {
        const response = await fetch(ITEMS_STORE_URL_DT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_COSTOS },
            body: JSON.stringify(result.value)
        });
        if (!response.ok) throw new Error('No se pudo guardar el ítem');
        const saved = await response.json();

        itemOptionsDT[saved.codigo] = {
            nombre: saved.nombre,
            codigo: saved.codigo,
            unidad_medida: saved.unidad_medida,
            categoria_id: saved.categoria_id
        };
        (itemsByCatDT[saved.categoria_id] ??= []).push(saved.codigo);

        Swal.fire('Ítem creado', 'El nuevo ítem fue guardado exitosamente.', 'success');
        return saved;
    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'No se pudo guardar el ítem.', 'error');
        return null;
    }
}

async function handleItemChangeDT(row, dataIndex, colIdx, val) {
    const idxNombre = window.tablaCostosDT.column('item_nombre:name').index();
    const idxUnidad = window.tablaCostosDT.column('unidad_medida:name').index();
    const rowData = window.tablaCostosDT.row(dataIndex).data();
    const catId = rowData.categoria_id;
    const $tds = $(row).find('td');

    if (val === NUEVO_ITEM_VALUE) {
        const saved = await crearNuevoItemPropioDT(catId);
        if (!saved) {
            $tds.eq(colIdx).find('select').val('');
            window.tablaCostosDT.cell(dataIndex, colIdx).data('');
            return;
        }

        window.tablaCostosDT.cell(dataIndex, colIdx).data(saved.codigo);
        window.tablaCostosDT.cell(dataIndex, idxNombre).data(saved.nombre);
        window.tablaCostosDT.cell(dataIndex, idxUnidad).data(saved.unidad_medida);

        $tds.eq(colIdx).html(renderItemSelectDT({ categoria_id: catId }, saved.codigo));
        $tds.eq(idxNombre).find('input').val(saved.nombre);
        $tds.eq(idxUnidad).find('select').val(saved.unidad_medida);
        return;
    }

    // Ítem existente elegido: sincronizar nombre y unidad
    window.tablaCostosDT.cell(dataIndex, colIdx).data(val);
    const item = itemOptionsDT[val];
    if (item) {
        window.tablaCostosDT.cell(dataIndex, idxNombre).data(item.nombre);
        window.tablaCostosDT.cell(dataIndex, idxUnidad).data(item.unidad_medida);
        $tds.eq(idxNombre).find('input').val(item.nombre);
        $tds.eq(idxUnidad).find('select').val(item.unidad_medida);
    }
}

// =========================== Inicialización tabla ============================
window.tablaCostosDT = null;

const getInitialCountCostosDT = () => (Array.isArray(initialData) ? initialData.length : 0);
const getGridCountCostosDT = () => (window.tablaCostosDT ? window.tablaCostosDT.rows().data().length : 0);

async function CargarCostosDT(primeraCarga = false) {
    if (!primeraCarga) {
        const base = getInitialCountCostosDT();
        const grid = getGridCountCostosDT();

        if (base !== grid) {
            const diff = grid - base;
            const detalle = diff > 0
                ? `Tienes ${diff} fila(s) nuevas sin guardar.`
                : `Se eliminaron ${Math.abs(diff)} fila(s) sin guardar.`;

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
            });

            if (res.dismiss === 'cancel' || res.dismiss) return;
        }
    }

    if ($.fn.DataTable.isDataTable('#tabla-parametrizacion-costos-dt')) {
        window.tablaCostosDT.destroy();
        $('#tabla-parametrizacion-costos-dt tbody').empty();
    }

    const data = Array.isArray(initialData) ? initialData : [];

    window.tablaCostosDT = $('#tabla-parametrizacion-costos-dt').DataTable({
        data: data,
        columns: [
            {
                data: 'categoria_id', name: 'categoria_id',
                render: function (data, type) {
                    if (type === 'display') return renderSelect(categoriasCostosArr, data, 'categoria_id');
                    const cat = categoriasCostosArr.find(c => c.id == data);
                    return cat ? cat.nombre : '';
                }
            },
            {
                data: 'item', name: 'item',
                render: function (data, type, row) {
                    if (type === 'display') return renderItemSelectDT(row, data);
                    return itemOptionsDT[data]?.codigo || data || '';
                }
            },
            {
                data: 'item_nombre', name: 'item_nombre',
                render: function (data, type) {
                    return type === 'display' ? renderInput(data) : data;
                }
            },
            {
                data: 'unidad_medida', name: 'unidad_medida',
                render: function (data, type) {
                    if (type === 'display') return renderSelect(unidadOptionsDT, data, 'unidad_medida');
                    return data || '';
                }
            },
            {
                data: 'costo_dia', name: 'costo_dia',
                render: function (data, type) {
                    if (type === 'display') return renderInput(data);
                    const v = toNumber(data);
                    return Number.isFinite(v) ? v : '';
                }
            },
            {
                data: 'costo_hora', name: 'costo_hora',
                render: function (data, type) {
                    const v = toNumber(data);
                    if (type === 'display') {
                        const txt = Number.isFinite(v) ? fmtMiles.format(v) : '';
                        return `<span class="costo-hora-auto">${txt}</span>`;
                    }
                    return Number.isFinite(v) ? v : '';
                }
            },
            {
                data: 'costo_unitario', name: 'costo_unitario',
                render: function (data, type) {
                    if (type === 'display') return renderInput(data);
                    const v = toNumber(data);
                    return Number.isFinite(v) ? v : '';
                }
            },
            {
                data: null, orderable: false, name: 'acciones',
                render: function (data, type, row, meta) {
                    return `<div class="d-flex justify-content-center">
                        <button class="btn btn-success btn-xs mr-1" onclick="guardarFilaCostosDT(${meta.row})" title="Guardar fila">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-danger btn-xs" onclick="eliminarFilaCostosDT(${meta.row})" title="Eliminar fila">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        paging: true,
        searching: true,
        ordering: true,
        responsive: true,
        language: { url: '/assets/js/spanish.json' },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: function (idx) {
                        return idx !== window.tablaCostosDT.columns().nodes().length - 1;
                    },
                    format: {
                        body: function (data) {
                            if (typeof data === 'string' && data.indexOf('input') !== -1) {
                                const match = data.match(/value=["']?([^"'> ]+)["']?/);
                                if (match && match[1] !== undefined) return match[1];
                            }
                            if ($(data).find('select').length) {
                                return $(data).find('select option:selected').text();
                            }
                            if ($(data).text && $(data).text().trim() !== '') return $(data).text();
                            return data;
                        }
                    }
                }
            }
        ],
        createdRow: function (row, data, dataIndex) {
            $(row).on('change', '.dt-editable', function () {
                const $td = $(this).closest('td');
                const colIdx = $td.index();
                const field = window.tablaCostosDT.column(colIdx).dataSrc();
                const val = $(this).val();

                if (field === 'item') {
                    handleItemChangeDT(row, dataIndex, colIdx, val);
                    return;
                }

                window.tablaCostosDT.cell(dataIndex, colIdx).data(val);

                if (field === 'categoria_id') {
                    resetItemDependientesDT(row, dataIndex, val);
                }

                if (field === 'costo_dia') {
                    recomputeCostoHoraDT(row, dataIndex, val);
                }

                if (field === 'costo_unitario') {
                    const n = toNumber(val);
                    if (Number.isFinite(n) && n < 0) {
                        window.tablaCostosDT.cell(dataIndex, colIdx).data(0);
                        $(this).val(0);
                        toastr.warning('El costo unitario no puede ser negativo');
                    }
                }
            });
        },
        drawCallback: function () {
            actualizarVisibilidadCostoUnitarioDT();
        }
    });
}

// ============================== Guardado =====================================
async function saveDataCostosDT(row = null) {
    const isSingle = !!row;
    let payload;

    if (isSingle) {
        payload = [row];
    } else {
        payload = [];
        window.tablaCostosDT.rows().every(function (rowIdx) {
            payload.push(leerFilaCostosDT(rowIdx));
        });
    }

    const invalida = payload.find(r => !r?.categoria_id || !r?.item || r.item === NUEVO_ITEM_VALUE);
    if (invalida) {
        await Swal.fire('Atención', 'Hay filas sin categoría o ítem.', 'warning');
        return false;
    }

    try {
        const res = await fetch(SAVE_COSTOS_URL_DT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_COSTOS },
            body: JSON.stringify({ tablaCostos: payload })
        });

        if (!res.ok) {
            const errText = await res.text().catch(() => '');
            throw new Error(errText || 'No se pudo guardar');
        }

        initialData = window.tablaCostosDT.rows().data().toArray();

        await Swal.fire('OK', isSingle ? 'Fila guardada correctamente' : 'Guardado correctamente', 'success');
        return true;
    } catch (err) {
        console.error(err);
        await Swal.fire('Error', err.message || 'No se pudo guardar', 'error');
        return false;
    }
}

async function guardarFilaCostosDT(idx) {
    const fila = leerFilaCostosDT(idx);
    await saveDataCostosDT(fila);
}

async function eliminarFilaCostosDT(idx) {
    const rowApi = window.tablaCostosDT.row(idx);
    const data = rowApi.data();
    const id = data?.id;

    const res = await Swal.fire({
        title: '¿Eliminar registro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true
    });

    if (res.dismiss) return;

    if (!id) {
        rowApi.remove().draw(false);
        return;
    }

    try {
        const response = await fetch(`${DELETE_COSTO_URL_DT}/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_COSTOS }
        });

        if (!response.ok) {
            const text = await response.text().catch(() => '');
            throw new Error(text || 'No se pudo eliminar');
        }

        rowApi.remove().draw(false);
        await Swal.fire('Eliminado', 'El registro fue eliminado correctamente.', 'success');
    } catch (err) {
        console.error(err);
        await Swal.fire('Error', err.message || 'No se pudo eliminar', 'error');
    }
}

// =============================== Init ===================================
document.addEventListener('DOMContentLoaded', () => {
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
    };

    CargarCostosDT(true);
});
