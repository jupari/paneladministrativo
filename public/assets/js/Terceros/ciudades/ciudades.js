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


     //carga de la datatable
    Cargar();

    $('#pais_id').change(function(){
        let pais_id = $(this).val();
        let dptos;
        dataPaises.forEach((p)=>{
            if(p.id==pais_id){
                dptos = p;
            }

        });
        $('#departamento_id').empty();
        $('#departamento_id').append('<option value="">Seleccione un departamento</option>');
        $.each(dptos.departamentos, function (index, value) {
            $('#departamento_id').append('<option value="'+ value.id +'">'+ value.nombre +'</option>');
        });

    });
});

//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalCiudad'), {
    keyboard: false
})

// var modalPaisDpto = new bootstrap.Modal(document.getElementById('ModalPaisDpto'), {
//     keyboard: false
// })


function Cargar() {
    if ($.fn.DataTable.isDataTable('#ciudades-table')) {
        $('#ciudades-table').DataTable().destroy();
    }

    let table = $('#ciudades-table').DataTable(
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
            ajax: '/admin/admin.ubicaciones.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'id', name: 'id'},
                { data: 'pais', name: 'pais'},
                { data: 'departamento', name: 'departamento'},
                { data: 'ciudad', name: 'ciudad',className:'text-center'},
                { data: 'acciones', name: 'acciones', className: 'exclude'},
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
        'pais_id',
        'departamento_id',
        'ciudad'
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

function showCustomCiudad(btn) {
    $.get("/admin/admin.ubicaciones.edit/" + btn, (response) => {
        const usr = response.data;

        // Mapear los campos del formulario
        const usuarioFields = [
            'nombre',
            'pais_id',
            'departamento_id',
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
                $('#ciudad').val(usr[field]);
            }
        });

        const tercero_id=$('#id').val();
        //CargarSucursales(tercero_id);
        //CargarContactos(tercero_id);
    });
}

//Registrar usuario
function regCiudad() {
    myModal.show()
    $('#exampleModalLabel').html('Registrar Ciudad');

    // LIMPIAR CAMPOS
    cleanInput();
     // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
     let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerCiudad()"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';

    $(".modal-footer").html(r);

}

function registerCiudad() {

    $('#spinnerRegister').addClass('d-none');
    $('#spinnerRegister').removeClass('d-block');

    const route = "/admin/admin.ubicaciones.store";

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
    ajax_data.append('pais_id', $('#pais_id').val());
    ajax_data.append('departamento_id', $('#departamento_id').val());
    ajax_data.append('nombre', $('#ciudad').val());
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
            $('#ModalCiudad').modal('toggle');
            toastr.warning(arr.error);
        }
    });
}

// Actualizar usuario
function upCiudad(btn) {
    myModal.show()
    $('#exampleModalLabel').html('Editar Vendedor');
    // LIMPIAR CAMPOS
    cleanInput();
    showCustomCiudad(btn);
    // FIN LIMPIAR CAMPOS
    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button id="editar" class="btn btn-primary" onclick="updateCiudad(' + btn + ')">Guardar</button>';
    $(".modal-footer").html(u);
}

function updateCiudad(btn) {
    const route = `/admin/admin.ubicaciones.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Recorrer los campos y agregarlos al FormData
    // fields.forEach(field => {
    //     if (field === 'active') {
    //         // Manejar checkbox (true o false)
    //         ajax_data.append(field, $('#' + field).is(':checked') ? 1 : 0);
    //     } else {
    //         ajax_data.append(field, $('#' + field).val());
    //     }
    // });
    let activo=$('#active').is(':checked')?1:0;
    $('#active').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    ajax_data.append('pais_id', $('#pais_id').val());
    ajax_data.append('departamento_id', $('#departamento_id').val());
    ajax_data.append('nombre', $('#ciudad').val());
    ajax_data.append('active', activo);

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
            $('#modalCiudad').data('bs.modal')._config.backdrop = 'static';
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
        'pais_id',
        'departamento_id',
        'ciudad'
    ];

    fields.forEach(field => {
        $('#error_' + field).text('');
    });
}

function openModalPaisDpto(e){

    $(document).on('click', '#btnCrearPais', function() {
        $('#ModalPaisDpto').modal('show');
      });


    $('#modalLabelPaisDpto').html('Registrar País o Departamento');

    let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
    '<button type="button" class="btn btn-primary" onclick="registerPais()"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';
    $("#modal_footer").html(r);
}

function registerPais(){
     const route = "/admin/admin.ubicaciones.storepais";


    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    // Agregar los nuevos campos del formulario
    ajax_data.append('pais_id', $('#input_pais_id').val());

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
        getPais();
        myModal.toggle(); // Reemplaza con tu lógica de modal
        toastr.success(response.message); // Muestra el mensaje de éxito

    }).catch(e => {
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
            $('#ModalPaisDpto').modal('toggle');
            toastr.warning(arr.error);
        }
    });
}

function getPais(){
    $.get("/admin/admin.ubicaciones.paises", (response) => {
        const pais = response.data;

        $('#input_pais_id').empty();
        $('#input_pais_id').append('<option value="">Seleccione un país</option>');
        $.each(pais, function (value) {
            $('#input_pais_id').append('<option value="'+ value.id +'">'+ value.nombre +'</option>');
        });

    });
}
