$(function () {

    // Toast
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    CargarCiudades();
    CargarDepartamentos();
    CargarPaises();

    $('#pais_id').change(function () {
        let pais_id = $(this).val();
        let seleccionado;
        dataPaises.forEach((p) => {
            if (p.id == pais_id) {
                seleccionado = p;
            }
        });
        $('#departamento_id').empty();
        $('#departamento_id').append('<option value="">Seleccione un departamento</option>');
        if (seleccionado) {
            $.each(seleccionado.departamentos, function (index, value) {
                $('#departamento_id').append('<option value="' + value.id + '">' + value.nombre + '</option>');
            });
        }
    });
});

function csrfHeader() {
    return { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
}

// Recarga dataPaises desde el servidor y repuebla los selects de país
// que dependen de él (modal de Ciudad y modal de Departamento), sin
// necesidad de refrescar toda la página.
function refrescarSelectsPaises() {
    $.get('/admin/admin.ubicaciones.paises.select', function (response) {
        dataPaises = response.data;

        const paisCiudadActual = $('#pais_id').val();
        $('#pais_id').empty().append('<option value="">Seleccione un país</option>');
        $.each(dataPaises, function (index, pais) {
            $('#pais_id').append('<option value="' + pais.id + '">' + pais.nombre + '</option>');
        });
        if (paisCiudadActual) {
            $('#pais_id').val(paisCiudadActual);
        }

        const paisDepartamentoActual = $('#departamento_pais_id').val();
        $('#departamento_pais_id').empty().append('<option value="">Seleccione un país</option>');
        $.each(dataPaises, function (index, pais) {
            $('#departamento_pais_id').append('<option value="' + pais.id + '">' + pais.nombre + '</option>');
        });
        if (paisDepartamentoActual) {
            $('#departamento_pais_id').val(paisDepartamentoActual);
        }
    });
}

function manejarErrorAjax(e, prefix) {
    const arr = e.responseJSON;
    if (e.status === 422) {
        $.each(arr.errors, function (key, value) {
            $('#error_' + prefix + key).text(value[0]);
            $('#error_' + key).text(value[0]);
        });
        toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
    } else if (e.status === 403) {
        toastr.warning('No tiene permisos para realizar esta acción.');
    } else {
        toastr.error(arr && arr.message ? arr.message : 'Ocurrió un error inesperado.');
    }
}

function confirmarEliminar(url, onSuccess) {
    Swal.fire({
        title: "¿Desea eliminar este registro?",
        text: "El registro eliminado no se puede volver a recuperar",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
    }).then((result) => {
        if (result.value == true) {
            $.ajax({
                url: url,
                headers: csrfHeader(),
                method: 'DELETE',
                dataType: 'json',
            }).then(response => {
                if (response.success) {
                    toastr.success(response.message);
                    onSuccess();
                } else {
                    toastr.warning(response.message);
                }
            }).catch(e => {
                const arr = e.responseJSON;
                toastr.error(arr && arr.message ? arr.message : 'No fue posible eliminar el registro.');
            });
        }
    });
}

/* =======================
   Ciudades
   ======================= */

function CargarCiudades() {
    if ($.fn.DataTable.isDataTable('#ciudades-table')) {
        // Solo refrescar los datos de la tabla ya inicializada. Destruir y
        // recrear el DataTable sobre el mismo <table> deja el <thead> en
        // blanco (DataTables 2.x + Buttons/Responsive no limpian del todo
        // las cabeceras al reinicializar sin recargar la página).
        $('#ciudades-table').DataTable().ajax.reload(null, false);
        return;
    }

    $('#ciudades-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'ltr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success',
            exportOptions: { columns: ':not(.exclude)' },
            text: '<i class="far fa-file-excel"></i>',
            titleAttr: 'Exportar a Excel',
            filename: 'reporte_ciudades'
        }],
        ajax: '/admin/admin.ubicaciones.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'pais', name: 'pais' },
            { data: 'departamento', name: 'departamento' },
            { data: 'ciudad', name: 'ciudad', className: 'text-center' },
            { data: 'active', name: 'active', className: 'text-center' },
            { data: 'acciones', name: 'acciones', className: 'exclude' },
        ],
        order: [[1, "asc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
    });
}

function cleanInputCiudad() {
    ['pais_id', 'departamento_id', 'ciudad'].forEach(field => $('#' + field).val(''));
    $('#ciudad_id').val('');
    $('#ciudad_active').prop('checked', true);
    ['error_pais_id', 'error_departamento_id', 'error_nombre'].forEach(field => $('#' + field).text(''));
}

function regCiudad() {
    $('#ModalCiudad').modal('show');
    $('#ciudadModalLabel').html('Registrar Ciudad');
    cleanInputCiudad();

    const footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerCiudad()">Agregar</button>';
    $('#modal_footer_ciudad').html(footer);
}

function upCiudad(id) {
    $('#ModalCiudad').modal('show');
    $('#ciudadModalLabel').html('Editar Ciudad');
    cleanInputCiudad();

    $.get("/admin/admin.ubicaciones.edit/" + id, (response) => {
        const c = response.data;
        $('#ciudad_id').val(c.id);
        $('#pais_id').val(c.pais_id).change();
        setTimeout(() => $('#departamento_id').val(c.departamento_id), 150);
        $('#ciudad').val(c.nombre);
        $('#ciudad_active').prop('checked', c.active == 1);
    });

    const footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button class="btn btn-primary" onclick="updateCiudad(' + id + ')">Guardar</button>';
    $('#modal_footer_ciudad').html(footer);
}

function datosFormularioCiudad() {
    const data = new FormData();
    data.append('pais_id', $('#pais_id').val());
    data.append('departamento_id', $('#departamento_id').val());
    data.append('nombre', $('#ciudad').val());
    data.append('active', $('#ciudad_active').is(':checked') ? 1 : 0);
    return data;
}

function registerCiudad() {
    $.ajax({
        url: "/admin/admin.ubicaciones.store",
        headers: csrfHeader(),
        type: 'POST',
        dataType: 'json',
        data: datosFormularioCiudad(),
        contentType: false,
        processData: false,
    }).then(response => {
        CargarCiudades();
        $('#ModalCiudad').modal('hide');
        toastr.success(response.message);
    }).catch(e => manejarErrorAjax(e, ''));
}

function updateCiudad(id) {
    $.ajax({
        url: "/admin/admin.ubicaciones.update/" + id,
        headers: csrfHeader(),
        type: 'POST',
        dataType: 'json',
        data: datosFormularioCiudad(),
        contentType: false,
        processData: false,
    }).then(response => {
        CargarCiudades();
        $('#ModalCiudad').modal('hide');
        toastr.success(response.message);
    }).catch(e => manejarErrorAjax(e, ''));
}

function deleteCiudad(id) {
    confirmarEliminar('/admin/admin.ubicaciones.destroy/' + id, CargarCiudades);
}

/* =======================
   Departamentos
   ======================= */

function CargarDepartamentos() {
    if ($.fn.DataTable.isDataTable('#departamentos-table')) {
        // Ver comentario en CargarCiudades(): reload en vez de destroy+reinit.
        $('#departamentos-table').DataTable().ajax.reload(null, false);
        return;
    }

    $('#departamentos-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'ltr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success',
            exportOptions: { columns: ':not(.exclude)' },
            text: '<i class="far fa-file-excel"></i>',
            titleAttr: 'Exportar a Excel',
            filename: 'reporte_departamentos'
        }],
        ajax: '/admin/admin.ubicaciones.departamentos.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'pais', name: 'pais' },
            { data: 'nombre', name: 'nombre' },
            { data: 'ciudades_count', name: 'ciudades_count', className: 'text-center' },
            { data: 'active', name: 'active', className: 'text-center' },
            { data: 'acciones', name: 'acciones', className: 'exclude' },
        ],
        order: [[1, "asc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
    });
}

function cleanInputDepartamento() {
    $('#departamento_form_id').val('');
    $('#departamento_pais_id').val('');
    $('#departamento_nombre').val('');
    $('#departamento_active').prop('checked', true);
    ['error_departamento_pais_id', 'error_departamento_nombre'].forEach(field => $('#' + field).text(''));
}

function regDepartamento() {
    $('#ModalDepartamento').modal('show');
    $('#departamentoModalLabel').html('Registrar Departamento');
    cleanInputDepartamento();

    const footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerDepartamento()">Agregar</button>';
    $('#modal_footer_departamento').html(footer);
}

function upDepartamento(id) {
    $('#ModalDepartamento').modal('show');
    $('#departamentoModalLabel').html('Editar Departamento');
    cleanInputDepartamento();

    $.get("/admin/admin.ubicaciones.departamentos.edit/" + id, (response) => {
        const d = response.data;
        $('#departamento_form_id').val(d.id);
        $('#departamento_pais_id').val(d.pais_id);
        $('#departamento_nombre').val(d.nombre);
        $('#departamento_active').prop('checked', d.active == 1);
    });

    const footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button class="btn btn-primary" onclick="updateDepartamento(' + id + ')">Guardar</button>';
    $('#modal_footer_departamento').html(footer);
}

function datosFormularioDepartamento() {
    const data = new FormData();
    data.append('pais_id', $('#departamento_pais_id').val());
    data.append('nombre', $('#departamento_nombre').val());
    data.append('active', $('#departamento_active').is(':checked') ? 1 : 0);
    return data;
}

function registerDepartamento() {
    $.ajax({
        url: "/admin/admin.ubicaciones.departamentos.store",
        headers: csrfHeader(),
        type: 'POST',
        dataType: 'json',
        data: datosFormularioDepartamento(),
        contentType: false,
        processData: false,
    }).then(response => {
        CargarDepartamentos();
        refrescarSelectsPaises();
        $('#ModalDepartamento').modal('hide');
        toastr.success(response.message);
    }).catch(e => manejarErrorAjax(e, 'departamento_'));
}

function updateDepartamento(id) {
    $.ajax({
        url: "/admin/admin.ubicaciones.departamentos.update/" + id,
        headers: csrfHeader(),
        type: 'POST',
        dataType: 'json',
        data: datosFormularioDepartamento(),
        contentType: false,
        processData: false,
    }).then(response => {
        CargarDepartamentos();
        refrescarSelectsPaises();
        $('#ModalDepartamento').modal('hide');
        toastr.success(response.message);
    }).catch(e => manejarErrorAjax(e, 'departamento_'));
}

function deleteDepartamento(id) {
    confirmarEliminar('/admin/admin.ubicaciones.departamentos.destroy/' + id, () => {
        CargarDepartamentos();
        refrescarSelectsPaises();
    });
}

/* =======================
   Países
   ======================= */

function CargarPaises() {
    if ($.fn.DataTable.isDataTable('#paises-table')) {
        // Ver comentario en CargarCiudades(): reload en vez de destroy+reinit.
        $('#paises-table').DataTable().ajax.reload(null, false);
        return;
    }

    $('#paises-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'ltr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success',
            exportOptions: { columns: ':not(.exclude)' },
            text: '<i class="far fa-file-excel"></i>',
            titleAttr: 'Exportar a Excel',
            filename: 'reporte_paises'
        }],
        ajax: '/admin/admin.ubicaciones.paises.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'departamentos_count', name: 'departamentos_count', className: 'text-center' },
            { data: 'active', name: 'active', className: 'text-center' },
            { data: 'acciones', name: 'acciones', className: 'exclude' },
        ],
        order: [[1, "asc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
    });
}

function cleanInputPais() {
    $('#pais_form_id').val('');
    $('#pais_nombre').val('');
    $('#pais_active').prop('checked', true);
    $('#error_pais_nombre').text('');
}

function regPais() {
    $('#ModalPais').modal('show');
    $('#paisModalLabel').html('Registrar País');
    cleanInputPais();

    const footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerPais()">Agregar</button>';
    $('#modal_footer_pais').html(footer);
}

function upPais(id) {
    $('#ModalPais').modal('show');
    $('#paisModalLabel').html('Editar País');
    cleanInputPais();

    $.get("/admin/admin.ubicaciones.paises.edit/" + id, (response) => {
        const p = response.data;
        $('#pais_form_id').val(p.id);
        $('#pais_nombre').val(p.nombre);
        $('#pais_active').prop('checked', p.active == 1);
    });

    const footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button class="btn btn-primary" onclick="updatePais(' + id + ')">Guardar</button>';
    $('#modal_footer_pais').html(footer);
}

function datosFormularioPais() {
    const data = new FormData();
    data.append('nombre', $('#pais_nombre').val());
    data.append('active', $('#pais_active').is(':checked') ? 1 : 0);
    return data;
}

function registerPais() {
    $.ajax({
        url: "/admin/admin.ubicaciones.paises.store",
        headers: csrfHeader(),
        type: 'POST',
        dataType: 'json',
        data: datosFormularioPais(),
        contentType: false,
        processData: false,
    }).then(response => {
        CargarPaises();
        refrescarSelectsPaises();
        $('#ModalPais').modal('hide');
        toastr.success(response.message);
    }).catch(e => manejarErrorAjax(e, 'pais_'));
}

function updatePais(id) {
    $.ajax({
        url: "/admin/admin.ubicaciones.paises.update/" + id,
        headers: csrfHeader(),
        type: 'POST',
        dataType: 'json',
        data: datosFormularioPais(),
        contentType: false,
        processData: false,
    }).then(response => {
        CargarPaises();
        refrescarSelectsPaises();
        $('#ModalPais').modal('hide');
        toastr.success(response.message);
    }).catch(e => manejarErrorAjax(e, 'pais_'));
}

function deletePais(id) {
    confirmarEliminar('/admin/admin.ubicaciones.paises.destroy/' + id, () => {
        CargarPaises();
        refrescarSelectsPaises();
    });
}
