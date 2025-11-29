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


    // // Escucha el evento de cierre de la ventana modal
    // $('#myModal').on('hidden.bs.modal', function () {
    //     // Activa la primera pestaña al cerrar la ventana modal
    //     $('#custom-content-below-home-tab').tab('show');
    // });


     //carga de la datatable
    Cargar();
});

//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalCargo'), {
    keyboard: false
})


function Cargar() {
    if ($.fn.DataTable.isDataTable('#novedades-table')) {
        $('#novedades-table').DataTable().destroy();
    }
    let table = $('#novedades-table').DataTable(
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
            ajax: '/admin/admin.novedad.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'id', name: 'id'},
                { data: 'nombre', name: 'nombre'},
                { data: 'active', name: 'active',className:'text-center'},
                { data: 'created_at', name: 'created_at'},
                { data: 'acciones', name: 'acciones', className:'text-center',className: 'exclude'},
            ],
            order: [[1, "asc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        }
    );
    // table.ajax.reload();
}

// Limpiar inputs
function cleanInput(btn) {

    const bool = (btn == null) ? false : true;

    // Campos del formulario actual
    const fields = [
        'nombre',
        'active'
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

function showCustomCargo(btn) {
    $.get("/admin/admin.cargos.edit/" + btn, (response) => {
        const usr = response.data;

        // Mapear los campos del formulario
        const usuarioFields = [
            'id',
            'nombre',
            'active'
        ];

        usuarioFields.forEach(field => {
            if (field === 'active') {
                // Configurar el checkbox
                $('#' + field).prop('checked', usr[field] == 1 ? true : false);
            } else if (field.endsWith('_id')) {
                // Configurar el valor de los selects
                $('#' + field).val(usr[field]).change();
            } else {
                // Configurar el valor de los campos de texto
                $('#' + field).val(usr[field]);
            }
        });
    });
}

//Registrar usuario
function regCargo() {
    myModal.show()
    $('#exampleModalLabel').html('Registrar Cargo');

    // LIMPIAR CAMPOS
    cleanInput();
     // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
     let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerCargo()"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';

    $(".modal-footer").html(r);

}

function registerCargo() {

    $('#spinnerRegister').addClass('d-none');
    $('#spinnerRegister').removeClass('d-block');

    const route = "/admin/admin.cargos.store";

    let activo=$('#active').is(':checked')?1:0;
    $('#active').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    // Agregar los nuevos campos del formulario
    ajax_data.append('nombre', $('#nombre').val());
    ajax_data.append('active', activo);


    // Realizar la solicitud AJAX
    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMÁGENES O ARCHIVOS POR AJAX
        processData: false,
    }).then(response => {
        $('#spinnerRegister').addClass('d-none');
        $('#spinnerRegister').removeClass('d-block');
        Cargar();
        myModal.toggle(); // Reemplaza con tu lógica de modal
        toastr.success(response.message); // Muestra el mensaje de éxito

    }).catch(e => {
        // Manejo de errores
        limpiarValidaciones(); // Reemplaza con tu función de limpieza de validaciones
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {
            // Errores de validación
            $.each(toast, function (key, value) {
                $('#error_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.'); // Muestra el mensaje de error
        } else if (e.status == 403) {
            // Errores de permisos
            $('#ModalCliente').modal('toggle');
            toastr.warning(arr.error);
        }
    });
}

// Actualizar usuario
function upCargo(btn) {
    myModal.show()
    $('#exampleModalLabel').html('Editar Cargo');
    // LIMPIAR CAMPOS
    cleanInput();
    showCustomCargo(btn);
    // FIN LIMPIAR CAMPOS
    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button id="editar" class="btn btn-primary" onclick="updateCargo(' + btn + ')">Guardar</button>';
    $(".modal-footer").html(u);
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
        myModal.toggle();
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
            myModal.toggle();
            toastr.warning(arr.message);
        }
    });
}

function limpiarValidaciones() {
    const fields = [
        'nombre',
        'active'
    ];

    fields.forEach(field => {
        $('#error_' + field).text('');
    });
}

