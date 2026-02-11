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
    Cargar();
});
//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalPlantillaEdit'), {
    keyboard: false
})

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

        if(campos.length>0){
            camposContainer.innerHTML ='';
            (campos || []).forEach(campo => {
                const campoHtml = `
                    <div class="input-group mb-2">
                        <input type="text" name="campos[]" class="form-control" value="${campo}" placeholder="Ingrese un campo dinámico">
                        <button type="button" class="btn btn-danger btn-remove-campo"><i class="fas fa-trash-alt"></i></button>
                    </div>`;
                camposContainer.insertAdjacentHTML('beforeend', campoHtml);
            });
        }

        data={
            'plantilla':response.data,
            'placeholders':Object.entries(campos),
            'columnas':response.columnas,
        };
        viewMapping(data);
    });
}

//Registrar usuario
function regPlantilla() {
    myModal.modal('show')
    $('#exampleModalLabel').html('Registrar Plantilla');

    // LIMPIAR CAMPOS
    cleanInput();
     // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
    // Colocar los campos
    // const camposContainer = document.getElementById('campos-container');
    // camposContainer.innerHTML='';
    const campoHtml = `
        <div class="input-group mb-2">
            <input type="text" name="campos[]" class="form-control" value="" placeholder="Ingrese un campo dinámico">
            <button type="button" class="btn btn-danger btn-remove-campo"><i class="fas fa-trash-alt"></i></button>
        </div>`;
    // camposContainer.innerHTML=campoHtml;

     let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerPlantilla()"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span>Agregar</button>';

    $(".modal-footer").html(r);
    $("#mapping").html('');
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
        //myModal.modal('toggle'); // Reemplaza con tu lógica de modal
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

// Actualizar usuario
function upPlantilla(btn) {
    myModal.modal('show')
    $('#exampleModalLabel').html('Editar Plantilla');
    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showCustomPlantilla(btn);
    // FIN LIMPIAR CAMPOS
    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button id="editar" class="btn btn-primary" onclick="updatePlantilla(' + btn + ')">Guardar</button>';
    $(".modal-footer").html(u);
}

function updatePlantilla(btn) {
    const route = `/admin/admin.plantillas.update/${btn}`;
    limpiarValidaciones();
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

    // Agregar campos dinámicos
    $('input[name="campos[]"]').each(function () {
        ajax_data.append('campos[]', $(this).val());
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

function viewMapping(data){
    const plantilla = data.plantilla;
    const placeholders =  data.placeholders;
    const columnas =  data.columnas;

    const containerFormMapping = document.getElementById('mapping');
    containerFormMapping.innerHTML ="";
    let columnasHml = '';
    let placeholdersHtml='';

    placeholders.forEach(placeh => {
        if(typeof placeh =='string'){
            placeholdersHtml += `<div>
                            <label>${placeh}</label>
                            <select name="mapa[${placeh}]" class="form-select" required>
                                <option value="">Seleccionar columna</option>`;
        }else{
            placeholdersHtml += `<div>
                                    <label>${placeh[0]}</label>
                                    <select name="mapa[${placeh[0]}]" class="form-select" required>
                                        <option value="">Seleccionar columna</option>`;
        }


        // Iterar sobre las columnas para generar las opciones
        if (typeof placeh == 'string'){
            columnas.forEach(columna => {
                placeholdersHtml += `
                            <option value="${columna}">${columna}</option>`;
            })
        }else{
            placeh.forEach(key=>{
                columnas.forEach(columna => {
                    placeholdersHtml += `
                                            <option value="${columna}" ${
                                                columna === key ? 'selected' : ''
                                            }>${columna}</option>`;
                });
            })
        }

        // Agregar la opción para ingreso manual
        placeholdersHtml += `  </select>
                            </div>`;
    });

    //console.log('placeholdersHtml',placeholdersHtml);

    const formulario = `
                    <h3>Mapear Placeholders</h3>
                        ${placeholdersHtml}
                     <button type="submit" class="btn btn-success my-2" onclick="saveMapping('${plantilla.id}')">Guardar Mapeo</button>`
    $("#mapping").html(formulario);
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

    // Verificar si hay campos vacíos
    // if (Object.values(formData).includes('')) {
    //     alert('Por favor, completa todos los campos.');
    //     return;
    // }
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
        myModal.modal('toggle');
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
