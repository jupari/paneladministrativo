// ======================= Modal "Nuevo Registro" de Parametrización de Costos =======================
// Mismo espíritu que novedadesModal.js: un formulario en modal para crear un
// registro y guardarlo directo contra el backend (POST admin.parametrizacion.storecostos).
// Depende de catálogos/funciones definidos en parametrizacionCostosDT.js
// (categoriasCostosArr, unidadOptionsDT, itemsByCatDT, itemOptionsDT,
// esCategoriaConCostoUnitarioDT, crearNuevoItemPropioDT, saveDataCostosDT,
// CargarCostosDT, upperEsCostos, toNumber, valorXCostosDT), cargado antes.

const COSTO_MODAL_ITEM_NUEVO = '__nuevo__';

function _costoModalPopulateCategorias() {
    const $sel = $('#costo-modal-categoria');
    $sel.empty().append('<option value="">-- Seleccione --</option>');
    categoriasCostosArr.forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre}</option>`);
    });
}

function _costoModalPopulateUnidades(selected) {
    const $sel = $('#costo-modal-unidad');
    $sel.empty().append('<option value="">-- Seleccione --</option>');
    unidadOptionsDT.forEach(u => {
        $sel.append(`<option value="${u.id}"${u.id === selected ? ' selected' : ''}>${u.nombre}</option>`);
    });
}

function _costoModalPopulateItems(categoriaId, selected) {
    const $sel = $('#costo-modal-item');
    $sel.empty();
    if (!categoriaId) {
        $sel.append('<option value="">-- Primero seleccione una categoría --</option>');
        $sel.trigger('change');
        return;
    }
    $sel.append('<option value="">-- Seleccione --</option>');
    const codigos = [...(itemsByCatDT[categoriaId] || [])].sort((a, b) => {
        const nombreA = itemOptionsDT[a]?.nombre || '';
        const nombreB = itemOptionsDT[b]?.nombre || '';
        return nombreA.localeCompare(nombreB, 'es');
    });
    codigos.forEach(cod => {
        const nombre = itemOptionsDT[cod]?.nombre || '';
        $sel.append(`<option value="${cod}"${cod === selected ? ' selected' : ''}>${cod} - ${nombre}</option>`);
    });
    $sel.append(`<option value="${COSTO_MODAL_ITEM_NUEVO}">➕ Crear nuevo ítem…</option>`);
    $sel.trigger('change');
}

function _costoModalActualizarVisibilidadUnitario() {
    const catId = Number($('#costo-modal-categoria').val()) || null;
    const visible = esCategoriaConCostoUnitarioDT(catId);
    $('#costo-modal-costo-unitario-group').toggleClass('d-none', !visible);
}

function _costoModalBindEvents() {
    $('#costo-modal-categoria').off('change.costoModal').on('change.costoModal', function () {
        const catId = Number($(this).val()) || null;
        _costoModalPopulateItems(catId, '');
        $('#costo-modal-nombre').val('');
        _costoModalPopulateUnidades('');
        _costoModalActualizarVisibilidadUnitario();
    });

    $('#costo-modal-item').off('change.costoModal').on('change.costoModal', async function () {
        const val = $(this).val();
        const catId = Number($('#costo-modal-categoria').val()) || null;

        if (val === COSTO_MODAL_ITEM_NUEVO) {
            const saved = await crearNuevoItemPropioDT(catId);
            if (!saved) {
                $(this).val('');
                return;
            }
            _costoModalPopulateItems(catId, saved.codigo);
            $('#costo-modal-nombre').val(saved.nombre);
            _costoModalPopulateUnidades(saved.unidad_medida);
            return;
        }

        const item = itemOptionsDT[val];
        if (item) {
            $('#costo-modal-nombre').val(item.nombre);
            _costoModalPopulateUnidades(item.unidad_medida);
        }
    });

    $('#modal-costo').on('hidden.bs.modal', function () {
        $('#form-costo')[0].reset();
        _costoModalPopulateItems(null, '');
        _costoModalPopulateUnidades('');
        $('#costo-modal-costo-unitario-group').addClass('d-none');
    });
}

$(document).ready(function () {
    _costoModalPopulateCategorias();
    _costoModalPopulateItems(null, '');
    _costoModalPopulateUnidades('');
    _costoModalBindEvents();

    $('#costo-modal-item').select2({
        placeholder: '-- Seleccione --',
        width: '100%',
        dropdownParent: $('#modal-costo')
    });
});

function abrirModalCosto() {
    $('#form-costo')[0].reset();
    _costoModalPopulateItems(null, '');
    _costoModalPopulateUnidades('');
    $('#costo-modal-costo-unitario-group').addClass('d-none');
    $('#modal-costo-title').text('Nuevo Registro de Costo');
    $('#modal-costo').modal('show');
}

async function guardarCosto() {
    const categoria_id = $('#costo-modal-categoria').val();
    const item = $('#costo-modal-item').val();
    const item_nombre = $('#costo-modal-nombre').val();
    const unidad_medida = $('#costo-modal-unidad').val();
    const costo_dia = $('#costo-modal-costo-dia').val();
    const costo_unitario = $('#costo-modal-costo-unitario').val();

    if (!categoria_id) {
        toastr.warning('Debe seleccionar una categoría.');
        return;
    }
    if (!item || item === COSTO_MODAL_ITEM_NUEVO) {
        toastr.warning('Debe seleccionar un ítem propio.');
        return;
    }
    if (!unidad_medida) {
        toastr.warning('Debe seleccionar la unidad de medida.');
        return;
    }
    const costoDiaNum = toNumber(costo_dia);
    if (costo_dia === '' || !Number.isFinite(costoDiaNum)) {
        toastr.warning('Debe ingresar un costo día válido.');
        return;
    }

    const costoHora = valorXCostosDT ? +(costoDiaNum / valorXCostosDT).toFixed(2) : null;
    const costoUnitarioNum = costo_unitario === '' ? 0 : (toNumber(costo_unitario) || 0);

    const rowData = {
        id: null,
        categoria_id: Number(categoria_id),
        item,
        item_nombre: upperEsCostos(item_nombre),
        unidad_medida,
        costo_dia: costoDiaNum,
        costo_hora: costoHora,
        costo_unitario: costoUnitarioNum,
        active: 1
    };

    if (!window.tablaCostosDT) {
        await CargarCostosDT(true);
    }

    window.tablaCostosDT.row.add(rowData).draw(false);
    actualizarVisibilidadCostoUnitarioDT();

    const ok = await saveDataCostosDT(rowData);
    if (ok) {
        $('#modal-costo').modal('hide');
    }
}
