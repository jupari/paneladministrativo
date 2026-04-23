$(function () {

    // ─── DataTable ───────────────────────────────────────────────────────────
    $('#conceptos-table').DataTable({
        language: { url: '/assets/js/spanish.json' },
        processing: true,
        serverSide: true,
        responsive: true,
        dom:
            "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'ltr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success',
            exportOptions: { columns: ':not(.exclude)' },
            text: '<i class="far fa-file-excel"></i>',
            titleAttr: 'Exportar a Excel',
            filename: 'reporte_conceptos'
        }],
        ajax: {
            url: '/admin/admin.conceptos.index',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'nombre',               name: 'nombre' },
            { data: 'tipo',                 name: 'tipo' },
            { data: 'porcentaje_defecto',   name: 'porcentaje_defecto', className: 'text-center' },
            { data: 'active',               name: 'active', className: 'text-center', orderable: false },
            { data: 'acciones',             name: 'acciones', className: 'exclude text-center', orderable: false, searchable: false },
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todo(s)']],
    });

    // ─── Toastr ───────────────────────────────────────────────────────────────
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-bottom-right',
        timeOut: '5000',
    };

    // ─── Limpiar modal al cerrar ──────────────────────────────────────────────
    $('#modalConcepto').on('hidden.bs.modal', function () {
        limpiarConcepto();
        limpiarValidacionesConcepto();
    });

});

// ─── Recargar tabla ──────────────────────────────────────────────────────────
function recargarConceptos() {
    $('#conceptos-table').DataTable().ajax.reload();
}

// ─── Limpiar campos ──────────────────────────────────────────────────────────
function limpiarConcepto() {
    $('#concepto_nombre').val('');
    $('#concepto_tipo').val('');
    $('#concepto_porcentaje').val('');
}

function limpiarValidacionesConcepto() {
    ['concepto_nombre', 'concepto_tipo', 'concepto_porcentaje'].forEach(function (id) {
        $('#error_' + id).text('');
    });
}

// ─── Abrir modal Crear ────────────────────────────────────────────────────────
function regConcepto() {
    limpiarConcepto();
    limpiarValidacionesConcepto();
    $('#modalConceptoLabel').text('Crear Concepto');
    $('#modalConceptoFooter').html(
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="storeConcepto()">Guardar</button>'
    );
    $('#modalConcepto').modal('show');
}

// ─── Registrar ────────────────────────────────────────────────────────────────
function storeConcepto() {
    limpiarValidacionesConcepto();

    $.ajax({
        url: '/admin/admin.conceptos',
        type: 'POST',
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {
            nombre: $('#concepto_nombre').val(),
            tipo: $('#concepto_tipo').val(),
            porcentaje_defecto: $('#concepto_porcentaje').val(),
        }
    }).then(function (res) {
        $('#modalConcepto').modal('hide');
        toastr.success(res.message);
        recargarConceptos();
    }).catch(function (e) {
        limpiarValidacionesConcepto();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, function (key, val) {
                var fieldMap = { nombre: 'concepto_nombre', tipo: 'concepto_tipo', porcentaje_defecto: 'concepto_porcentaje' };
                var domId = fieldMap[key] || key;
                $('#error_' + domId).text(val[0]);
            });
        } else if (e.status === 403) {
            toastr.warning(e.responseJSON.message || 'Sin permiso.');
        } else {
            toastr.error((e.responseJSON && e.responseJSON.error) || 'Error al guardar.');
        }
    });
}

// ─── Abrir modal Editar ───────────────────────────────────────────────────────
function upConcepto(id) {
    limpiarConcepto();
    limpiarValidacionesConcepto();

    $.get('/admin/admin.conceptos/' + id, function (res) {
        var c = res.concepto;
        $('#concepto_nombre').val(c.nombre);
        $('#concepto_tipo').val(c.tipo || '');
        $('#concepto_porcentaje').val(c.porcentaje_defecto);
    }).fail(function () {
        toastr.error('No se pudo cargar el concepto.');
    });

    $('#modalConceptoLabel').text('Editar Concepto');
    $('#modalConceptoFooter').html(
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="updateConcepto(' + id + ')">Actualizar</button>'
    );
    $('#modalConcepto').modal('show');
}

// ─── Actualizar ───────────────────────────────────────────────────────────────
function updateConcepto(id) {
    limpiarValidacionesConcepto();

    $.ajax({
        url: '/admin/admin.conceptos/' + id,
        type: 'PUT',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
        data: {
            nombre: $('#concepto_nombre').val(),
            tipo: $('#concepto_tipo').val(),
            porcentaje_defecto: $('#concepto_porcentaje').val(),
        }
    }).then(function (res) {
        $('#modalConcepto').modal('hide');
        toastr.success(res.message);
        recargarConceptos();
    }).catch(function (e) {
        limpiarValidacionesConcepto();
        if (e.status === 422) {
            $.each(e.responseJSON.errors, function (key, val) {
                var fieldMap = { nombre: 'concepto_nombre', tipo: 'concepto_tipo', porcentaje_defecto: 'concepto_porcentaje' };
                var domId = fieldMap[key] || key;
                $('#error_' + domId).text(val[0]);
            });
        } else if (e.status === 403) {
            toastr.warning(e.responseJSON.message || 'Sin permiso.');
        } else {
            toastr.error((e.responseJSON && e.responseJSON.error) || 'Error al actualizar.');
        }
    });
}

// ─── Activar / Desactivar ─────────────────────────────────────────────────────
function toggleConcepto(id) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: 'El concepto será activado o desactivado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '/admin/admin.conceptos/' + id + '/toggle',
            type: 'PATCH',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).then(function (res) {
            toastr.success(res.message);
            recargarConceptos();
        }).catch(function (e) {
            toastr.error((e.responseJSON && e.responseJSON.error) || 'Error al cambiar el estado.');
        });
    });
}

// ─── Eliminar ─────────────────────────────────────────────────────────────────
function deleteConcepto(id) {
    Swal.fire({
        title: '¿Eliminar concepto?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '/admin/admin.conceptos/' + id,
            type: 'DELETE',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).then(function (res) {
            toastr.success(res.message);
            recargarConceptos();
        }).catch(function (e) {
            var msg = (e.responseJSON && (e.responseJSON.error || e.responseJSON.message)) || 'Error al eliminar.';
            toastr.error(msg);
        });
    });
}
