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

    if ($.fn.DataTable.isDataTable('#payruns-table')) {
        $('#payruns-table').DataTable().destroy();
    }

    $('#payruns-table').DataTable({
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
                filename: 'nomina_pay_runs'
            }
        ],
        ajax: '/admin/admin.nomina.payruns.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'id', name: 'id', className: 'exclude' },
            { data: 'run_type', name: 'run_type' },
            { data: 'period', name: 'period' },         // sugerido: "YYYY-MM-DD a YYYY-MM-DD"
            { data: 'pay_date', name: 'pay_date' },
            { data: 'status', name: 'status', className: 'text-center' }, // badge html
            { data: 'created_at', name: 'created_at' },
            { data: 'acciones', name: 'acciones', className: 'text-center exclude', orderable: false, searchable: false }
        ],
        order: [[1, "desc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
    });
}

function cleanInput() {
    const fields = [
        'id',
        'run_type',
        'period_start',
        'period_end',
        'pay_date',
        'notes'
    ];

    fields.forEach(f => $('#' + f).val(''));

    $('#include_laboral').prop('checked', true);
    $('#include_contratistas').prop('checked', true);
}

function limpiarValidaciones() {
    const fields = [
        'run_type',
        'period_start',
        'period_end',
        'pay_date',
        'include_laboral',
        'include_contratistas',
        'notes'
    ];
    fields.forEach(f => $('#error_' + f).text(''));
}

function regPayRun() {
    $('#ModalPayRun').modal('show');
    $('#payrunModalTitle').html('Registrar Periodo');
    cleanInput();
    limpiarValidaciones();

    const r =
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerPayRun()">' +
        '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegisterPayRun"></span> ' +
        'Agregar</button>';

    $(".modal-footer").html(r);
}

function registerPayRun() {

    $('#spinnerRegisterPayRun').removeClass('d-none');

    const route = "/admin/admin.nomina.payruns.store";

    const ajax_data = new FormData();
    ajax_data.append('run_type', $('#run_type').val());
    ajax_data.append('period_start', $('#period_start').val());
    ajax_data.append('period_end', $('#period_end').val());
    ajax_data.append('pay_date', $('#pay_date').val());
    ajax_data.append('include_laboral', $('#include_laboral').is(':checked') ? 1 : 0);
    ajax_data.append('include_contratistas', $('#include_contratistas').is(':checked') ? 1 : 0);
    ajax_data.append('notes', $('#notes').val());

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false,
        processData: false,
    }).then(response => {
        $('#spinnerRegisterPayRun').addClass('d-none');
        Cargar();
        $('#ModalPayRun').modal('hide');
        toastr.success(response.message || 'Periodo creado correctamente.');
    }).catch(e => {
        $('#spinnerRegisterPayRun').addClass('d-none');
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

function showCustomPayRun(id) {
    $.get("/admin/admin.nomina.payruns.show/" + id, (response) => {
        const row = response.data;

        console.log('showCustomPayRun - response:', response.data);


        $('#id').val(row.id);
        $('#run_type').val(row.run_type);
        // Formato YYYY-MM-DD para inputs tipo date
        $('#period_start').val(row.period_start ? row.period_start.substring(0, 10) : '');
        $('#period_end').val(row.period_end ? row.period_end.substring(0, 10) : '');
        $('#pay_date').val(row.pay_date ? row.pay_date.substring(0, 10) : '');
        $('#notes').val(row.notes ?? '');

        // estos campos podrían no existir en DB, pero si el backend los retorna, los mapeamos:
        if (row.run_type !== undefined) $('#include_laboral').prop('checked', row.run_type == 'NOMINA');
        if (row.run_type !== undefined) $('#include_contratistas').prop('checked', row.run_type == 'CONTRATISTAS');
    });
}

function upPayRun(id) {
    $('#ModalPayRun').modal('show');
    $('#payrunModalTitle').html('Editar Periodo');
    cleanInput();
    limpiarValidaciones();
    showCustomPayRun(id);

    const u =
        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button class="btn btn-primary" onclick="updatePayRun(' + id + ')">Guardar</button>';

    $(".modal-footer").html(u);
}

function updatePayRun(id) {
    const route = `/admin/admin.nomina.payruns.update/${id}`;

    const ajax_data = new FormData();
    ajax_data.append('run_type', $('#run_type').val());
    ajax_data.append('period_start', $('#period_start').val());
    ajax_data.append('period_end', $('#period_end').val());
    ajax_data.append('pay_date', $('#pay_date').val());
    ajax_data.append('include_laboral', $('#include_laboral').is(':checked') ? 1 : 0);
    ajax_data.append('include_contratistas', $('#include_contratistas').is(':checked') ? 1 : 0);
    ajax_data.append('notes', $('#notes').val());

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
    }).then(response => {
        Cargar();
        $('#ModalPayRun').modal('hide');
        toastr.success(response.message || 'Periodo actualizado correctamente.');
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

/**
 * Ejecuta el cálculo del periodo
 * Backend: POST /admin/admin.nomina.payruns.calculate/{id}
 */
function calculatePayRun(id) {
    Swal.fire({
        title: 'Calcular nómina',
        text: 'Se calcularán devengados/deducciones y se aplicarán novedades pendientes. ¿Desea continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, calcular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        console.log('result>>>>>',result);

        if (!result.value==true) return;

        $.ajax({
            url: `/admin/admin.nomina.payruns.calculate/${id}`,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            dataType: 'json',
        }).then(resp => {
            Cargar();
            toastr.success(resp.message || 'Cálculo realizado correctamente.');
        }).catch(e => {
            const arr = e.responseJSON || {};
            toastr.error(arr.message || 'No fue posible calcular el periodo.');
        });
    });
}
