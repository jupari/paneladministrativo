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


    // Escucha el evento de cierre de la ventana modal
    $('#myModal').on('hidden.bs.modal', function () {
        // Activa la primera pestaña al cerrar la ventana modal
        $('#custom-content-below-home-tab').tab('show');
    });


     //carga de la datatable
    Cargar();

    $('#addContactoBtn').click(() => { registerContacto(); });
    $('#addSucursalBtn').click(() => { registerSucursal(); });


    let dptos;
    // Evento al cambiar país
    $('#pais_id').change(function () {
        let pais_id = $(this).val();
        $('#ciudad_id').val('').change();
        cargarDepartamentos(pais_id);
    });

    // Evento al cambiar departamento
    $('#departamento_id').change(function () {
        let departamento_id = $(this).val();
        cargarCiudades(departamento_id);
    });


    $('#sucursal_pais_id').change(function(){
        let pais_id = $(this).val();
        dataPaises.forEach((p)=>{
            if(p.id==pais_id){
                dptos = p;
            }

        });
        $('#sucursal_departamento_id').empty();
        $('#sucursal_departamento_id').append('<option value="">Seleccione un departamento</option>');
        $.each(dptos.departamentos, function (index, value) {
            $('#sucursal_departamento_id').append('<option value="'+ value.id +'">'+ value.nombre +'</option>');
        });
    });

    $('#sucursal_departamento_id').change(function(){
        let departamento_id = $(this).val();
        let dpto;

        if(dptos.departamentos){
            dptos.departamentos.forEach((p)=>{
                if(p.id==departamento_id){
                    dpto = p;
                }

            });
            $('#sucursal_ciudad_id').empty();
            $('#sucursal_ciudad_id').append('<option value="">Seleccione una ciudad</option>');
            $.each(dpto.ciudades, function (index, value) {
                $('#sucursal_ciudad_id').append('<option value="'+ value.id +'">'+ value.nombre +'</option>');
            });
        }
    });

});

//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalProveedor'), {
    keyboard: false
})

function Cargar() {
    if ($.fn.DataTable.isDataTable('#proveedores-table')) {
        $('#proveedores-table').DataTable().destroy();
    }
    let table = $('#proveedores-table').DataTable(
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
            ajax: '/admin/admin.clientes.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'tipoid', name: 'tipoid'},
                { data: 'identificacion', name: 'identificacion'},
                { data: 'tipopersona', name: 'tipopersona'},
                { data: 'nombres', name: 'nombres'},
                { data: 'apellidos', name: 'apellidos'},
                { data: 'nombre_estableciemiento', name: 'nombre_estableciemiento'},
                { data: 'correo', name: 'correo'},
                { data: 'telefono', name: 'telefono'},
                { data: 'celular', name: 'celular'},
                { data: 'created_at', name: 'created_at'},
                //{ data: 'active', name: 'active',className:'text-center'},
                { data: 'acciones', name: 'acciones', className: 'exclude'},
            ],
            order: [[1, "asc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        }
    );
    // table.ajax.reload();
}

function CargarSucursales(id) {
    if ($.fn.DataTable.isDataTable('#sucursales-table')) {
        $('#sucursales-table').DataTable().destroy();
    }
    let table = $('#sucursales-table').DataTable(
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
            ajax: '/admin/admin.sucursales.index/'+id,
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'comercial', name: 'comercial'},
                { data: 'nombre_sucursal', name: 'nombre_sucursal'},
                { data: 'correo', name: 'correo'},
                { data: 'telefono', name: 'telefono'},
                { data: 'celular', name: 'celular'},
                { data: 'ciudad', name: 'ciudad'},
                { data: 'direccion', name: 'direccion'},
                { data: 'persona_contacto', name: 'persona_contacto'},
                //{ data: 'active', name: 'active',className:'text-center'},
                { data: 'acciones', name: 'acciones', className: 'exclude'},
            ],
            order: [[1, "asc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        }
    );
    // table.ajax.reload();
}

function CargarContactos(id) {
    if ($.fn.DataTable.isDataTable('#contactos-table')) {
        $('#contactos-table').DataTable().destroy();
    }
    let table = $('#contactos-table').DataTable(
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
            ajax: '/admin/admin.contactos.index/'+id,
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'nombres', name: 'nombres'},
                { data: 'apellidos', name: 'apellidos'},
                { data: 'cargo', name: 'cargo'},
                { data: 'correo', name: 'correo'},
                { data: 'telefono', name: 'telefono'},
                { data: 'celular', name: 'celular'},
                //{ data: 'active', name: 'active',className:'text-center'},
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
        'tipoidentificacion_id',
        'identificacion',
        'dv',
        'tipopersona_id',
        'nombres',
        'apellidos',
        'nombre_establecimiento',
        'telefono',
        'celular',
        'correo',
        'correo_fe',
        'ciudad_id',
        'direccion',
        'vendedor_id',
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

function cleanInputSucursal(btn) {

    const bool = (btn == null) ? false : true;

    // Campos del formulario actual
    const fields = [
        'nombre_sucursal',
        'vendedor_id',
        'telefono',
        'celular',
        'correo',
        'ciudad_id',
        'sucursal_departamento_id',
        'sucursal_pais_id',
        'direccion',
        'persona_contacto',
        'id'
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#sucursal_' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

function cleanInputContacto(btn) {

    const bool = (btn == null) ? false : true;

    // Campos del formulario actual
    const fields = [
        'nombres',
        'apellidos',
        'telefono',
        'celular',
        'correo',
        'ext',
        'cargo'
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#contacto_' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

function showCustomUser(btn) {
    $.get("/admin/admin.proveedores.edit/" + btn, (response) => {
        const usr = response.data;

        // Mapear los campos del formulario
        const usuarioFields = [
            'tercerotipo_id',
            'tipoidentificacion_id',
            'identificacion',
            'dv',
            'tipopersona_id',
            'nombres',
            'apellidos',
            'nombre_establecimiento',
            'telefono',
            'celular',
            'correo',
            'correo_fe',
            'ciudad_id',
            'direccion',
            'vendedor_id',
            'id'
        ];

        usuarioFields.forEach(field => {
            if (field === 'cta_ppal') {
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

        const tercero_id=$('#id').val();
        CargarSucursales(tercero_id);
        CargarContactos(tercero_id);
        actualizarValidaciones();

        let ciudadSeleccionada = usr['ciudad_id']; // ID de la ciudad guardada

        if (ciudadSeleccionada) {
            let { pais, departamento } = obtenerPaisYDepartamento(ciudadSeleccionada);

            if (pais) {
                $('#pais_id').val(pais);
                cargarDepartamentos(pais, departamento,ciudadSeleccionada);
            }
        }
    });
}

//Registrar usuario
function regProv() {
    $('#ModalProveedor').modal('show');
    $('#exampleModalLabel').html('Registrar Proveedor');

    // LIMPIAR CAMPOS
    cleanInput();
     // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
    cleanInputSucursal();
    //Se limpia el contacto_id
    $('#contacto_id').val('');
    $('#sucursal_id').val('');
    CargarSucursales(0);
    CargarContactos(0);
    let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerCli()"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';

    $(".modal-footer").html(r);

}

function registerProv() {

    $('#spinnerRegister').addClass('d-none');
    $('#spinnerRegister').removeClass('d-block');

    const route = "/admin/admin.proveedores.store";

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    // Agregar los nuevos campos del formulario
    ajax_data.append('tercerotipo_id', $('#tercerotipo_id').val());
    ajax_data.append('tipoidentificacion_id', $('#tipoidentificacion_id').val());
    ajax_data.append('identificacion', $('#identificacion').val());
    ajax_data.append('dv', $('#dv').val());
    ajax_data.append('tipopersona_id', $('#tipopersona_id').val());
    ajax_data.append('nombres', $('#nombres').val());
    ajax_data.append('apellidos', $('#apellidos').val());
    ajax_data.append('nombre_establecimiento', $('#nombre_establecimiento').val());
    ajax_data.append('telefono', $('#telefono').val());
    ajax_data.append('celular', $('#celular').val());
    ajax_data.append('correo', $('#correo').val());
    ajax_data.append('correo_fe', $('#correo_fe').val());
    ajax_data.append('ciudad_id', $('#ciudad_id').val());
    ajax_data.append('direccion', $('#direccion').val());
    ajax_data.append('vendedor_id', obtenerVendedor());
    ajax_data.append('user_id', $('#user_id').val());

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
        $('#id').val(response.data.id);
        $('#spinnerRegister').addClass('d-none');
        $('#spinnerRegister').removeClass('d-block');
        Cargar();
        //myModal.toggle(); // Reemplaza con tu lógica de modal
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
            $('#ModalProveedor').modal('toggle');
            toastr.warning(arr.error);
        }
    });
}

// Actualizar usuario
function upProv(btn) {
    myModal.show()
    $('#exampleModalLabel').html('Editar Proveedor');
    // LIMPIAR CAMPOS
    cleanInput();
    cleanInputSucursal();
    cleanInputContacto();
    limpiarValidaciones();
    limpiarValidacionesSucursal();
    limpiarValidacionesContacto();
    $('#contacto_id').val('');
    $('#sucursal_id').val('');
    showCustomUser(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button id="editar" class="btn btn-primary" onclick="updateProv(' + btn + ')">Guardar</button>';
    $(".modal-footer").html(u);

}

function updateProv(btn) {
    const route = `/admin/admin.proveedores.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Lista de campos del formulario
    const fields = [
        'tercerotipo_id',
        'tipoidentificacion_id',
        'identificacion',
        'dv',
        'tipopersona_id',
        'nombres',
        'apellidos',
        'nombre_establecimiento',
        'telefono',
        'celular',
        'correo',
        'correo_fe',
        'ciudad_id',
        'direccion',
        'vendedor_id',
        'user_id'
    ];

    // Recorrer los campos y agregarlos al FormData
    fields.forEach(field => {
        if (field === 'cta_ppal') {
            // Manejar checkbox (true o false)
            ajax_data.append(field, $('#' + field).is(':checked') ? 1 : 0);
        } else {
            if(field === 'vendedor_id'){
                ajax_data.append(field, obtenerVendedor());
            }else{
                ajax_data.append(field, $('#' + field).val());
            }
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
        contentType: false, // Importante para enviar FormData
        processData: false,
    })
    .then(response => {
        // Acción después de una respuesta exitosa
        Cargar(); // Reemplaza con tu función para recargar la lista o tabla
        myModal.toggle(); // Cierra el modal
        toastr.success(response.message); // Mensaje de éxito
    })
    .catch(e => {
        // Manejo de errores
        limpiarValidaciones(); // Limpia errores previos
        limpiarValidacionesContacto();
        limpiarValidacionesSucursal();
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
            // Errores de permisos
            myModal.toggle();
            toastr.warning(arr.message);
        }
    });
}

function limpiarValidaciones() {
    const fields = [
        'tipoidentificacion_id',
        'identificacion',
        'dv',
        'tipopersona_id',
        'nombres',
        'apellidos',
        'nombre_establecimiento',
        'telefono',
        'celular',
        'correo',
        'correo_fe',
        'ciudad_id',
        'direccion',
        'vendedor',
    ];

    // Limpiar los mensajes de error para cada campo
    fields.forEach(field => {
        $('#error_' + field).text(''); // Asume que los errores tienen el formato 'error_<campo>'
    });
}

function limpiarValidacionesSucursal() {
    const fields = [
        'nombres',
        'apellidos',
        'telefono',
        'celular',
        'cargo',
        'ext',
    ];

    // Limpiar los mensajes de error para cada campo
    fields.forEach(field => {
        $('#error_sucursal_' + field).text(''); // Asume que los errores tienen el formato 'error_<campo>'
    });
}

function limpiarValidacionesContacto() {
    const fields = [
        'nombres',
        'apellidos',
        'telefono',
        'celular',
        'cargo',
        'ext',
    ];

    // Limpiar los mensajes de error para cada campo
    fields.forEach(field => {
        $('#error_contacto_' + field).text(''); // Asume que los errores tienen el formato 'error_<campo>'
    });
}

function registerSucursal(){

    $('#spinnerRegisterSucursal').addClass('d-none');
    $('#spinnerRegisterSucursal').removeClass('d-block');

    tercero_id=$('#id').val();
    sucursal_id=$('#sucursal_id').val();
    if(tercero_id==''){
        registerCli();
        saveSucursal();
    }else if(tercero_id!='' && sucursal_id==''){
        saveSucursal();
    }else if(tercero_id!='' && sucursal_id!=''){
        updateSucursal(sucursal_id);
    }
}

function registerContacto(){

    $('#spinnerRegisterContacto').addClass('d-none');
    $('#spinnerRegisterContacto').removeClass('d-block');

    tercero_id=$('#id').val();
    contacto_id=$('#contacto_id').val();
    if(tercero_id==''){
        registerCli();
        saveContacto();
    }else if(tercero_id!='' && contacto_id==''){
        saveContacto();
    }else if(tercero_id!='' && contacto_id!=''){
        updateContacto(contacto_id);
    }
}

function saveSucursal(){
    //crear el registro
    const route = "/admin/admin.sucursales.store";

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();
    setTimeout(() => {
        // Agregar los nuevos campos del formulario
        ajax_data.append('tercero_id', $('#id').val());
        ajax_data.append('ciudad_id', $('#sucursal_ciudad_id').val());
        ajax_data.append('vendedor_id', obtenerVendedor());
        ajax_data.append('nombre_sucursal', $('#sucursal_nombre_sucursal').val());
        ajax_data.append('celular', $('#sucursal_celular').val());
        ajax_data.append('telefono', $('#sucursal_telefono').val());
        ajax_data.append('correo', $('#sucursal_correo').val());
        ajax_data.append('direccion', $('#sucursal_direccion').val());
        ajax_data.append('persona_contacto', $('#sucursal_persona_contacto').val());

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
            $('#sucursal_id').val('');

            $('#spinnerRegisterSucursal').addClass('d-none');
            $('#spinnerRegisterSucursal').removeClass('d-block');

            cleanInputSucursal();
            const tercero_id=$('#id').val();
            CargarSucursales(tercero_id);
            limpiarValidacionesSucursal();
            //myModal.toggle(); // Reemplaza con tu lógica de modal
            //toastr.success(response.message); // Muestra el mensaje de éxito
        }).catch(e => {
            // Manejo de errores
            limpiarValidacionesSucursal();
            const arr = e.responseJSON;
            const toast = arr.errors;

            if (e.status == 422) {
                // Errores de validación
                $.each(toast, function (key, value) {
                    $('#error_sucursal_' + key).text(value[0]);
                });
                toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.'); // Muestra el mensaje de error
            } else if (e.status == 403) {
                // Errores de permisos
                $('#ModalProveedor').modal('toggle');
                toastr.warning(arr.error);
            }
        });
    }, 1000);
}

function saveContacto(){
    //crear el registro
    const route = "/admin/admin.contactos.store";

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();
    setTimeout(() => {
        // Agregar los nuevos campos del formulario
        ajax_data.append('tercero_id', $('#id').val());
        ajax_data.append('nombres', $('#contacto_nombres').val());
        ajax_data.append('apellidos', $('#contacto_apellidos').val());
        ajax_data.append('telefono', $('#contacto_telefono').val());
        ajax_data.append('celular', $('#contacto_celular').val());
        ajax_data.append('ext', $('#contacto_ext').val());
        ajax_data.append('correo', $('#contacto_correo').val());
        ajax_data.append('cargo', $('#contacto_cargo').val());

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
            $('#contacto_id').val('');

            $('#spinnerRegisterSucursal').addClass('d-none');
            $('#spinnerRegisterSucursal').removeClass('d-block');

            cleanInputContacto();
            const tercero_id=$('#id').val();
            CargarContactos(tercero_id);
            limpiarValidacionesContacto();
            //myModal.toggle(); // Reemplaza con tu lógica de modal
            //toastr.success(response.message); // Muestra el mensaje de éxito
        }).catch(e => {
            // Manejo de errores
            limpiarValidacionesContacto();
            const arr = e.responseJSON;
            const toast = arr.errors;

            if (e.status == 422) {
                // Errores de validación
                $.each(toast, function (key, value) {
                    $('#error_contacto_' + key).text(value[0]);
                });
                toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.'); // Muestra el mensaje de error
            } else if (e.status == 403) {
                // Errores de permisos
                $('#ModalProveedor').modal('toggle');
                toastr.warning(arr.error);
            }
        });
    }, 1000);
}

function showSucursal(btn){
    cleanInputSucursal();
    $.get("/admin/admin.sucursales.edit/" + btn, (response) => {
        const usr = response.data;

        // Mapear los campos del formulario
        const usuarioFields = [
            'nombre_sucursal',
            'vendedor_id',
            'telefono',
            'celular',
            'correo',
            'ciudad_id',
            'direccion',
            'persona_contacto',
            'id'
        ];

        usuarioFields.forEach(field => {
            if (field === 'cta_ppal') {
                // Configurar el checkbox
                $('#sucursal_' + field).prop('checked', usr[field] == 1 ? true : false);
            } else if (field.endsWith('_id')) {
                // Configurar el valor de los selects
                $('#sucursal_' + field).val(usr[field]).change();
            } else {
                // Configurar el valor de los campos de texto
                $('#sucursal_' + field).val(usr[field]);
            }
        });
    });
}

function showContacto(btn){
    cleanInputContacto();
    $.get("/admin/admin.contactos.edit/" + btn, (response) => {
        const usr = response.data;

        // Mapear los campos del formulario
        const usuarioFields = [
            'nombres',
            'apellidos',
            'telefono',
            'celular',
            'correo',
            'cargo',
            'ext',
            'id'
        ];

        usuarioFields.forEach(field => {
            if (field === 'cta_ppal') {
                // Configurar el checkbox
                $('#contacto_' + field).prop('checked', usr[field] == 1 ? true : false);
            } else if (field.endsWith('_id')) {
                // Configurar el valor de los selects
                $('#contacto_' + field).val(usr[field]).change();
            } else {
                // Configurar el valor de los campos de texto
                $('#contacto_' + field).val(usr[field]);
            }
        });


    });
}

function updateSucursal(btn){

    const route = `/admin/admin.sucursales.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Agregar los nuevos campos del formulario
    ajax_data.append('tercero_id', $('#id').val());
    ajax_data.append('ciudad_id', $('#sucursal_ciudad_id').val());
    ajax_data.append('vendedor_id', obtenerVendedor());
    ajax_data.append('nombre_sucursal', $('#sucursal_nombre_sucursal').val());
    ajax_data.append('celular', $('#sucursal_celular').val());
    ajax_data.append('telefono', $('#sucursal_telefono').val());
    ajax_data.append('correo', $('#sucursal_correo').val());
    ajax_data.append('direccion', $('#sucursal_direccion').val());
    ajax_data.append('persona_contacto', $('#sucursal_persona_contacto').val());

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
        contentType: false, // Importante para enviar FormData
        processData: false,
    })
    .then(response => {
        // Acción después de una respuesta exitosa
        $('#sucursal_id').val('');
        cleanInputSucursal();
        const tercero_id=$('#id').val();
        CargarSucursales(tercero_id); // Reemplaza con tu función para recargar la lista o tabla
        limpiarValidacionesSucursal();
        toastr.success(response.message); // Mensaje de éxito
    })
    .catch(e => {
        // Manejo de errores
        limpiarValidacionesSucursal();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status === 422) {
            // Errores de validación
            $('#myModal').data('bs.modal')._config.backdrop = 'static';
            $.each(toast, function(key, value) {
                $('#error_sucursal_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');  // Muestra el mensaje de error
        } else if (e.status === 403) {
            // Errores de permisos
            myModal.toggle();
            toastr.warning(arr.message);
        }
    });
}

function updateContacto(btn){

    const route = `/admin/admin.contactos.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

     // Agregar los nuevos campos del formulario
     ajax_data.append('tercero_id', $('#id').val());
     ajax_data.append('nombres', $('#contacto_nombres').val());
     ajax_data.append('apellidos', $('#contacto_apellidos').val());
     ajax_data.append('telefono', $('#contacto_telefono').val());
     ajax_data.append('celular', $('#contacto_celular').val());
     ajax_data.append('ext', $('#contacto_ext').val());
     ajax_data.append('correo', $('#contacto_correo').val());
     ajax_data.append('cargo', $('#contacto_cargo').val());

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
        contentType: false, // Importante para enviar FormData
        processData: false,
    })
    .then(response => {
        // Acción después de una respuesta exitosa
        $('#contacto_id').val('');
        cleanInputContacto();
        const tercero_id=$('#id').val();
        CargarContactos(tercero_id); // Reemplaza con tu función para recargar la lista o tabla
        limpiarValidacionesContacto();
        toastr.success(response.message); // Mensaje de éxito
    })
    .catch(e => {
        // Manejo de errores
        limpiarValidacionesContacto();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status === 422) {
            // Errores de validación
            $('#myModal').data('bs.modal')._config.backdrop = 'static';
            $.each(toast, function(key, value) {
                $('#error_contacto_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');  // Muestra el mensaje de error
        } else if (e.status === 403) {
            // Errores de permisos
            myModal.toggle();
            toastr.warning(arr.message);
        }
    });
}

function deleteSucursal(id){

    Swal.fire({
                title: "¿Desea quitar este registro?",
                text: "El registro eliminado no se puede volver a recuperar",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí",
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
            }).then((result) => {
                console.log('<<',result.value);
                if (result.value==true) {
                    $.ajax({
                        url: "/admin/admin.sucursales.destroy/" + id,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        method: 'DELETE',
                        dataType: 'json',
                    }).then(response => {
                          if(response.Ok){
                            Swal.fire({
                                title: "Borrado",
                                text: response.message,
                                icon: "success"
                            });
                            cleanInputSucursal();
                            const tercero_id=$('#id').val();
                            CargarSucursales(tercero_id);
                        }
                    }).catch(e => {
                        const arr = e.error;
                        if (e.status == 403) {
                            Swal.fire("Cancelado", arr, "error");
                        }
                    });
                }
            });
}

function deleteContacto(id){

    Swal.fire({
                title: "¿Desea quitar este registro?",
                text: "El registro eliminado no se puede volver a recuperar",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí",
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
            }).then((result) => {
                if (result.value==true) {
                    $.ajax({
                        url: "/admin/admin.contactos.destroy/" + id,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        method: 'DELETE',
                        dataType: 'json',
                    }).then(response => {
                          if(response.Ok){
                            Swal.fire({
                                title: "Borrado",
                                text: response.message,
                                icon: "success"
                            });
                            cleanInputContacto();
                            const tercero_id=$('#id').val();
                            CargarContactos(tercero_id);
                        }
                    }).catch(e => {
                        const arr = e.error;
                        if (e.status == 403) {
                            Swal.fire("Cancelado", arr, "error");
                        }
                    });
                }
            });
}

function actualizarValidaciones()
{
    const tipoPersonaSelect =  document.getElementById('tipopersona_id');
    const nombreEstablecimientoGroup = document.getElementById("nombre_establecimiento_group");
    const nombresGroup = document.getElementById("nombres_group");
    const apellidosGroup = document.getElementById("apellidos_group");
    const nombreEstablecimientoInput = document.getElementById("nombre_establecimiento");
    const nombresInput = document.getElementById("nombres");
    const apellidosInput = document.getElementById("apellidos");

    let tipoPersonaSeleccionado = tipoPersonaSelect.options[tipoPersonaSelect.selectedIndex].text.toLowerCase();

    if (tipoPersonaSeleccionado.includes("jurídica")) {
        // Mostrar campo "Nombre del Establecimiento" y hacerlo obligatorio
        nombreEstablecimientoGroup.style.display = "block";
        nombreEstablecimientoInput.setAttribute("required", "required");

        // Ocultar "Nombres" y "Apellidos" y quitar obligatoriedad
        nombresGroup.style.display = "none";
        apellidosGroup.style.display = "none";
        nombresInput.removeAttribute("required");
        apellidosInput.removeAttribute("required");
    } else {
        // Mostrar "Nombres" y "Apellidos" y hacerlos obligatorios
        nombresGroup.style.display = "block";
        apellidosGroup.style.display = "block";
        nombresInput.setAttribute("required", "required");
        apellidosInput.setAttribute("required", "required");

        // Ocultar "Nombre del Establecimiento" y quitar obligatoriedad
        nombreEstablecimientoGroup.style.display = "none";
        nombreEstablecimientoInput.removeAttribute("required");
    }

}

function obtenerVendedor() {
    let vendedorSelect = document.getElementById("vendedor_id");
    let vendedorHidden = document.getElementById("vendedor_hidden");

    if (vendedorSelect) {
        if(vendedorSelect.disabled)
        {
            return vendedorHidden.value
        }else{
            return vendedorSelect.value;
        }
    }else{
        return vendedorHidden.value;
    }
}

function obtenerPaisYDepartamento(ciudadId) {
    let paisEncontrado = null;
    let departamentoEncontrado = null;

    // Buscar en los datos cargados
    dataPaises.forEach((pais) => {
        pais.departamentos.forEach((departamento) => {
            let ciudad = departamento.ciudades.find((c) => c.id == ciudadId);
            if (ciudad) {
                paisEncontrado = pais.id;
                departamentoEncontrado = departamento.id;
            }
        });
    });

    return { pais: paisEncontrado, departamento: departamentoEncontrado };
}

// Función para cargar departamentos según el país seleccionado
function cargarDepartamentos(pais_id, selectedDepartamento = null,ciudadSeleccionada=null) {
    let paisSeleccionado = dataPaises.find(p => p.id == pais_id);
    if (!paisSeleccionado) return;

    dptos = paisSeleccionado;
    $('#departamento_id').empty().append('<option value="">Seleccione un departamento</option>');

    dptos.departamentos.forEach((dep) => {
        let selected = selectedDepartamento && selectedDepartamento == dep.id ? 'selected' : '';
        $('#departamento_id').append(`<option value="${dep.id}" ${selected}>${dep.nombre}</option>`);
    });

    if (selectedDepartamento) {
        cargarCiudades(selectedDepartamento, ciudadSeleccionada);
    }
}

// Función para cargar ciudades según el departamento seleccionado
function cargarCiudades(departamento_id, selectedCiudad = null) {
    let departamentoSeleccionado = dptos.departamentos.find(dep => dep.id == departamento_id);
    if (!departamentoSeleccionado) return;
    $('#ciudad_id').empty().append('<option value="">Seleccione una ciudad</option>');
    departamentoSeleccionado.ciudades.forEach((ciudad) => {
        $('#ciudad_id').append(`<option value="${ciudad.id}">${ciudad.nombre}</option>`);
    });
    $('#ciudad_id').val(selectedCiudad).change();
}

