$(function () {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-bottom-right',
        timeOut: '5000',
    };

    Cargar();
});

/* ─────────────────────────────────────────────
   DATATABLE
───────────────────────────────────────────── */
function Cargar() {
    if ($.fn.DataTable.isDataTable('#parametros-table')) {
        $('#parametros-table').DataTable().destroy();
    }

    $('#parametros-table').DataTable({
        language: { url: '/assets/js/spanish.json' },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'ltr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success btn-sm',
            exportOptions: { columns: ':not(.exclude)' },
            text: '<i class="far fa-file-excel"></i> Excel',
            titleAttr: 'Exportar a Excel',
            filename: 'parametros_globales_nomina',
        }],
        ajax: '/admin/admin.nomina.parametros.index',
        columns: [
            { data: 'DT_RowIndex',          name: 'DT_RowIndex',               className: 'exclude text-center', orderable: false, searchable: false },
            { data: 'vigencia_badge',        name: 'vigencia',                  className: 'text-center' },
            { data: 'smlv_fmt',              name: 'smlv',                      className: 'text-right' },
            { data: 'aux_transporte_fmt',    name: 'aux_transporte',            className: 'text-right' },
            { data: 'uvt_fmt',               name: 'uvt',                       className: 'text-right' },
            { data: 'tope_badge',            name: 'tope_exoneracion_ley1607',  className: 'text-center', orderable: false },
            { data: 'active',                name: 'active',                    className: 'text-center' },
            { data: 'acciones',              name: 'acciones',                  className: 'text-center exclude', orderable: false },
        ],
        order: [[1, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, 'Todos']],
    });
}

/* ─────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────── */
function cleanInput() {
    $('#param_id').val('');
    $('#param_vigencia').val('');
    $('#param_smlv').val('');
    $('#param_aux_transporte').val('');
    $('#param_uvt').val('');
    $('#param_tope').val('10');
    $('#param_active').prop('checked', true);
}

function limpiarValidaciones() {
    ['vigencia', 'smlv', 'aux_transporte', 'uvt', 'tope_exoneracion_ley1607', 'active']
        .forEach(f => $('#error_' + f).text(''));
}

function getDatosFormulario() {
    return {
        vigencia:                   $('#param_vigencia').val(),
        smlv:                       $('#param_smlv').val(),
        aux_transporte:             $('#param_aux_transporte').val(),
        uvt:                        $('#param_uvt').val(),
        tope_exoneracion_ley1607:   $('#param_tope').val(),
        active:                     $('#param_active').is(':checked') ? 1 : 0,
    };
}

/* ─────────────────────────────────────────────
   REGISTRAR
───────────────────────────────────────────── */
function regParametro() {
    cleanInput();
    limpiarValidaciones();
    $('#modalParametrosLabel').text('Nuevo Parámetro de Nómina');
    $('.modal-footer').html(`
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="registerParametro()">
            <span class="spinner-border spinner-border-sm d-none" id="spinnerRegister"></span>
            <i class="fas fa-save mr-1"></i>Guardar
        </button>
    `);
    $('#ModalParametros').modal('show');
}

function registerParametro() {
    limpiarValidaciones();
    $('#spinnerRegister').removeClass('d-none');

    $.ajax({
        url: '/admin/admin.nomina.parametros.store',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: getDatosFormulario(),
    }).then(response => {
        $('#spinnerRegister').addClass('d-none');
        $('#ModalParametros').modal('hide');
        Cargar();
        toastr.success(response.message);
    }).catch(e => {
        $('#spinnerRegister').addClass('d-none');
        limpiarValidaciones();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, (key, val) => $('#error_' + key).text(val[0]));
            toastr.warning('Revise los errores en el formulario.');
        } else if (e.status === 403) {
            $('#ModalParametros').modal('hide');
            toastr.error(e.responseJSON.error || 'Sin permiso.');
        } else {
            toastr.error('Error inesperado. Intente nuevamente.');
        }
    });
}

/* ─────────────────────────────────────────────
   EDITAR
───────────────────────────────────────────── */
function upParametro(id) {
    cleanInput();
    limpiarValidaciones();
    $('#modalParametrosLabel').text('Editar Parámetro de Nómina');
    $('.modal-footer').html(`
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="updateParametro(${id})">
            <i class="fas fa-save mr-1"></i>Guardar cambios
        </button>
    `);

    $.get('/admin/admin.nomina.parametros.edit/' + id, response => {
        const p = response.data;
        $('#param_id').val(p.id);
        $('#param_vigencia').val(p.vigencia);
        $('#param_smlv').val(p.smlv);
        $('#param_aux_transporte').val(p.aux_transporte);
        $('#param_uvt').val(p.uvt);
        $('#param_tope').val(p.tope_exoneracion_ley1607);
        $('#param_active').prop('checked', !!p.active);
        // Actualizar el badge de tope
        $('#param_smlv, #param_tope').trigger('change');
    }).fail(() => toastr.error('No se pudo cargar el registro.'));

    $('#ModalParametros').modal('show');
}

function updateParametro(id) {
    limpiarValidaciones();

    $.ajax({
        url: '/admin/admin.nomina.parametros.update/' + id,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: getDatosFormulario(),
    }).then(response => {
        $('#ModalParametros').modal('hide');
        Cargar();
        toastr.success(response.message);
        // Recargar la página para actualizar la alerta informativa del encabezado
        setTimeout(() => location.reload(), 1200);
    }).catch(e => {
        limpiarValidaciones();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, (key, val) => $('#error_' + key).text(val[0]));
            toastr.warning('Revise los errores en el formulario.');
        } else if (e.status === 403) {
            $('#ModalParametros').modal('hide');
            toastr.error(e.responseJSON.message || 'Sin permiso.');
        } else {
            toastr.error('Error inesperado. Intente nuevamente.');
        }
    });
}

/* ─────────────────────────────────────────────
   ELIMINAR
───────────────────────────────────────────── */
function deleteParametro(id, esActivo) {
    const advertencia = esActivo
        ? '<br><br><span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i>ADVERTENCIA</span> '
          + 'Este es el registro <strong>activo actualmente</strong>. '
          + 'El motor de liquidación quedará sin parámetros si no existe otro registro activo.'
        : 'Esta acción no se puede deshacer.';

    Swal.fire({
        title: '¿Eliminar parámetro?',
        html: 'Se eliminarán los valores del año seleccionado.' + advertencia,
        type: esActivo ? 'warning' : 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.value) return;
        $.ajax({
            url: '/admin/admin.nomina.parametros.destroy/' + id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'DELETE',
            dataType: 'json',
        }).then(response => {
            Cargar();
            toastr.success(response.message);
            setTimeout(() => location.reload(), 1200);
        }).catch(e => {
            const msg = e.responseJSON?.message || 'Error al eliminar.';
            toastr.error(msg);
            if (e.status === 422) {
                Swal.fire('No se puede eliminar', msg, 'error');
            }
        });
    });
}
