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
    if ($.fn.DataTable.isDataTable('#turnos-table')) {
        $('#turnos-table').DataTable().destroy();
    }

    $('#turnos-table').DataTable({
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
            filename: 'turnos_trabajo',
        }],
        ajax: '/admin/admin.nomina.turnos.index',
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',  className: 'exclude text-center', orderable: false, searchable: false },
            { data: 'nombre',         name: 'nombre' },
            { data: 'tipo_badge',     name: 'tipo_ordinaria',         className: 'text-center', orderable: false },
            { data: 'dominical_badge',name: 'es_dominical_festivo',   className: 'text-center', orderable: false },
            { data: 'horas_ord',      name: 'max_horas_ordinarias',   className: 'text-center', orderable: false },
            { data: 'extras_badge',   name: 'max_horas_extras',       className: 'text-center', orderable: false },
            { data: 'active',         name: 'active',                  className: 'text-center' },
            { data: 'acciones',       name: 'acciones',                className: 'text-center exclude', orderable: false },
        ],
        order: [[1, 'asc']],
        pageLength: 15,
    });
}

/* ─────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────── */
function cleanInput() {
    $('#turno_id').val('');
    $('#turno_nombre').val('');
    $('#turno_descripcion').val('');
    // Tipo ordinaria: diurna por defecto
    $('#tipo_diurna').prop('checked', true);
    $('#btnDiurna').addClass('active');
    $('#btnNocturna').removeClass('active');
    $('#turno_max_horas_ord').val($('#turno_max_horas_ord').attr('max') || '7');
    $('#turno_dominical').prop('checked', false);
    $('#turno_he_diurnas').prop('checked', true);
    $('#turno_he_nocturnas').prop('checked', false);
    $('#turno_max_extras').val($('#turno_max_extras').attr('max') || '2');
    $('#turno_active').prop('checked', true);
}

function limpiarValidaciones() {
    ['nombre', 'descripcion', 'tipo_ordinaria', 'es_dominical_festivo',
     'max_horas_ordinarias', 'tiene_extras_diurnas', 'tiene_extras_nocturnas',
     'max_horas_extras', 'active']
        .forEach(f => $('#error_' + f).text(''));
}

function getDatosFormulario() {
    return {
        nombre:                  $('#turno_nombre').val(),
        descripcion:             $('#turno_descripcion').val() || null,
        tipo_ordinaria:          $('input[name="tipo_ordinaria"]:checked').val(),
        es_dominical_festivo:    $('#turno_dominical').is(':checked') ? 1 : 0,
        max_horas_ordinarias:    $('#turno_max_horas_ord').val(),
        tiene_extras_diurnas:    $('#turno_he_diurnas').is(':checked') ? 1 : 0,
        tiene_extras_nocturnas:  $('#turno_he_nocturnas').is(':checked') ? 1 : 0,
        max_horas_extras:        $('#turno_max_extras').val(),
        active:                  $('#turno_active').is(':checked') ? 1 : 0,
    };
}

/* ─────────────────────────────────────────────
   REGISTRAR
───────────────────────────────────────────── */
function regTurno() {
    cleanInput();
    limpiarValidaciones();
    $('#modalTurnoLabel').text('Nuevo Turno de Trabajo');
    $('.modal-footer').html(`
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="registerTurno()">
            <span class="spinner-border spinner-border-sm d-none" id="spinnerRegister"></span>
            <i class="fas fa-save mr-1"></i>Guardar
        </button>
    `);
    $('#ModalTurno').modal('show');
}

function registerTurno() {
    limpiarValidaciones();
    $('#spinnerRegister').removeClass('d-none');

    $.ajax({
        url: '/admin/admin.nomina.turnos.store',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: getDatosFormulario(),
    }).then(response => {
        $('#spinnerRegister').addClass('d-none');
        $('#ModalTurno').modal('hide');
        Cargar();
        toastr.success(response.message);
    }).catch(e => {
        $('#spinnerRegister').addClass('d-none');
        limpiarValidaciones();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, (key, val) => $('#error_' + key).text(val[0]));
            toastr.warning('Revise los errores en el formulario.');
        } else if (e.status === 403) {
            $('#ModalTurno').modal('hide');
            toastr.error(e.responseJSON?.error || 'Sin permiso.');
        } else {
            toastr.error('Error inesperado. Intente nuevamente.');
        }
    });
}

/* ─────────────────────────────────────────────
   EDITAR
───────────────────────────────────────────── */
function upTurno(id) {
    cleanInput();
    limpiarValidaciones();
    $('#modalTurnoLabel').text('Editar Turno de Trabajo');
    $('.modal-footer').html(`
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="updateTurno(${id})">
            <i class="fas fa-save mr-1"></i>Guardar cambios
        </button>
    `);

    $.get('/admin/admin.nomina.turnos.edit/' + id, response => {
        const t = response.data;
        $('#turno_id').val(t.id);
        $('#turno_nombre').val(t.nombre);
        $('#turno_descripcion').val(t.descripcion || '');
        $('#turno_max_horas_ord').val(t.max_horas_ordinarias);
        $('#turno_dominical').prop('checked', !!t.es_dominical_festivo);
        $('#turno_he_diurnas').prop('checked', !!t.tiene_extras_diurnas);
        $('#turno_he_nocturnas').prop('checked', !!t.tiene_extras_nocturnas);
        $('#turno_max_extras').val(t.max_horas_extras);
        $('#turno_active').prop('checked', !!t.active);

        // Tipo ordinaria
        if (t.tipo_ordinaria === 'nocturna') {
            $('#tipo_nocturna').prop('checked', true);
            $('#btnNocturna').addClass('active');
            $('#btnDiurna').removeClass('active');
        } else {
            $('#tipo_diurna').prop('checked', true);
            $('#btnDiurna').addClass('active');
            $('#btnNocturna').removeClass('active');
        }

        // Dispara actualización de factores
        $('input[name="tipo_ordinaria"]').trigger('change');
    }).fail(() => toastr.error('No se pudo cargar el turno.'));

    $('#ModalTurno').modal('show');
}

function updateTurno(id) {
    limpiarValidaciones();

    $.ajax({
        url: '/admin/admin.nomina.turnos.update/' + id,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: getDatosFormulario(),
    }).then(response => {
        $('#ModalTurno').modal('hide');
        Cargar();
        toastr.success(response.message);
    }).catch(e => {
        limpiarValidaciones();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, (key, val) => $('#error_' + key).text(val[0]));
            toastr.warning('Revise los errores en el formulario.');
        } else if (e.status === 403) {
            $('#ModalTurno').modal('hide');
            toastr.error(e.responseJSON?.message || 'Sin permiso.');
        } else {
            toastr.error('Error inesperado. Intente nuevamente.');
        }
    });
}

/* ─────────────────────────────────────────────
   ELIMINAR
───────────────────────────────────────────── */
function deleteTurno(id) {
    Swal.fire({
        title: '¿Eliminar turno?',
        text: 'Este turno ya no estará disponible en las cotizaciones de nómina. Esta acción no se puede deshacer.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.value) return;
        $.ajax({
            url: '/admin/admin.nomina.turnos.destroy/' + id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'DELETE',
            dataType: 'json',
        }).then(response => {
            Cargar();
            toastr.success(response.message);
        }).catch(e => {
            toastr.error(e.responseJSON?.message || 'Error al eliminar.');
        });
    });
}
