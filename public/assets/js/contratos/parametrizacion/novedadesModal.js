/**
 * novedadesModal.js
 * Modal de creación y edición de registros de Novedades (Parametrización).
 *
 * Requiere: jQuery, Bootstrap modal, toastr
 * Variables globales del blade: categorias, cargos, novedadesCombo
 */

const NOVEDAD_MODAL_SAVE_URL = '/admin/admin.parametrizacion.storenovedades';

// ─── Inicialización ────────────────────────────────────────────────────────────
$(document).ready(function () {
    _novedadModalPopulateSelects();
    _novedadModalBindEvents();
});

/**
 * Pobla los selects del modal con los datos globales del blade.
 */
function _novedadModalPopulateSelects() {
    // ── Categorías (solo nomina / no-costos) ──────────────────────────────────
    let catArr = [];
    if (Array.isArray(categorias)) {
        catArr = categorias.filter(c => c.costos === 0);
    } else if (typeof categorias === 'object') {
        catArr = Object.entries(categorias).map(([id, nombre]) => ({ id, nombre }));
    }
    const $selCat = $('#novedad-modal-categoria');
    $selCat.empty().append('<option value="">-- Seleccione --</option>');
    catArr.forEach(c => {
        $selCat.append(`<option value="${c.id}">${c.nombre}</option>`);
    });

    // ── Cargos (ordenados alfabéticamente) ────────────────────────────────────
    let cargosArr = [];
    if (Array.isArray(cargos)) {
        cargosArr = [...cargos].sort((a, b) => a.nombre.localeCompare(b.nombre));
    } else if (typeof cargos === 'object') {
        cargosArr = Object.entries(cargos)
            .map(([id, nombre]) => ({ id, nombre }))
            .sort((a, b) => a.nombre.localeCompare(b.nombre));
    }
    const $selCargo = $('#novedad-modal-cargo');
    $selCargo.empty().append('<option value="">-- Seleccione --</option>');
    cargosArr.forEach(c => {
        $selCargo.append(`<option value="${c.id}">${c.nombre}</option>`);
    });

    // ── Novedades ─────────────────────────────────────────────────────────────
    let novedadesArr = [];
    if (Array.isArray(novedadesCombo)) {
        novedadesArr = novedadesCombo;
    } else if (typeof novedadesCombo === 'object') {
        novedadesArr = Object.entries(novedadesCombo).map(([id, nombre]) => ({ id, nombre }));
    }
    const $selNov = $('#novedad-modal-novedad');
    $selNov.empty().append('<option value="">-- Seleccione --</option>');
    novedadesArr.forEach(n => {
        $selNov.append(`<option value="${n.id}">${n.nombre}</option>`);
    });
}

/**
 * Registra los eventos del modal (checkboxes, reset al cerrar).
 */
function _novedadModalBindEvents() {
    const $modal  = $('#modal-novedad');
    const $admon  = $('#novedad-modal-admon');
    const $obra   = $('#novedad-modal-obra');
    const $valPct = $('#novedad-modal-valor-pct');

    // Auto-fill de valor_porcentaje y bloqueo del checkbox opuesto
    $admon.on('change', function () {
        if ($(this).is(':checked')) {
            $obra.prop('disabled', true).prop('checked', false);
            _autoFillValorNovedad('admon');
        } else {
            $obra.prop('disabled', false);
            $valPct.val('');
        }
    });

    $obra.on('change', function () {
        if ($(this).is(':checked')) {
            $admon.prop('disabled', true).prop('checked', false);
            _autoFillValorNovedad('obra');
        } else {
            $admon.prop('disabled', false);
            $valPct.val('');
        }
    });

    // Limpiar estado al cerrar el modal
    $modal.on('hidden.bs.modal', function () {
        $('#form-novedad')[0].reset();
        $('#novedad-modal-id').val('');
        $admon.prop('disabled', false);
        $obra.prop('disabled', false);
    });
}

/**
 * Llama al endpoint para obtener el valor por defecto de la novedad seleccionada.
 * @param {'admon'|'obra'} tipo
 */
function _autoFillValorNovedad(tipo) {
    const novedadId = $('#novedad-modal-novedad').val();
    if (!novedadId) return;
    $.get('/admin/admin.novedaddetalle.show/' + novedadId, function (resp) {
        if (!resp) return;
        const valor = tipo === 'admon' ? resp.valor_admon : resp.valor_operativo;
        if (valor !== undefined && valor !== null) {
            $('#novedad-modal-valor-pct').val(valor);
        }
    });
}

// ─── API pública ───────────────────────────────────────────────────────────────

/**
 * Abre el modal para crear un registro nuevo o editar uno existente.
 * @param {object|null} data  Datos del registro a editar; null para creación.
 */
function abrirModalNovedad(data) {
    // Siempre resetear primero
    $('#form-novedad')[0].reset();
    $('#novedad-modal-id').val('');
    $('#novedad-modal-admon').prop('disabled', false);
    $('#novedad-modal-obra').prop('disabled', false);

    if (data) {
        // ── Modo edición ──────────────────────────────────────────────────────
        $('#modal-novedad-title').text('Editar Novedad');
        $('#novedad-modal-id').val(data.id || '');
        $('#novedad-modal-categoria').val(data.categoria_id || '');
        $('#novedad-modal-cargo').val(data.cargo_id || '');
        $('#novedad-modal-novedad').val(data.novedad_detalle_id || '');
        $('#novedad-modal-valor-pct').val(data.valor_porcentaje || '');
        $('#novedad-modal-admon').prop('checked', data.valor_admon == 1);
        $('#novedad-modal-obra').prop('checked', data.valor_obra == 1);

        // Bloquear el checkbox opuesto según el estado guardado
        if (data.valor_admon == 1) $('#novedad-modal-obra').prop('disabled', true);
        if (data.valor_obra == 1)  $('#novedad-modal-admon').prop('disabled', true);
    } else {
        // ── Modo creación ─────────────────────────────────────────────────────
        $('#modal-novedad-title').text('Nueva Novedad');
    }

    $('#modal-novedad').modal('show');
}

/**
 * Obtiene los datos de la fila en la DataTable y abre el modal en modo edición.
 * @param {number} rowIdx  Índice de la fila en la DataTable global tablaNovedadesDT.
 */
function editarNovedad(rowIdx) {
    if (typeof tablaNovedadesDT === 'undefined' || !tablaNovedadesDT) return;

    const rowData = tablaNovedadesDT.row(rowIdx).data();
    const $tr     = $(tablaNovedadesDT.row(rowIdx).node());

    const idxCat     = tablaNovedadesDT.column('categoria_id:name').index();
    const idxCargo   = tablaNovedadesDT.column('cargo_id:name').index();
    const idxNovedad = tablaNovedadesDT.column('novedad_detalle_id:name').index();
    const idxAdmon   = tablaNovedadesDT.column('valor_admon:name').index();
    const idxObra    = tablaNovedadesDT.column('valor_obra:name').index();
    const idxValor   = tablaNovedadesDT.column('valor_porcentaje:name').index();

    // Leer desde el DOM para capturar cambios no persistidos aún
    abrirModalNovedad({
        id:                 rowData.id ?? null,
        categoria_id:       $tr.find('td').eq(idxCat).find('select').val()             || rowData.categoria_id,
        cargo_id:           $tr.find('td').eq(idxCargo).find('select').val()           || rowData.cargo_id,
        novedad_detalle_id: $tr.find('td').eq(idxNovedad).find('select').val()         || rowData.novedad_detalle_id,
        valor_admon:        $tr.find('td').eq(idxAdmon).find('input[type="checkbox"]').is(':checked') ? 1 : 0,
        valor_obra:         $tr.find('td').eq(idxObra).find('input[type="checkbox"]').is(':checked') ? 1 : 0,
        valor_porcentaje:   $tr.find('td').eq(idxValor).find('input').val()            ?? rowData.valor_porcentaje,
    });
}

/**
 * Valida y envía el formulario del modal al servidor.
 * Llamado por el botón "Guardar" del modal.
 */
function guardarNovedad() {
    const id                 = $('#novedad-modal-id').val() || null;
    const categoria_id       = $('#novedad-modal-categoria').val();
    const cargo_id           = $('#novedad-modal-cargo').val();
    const novedad_detalle_id = $('#novedad-modal-novedad').val();
    const valor_admon        = $('#novedad-modal-admon').is(':checked') ? 1 : 0;
    const valor_obra         = $('#novedad-modal-obra').is(':checked') ? 1 : 0;
    const valor_porcentaje   = $('#novedad-modal-valor-pct').val();

    if (!categoria_id || !cargo_id || !novedad_detalle_id) {
        toastr.warning('Complete Categoría, Cargo y Novedad antes de guardar.');
        return;
    }

    const payload = {
        parametrizacion: [{
            id,
            categoria_id,
            cargo_id,
            novedad_detalle_id,
            valor_admon,
            valor_obra,
            valor_porcentaje,
        }]
    };

    const $btn = $('#modal-novedad .btn-guardar-novedad');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: NOVEDAD_MODAL_SAVE_URL,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function () {
            toastr.success('Guardado correctamente');
            $('#modal-novedad').modal('hide');
            window.location.reload();
        },
        error: function (err) {
            const msg = err.responseJSON?.message || 'Error al guardar';
            toastr.error(msg);
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
}
