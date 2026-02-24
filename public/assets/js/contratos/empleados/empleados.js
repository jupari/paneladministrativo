let salarioInput;

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
    Cargar();
    //variable global
    loadLibrerias();
    loadDepartamentos(window.paises);
});



function loadLibrerias()
{
    salarioInput = new AutoNumeric('#salario', {
        currencySymbol: '$ ',
        decimalCharacter: '.',
        digitGroupSeparator: ',',
        decimalPlaces: 2,
    });
}


function Cargar() {
    if ($.fn.DataTable.isDataTable('#empleados-table')) {
        $('#empleados-table').DataTable().destroy();
    }
    let table = $('#empleados-table').DataTable(
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
            ajax: '/admin/admin.empleados.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'id', name: 'id'},
                { data: 'nombres_completos', name: 'nombres_completos'},
                { data: 'identificacion', name: 'identificacion'},
                { data: 'expedida_en', name: 'expedida_en'},
                { data: 'fecha_nacimiento', name: 'fecha_nacimiento'},
                { data: 'fecha_inicio_labor', name: 'fecha_inicio_labor'},
                { data: 'direccion', name: 'direccion'},
                { data: 'cargo', name: 'cargo'},
                { data: 'active', name: 'active',className:'text-center'},
                { data: 'created_at', name: 'created_at'},
                { data: 'acciones', name: 'acciones', className: 'exclude'},
            ],
            order: [[1, "desc"]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
            //ocultar la columna con el name id
            columnDefs: [
                { targets: 1, visible: false }
            ],
        }
    );
    // table.ajax.reload();
}

// Limpiar inputs
function cleanInput(btn) {

    const bool = (btn == null) ? false : true;

    // Campos del formulario actual
    const fields = [
        'nombres',
        'apellidos',
        'identificacion',
        'expedida_en',
        'fecha_nacimiento',
        'fecha_inicio_labor',
        'direccion',
        'cargo_id',
        'active',
        'salario',
        'telefono',
        'celular',
        'correo',
        'ubicacion',
        'cliente_id',
        'sucursal_id',
        'tipo_contrato',
        'fecha_finalizacion_contrato',
        'tipo_identificacion_id',
        'ciudad_residencia'
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

async function showCustomEmpleado(btn) {
    try {
        const response = await $.get(`/admin/admin.empleados.edit/${btn}`);
        const usr = response.data;
        const usuarioFields = [
            'nombres',
            'apellidos',
            'identificacion',
            'expedida_en',
            'fecha_nacimiento',
            'fecha_inicio_labor',
            'fecha_finalizacion_contrato',
            'direccion',
            'cargo_id',
            'active',
            'salario',
            'created_at',
            'user_id',
            'telefono',
            'celular',
            'correo',
            'cliente_id',
            'sucursal_id',
            'ubicacion',
            'tipo_contrato',
            'ciudad_residencia',
            'tipo_identificacion_id',
            'ciudad_id',
        ];

        usuarioFields.forEach(field => {
            if (field === 'active') {
                $('#' + field).prop('checked', usr[field] == 1);
            } else if (field.endsWith('_id')) {
                $('#' + field).val(usr[field]).change();
            } else if (field === 'fecha_nacimiento' || field === 'fecha_inicio_labor' || field == 'fecha_finalizacion_contrato') {
                $('#' + field).val(usr[field]?.split('T')[0] || '');
            } else if(field=='salario') {
                salarioInput.set(usr[field]?usr[field]:0);
            } else {
                $('#' + field).val(usr[field]);
            }
        });
        actualizarValidaciones();
        const clienteId = usr.cliente_id;
        const sucursalId = usr.sucursal_id;
        await getSucursal(clienteId, sucursalId);

    } catch (error) {
        console.error('❌ Error al cargar el empleado o sucursal:', error);
    }
}

//Registrar usuario
function regEmpleado() {

    $('#ModalEmpleado').modal('show');
    $('#modal-title-text').html('Registrar Empleado');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();

    // Configurar el botón para crear nuevo empleado (limpiar eventos previos)
    let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="resetButton()"><i class="fas fa-times mr-1"></i>Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerEmpleado()"><i class="fas fa-save mr-1"></i><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';

    $(".modal-footer").html(r);
}

function registerEmpleado() {
    // Configurar estado de loading del botón
    setButtonLoading();

    const route = "/admin/admin.empleados.store";

    limpiarValidaciones();

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
    ajax_data.append('nombres', $('#nombres').val());
    ajax_data.append('apellidos', $('#apellidos').val());
    ajax_data.append('tipo_identificacion_id', $('#tipo_identificacion_id').val());
    ajax_data.append('identificacion', $('#identificacion').val());
    ajax_data.append('expedida_en', $('#expedida_en').val());
    ajax_data.append('fecha_nacimiento', $('#fecha_nacimiento').val());
    ajax_data.append('fecha_inicio_labor', $('#fecha_inicio_labor').val());
    ajax_data.append('fecha_finalizacion_contrato', $('#fecha_finalizacion_contrato').val());
    ajax_data.append('direccion', $('#direccion').val());
    ajax_data.append('ciudad_residencia', $('#ciudad_residencia').val());
    ajax_data.append('telefono', $('#telefono').val());
    ajax_data.append('celular', $('#celular').val());
    ajax_data.append('correo', $('#correo').val());
    ajax_data.append('cargo_id', $('#cargo_id').val());
    ajax_data.append('ciudad_id', $('#ciudad_id').val());
    ajax_data.append('cliente_id', $('#cliente_id').val());
    ajax_data.append('sucursal_id', $('#sucursal_id').val());
    ajax_data.append('ubicacion', $('#ubicacion').val());
    ajax_data.append('salario', salarioInput.getNumber());
    ajax_data.append('active', activo);
    ajax_data.append('user_id', $('#user_id').val());
    ajax_data.append('tipo_contrato', $('#tipo_contrato').val());


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
        resetButton();
        Cargar();
        $('#ModalEmpleado').modal('hide');
        showSuccessMessage(response.message || 'Empleado guardado exitosamente');

    }).catch(e => {
        // Manejo de errores
        resetButton();
        limpiarValidaciones();
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
function upEmpleado(btn) {
    console.log('📝 Iniciando upEmpleado() para empleado ID:', btn);

    $('#ModalEmpleado').modal('show');
    $('#modal-title-text').html('Editar Empleado');
    $('#btn-text').html('Actualizar Empleado');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showCustomEmpleado(btn);

      // Configurar el botón para crear nuevo empleado (limpiar eventos previos)
    let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="resetButton()"><i class="fas fa-times mr-1"></i>Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="updateEmpleado(' + btn + ')"><i class="fas fa-save mr-1"></i><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';

    $(".modal-footer").html(r);

}

function updateEmpleado(btn) {
    console.log('🔄 Iniciando updateEmpleado() para empleado ID:', btn);
    // Configurar estado de loading del botón
    setButtonLoading();

    const route = `/admin/admin.empleados.update/${btn}`;
    limpiarValidaciones();
    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Lista de campos del formulario
    const fields = [
        'nombres',
        'apellidos',
        'identificacion',
        'expedida_en',
        'fecha_nacimiento',
        'fecha_inicio_labor',
        'fecha_finalizacion_contrato',
        'direccion',
        'cargo_id',
        'active',
        'salario',
        'user_id',
        'telefono',
        'celular',
        'correo',
        'cliente_id',
        'sucursal_id',
        'ubicacion',
        'tipo_contrato',
        'ciudad_residencia',
        'tipo_identificacion_id',
        'ciudad_id'
    ];

    // Recorrer los campos y agregarlos al FormData
    fields.forEach(field => {
        if (field === 'active') {
            // Manejar checkbox (true o false)
            ajax_data.append(field, $('#' + field).is(':checked') ? 1 : 0);
        }else if(field=='salario'){
            ajax_data.append(field, salarioInput.getNumber());
        }
        else {
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
        resetButton();
        Cargar();
        $('#ModalEmpleado').modal('hide');
        showSuccessMessage(response.message || 'Empleado actualizado exitosamente');
    })
    .catch(e => {
        resetButton();
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

function getSucursal(clienteId, sucursalId = null) {
    const $select = $('#sucursal_id');

    return new Promise((resolve, reject) => {
        $.get(`/admin/admin.sucursales.getSucursales/${clienteId}`)
            .done((response) => {
                const sucursales = response.data || [];

                $select.empty().append(
                    $('<option>', { value: '', text: 'Seleccione la sucursal' })
                );

                sucursales.forEach(sucursal => {
                    $select.append(
                        $('<option>', {
                            value: sucursal.id,
                            text: sucursal.direccion
                        })
                    );
                });

                // Seleccionar sucursal después de poblar todas
                if (sucursalId !== null) {
                    $select.val(sucursalId);
                }

                resolve(sucursales);
            })
            .fail((xhr, status, error) => {
                console.error('Error al obtener sucursales:', error);
                reject(error);
            });
    });
}

function loadDepartamentos(paises) {
    $('#departamento_id').empty().append('<option value="">Seleccione un departamento</option>');
    paises.forEach((pais) => {
        if(pais.nombre === 'Colombia' || pais.nombre === 'COLOMBIA' || pais.nombre === 'colombia') {
            pais.departamentos.forEach((departamento) => {
                $('#departamento_id').append(`<option value="${departamento.id}">${departamento.nombre}</option>`);
            });
        }
    });
}

function getCiudades(departamento_id, selectedCiudad = null) {
    let departamentoSeleccionado = window.ciudades.filter(ciudad => ciudad.departamento_id == departamento_id);
    if (!departamentoSeleccionado) return;
    $('#ciudad_id').empty().append('<option value="">Seleccione una ciudad</option>');
    departamentoSeleccionado.forEach((ciudad) => {
        $('#ciudad_id').append(`<option value="${ciudad.id}">${ciudad.nombre}</option>`);
    });
    $('#ciudad_id').val(selectedCiudad).change();
}

function actualizarCiudadResidencia(ciudadId) {
 const ciudad = $('#ciudad_id option:selected').text();
 if(ciudadId) {
     $('#ciudad_residencia').val(ciudad);
 } else {
     $('#ciudad_residencia').val('');
 }

}

function setUbicacion(ubicacion){
    if(ubicacion || ubicacion!='' ){
        const ubicacionInput = document.getElementById("ubicacion");
        ubicacionInput.value =  ubicacion.options[ubicacion.selectedIndex].text;
    }

}

function limpiarValidaciones() {
    const fields = [
        'nombres',
        'apellidos',
        'identificacion',
        'expedida_en',
        'fecha_nacimiento',
        'fecha_inicio_labor',
        'fecha_finalizacion_contrato',
        'direccion',
        'cargo_id',
        'active',
        'salario',
        'telefono',
        'celular',
        'correo',
        'cliente_id',
        'sucursal_id',
        'tipo_contrato_id',
        'ubicacion',
        'tipo_identificacion_id',
        'ciudad_residencia'
    ];

    fields.forEach(field => {
        $('#error_' + field).text('');
    });
}

function actualizarValidaciones() {
    const tipoContrato = document.getElementById("tipo_contrato");
    const fechaFinalizacion = document.getElementById("fecha_finalizacion_contrato_group");
    const cliente = document.getElementById("cliente");
    const ubicacion = document.getElementById("ubicacion_group");
    const sucursal = document.getElementById("sucursal_cliente_group");

    if(tipoContrato.value==null){
        return;
    }
    let tipoSeleccionado = tipoContrato.value;



    fechaFinalizacion.style.display='none';
    cliente.style.display='none';
    ubicacion.style.display='none';
    sucursal.style.display = 'none';

    if (tipoSeleccionado == 'termino_fijo') {
        fechaFinalizacion.style.display='block';
    }
    if (tipoSeleccionado == 'obra_labor') {
        cliente.style.display='block';
        ubicacion.style.display='block';
        sucursal.style.display = 'block';

        document.getElementById("cliente_id").addEventListener('change', function() {
                getSucursal(this.value)
            });

        document.getElementById("sucursal_id").addEventListener('change', function() {
            setUbicacion(this)
        });
    }
}

// ========================================
// FUNCIONES PARA MANEJO DE ESTADO DE BOTONES Y UX
// ========================================

function setButtonLoading() {
    const btn = $('#btn-guardar-empleado');
    btn.prop('disabled', true);
    btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Procesando...');
}

function resetButton() {
    const btn = $('#btn-guardar-empleado');
    btn.prop('disabled', false);
    const isEditing = $('#modal-title-text').text().includes('Editar');
    const text = isEditing ? 'Actualizar Empleado' : 'Guardar Empleado';
    const icon = isEditing ? 'fas fa-save' : 'fas fa-save';
    btn.html(`<i class="${icon} mr-1"></i>${text}`);
}

// Función para mostrar feedback visual mejorado
function showSuccessMessage(message) {
    toastr.success(message, '¡Éxito!', {
        timeOut: 3000,
        positionClass: "toast-top-right",
        showMethod: 'fadeIn'
    });
}

function showErrorMessage(message) {
    toastr.error(message, 'Error', {
        timeOut: 5000,
        positionClass: "toast-top-right",
        showMethod: 'fadeIn'
    });
}
