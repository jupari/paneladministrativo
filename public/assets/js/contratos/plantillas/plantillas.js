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

});
//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalPlantilla'), {
    keyboard: false
})

function Cargar() {
    if ($.fn.DataTable.isDataTable('#plantillas-table')) {
        $('#plantillas-table').DataTable().destroy();
    }
    let table = $('#plantillas-table').DataTable(
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
            ajax: '/admin/admin.plantillas.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'id', name: 'id'},
                { data: 'plantilla', name: 'plantilla'},
                { data: 'archivo', name: 'archivo'},
                { data: 'campos', name: 'campos'},
                { data: 'active', name: 'active',className:'text-center'},
                { data: 'created_at', name: 'created_at'},
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
        'campos',
        'plantilla',
        'nombre_archivo',
        'active',
    ];

    // Limpiar cada campo
    fields.forEach(field => {
        $('#' + field).val(''); // Limpiar el valor
    });

    // Opcional: desmarcar todos los checkboxes si es necesario
    $('input[type="checkbox"]').prop('checked', false);
}

function showCustomPlantilla(btn) {
    $.get("/admin/admin.plantillas.edit/" + btn, (response) => {
        const usr = response.data;
        const campos =  response.campos;
        const camposContainer = document.getElementById('campos-container');
        // Mapear los campos del formulario
        const usuarioFields = [
            'campos',
            'plantilla',
            'nombre_archivo',
            'active',
            'id',
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
        dataRes={
            'plantilla':response.data,
            'placeholders':Object.entries(campos),
            'columnas':response.columnas,
        };
        viewMapping(dataRes);
    });
}

function regPlantilla() {
    $('#ModalPlantilla').modal('show');
    $('#exampleModalLabel').html('Registrar Plantilla');
    // LIMPIAR CAMPOS
    cleanInput();
     // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
    setPlantillaAction(false);
    let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
    $(".modal-footer").html(r);
    $("#mapping").html('');
    //$("#active").prop("checked",true);
}

function registerPlantilla() {
    $('#spinnerRegister').removeClass('d-none');
    const route = "/admin/admin.plantillas.store";

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
    ajax_data.append('plantilla', $('#plantilla').val());
    ajax_data.append('archivo', $('#archivo')[0].files[0]);
    ajax_data.append('active', activo);

    // Agregar campos dinámicos
    $('input[name="campos[]"]').each(function () {
        ajax_data.append('campos[]', $(this).val());
    });

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
        Cargar();
        $('#plantillaId').val(response.plantilla.id);
        getDataMapping(response.plantilla.id);
        toastr.success(response.message); // Muestra el mensaje de éxito


    }).catch(e => {
        // Manejo de errores
        limpiarValidaciones(); // Reemplaza con tu función de limpieza de validaciones
        const arr = e.responseJSON;
        const toast = arr.errors;
        $('#spinnerRegister').addClass('d-none');
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

function upPlantilla(btn) {
    $('#ModalPlantilla').modal('show');
    $('#exampleModalLabel').html('Editar Plantilla');
    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showCustomPlantilla(btn);
    setPlantillaAction(true);
    // FIN LIMPIAR CAMPOS
    let u = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
    $(".modal-footer").html(u);
}

function updatePlantilla(btn) {
    limpiarValidaciones();
    const plantillaId = $('#id').val();
    const route = `/admin/admin.plantillas.update/${plantillaId}`;
    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();
    // Lista de campos del formulario
    const fields = [
        'plantilla',
        'archivo',
        'active',
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

    // const formData = {};
    // $('#mapping select').each(function () {
    //     const name = $(this).attr('name');
    //     const value = $(this).val();

    //     if (value) {
    //         formData[name] = value;
    //     }
    // });
    ajax_data.append('campos',[]);


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
        const plantillaId = $('#id').val();
        getDataMapping(plantillaId);
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
            $('#ModalPlantilla').modal('toggle');
            toastr.warning(arr.message);
        }
    });
}

function limpiarValidaciones() {
    const fields = [
        'plantilla',
        'archivo',
        'active',
    ];

    fields.forEach(field => {
        $('#error_' + field).text('');
    });
}

function viewMapping(data) {
    const plantilla = data.plantilla;
    const placeholders = data.placeholders;
    const columnas = data.columnas;

    const containerFormMapping = document.getElementById('mapping');
    containerFormMapping.innerHTML = "";

    let placeholdersHtml = '';

    placeholders.forEach(placeh => {
        let placeholderName = '';
        let selectedValue = '';

        if (typeof placeh === 'string') {
            placeholderName = placeh;
            selectedValue = '';
        } else {
            placeholderName = placeh[0];
            selectedValue = placeh[1];
        }

        placeholdersHtml += `
            <div class="mb-2">
                <label class="form-label">${placeholderName}</label>
                <select name="mapa[${placeholderName}]" class="form-select" required>
                    <option value="">Seleccionar columna</option>`;

        columnas.forEach(columna => {
            const selected = columna === selectedValue ? 'selected' : '';
            placeholdersHtml += `<option value="${columna}" ${selected}>${columna}</option>`;
        });

        placeholdersHtml += `</select>
            </div>`;
    });

    const formulario = `
        <h3 class="mb-3">Mapear Placeholders</h3>
        ${placeholdersHtml}
        <button type="submit" class="btn btn-success my-2" onclick="saveMapping('${plantilla.id}')">Guardar Mapeo</button>
    `;

    containerFormMapping.innerHTML = formulario;
}


function getDataMapping(plantillaId) {

    $.get("/admin/admin.plantillas.getPlaceHolders/" + plantillaId, (response) => {
        const data = response.data;
        console.log('data',data);

        viewMapping(data)
    });

}

function saveMapping(plantillaId){
    const route = "/admin/admin.plantillas.saveMapping/"+plantillaId;
    let ajax_data = new FormData();
    const formData = {};
    $('#mapping select').each(function () {
        const name = $(this).attr('name');
        const value = $(this).val();

        if (value) {
            formData[name] = value;
        }
    });
    ajax_data.append('mapa', JSON.stringify(formData));

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        data:ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMÁGENES O ARCHIVOS POR AJAX
        processData: false,
    }).then(response => {
        Cargar();
        $('#ModalPlantilla').modal('toggle');
        toastr.success(response.message); // Muestra el mensaje de éxito
    }).catch(e => {
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
            $('#ModalPlantilla').modal('toggle');
            toastr.warning(arr.error);
        }
    });
}

function setPlantillaAction(isEdit = false) {
    const boton = document.getElementById('save-file');

    // Limpiar eventos previos
    boton.replaceWith(boton.cloneNode(true));

    const nuevoBoton = document.getElementById('save-file');

    // Asignar la función correspondiente
    if (isEdit) {
        nuevoBoton.addEventListener('click', updatePlantilla);
    } else {
        nuevoBoton.addEventListener('click', registerPlantilla);
    }
}
