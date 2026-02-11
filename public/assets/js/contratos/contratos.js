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
    $('#ModalGenerarContrato').on('hidden.bs.modal', function () {
        // Activa la primera pestaña al cerrar la ventana modal
        $('#custom-content-below-home-tab').tab('show');
    });
     //carga de la datatable


    $('#plantilla-select').on('change', function(even){
        getCamposManuales(even.target.value)
    });

    $('#plantilla-select-ge').on('change', function(even){
        getCamposManuales(even.target.value)
    });

    $('#selectAll').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.employee-select').prop('checked', isChecked);
    });

    //hostname
    const url = new URL(window.location.href);
    hostName= url.origin;
    Cargar();

});

const hosName='';
//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalGenerarContrato'), {
    keyboard: false
})


var myModalContratosEmpleados = new bootstrap.Modal(document.getElementById('ModalGenerarContratosEmpleados'), {
    keyboard: false
})



//Variable que almacena el arreglo de empleados
selectedEmployees=[];


function Cargar() {
    if ($.fn.DataTable.isDataTable('#contratos-table')) {
        $('#contratos-table').DataTable().destroy();
    }
    let table = $('#contratos-table').DataTable(
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
            ajax: '/admin/admin.contratos.index',
                columns: [
                {
                    data: null,
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="employee-select" value="${row.id}">`;
                    },
                    orderable: false,
                    searchable: false
                },
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
                { data: 'acciones', name: 'acciones', className: 'd-none'},
            ],
            columnDefs:[
                {
                    target:1,
                    visible:false,
                }
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
        'plantilla-select',
        'plantilla-select-ge',
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);

    $('#campos-manuales').html('');
    $('#campos-manuales-ge').html('');
    $('#body-descargas').html('');
    $('#body-descargas-empleados').html('');
}

function showCustomEmpleado(btn) {
    $.get("/admin/admin.empleados.edit/" + btn, (response) => {
        const usr = response.data;

        // Mapear los campos del formulario
        const usuarioFields = [
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
            'created_at',
            'user_id',
            'telefono',
            'celular',
            'correo',
        ];

        usuarioFields.forEach(field => {
            if (field === 'active') {
                // Configurar el checkbox
                $('#' + field).prop('checked', usr[field] == 1 ? true : false);
            } else if (field.endsWith('_id')) {
                // Configurar el valor de los selects
                $('#' + field).val(usr[field]).change();
            } else if(field =='fecha_nacimiento' || field =='fecha_inicio_labor'){
                $('#' + field).val(usr[field].split('T')[0]);
            }else {
                // Configurar el valor de los campos de texto
                $('#' + field).val(usr[field]);
            }
        });
    });
}

// Actualizar usuario
function upGenerarContrato(btn) {
    $("#ModalGenerarContrato").modal('show');
    $('#exampleModalLabel').html('Generar Contrato');
    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showCustomEmpleado(btn);
    const camposManualesHtml= document.getElementById('campos-manuales');
    camposManualesHtml.innerHTML='';
    // FIN LIMPIAR CAMPOS
    // let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
    //     '<button id="editar" class="btn btn-primary" onclick="generarContrato(' + btn + ')">Generar Contrato</button>';
    // $(".modal-footer").html(u);
    let u = `
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button id="editar" class="btn btn-primary" onclick="generarContrato(this, ${btn})">
            <span id="spinner-editar" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <span id="text-editar">Generar Contrato...</span>
        </button>
    `;
    $(".modal-footer").html(u);
}

function generarContrato(btn,param) {

    console.log('btn', btn);

    btn.disabled = true;
    const route = `/admin/admin.plantillas.generateDocument/${btn}`;
    limpiarValidaciones();
    let ajax_data = new FormData();

    const camposManualesHtml = document.querySelectorAll('.control');
    const body = document.getElementById('body-descargas');
    body.innerHTML='';
    const data = {};
    camposManualesHtml.forEach(input => {
        const key = input.name.match(/campos\['(.*?)'\]/)[1];
        const value = input.value;
        data[key] = value;
    });

    ajax_data.append('campos', JSON.stringify(data));
    ajax_data.append('plantillaId', $('#plantilla-select').val());

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
        const documento=response.data;
        let celda='';
        celda =`<td>${documento}</td>
                <td class="text-center">
                    <a class="btn btn-secondary" href="${hostName}/storage/${documento}" target="_blank">Descargar</a>
                </td>`;
        body.innerHTML+=celda;
        toastr.success(response.message);
    })
    .catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status === 422) {
            // Errores de validación
            //$('#ModalGenerarContrato').data('bs.modal')._config.backdrop = 'static';
            $.each(toast, function(key, value) {
                console.log('key', key);

                if(key=='plantillaId'){
                    $('#error_plantilla-select').text(value[0]);
                }else{
                    $('#error_' + key).text(value[0]);
                }
            });
        toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            $("#ModalGenerarContrato").modal('hide');
            toastr.warning(arr.message);
        }
    });
}

function limpiarValidaciones() {
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
        'campos_manuales',
        'plantilla-select-ge',
        'plantilla-select',

    ];

    fields.forEach(field => {
        $('#error_' + field).text('');
    });
}

function getCamposManuales(plantillaId){
    if(plantillaId!='' && plantillaId!=undefined){
        $.get("/admin/admin.plantillas.getPlaceHolders/" + plantillaId, (response) => {
            const data = response.data;
            $('#error_plantilla-select-ge').text('');
            $('#error_plantilla-select').text('');
            inputsManuales(data)
        });
    }
}

function inputsManuales(data){
    const camposManuales ={};
    const camposManualesHtml= document.getElementById('campos-manuales');
    const camposManualesgeHtml= document.getElementById('campos-manuales-ge');
    camposManualesHtml.innerHTML='';
    camposManualesgeHtml.innerHTML='';
    let placeholdersHtml='';
    placeholdersHtml.innerHTML='';
    if(data){
        const placeholders=JSON.parse(data.plantilla.campos);
        for (const key in placeholders) {
            if (placeholders[key] === "manual") {
                camposManuales[key] = placeholders[key];
            }
        }

        Object.entries(camposManuales).forEach(([key, value]) => {
            placeholdersHtml += `<div class="form-group">
                                    <label for="${key}">${key}</label>
                                    <input type="text" name="campos['${key}']" id="${key}" class="form-control control" placeholder="${key}" />
                                </div>`
            camposManualesHtml.innerHTML = placeholdersHtml;
            camposManualesgeHtml.innerHTML = placeholdersHtml;
        });
    }
}

function selectEmpleados(){
    selectedEmployees=[];
    const selectedEmployeesL = [];
    $('.employee-select:checked').each(function () {
        selectedEmployeesL.push($(this).val());
    });

    if (selectedEmployeesL.length === 0) {
        toastr.error('Por favor, selecciona al menos un empleado.');
        return;
    }

    selectedEmployees=selectedEmployeesL;
}

function generarContratosEmpleados(){
    const boton = document.getElementById('btnGenerarContratos');

    const route ='/admin/admin.plantillas.generarContratos';
    limpiarValidaciones();
    const camposManualesHtml = document.querySelectorAll('.control');
    const data = {};
    camposManualesHtml.forEach(input => {
        const key = input.name.match(/campos\['(.*?)'\]/)[1];
        const value = input.value;
        data[key] = value;
    });

    if($('#plantilla-select-ge').val()==''){
        $('#error_plantilla-select-ge').text('Por favor seleccionar una plantilla');
        return;
    }

    if (Object.values(data).includes('')) {
        $('#error_campos_manuales').text('Los campos manuales son obligatorios.');
        return;
    }

    toggleLoadingScreen(true);
    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'POST'
        },
        method: 'POST',
        data: {
            empleados: selectedEmployees,
            campos:JSON.stringify(data),
            plantillaId:$('#plantilla-select-ge').val()
        },
        success: function (response) {
            const documentos=response.data;
            if(documentos[0].length!=0){
                const body = document.getElementById('body-descargas-empleados');
                let celda='';
                documentos.forEach(el => {
                    if(el){
                        ruta=el.replace("/home/u168992517/domains/app.minduval.com/storage/app/public", "/storage");
                        nombreArchivo=el.replace("/home/u168992517/domains/app.minduval.com/storage/app/public/documentos_generados/", " ");
                        console.log('ruta', ruta);
                        celda =`<td>${nombreArchivo}</td>
                                <td class="text-center">
                                    <a class="btn btn-secondary" href="${hostName}${ruta}" target="_blank">Descargar</a>
                                </td>`;
                        body.innerHTML+=celda;
                    }
                });
                toggleLoadingScreen(false);
                toastr.success(response.message);
            }else{
                toggleLoadingScreen(false);
            }

        },
        error: function (error) {
            console.log(error);
            ('Hubo un error al generar los documentos.');
            toggleLoadingScreen(false);
        }
    });
}

function openModalGenerarContratosEmpleados(){
    selectEmpleados();
    if(selectedEmployees.length==0) return;
    $("#ModalGenerarContratosEmpleados").modal('show');

    $('#exampleModalLabelGE').html('Generar Contrato  números de empleados seleccionados '+ selectedEmployees.length);
    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    // FIN LIMPIAR CAMPOS
    let u = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button id="btnGenerarContratos" class="btn btn-primary" onclick="generarContratosEmpleados()">
           Generar Contrato...
        </button>`;
    $(".modal-footer").html(u);

}

// function toggleButtonSpinner(button, isLoading, text = "Procesando...") {
//     console.log('button', isLoading);

//     if (isLoading) {
//         button.dataset.originalText = button.innerHTML;
//         console.log('1',button.dataset.originalText);

//         document.getElementById('generarContrato').innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${text}`;
//         console.log('2',button);
//         button.disabled = true;
//         console.log('3',button);
//         setTimeout(() => button.offsetHeight, 0);
//         console.log('boton modificado', button);

//     } else {
//         button.innerHTML = button.dataset.originalText; // Restaura el texto original
//         button.disabled = false;
//         console.log('boton modificado nuevo', button);
//     }
// }

function toggleButtonSpinner(button, isLoading, text = "Procesando...") {
    console.log('button', isLoading, button);

    console.log('btn', document.getElementById('generarContrato'));


    if (!button) {
        console.error("El botón no existe o no está definido.");
        return;
    }

    if (isLoading) {
        // Guardar el texto original solo si no se ha guardado antes
        if (!button.dataset.originalText) {
            button.dataset.originalText = button.innerHTML;
        }

        // Modificar el botón para mostrar el spinner y el texto de carga
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">${text}</span>`;
        button.disabled = true;

        console.log('Botón modificado con spinner:', button);

    } else {
        // Restaurar el texto original del botón
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
        button.disabled = false;

        console.log('Botón restaurado:', button);
    }
}


