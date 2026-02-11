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

    // Obtener la fecha actual
    let fechaActual = new Date();

    // Formatear la fecha al formato "2009-04-19"
    let fechaFormateada = '2009-' + ('0' + (fechaActual.getMonth() + 1)).slice(-2) + '-' + ('0' + fechaActual.getDate()).slice(-2);

    CargarCotizaciones();
    CargarSolicitudes();
});


function CargarCotizaciones() {
    if ($.fn.DataTable.isDataTable('#cotizaciones-table')) {
        $('#cotizaciones-table').DataTable().destroy();
    }
    let table = $('#cotizaciones-table').DataTable(
        {
            language: {
                "url": "/assets/js/spanish.json"
            },
            responsive: true,
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'ltr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: ':not(.exclude)'
                    },
                    text: '<i class="far fa-file-excel"></i>',
                    titleAttr: 'Exportar a Excel',
                    filename: 'reporte_excel'
                }],
            ajax: '/admin/admin.cotizaciones.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'id', name: 'id' , visible:false},
                { data: 'num_documento', name: 'num_documento'},
                { data: 'cliente', name: 'cliente',className:'text-center'},
                { data: 'sede', name: 'sede',className:'text-center'},
                { data: 'proyecto', name: 'proyecto',className:'text-center'},
                { data: 'fecha', name: 'fecha',className:'text-center'},
                { data: 'estado', name: 'estado', className:'text-center'},
                { data: 'total', name: 'total', className:'text-center'},
                { data: 'actions', name: 'actions', className:'text-center', orderable: false, searchable: false},
            ],
            columnDefs:[
                {
                    targets:1,
                    visible:false
                }
            ],
            order: [[1, "desc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        }
    );
    // table.ajax.reload();
}

function CargarSolicitudes() {
    if ($.fn.DataTable.isDataTable('#cotizaciones-aprobacion-table')) {
        $('#cotizaciones-aprobacion-table').DataTable().destroy();
    }
    let table = $('#cotizaciones-aprobacion-table').DataTable(
        {
            language: {
                "url": "/assets/js/spanish.json"
            },
            responsive: true,
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'ltr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: ':not(.exclude)'
                    },
                    text: '<i class="far fa-file-excel"></i>',
                    titleAttr: 'Exportar a Excel',
                    filename: 'reporte_excel'
                }],
            ajax: '/admin/admin.cotizaciones.solicitudes.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'id', name: 'id', visible:false},
                { data: 'num_documento', name: 'num_documento'},
                { data: 'cliente', name: 'cliente',className:'text-center'},
                { data: 'sede', name: 'sede',className:'text-center'},
                { data: 'proyecto', name: 'proyecto',className:'text-center'},
                { data: 'fecha', name: 'fecha',className:'text-center'},
                { data: 'estado', name: 'estado', className:'text-center'},
                { data: 'autorizacion', name: 'autorizacion', className:'text-center'},
                { data: 'total', name: 'total', className:'text-center'},
                { data: 'actions', name: 'actions', className:'text-center', orderable: false, searchable: false},
            ],
            columnDefs:[
                {
                    targets:2,
                    visible:false
                }
            ],
            order: [[0, "desc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        }
    );
    // table.ajax.reload();
}

/**
 * Función para editar una cotización
 * @param {number} id - ID de la cotización
 */
function editCotizacion(id) {
    // Redirigir a la página de edición
    window.location.href = `/admin/admin.cotizaciones.edit/${id}`;
}

/**
 * Función para ver una cotización
 * @param {number} id - ID de la cotización
 */
function showCotizacion(id) {
    window.location.href = `/admin/admin.cotizaciones.show/${id}`;
}

/**
 * Función para duplicar una cotización
 * @param {number} id - ID de la cotización
 */
function duplicateCotizacion(id) {
    Swal.fire({
        title: '¿Duplicar cotización?',
        text: "Se creará una copia completa de esta cotización incluyendo conceptos, observaciones y condiciones comerciales.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        console.log('result', result);

        if (result.value) {
            // Mostrar loading
            Swal.fire({
                title: 'Duplicando...',
                text: 'Por favor espere mientras se duplica la cotización.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admin/admin.cotizaciones.duplicate/${id}`,
                type: 'POST',
                data: {
                    confirmed: true
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Duplicada!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Ir a la nueva cotización'
                        }).then(() => {
                            if (response.data && response.data.id) {
                                // Redirigir a la nueva cotización
                                window.location.href = `/admin/admin.cotizaciones.edit/${response.data.id}`;
                            } else {
                                CargarCotizaciones();
                            }
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'No se pudo duplicar la cotización';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
}

/**
 * Función para eliminar una cotización
 * @param {number} id - ID de la cotización
 */
function deleteCotizacion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: `/admin/admin.cotizaciones.destroy/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Anulada!', response.message, 'success');
                        CargarCotizaciones();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'No se pudo eliminar la cotización', 'error');
                }
            });
        }
    });
}

function updateCargo(btn) {
    const route = `/admin/admin.cargos.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Lista de campos del formulario
    const fields = [
        'nombre',
        'active'
    ];

    // Recorrer los campos y agregarlos al FormData
    fields.forEach(field => {
        if (field === 'active') {
            // Manejar checkbox (true o false)
            ajax_data.append(field, $('#' + field).is(':checked') ? 1 : 0);
        } else {
            ajax_data.append(field, $('#' + field).val());
        }
    });

    // Enviar la solicitud AJAX
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
    })
    .then(response => {
        Cargar();
        myModal.modal('toggle');
        toastr.success(response.message);
    })
    .catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status === 422) {
            // Errores de validación
            $('#myModal').data('bs.modal')._config.backdrop = 'static';
            $.each(toast, function(key, value) {
                $('#error_' + key).text(value[0]);
            });
        toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            myModal.modal('toggle');
            toastr.warning(arr.message);
        }
    });
}

function autorizarCotizacion(id) {
    Swal.fire({
        title: '¿Autorizar cotización?',
        text: "Se autorizará esta cotización según los parámetros establecidos.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, autorizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: `/admin/admin.cotizaciones.solicitudes.auth/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Autorizada!', response.message, 'success');
                        CargarSolicitudes();
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'No se pudo autorizar la cotización';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
}


