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
    if ($.fn.DataTable.isDataTable('#cargos-table')) {
        $('#cargos-table').DataTable().destroy();
    }

    $('#cargos-table').DataTable({
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
            filename: 'cargos',
        }],
        ajax: '/admin/admin.cargos.index',
        columns: [
            { data: 'DT_RowIndex',              name: 'DT_RowIndex',              className: 'exclude text-center', orderable: false, searchable: false },
            { data: 'nombre',                   name: 'nombre' },
            { data: 'salario_base_fmt',         name: 'salario_base',             className: 'text-center' },
            { data: 'arl_badge',                name: 'arl_nivel',                className: 'text-center', orderable: false },
            { data: 'exoneracion_badge',        name: 'aplica_exoneracion',       className: 'text-center', orderable: false },
            { data: 'active',                   name: 'active',                   className: 'text-center' },
            { data: 'acciones',                 name: 'acciones',                 className: 'text-center exclude', orderable: false },
        ],
        order: [[1, 'asc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, 'Todos']],
    });
}

/* ─────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────── */
function cleanInput() {
    $('#id').val('');
    $('#nombre').val('');
    $('#active').prop('checked', true);
    $('#salario_base').val('');
    $('#arl_nivel').val('1');
    $('#aplica_exoneracion_ley1607').prop('checked', true);
}

function limpiarValidaciones() {
    ['nombre', 'active', 'salario_base', 'arl_nivel', 'aplica_exoneracion_ley1607']
        .forEach(f => $('#error_' + f).text(''));
}

function getDatosFormulario() {
    return {
        nombre:                     $('#nombre').val(),
        active:                     $('#active').is(':checked') ? 1 : 0,
        salario_base:               $('#salario_base').val() || null,
        arl_nivel:                  $('#arl_nivel').val(),
        aplica_exoneracion_ley1607: $('#aplica_exoneracion_ley1607').is(':checked') ? 1 : 0,
    };
}

/* ─────────────────────────────────────────────
   REGISTRAR
───────────────────────────────────────────── */
function regCargo() {
    cleanInput();
    limpiarValidaciones();
    $('#exampleModalLabel').text('Nuevo Cargo');
    $('.modal-footer').html(`
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="registerCargo()">
            <span class="spinner-border spinner-border-sm d-none" id="spinnerRegister"></span>
            <i class="fas fa-save mr-1"></i>Guardar
        </button>
    `);
    $('#ModalCargo').modal('show');
}

function registerCargo() {
    limpiarValidaciones();
    $('#spinnerRegister').removeClass('d-none');

    $.ajax({
        url: '/admin/admin.cargos.store',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: getDatosFormulario(),
    }).then(response => {
        $('#spinnerRegister').addClass('d-none');
        $('#ModalCargo').modal('hide');
        Cargar();
        toastr.success(response.message);
    }).catch(e => {
        $('#spinnerRegister').addClass('d-none');
        limpiarValidaciones();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, (key, val) => $('#error_' + key).text(val[0]));
            toastr.warning('Revise los errores en el formulario.');
        } else if (e.status === 403) {
            $('#ModalCargo').modal('hide');
            toastr.error(e.responseJSON.error || 'Sin permiso.');
        } else {
            toastr.error('Error inesperado. Intente nuevamente.');
        }
    });
}

/* ─────────────────────────────────────────────
   EDITAR
───────────────────────────────────────────── */
function upCargo(id) {
    cleanInput();
    limpiarValidaciones();
    $('#exampleModalLabel').text('Editar Cargo');
    $('.modal-footer').html(`
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" onclick="updateCargo(${id})">
            <i class="fas fa-save mr-1"></i>Guardar cambios
        </button>
    `);

    $.get('/admin/admin.cargos.edit/' + id, response => {
        const c = response.data;
        $('#id').val(c.id);
        $('#nombre').val(c.nombre);
        $('#active').prop('checked', c.active == 1);
        $('#salario_base').val(c.salario_base || '');
        $('#arl_nivel').val(c.arl_nivel || 1);
        $('#aplica_exoneracion_ley1607').prop('checked', c.aplica_exoneracion_ley1607 != 0);
        // Disparar actualización del resumen visual
        $('#arl_nivel').trigger('change');
    }).fail(() => toastr.error('No se pudo cargar el cargo.'));

    $('#ModalCargo').modal('show');
}

function updateCargo(id) {
    limpiarValidaciones();

    $.ajax({
        url: '/admin/admin.cargos.update/' + id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'POST',
        },
        type: 'POST',
        dataType: 'json',
        data: getDatosFormulario(),
    }).then(response => {
        $('#ModalCargo').modal('hide');
        Cargar();
        toastr.success(response.message);
    }).catch(e => {
        limpiarValidaciones();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, (key, val) => $('#error_' + key).text(val[0]));
            toastr.warning('Revise los errores en el formulario.');
        } else if (e.status === 403) {
            $('#ModalCargo').modal('hide');
            toastr.error(e.responseJSON.message || 'Sin permiso.');
        } else {
            toastr.error('Error inesperado. Intente nuevamente.');
        }
    });
}

/* ─────────────────────────────────────────────
   ELIMINAR
───────────────────────────────────────────── */
function deleteCargo(id) {
    Swal.fire({
        title: '¿Eliminar cargo?',
        text: 'Esta acción no se puede deshacer.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.value) return;
        $.ajax({
            url: '/admin/admin.cargos.destroy/' + id,
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
