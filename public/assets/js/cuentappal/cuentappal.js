$(function() {

    $('#cppal-table').DataTable({
        "language": {
                "url": "/assets/js/spanish.json"
        },
        processing: false,
        responsive: true,
        serverSide: true,
        dom:  "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
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
        ajax: '/admin/admin.cuentappal.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'id', name: 'id'},
            { data: 'nombre', name: 'nombre'},
            { data: 'correo', name: 'correo'},
            { data: 'password', name: 'password', className:'text-break'},
            { data: 'cm_asociada', name: 'cm_asociada', className:'text-break'},
            { data: 'usuario_dist', name: 'usuario_dist'},
            { data: 'cta_ppal', name: 'cta_ppal'},
            { data: 'acciones', name: 'acciones', className: 'exclude text-center'},
        ],
        columnDefs:[
            {
                targets:1,
                visible:false
            },
            // {
            //     targets:1,
            //     visible:false
            // },
            // {
            //     targets:2,


            // },
            // {
            //     targets:3,

            // },
            // {
            //     targets:4,

            // },
            // {
            //     targets:5,

            // },
            // {
            //     targets:6,

            // },
            // {
            //     targets:7,

            // },
        ],
        order: [[ 1, "desc" ]],
        pageLength: 8,
        lengthMenu: [[2, 4, 6, 8, 10, -1], [2, 4, 6, 8, 10, "Todo(s)"]],
    });

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

     // Escucha el evento de cierre de la ventana modal
    $('#myModal').on('hidden.bs.modal', function () {
        // Activa la primera pestaña al cerrar la ventana modal
        $('#custom-content-below-home-tab').tab('show');
    });

    setTimeout(() => {
        const cuentaMadres=

        $('#usuario_dist').select2({
            placeholder: {
                id: '-1', // the value of the option
                text: 'Seleccionar un usuario'
              },
            allowClear: true,
            theme: "classic",
            dropdownParent: $('#myModal .modal-body'),
        });
        $('#cm_asociada').select2({
            placeholder: {
                id: '-1', // the value of the option
                text: 'Selecccionar una cuenta asociada'
              },
            allowClear: true,
            theme: "classic",
            dropdownParent: $('#myModal .modal-body'),
        });
        //se limpia el control
        $('#cm_asociada').val(null).trigger("change");
        $('#usuario_dist').val(null).trigger("change");
        //hacer un trigger al control
        getDataCuentaMadre();

    }, 2000);




    document.getElementById('fileexcel').addEventListener('change', function (event) {
        var inputFile = event.target;
        var fileName = inputFile.files[0].name;
        inputFile.nextElementSibling.innerHTML = fileName;
    });
});
//declara la variable global del modal
const myModal = new bootstrap.Modal(document.getElementById('myModal'), {
    keyboard: false
  })

const modalLoadFile = new bootstrap.Modal(document.getElementById('modal-loadFile'), {
    keyboard: false
  })

function Cargar()
{
    let table = $('#cppal-table').DataTable();
    table.ajax.reload();
}

// Limpiar inputs
function cleanInput() {

    // Campos usuario
    const rolesFields = ['nombre', 'email', 'password', 'usuario_reg','clientId','tenant_id','clientSecret','cm_asociada','cta_ppal'];
    rolesFields.forEach(field => {
        if(field!='cta_ppal'){
            $('#' + field).val('');
        }else{
            $('#' + field).prop('checked',false);
        }
        // $('#' + field).val('');
    });

    $('.permissions').prop('checked', false);
    $('#cm_asociada').val(null).trigger('change');
    $('#usuario_dist').val(null).trigger('change');

}

function showCppal(btn) {

     // LIMPIAR CAMPOS
    cleanInput();

    $.get("/admin/admin.cuentappal/" + btn, (response) => {

        const cuentaPpal = response.cuentappal;

        console.log('cuentaPpal', cuentaPpal);
        const cuentaPpalFields = ['nombre', 'email', 'password','usuario_dist','clientId','tenant_id','clientSecret','cm_asociada','cta_ppal'];

        cuentaPpalFields.forEach(field => {
            if(field!='cta_ppal'){
                $('#' + field).val(cuentaPpal[field]);
            }else{
                $('#' + field).prop('checked',cuentaPpal[field]==1?true:false);
            }
            //$('#' + field).val(cuentaPpal[field]);
        });
        // Select the option with a value of '1'
        $('#cm_asociada').trigger('change');
        $('#usuario_dist').trigger('change');


    });

}

//Registrar usuario
function regCuentaPpal()
{
    myModal.modal('show');
    $('#exampleModalLabel').html('Crear Cuenta Principal');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();

    // FIN LIMPIAR CAMPOS

    let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'+
            '<button id="registro" class="btn btn-primary" onclick="registerCuentaPpal()">Agregar</button>';

    $(".modal-footer").html(r);

}

function registerCuentaPpal()
{

    const route = "/admin/admin.cuentappal";

    let ctappal=$('#cta_ppal').is(':checked')?1:0;
    $('#cta_ppal').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    let ajax_data = {

        // Datos formulario
        nombre: $('#nombre').val(),
        email: $('#email').val(),
        password:$('#password').val(),
        usuario_reg:$('#usuario_dist').val(),
        clientId:$('#clientId').val(),
        tenant_id:$('#tenant_id').val(),
        clientSecret:$('#clientSecret').val(),
        cm_asociada:$('#cm_asociada').val(),
        cta_ppal:ctappal
    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        myModal.modal('toggle');
        toastr.success(response.message);

    })
    .catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {

            $.each(toast, function(key, value) {
                 $('#error_'+key).text(value[0]);
            });

            // for (const key in toast) {
            //     if (toast.hasOwnProperty(key) && toast[key] != null) {
            //         toastr.error(toast[key][0]);
            //     }
            // }

        }

        else if(e.status == 403){

            myModal.modal('toggle');
            toastr.warning(arr.error);

        }
        else if(e.status == 423){
            toastr.warning(arr.error);
        }

    });

}

function limpiarValidaciones(){
    $('#error_nombre').text('');
    $('#error_email').text('');
}

// Actualizar usuario
function upCuentaPpal(btn)
{

    myModal.modal('show')
    $('#exampleModalLabel').html('Editar Cuenta Principal');

    // LIMPIAR CAMPOS
    cleanInput();

    showCppal(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'+
            '<button id="editar" class="btn btn-primary" onclick="updateCuentaPpal('+btn+')">Guardar</button>';

    $(".modal-footer").html(u);

}

function updateCuentaPpal(btn)
{
    $('#myModal').modal();

    const route = "/admin/admin.cuentappal/"+btn;

    // const selectedPermissions = [];

    // // Recorre los checkboxes y agrega los seleccionados al array
    // $('.permissions:checked').each(function() {
    //     selectedPermissions.push($(this).val());
    // });

    let ctappal=$('#cta_ppal').is(':checked')?1:0;
    $('#cta_ppal').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    let ajax_data = {

        // Datos formulario
        nombre: $('#nombre').val(),
        email: $('#email').val(),
        password:$('#password').val(),
        usuario_reg:$('#usuario_dist').val(),
        clientId:$('#clientId').val(),
        tenant_id:$('#tenant_id').val(),
        clientSecret:$('#clientSecret').val(),
        cm_asociada:$('#cm_asociada').val(),
        cta_ppal:ctappal
    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        myModal.modal('toggle');
        toastr.success(response.message);

    })
    .catch(e => {

        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {

            for (const key in toast) {
                if (toast.hasOwnProperty(key) && toast[key] != null) {
                    toastr.error(toast[key][0]);
                }
            }

        }

        else if(e.status == 403){

            myModal.modal('toggle');
            toastr.warning(arr.message);

        }

    });

}

//Funcion para cargar el archivo y cambiar el estado
function saveFileExcel(){
    let fileExport  = $("#fileexcel")[0].files[0];
    console.log("fileExport => ", fileExport);
    // let offerId     = $("#offer_id").val();

    $('#spinnerImportar').addClass('d-block');
    $('#spinnerImportar').removeClass('d-none');

    if(fileExport == "" || fileExport == undefined){
        console.log("Por favor selecccione un archivo de Excel para continuar.");

        $('#spinnerImportar').addClass('d-none');
        $('#spinnerImportar').removeClass('d-block');

    }else{
        let formData = new FormData();
        formData.append('excel_file', fileExport);
        // formData.append('offer_id', offerId);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: 'admin.cuentappal.uploadfile',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Manejar la respuesta del servidor en caso de éxito
                // location.reload(response)
                $('#spinnerImportar').addClass('d-none');
                $('#spinnerImportar').removeClass('d-block');

                //abrir el modal del resultado
                openModalLoadFile(response);
            },
            error: function(xhr, status, error) {
                // Manejar los errores de la solicitud AJAX
                console.error(error);
                $('#spinnerImportar').addClass('d-none');
                $('#spinnerImportar').removeClass('d-block');
                toastr.error(xhr.responseJSON.error);
            }
        });
    }
}

function openModalLoadFile(res)
{

    modalLoadFile.modal('show')
    $('#modalLabel').html('<b>Carga de Archivo</b>');
    $('#lote').html(res.lote);
    $('#cta_ppal').prop('checked', false);
    //borrar el boton de enviar email
    let button = document.getElementById('btn-sendEmail');
    // Elimina el botón
    if (button) {
        button.remove(button);
    }


    // construir la tabla

    setTimeout(() => {
        builTablePaginatorDet(res);
    }, 500);
   // FIN LIMPIAR CAMPO
   let us = `<button id="editar" class="btn btn-primary" onclick="guardarLote()">  Insertar cuentas
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerCambiarEstado"></span>
            </button>`;

   let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>'+us;

   $("#modal-footer-file").html(u);

}

//Funcion para crear la vista de la table y el paginador
function builTablePaginatorDet(res) {

    let regLeidos = document.getElementById('regLeidos');
    let regCambiar = document.getElementById('regCambiar');
    let regNoValidos=document.getElementById('regNoValidos');

    let cantRegNoValidos=0;
    let body = document.getElementById('bodyRes');
    regLeidos.innerHTML = (res.cantExcelFiles).toString();


    if (res.rows.length > 0) {
        body.innerHTML = '';
        res.rows.forEach((el, index) => {

            if((el.dato1.split('(')[0].trim())=='Correo no valido' || el.dato4=='No es una cuenta principal' || el.dato5=='Usuario distribuidor no existe' || el.dato1.split('(')[0].trim()=='Correo ya existe'){
                cantRegNoValidos++;
            }
            let row = `
                        <tr>
                                 <td> ${ index+1  } </td>
                                 <td> ${ el.dato1 } </td>
                                 <td> ${ el.dato2.substring(0, 10)  } </td>
                                 <td> ${ el.dato3 } </td>
                                 <td> ${ el.dato4 } </td>
                                 <td> ${ el.dato5 } </td>
                                 <td> ${ el.dato6 } </td>
                                 <td> ${ el.dato7 } </td>
                                 <td> ${ el.dato8 } </td>
                                 <td> ${ el.dato9==1?'SI':'NO' } </td>
                                ${  (el.dato1.split('(')[0].trim())=='Correo no valido'
                                    || el.dato4=='No es una cuenta principal'
                                    || el.dato5=='Usuario distribuidor no existe'
                                    || el.dato1.split('(')[0].trim()=='Correo ya existe'
                                    ?'<td> <span class="badge badge-danger">No Válido</span>':'<td> <span class="badge badge-primary">Correcto</span>' } </td>
                        </tr>
                             `
            body.innerHTML += row;
        });
        //Setea la cantidad de registro no validos//
        regNoValidos.innerHTML  = cantRegNoValidos.toString();
        regCambiar.innerHTML    = (res.cantFilesFound-cantRegNoValidos).toString();
        $('#file-loading').DataTable({
            "language": {
                    "url": "/assets/js/spanish.json"
            },
            responsive: true,
            dom:  "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
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
            columnDefs: [
                { "width": "10px", "targets": 0 } // Ajusta "50px" según tus necesidades
            ],
            order: [[ 0, "asc" ]],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        });

    }

}

function guardarLote(lote){
    const loteNo =  document.getElementById('lote');
    $('#spinnerCambiarEstado').addClass('d-block');
    $('#spinnerCambiarEstado').removeClass('d-none');

    let formData = new FormData();
    formData.append('lote', loteNo.innerText);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: 'admin.cuentappal.saveLote',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Manejar la respuesta del servidor en caso de éxito
            // location.reload(response)
            $('#spinnerCambiarEstado').addClass('d-none');
            $('#spinnerCambiarEstado').removeClass('d-block');

            // Crear el botón
            //botonSenEmail =  document.getElementById('btn-sendEmail');
            // if(!botonSenEmail){
            //     let button = document.createElement('button');
            //     button.id = 'btn-sendEmail';
            //     button.className = 'btn btn-warning';
            //     button.onclick = sendEmail;
            //     button.innerHTML = 'Enviar correos de notificación';

            //     // Crear el span
            //     let spinner = document.createElement('span');
            //     spinner.className = 'spinner-border spinner-border-sm d-none';
            //     spinner.role = 'status';
            //     spinner.ariaHidden = 'true';
            //     spinner.id = 'spinnerSendEmail';

            //     // Añadir el span al botón
            //     button.appendChild(spinner);

            //     // Añadir el botón al contenedor
            //     document.getElementById('modal-footer-file').appendChild(button);

            //  }else{}
            Cargar();
            toastr.success(response.msg);
        },
        error: function(xhr, status, error) {
            // Manejar los errores de la solicitud AJAX
            //borrar el boton de enviar email
            let button = document.getElementById('btn-sendEmail');
            // Elimina el botón
            if (button) {
                button.remove(button);
            }
            $('#spinnerCambiarEstado').addClass('d-none');
            $('#spinnerCambiarEstado').removeClass('d-block');
        }
    });
}

function sendEmail(){
    const loteNo =  document.getElementById('lote');

    console.log('lote', loteNo.innerText );

    $('#spinnerSendEmail').addClass('d-block');
    $('#spinnerSendEmail').removeClass('d-none');

    let formData = new FormData();
    formData.append('lote', loteNo.innerText);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: 'excel/enviarCorreo',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Manejar la respuesta del servidor en caso de éxito
            // location.reload(response)

            console.log('response', response);
            $('#spinnerSendEmail').addClass('d-none');
            $('#spinnerSendEmail').removeClass('d-block');

            toastr.success(response.msg);
        },
        error: function(xhr, status, error) {
            // Manejar los errores de la solicitud AJAX
            console.log(xhr.responseJSON.msg);
            toastr.error(xhr.responseJSON.msg);
            $('#spinnerSendEmail').addClass('d-none');
            $('#spinnerSendEmail').removeClass('d-block');
        }
    });

}

function getDataCuentaMadre(){
    $('#cm_asociada').on('select2:select',function(e){
        $.get("/admin/admin.cuentappal.find/" + e.target.value, (response) => {
            if(response.Ok){
                const data =  response.data;
                $('#clientSecret').val(data.account.clientSecret);
                $('#tenant_id').val(data.account.tenant_id);
                $('#clientId').val(data.account.clientId);
                $('#cta_ppal').prop('checked', false);
                $('#cta_ppal').attr('disabled', true);
            }
        });
    });

    $('#cm_asociada').on('select2:unselect',function(e){
        $('#clientSecret').val('');
        $('#tenant_id').val('');
        $('#clientId').val('');
        $('#cta_ppal').prop('checked', false);
        $('#cta_ppal').removeAttr('disabled');
    })

}

function seePasword(){
    const password =  $('#password');
    const passwordType= password.attr('type');
    const eye= $('#eye');

    if(passwordType==='password'){
        password.attr('type','text');
        eye.removeClass('fa-eye');
        eye.addClass('fa-eye-slash')
    }else{
        password.attr('type','password');
        eye.removeClass('fa-eye-slash');
        eye.addClass('fa-eye')
    }

}
