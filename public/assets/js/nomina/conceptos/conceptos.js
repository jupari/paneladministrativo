$(function () {

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

    Cargar();
});

function Cargar() {

    if ($.fn.DataTable.isDataTable('#concepts-table')) {
        $('#concepts-table').DataTable().destroy();
    }

    $('#concepts-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'ltr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-success',
                exportOptions: { columns: ':not(.exclude)' },
                text: '<i class="far fa-file-excel"></i>',
                titleAttr: 'Exportar a Excel',
                filename: 'nomina_concepts'
            }
        ],
        ajax: '/admin/admin.nomina.concepts.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'id', name: 'id', className: 'exclude' },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'scope', name: 'scope' },
            { data: 'kind', name: 'kind' },
            { data: 'calc_method', name: 'calc_method' },
            { data: 'is_active', name: 'is_active', className: 'text-center' }, // badge html
            { data: 'created_at', name: 'created_at' },
            { data: 'acciones', name: 'acciones', className: 'text-center exclude', orderable: false, searchable: false }
        ],
        order: [[2, "asc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
    });
}

function cleanInput() {
    const fields = [
        'id',
        'code',
        'name',
        'scope',
        'kind',
        'calc_method',
        'tax_nature',
        'base_code',
        'priority'
    ];
    fields.forEach(f => $('#' + f).val(''));

    $('#is_active').prop('checked', true);
    $('#priority').val(100);
}

function limpiarValidaciones() {
    const fields = [
        'code',
        'name',
        'scope',
        'kind',
        'calc_method',
        'tax_nature',
        'base_code',
        'priority',
        'is_active'
    ];
    fields.forEach(f => $('#error_' + f).text(''));
}

function regConcept() {
    $('#ModalConcept').modal('show');
    $('#conceptModalTitle').html('Registrar Concepto');
    cleanInput();
    limpiarValidaciones();

    const r =
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerConcept()">Agregar</button>';

    $(".modal-footer").html(r);
}

function registerConcept() {
    const route = "/admin/admin.nomina.concepts.store";

    const ajax_data = new FormData();
    ajax_data.append('code', $('#code').val());
    ajax_data.append('name', $('#name').val());
    ajax_data.append('scope', $('#scope').val());
    ajax_data.append('kind', $('#kind').val());
    ajax_data.append('calc_method', $('#calc_method').val());
    ajax_data.append('tax_nature', $('#tax_nature').val());
    ajax_data.append('base_code', $('#base_code').val());
    ajax_data.append('priority', $('#priority').val());
    ajax_data.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false,
        processData: false,
    }).then(resp => {
        Cargar();
        $('#ModalConcept').modal('hide');
        toastr.success(resp.message || 'Concepto creado correctamente.');
    }).catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON || {};
        if (e.status === 422 && arr.errors) {
            $.each(arr.errors, function (key, value) {
                $('#error_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            toastr.warning(arr.message || 'No autorizado.');
        } else {
            toastr.error(arr.message || 'Error inesperado.');
        }
    });
}

function showCustomConcept(id) {
    $.get("/admin/admin.nomina.concepts.edit/" + id, (response) => {
        const row = response.data;

        $('#id').val(row.id);
        $('#code').val(row.code);
        $('#name').val(row.name);
        $('#scope').val(row.scope);
        $('#kind').val(row.kind);
        $('#calc_method').val(row.calc_method);
        $('#tax_nature').val(row.tax_nature);
        $('#base_code').val(row.base_code ?? '');
        $('#priority').val(row.priority ?? 100);
        $('#is_active').prop('checked', row.is_active == 1);
    });
}

function upConcept(id) {
    $('#ModalConcept').modal('show');
    $('#conceptModalTitle').html('Editar Concepto');
    cleanInput();
    limpiarValidaciones();
    showCustomConcept(id);

    const u =
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button class="btn btn-primary" onclick="updateConcept(' + id + ')">Guardar</button>';

    $(".modal-footer").html(u);
}

function updateConcept(id) {
    const route = `/admin/admin.nomina.concepts.update/${id}`;

    const ajax_data = new FormData();
    ajax_data.append('code', $('#code').val());
    ajax_data.append('name', $('#name').val());
    ajax_data.append('scope', $('#scope').val());
    ajax_data.append('kind', $('#kind').val());
    ajax_data.append('calc_method', $('#calc_method').val());
    ajax_data.append('tax_nature', $('#tax_nature').val());
    ajax_data.append('base_code', $('#base_code').val());
    ajax_data.append('priority', $('#priority').val());
    ajax_data.append('is_active', $('#is_active').is(':checked') ? 1 : 0);

    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'POST'
        },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false,
        processData: false,
    }).then(resp => {
        Cargar();
        $('#ModalConcept').modal('hide');
        toastr.success(resp.message || 'Concepto actualizado correctamente.');
    }).catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON || {};
        if (e.status === 422 && arr.errors) {
            $.each(arr.errors, function (key, value) {
                $('#error_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            toastr.warning(arr.message || 'No autorizado.');
        } else {
            toastr.error(arr.message || 'Error inesperado.');
        }
    });
}
