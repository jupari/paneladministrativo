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
    $('#ModalCliente').on('hidden.bs.modal', function () {
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

//se declara la variable del modal - Bootstrap 4.6 compatible
var myModal = $('#ModalCliente');

function Cargar() {
    if ($.fn.DataTable.isDataTable('#clientes-table')) {
        $('#clientes-table').DataTable().destroy();
    }
    let table = $('#clientes-table').DataTable(
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

    console.log('🧹 cleanInput() iniciado');

    // PRESERVAR valores críticos antes de limpiar
    const tercerotipo_preserved = $('#tercerotipo_id').val();
    const user_preserved = $('#user_id').val();

    console.log('🔒 PRESERVANDO valores críticos:', {
        tercerotipo_id: tercerotipo_preserved,
        user_id: user_preserved,
        tercerotipo_existe: $('#tercerotipo_id').length > 0,
        user_existe: $('#user_id').length > 0
    });

    // Campos del formulario actual (SIN tercerotipo_id ni user_id)
    const fields = [
        'id',
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

    console.log('🧽 Campos limpiados, procediendo a restaurar valores críticos...');

    // RESTAURAR valores críticos
    $('#tercerotipo_id').val(tercerotipo_preserved);
    $('#user_id').val(user_preserved);

    const restaurado = {
        tercerotipo_id: $('#tercerotipo_id').val(),
        user_id: $('#user_id').val()
    };

    console.log('🔓 RESTAURADOS valores críticos:', restaurado);

    // Verificar que la restauración fue exitosa
    if (restaurado.tercerotipo_id !== tercerotipo_preserved) {
        console.warn('⚠️ ADVERTENCIA: tercerotipo_id no se restauró correctamente');
        console.warn('   Esperado:', tercerotipo_preserved, 'Obtenido:', restaurado.tercerotipo_id);
    }

    if (restaurado.user_id !== user_preserved) {
        console.warn('⚠️ ADVERTENCIA: user_id no se restauró correctamente');
        console.warn('   Esperado:', user_preserved, 'Obtenido:', restaurado.user_id);
    }

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);

    console.log('✅ cleanInput() completado');
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
    $.get("/admin/admin.clientes.edit/" + btn, (response) => {
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
            'id',
            'user_id'
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

        // ⭐ MAPEAR CAMPOS ESPECIALES que vienen en la respuesta separadamente
        if (response.user_id) {
            $('#user_id').val(response.user_id);
            console.log('✅ user_id cargado desde respuesta del servidor:', response.user_id);
        }

        if (response.tercerotipo_id) {
            $('#tercerotipo_id').val(response.tercerotipo_id);
            console.log('✅ tercerotipo_id cargado desde respuesta del servidor:', response.tercerotipo_id);
        }

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
function regCli() {
    console.log('🆕 regCli() - Abriendo modal para crear cliente');

    try {
        // Desmarcar modo de edición para creación
        $('#ModalCliente').removeData('edit-mode');
        console.log('🔄 Modo de edición desmarcado para CREACIÓN');

        // Método directo y simple para abrir el modal PRIMERO
        console.log('🔄 Abriendo modal...');
        $('#ModalCliente').modal('show');
        console.log('✅ Modal abierto');

        $('#exampleModalLabel').html('<i class="fas fa-user-plus mr-2"></i>Registrar Cliente');

        // VERIFICAR valores iniciales ANTES de cualquier limpieza
        console.log('📊 Valores ANTES de limpiar:');
        console.log('   - tercerotipo_id:', $('#tercerotipo_id').val());
        console.log('   - user_id:', $('#user_id').val());

        // ASEGURAR valores críticos ANTES de cualquier limpieza
        let tercerotipo_inicial = $('#tercerotipo_id').val();
        let user_inicial = $('#user_id').val();

        if (!tercerotipo_inicial || tercerotipo_inicial === '') {
            tercerotipo_inicial = '2';
            $('#tercerotipo_id').val(tercerotipo_inicial);
            console.log('🔧 ESTABLECIDO tercerotipo_id = 1');
        }

        if (!user_inicial || user_inicial === '') {
            // Intentar obtener user_id desde alguna variable global o usar un valor por defecto
            const userFromGlobal = window.permisos && window.permisos.id ? window.permisos.id : null;
            user_inicial = userFromGlobal || '1'; // Valor por defecto
            $('#user_id').val(user_inicial);
            console.log('🔧 ESTABLECIDO user_id =', user_inicial);
        }

        console.log('💾 Valores críticos asegurados:', {
            tercerotipo_id: tercerotipo_inicial,
            user_id: user_inicial
        });

        // LIMPIAR CAMPOS - pero preservando los críticos
        cleanInput();
         // FIN LIMPIAR CAMPOS
        limpiarValidaciones();
        cleanInputSucursal();
        //Se limpia el contacto_id
        $('#contacto_id').val('');
        $('#sucursal_id').val('');
        CargarSucursales(0);
        CargarContactos(0);

        // VERIFICAR Y RESTAURAR si es necesario
        if (!$('#tercerotipo_id').val() || $('#tercerotipo_id').val() === '') {
            $('#tercerotipo_id').val(tercerotipo_inicial);
            console.log('🔧 RESTAURADO tercerotipo_id =', tercerotipo_inicial);
        }

        if (!$('#user_id').val() || $('#user_id').val() === '') {
            $('#user_id').val(user_inicial);
            console.log('🔧 RESTAURADO user_id =', user_inicial);
        }

        // VERIFICAR valores DESPUÉS de la limpieza
        console.log('📊 Valores DESPUÉS de limpiar:');
        console.log('   - tercerotipo_id:', $('#tercerotipo_id').val());
        console.log('   - user_id:', $('#user_id').val());

        // RESETEAR el sistema de navegación por pasos PERO preservando campos críticos
        if (typeof resetModal === 'function') {
            console.log('🔄 Ejecutando resetModal...');
            resetModal();

            // Asegurar nuevamente los valores después del reset
            if (!$('#tercerotipo_id').val()) {
                $('#tercerotipo_id').val(tercerotipo_inicial);
                console.log('🔧 POST-RESET: Restaurado tercerotipo_id');
            }
            if (!$('#user_id').val()) {
                $('#user_id').val(user_inicial);
                console.log('🔧 POST-RESET: Restaurado user_id');
            }
        }

        // VERIFICACIÓN FINAL
        console.log('🔍 VERIFICACIÓN FINAL:');
        console.log('   - tercerotipo_id:', $('#tercerotipo_id').val());
        console.log('   - user_id:', $('#user_id').val());

    } catch (error) {
        console.error('❌ Error al abrir modal:', error);
        alert('Error al abrir el modal. Verifique la consola para más detalles.');
    }
}

function registerCli() {

    $('#spinnerRegister').addClass('d-none');
    $('#spinnerRegister').removeClass('d-block');

    // Detectar si está en modo edición o creación
    const clienteId = $('#id').val();
    const isEditMode = clienteId && clienteId !== '' && clienteId !== '0';

    console.log('🔍 Detectando modo:', {
        clienteId: clienteId,
        isEditMode: isEditMode,
        modalEditMode: $('#ModalCliente').data('edit-mode')
    });

    // Usar la ruta correcta según el modo
    const route = isEditMode ? `/admin/admin.clientes.update/${clienteId}` : "/admin/admin.clientes.store";
    const method = isEditMode ? 'POST' : 'POST';

    console.log('🔗 Usando ruta:', route);

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    // Limpiar números de teléfono (solo dígitos)
    const telefonoLimpio = $('#telefono').val().replace(/[^\d]/g, '');
    const celularLimpio = $('#celular').val().replace(/[^\d]/g, '');

    // Agregar los nuevos campos del formulario
    ajax_data.append('tercerotipo_id', $('#tercerotipo_id').val());
    ajax_data.append('tipoidentificacion_id', $('#tipoidentificacion_id').val());
    ajax_data.append('identificacion', $('#identificacion').val());
    ajax_data.append('dv', $('#dv').val());
    ajax_data.append('tipopersona_id', $('#tipopersona_id').val());
    ajax_data.append('nombres', $('#nombres').val());
    ajax_data.append('apellidos', $('#apellidos').val());
    ajax_data.append('nombre_establecimiento', $('#nombre_establecimiento').val());
    ajax_data.append('telefono', telefonoLimpio);
    ajax_data.append('celular', celularLimpio);
    ajax_data.append('correo', $('#correo').val());
    ajax_data.append('correo_fe', $('#correo_fe').val());
    ajax_data.append('ciudad_id', $('#ciudad_id').val());
    ajax_data.append('direccion', $('#direccion').val());
    ajax_data.append('vendedor_id', obtenerVendedor());
    ajax_data.append('user_id', $('#user_id').val());

    // Configurar headers según el modo
    const headers = { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
    if (isEditMode) {
        headers['X-HTTP-Method-Override'] = 'POST';
    }

    // Realizar la solicitud AJAX
    $.ajax({
        url: route,
        headers: headers,
        type: method,
        dataType: 'json',
        data: ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMÁGENES O ARCHIVOS POR AJAX
        processData: false,
    }).then(response => {
        $('#id').val(response.data.id);
        $('#spinnerRegister').addClass('d-none');
        $('#spinnerRegister').removeClass('d-block');
        Cargar();
        //myModal.modal('toggle'); // Reemplaza con tu lógica de modal
        toastr.success(response.message); // Muestra el mensaje de éxito

    }).catch(e => {
        // Manejo de errores
        console.error('❌ Error en registerCli():', e);
        limpiarValidaciones(); // Reemplaza con tu función de limpieza de validaciones
        
        // Verificar si existe la respuesta JSON
        if (!e.responseJSON) {
            console.error('❌ No hay responseJSON en la respuesta');
            toastr.error('Error de conexión o servidor');
            return;
        }
        
        const arr = e.responseJSON;
        console.log('🔍 Respuesta del servidor:', arr);

        if (e.status == 422) {
            // Errores de validación
            if (arr.errors) {
                console.log('🔍 Errores de validación encontrados:', arr.errors);
                
                // Verificar qué elementos de error existen en el DOM
                const errorElements = [];
                $.each(arr.errors, function (key, value) {
                    const errorElement = $('#error_' + key);
                    errorElements.push({
                        field: key,
                        error: value[0],
                        elementExists: errorElement.length > 0,
                        elementVisible: errorElement.is(':visible'),
                        currentText: errorElement.text()
                    });
                    
                    console.log(`🔍 Campo '${key}':`, {
                        error: value[0],
                        element: errorElement,
                        exists: errorElement.length > 0,
                        visible: errorElement.is(':visible')
                    });
                    
                    if (errorElement.length > 0) {
                        errorElement.text(value[0]);
                        errorElement.show(); // Asegurar que sea visible
                        // Para campos hidden, también mostrar en console
                        if (key === 'tercerotipo_id' || key === 'user_id') {
                            console.log(`🔍 Error en campo hidden '${key}':`, value[0]);
                        }
                    } else {
                        console.warn(`⚠️ Elemento #error_${key} no encontrado en el DOM`);
                    }
                });
                
                console.table(errorElements);
                toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
            } else {
                console.error('❌ No hay errores en la respuesta 422');
                toastr.error(arr.message || 'Error de validación');
            }
        } else if (e.status == 403) {
            // Errores de permisos
            $('#ModalCliente').modal('toggle');
            toastr.warning(arr.error || 'No tienes permisos para realizar esta acción');
        } else {
            // Otros errores
            console.error(`❌ Error ${e.status}:`, arr);
            toastr.error(arr.message || 'Error inesperado del servidor');
        }
    });
}

// Actualizar usuario
function upCli(btn) {
    console.log('✏️ upCli() - Abriendo modal para editar cliente:', btn);

    // Marcar que está en modo edición
    $('#ModalCliente').data('edit-mode', true);
    console.log('📝 Modo de edición MARCADO para cliente ID:', btn);

    // Usar la nueva función compatible para editar
    if (window.openEditClientModal) {
        window.openEditClientModal(btn);
    } else {
        // Fallback al método original
        myModal.modal('show');
    }

    $('#exampleModalLabel').html('Editar Cliente');
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

    let u = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '<button id="editar" class="btn btn-primary" onclick="updateCli(' + btn + ')">Guardar</button>';
    $(".modal-footer").html(u);

}

function updateCli(btn) {
    const route = `/admin/admin.clientes.update/${btn}`;

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
        myModal.modal('hide'); // Cierra el modal
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
            // Bootstrap 4 - backdrop configurado en modal show
            $.each(toast, function(key, value) {
                $('#error_' + key).text(value[0]);
            });
        toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            // Errores de permisos
            myModal.modal('toggle');
            toastr.warning(arr.message);
        }
    });
}

function limpiarValidaciones() {
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

    // Limpiar los mensajes de error para cada campo
    fields.forEach(field => {
        $('#error_' + field).text('').hide(); // Limpiar texto y ocultar elemento
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

    $('#spinnerRegisterContacto').removeClass('d-none');
    $('#spinnerRegisterContacto').addClass('d-block');

    tercero_id=$('#id').val();
    contacto_id=$('#contacto_id').val();

    if(tercero_id==''){
        // Primero crear el cliente, luego el contacto
        registerCliForContacto();
    }else if(tercero_id!='' && contacto_id==''){
        saveContacto();
    }else if(tercero_id!='' && contacto_id!=''){
        updateContacto(contacto_id);
    }
}

// Nueva función para registrar cliente cuando se crea un contacto
function registerCliForContacto() {
    const route = "/admin/admin.clientes.store";

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    // Limpiar números de teléfono (solo dígitos)
    const telefonoLimpio = $('#telefono').val().replace(/[^\d]/g, '');
    const celularLimpio = $('#celular').val().replace(/[^\d]/g, '');

    // Agregar los nuevos campos del formulario
    ajax_data.append('tercerotipo_id', $('#tercerotipo_id').val());
    ajax_data.append('tipoidentificacion_id', $('#tipoidentificacion_id').val());
    ajax_data.append('identificacion', $('#identificacion').val());
    ajax_data.append('dv', $('#dv').val());
    ajax_data.append('tipopersona_id', $('#tipopersona_id').val());
    ajax_data.append('nombres', $('#nombres').val());
    ajax_data.append('apellidos', $('#apellidos').val());
    ajax_data.append('nombre_establecimiento', $('#nombre_establecimiento').val());
    ajax_data.append('telefono', telefonoLimpio);
    ajax_data.append('celular', celularLimpio);
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
        contentType: false,
        processData: false,
    }).then(response => {
        $('#id').val(response.data.id);
        // Una vez creado el cliente, crear el contacto
        saveContacto();
        toastr.success('Cliente creado exitosamente');

    }).catch(e => {
        $('#spinnerRegisterContacto').addClass('d-none');
        $('#spinnerRegisterContacto').removeClass('d-block');

        limpiarValidaciones();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {
            $.each(toast, function (key, value) {
                $('#error_' + key).text(value[0]);
            });
            toastr.error('Por favor corrija los errores en el formulario');
        } else {
            console.log(e.responseJSON);
            toastr.error('Error al crear el cliente');
        }
    });
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
            //myModal.modal('toggle'); // Reemplaza con tu lógica de modal
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
                $('#ModalCliente').modal('toggle');
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

        $('#spinnerRegisterContacto').addClass('d-none');
        $('#spinnerRegisterContacto').removeClass('d-block');

        cleanInputContacto();
        const tercero_id=$('#id').val();
        CargarContactos(tercero_id);
        limpiarValidacionesContacto();
        toastr.success('Contacto guardado exitosamente');
        toastr.success('Contacto guardado exitosamente');

    }).catch(e => {
        // Manejo de errores
        $('#spinnerRegisterContacto').addClass('d-none');
        $('#spinnerRegisterContacto').removeClass('d-block');

        limpiarValidacionesContacto();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {
            // Errores de validación
            $.each(toast, function (key, value) {
                $('#error_contacto_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el contacto, revisar los errores en los campos.');
        } else if (e.status == 403) {
            // Errores de permisos
            $('#ModalCliente').modal('toggle');
            toastr.warning(arr.error);
        } else {
            toastr.error('Error al guardar el contacto');
            console.log(e.responseJSON);
        }
    });
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
            // Bootstrap 4 - backdrop configurado en modal show
            $.each(toast, function(key, value) {
                $('#error_sucursal_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');  // Muestra el mensaje de error
        } else if (e.status === 403) {
            // Errores de permisos
            myModal.modal('toggle');
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
            // Bootstrap 4 - backdrop configurado en modal show
            $.each(toast, function(key, value) {
                $('#error_contacto_' + key).text(value[0]);
            });
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');  // Muestra el mensaje de error
        } else if (e.status === 403) {
            // Errores de permisos
            myModal.modal('toggle');
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
    console.log('🔄 actualizarValidaciones llamada');

    const tipoPersonaSelect = document.getElementById('tipopersona_id');
    if (!tipoPersonaSelect) {
        console.log('⚠️ Elemento tipopersona_id no encontrado - reintentando en 100ms');
        setTimeout(actualizarValidaciones, 100);
        return;
    }

    // Buscar los elementos por su input ID y luego encontrar su contenedor form-group
    const nombreEstablecimientoInput = document.getElementById("nombre_establecimiento");
    const nombresInput = document.getElementById("nombres");
    const apellidosInput = document.getElementById("apellidos");

    if (!nombreEstablecimientoInput || !nombresInput || !apellidosInput) {
        console.log('⚠️ Algunos campos no encontrados - reintentando en 100ms');
        console.log('nombreEstablecimiento:', !!nombreEstablecimientoInput);
        console.log('nombres:', !!nombresInput);
        console.log('apellidos:', !!apellidosInput);
        setTimeout(actualizarValidaciones, 100);
        return;
    }

    // Encontrar los contenedores form-group de cada campo
    const nombreEstablecimientoGroup = nombreEstablecimientoInput.closest('.form-group');
    const nombresGroup = nombresInput.closest('.form-group');
    const apellidosGroup = apellidosInput.closest('.form-group');

    if (!nombreEstablecimientoGroup || !nombresGroup || !apellidosGroup) {
        console.log('⚠️ Contenedores form-group no encontrados - usando fallback');
        // Fallback: usar los mismos campos si no se encuentran los contenedores
        const nombreEstablecimientoGroupFallback = nombreEstablecimientoInput.parentElement;
        const nombresGroupFallback = nombresInput.parentElement;
        const apellidosGroupFallback = apellidosInput.parentElement;

        console.log('Usando contenedores padre directos');
    }

    const finalNombreGroup = nombreEstablecimientoGroup || nombreEstablecimientoInput.parentElement;
    const finalNombresGroup = nombresGroup || nombresInput.parentElement;
    const finalApellidosGroup = apellidosGroup || apellidosInput.parentElement;

    let tipoPersonaSeleccionado = tipoPersonaSelect.options[tipoPersonaSelect.selectedIndex].text.toLowerCase();
    console.log('🏷️ Tipo de persona seleccionado:', tipoPersonaSeleccionado);

    if (tipoPersonaSeleccionado.includes("jurídica")) {
        console.log('👤 Configurando para persona jurídica');
        // Mostrar campo "Nombre del Establecimiento" y hacerlo obligatorio
        if (finalNombreGroup) {
            finalNombreGroup.style.display = "block";
            console.log('✅ Mostrando nombre del establecimiento');
        }
        nombreEstablecimientoInput.setAttribute("required", "required");

        // Ocultar "Nombres" y "Apellidos" y quitar obligatoriedad
        if (finalNombresGroup) finalNombresGroup.style.display = "none";
        if (finalApellidosGroup) finalApellidosGroup.style.display = "none";
        nombresInput.removeAttribute("required");
        apellidosInput.removeAttribute("required");
        console.log('✅ Ocultando nombres y apellidos');
    } else {
        console.log('👤 Configurando para persona natural');
        // Mostrar "Nombres" y "Apellidos" y hacerlos obligatorios
        if (finalNombresGroup) finalNombresGroup.style.display = "block";
        if (finalApellidosGroup) finalApellidosGroup.style.display = "block";
        nombresInput.setAttribute("required", "required");
        apellidosInput.setAttribute("required", "required");
        console.log('✅ Mostrando nombres y apellidos');

        // Ocultar "Nombre del Establecimiento" y quitar obligatoriedad
        if (finalNombreGroup) {
            finalNombreGroup.style.display = "none";
            console.log('✅ Ocultando nombre del establecimiento');
        }
        nombreEstablecimientoInput.removeAttribute("required");
    }

    console.log('✅ actualizarValidaciones completada');
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

// Función temporal de debug para probar validaciones
function testValidaciones() {
    console.log('🧪 Iniciando test de validaciones...');
    
    // Limpiar formulario
    $('#ModalCliente form')[0].reset();
    limpiarValidaciones();
    
    // Enviar formulario vacío para provocar errores de validación
    const route = "/admin/admin.clientes.store";
    let ajax_data = new FormData();
    
    // Enviar solo algunos datos para provocar errores específicos
    ajax_data.append('tercerotipo_id', '1');
    ajax_data.append('tipoidentificacion_id', ''); // Error: requerido
    ajax_data.append('identificacion', ''); // Error: requerido
    ajax_data.append('tipopersona_id', ''); // Error: requerido
    ajax_data.append('correo', 'email-invalido'); // Error: formato
    ajax_data.append('user_id', ''); // Error: requerido
    
    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        contentType: false,
        processData: false,
        data: ajax_data
    }).then(response => {
        console.log('✅ Respuesta exitosa (no esperado en test):', response);
    }).catch(e => {
        console.log('🧪 Test de validación - Error capturado (esperado):', e);
        
        if (!e.responseJSON) {
            console.error('❌ Test falló: No hay responseJSON');
            return;
        }
        
        const arr = e.responseJSON;
        console.log('🔍 Estructura de respuesta:', arr);
        
        if (e.status === 422 && arr.errors) {
            console.log('✅ Test exitoso: Se recibieron errores de validación');
            console.log('🔍 Errores recibidos:', arr.errors);
            
            // Verificar que se muestren en la UI
            $.each(arr.errors, function (key, value) {
                const errorElement = $('#error_' + key);
                console.log(`🔍 Campo ${key}:`, {
                    error: value[0],
                    elementExists: errorElement.length > 0,
                    elementWillShow: errorElement.length > 0 ? 'Sí' : 'No'
                });
                
                if (errorElement.length > 0) {
                    errorElement.text(value[0]);
                    errorElement.show();
                }
            });
        } else {
            console.error('❌ Test falló: No se recibieron errores de validación correctamente');
        }
    });
}

// Exponer función para test manual desde consola
window.testValidaciones = testValidaciones;

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

