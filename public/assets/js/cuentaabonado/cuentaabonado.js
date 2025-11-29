$(function() {

    $('#cuenta-table').DataTable({
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
        ajax: '/admin/admin.cuenta.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'id', name: 'id' },
            { data: 'nombre_usuario', name: 'nombre_usuario'},
            { data: 'nombre_cuenta', name: 'nombre_cuenta'},
            { data: 'password_cuenta', name: 'password_cuenta'},
            { data: 'fecha_asig', name: 'fecha_asig'},
            { data: 'tiempo', name: 'tiempo'},
            { data: 'estado', name: 'estado'},
            { data: 'acciones', name: 'acciones', className: 'exclude text-center'},
        ],
        columnDefs:[
            {
                targets:1,
                visible:false,
            },
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
        $('#usuario_dist').select2({
            placeholder: {
                id: '-1', // the value of the option
                text: 'Seleccionar un usuario'
              },
            allowClear: true,
            theme: "classic",
            dropdownParent: $('#myModal .modal-body'),
        } );
        $('#estado_id').select2({
            placeholder: {
                id: '-1', // the value of the option
                text: 'Seleccionar un estado'
              },
            allowClear: true,
            theme: "classic",
            dropdownParent: $('#myModal'),
        } );

        //se limpia el control
        $('#usuario_dist').val(null).trigger("change");
        $('#estado_id').val(null).trigger("change");
    }, 2000);


    document.getElementById('fileexcel').addEventListener('change', function (event) {
        var inputFile = event.target;
        var fileName = inputFile.files[0].name;
        inputFile.nextElementSibling.innerHTML = fileName;
    });

});

//variable para instanciar el modal
const myModal = new bootstrap.Modal(document.getElementById('myModal'), {
    keyboard: false
  })

  const modalLoadFile = new bootstrap.Modal(document.getElementById('modal-loadFile'), {
    keyboard: false
  })

function Cargar()
{
    let table = $('#cuenta-table').DataTable();
    table.ajax.reload();
}

// Limpiar inputs
function cleanInput() {

    // Campos usuario
    const Fields = ['user_id', 'nombre_cuenta','password_cuenta','estado_id','fecha_asig'];
    Fields.forEach(field => {
        $('#' + field).val('');
    });

}

function showCuenta(btn) {

     // LIMPIAR CAMPOS
    cleanInput();

    $.get("/admin/admin.cuenta/" + btn, (response) => {

        const cuenta = response.cuentas;

        const cuentas = ['usuario_dist', 'nombre_cuenta','password_cuenta','estado_id','fecha_asig'];

        cuentas.forEach(field => {
            if(field=='fecha_asig'){
                const date = new Date(cuenta[field]);
                const options = {  day: '2-digit',month: '2-digit',year: 'numeric'};
                const fecha = date.toLocaleDateString('es-ES', options);
                const fechaConver= convertDateFormat(fecha);
                 $('#' + field).val(fechaConver);
            }else{
                $('#' + field).val(cuenta[field]);
            }
        });
        // Select the option with a value of '1'
        $('#usuario_dist').trigger('change');
        $('#estado_id').trigger('change');

    });

}

//Registrar usuario
function regCuenta()
{
    myModal.show()
    $('#exampleModalLabel').html('Crear Cuenta distribuidor');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    // FIN LIMPIAR CAMPOS

    let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
            '<button id="registro" class="btn btn-primary" onclick="registerCuenta()">Agregar</button>';

    $(".modal-footer").html(r);

}

function registerCuenta()
{

    const route = "/admin/admin.cuenta";

    let ajax_data = {

        // Datos formulario
        usuario_dist: $('#usuario_dist').val(),
        nombre_cuenta: $('#nombre_cuenta').val(),
        password_cuenta:$('#password_cuenta').val(),
        fecha_asig:$('#fecha_asig').val(),
        estado_id:$('#estado_id').val(),
    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        myModal.toggle();
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

            myModal.toggle();
            toastr.warning(arr.error);

        }

    });

}

// Actualizar usuario
function upCuenta(btn)
{
    myModal.show()
    $('#exampleModalLabel').html('Editar Cuenta distribuidor');
    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showCuenta(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'+
            '<button id="editar" class="btn btn-primary" onclick="updateCuenta('+btn+')">Guardar</button>';

    $(".modal-footer").html(u);

}

function updateCuenta(btn)
{
    const route = "/admin/admin.cuenta/"+btn;

    // const selectedPermissions = [];

    // // Recorre los checkboxes y agrega los seleccionados al array
    // $('.permissions:checked').each(function() {
    //     selectedPermissions.push($(this).val());
    // });

    let ajax_data = {

        // Datos formulario
        usuario_dist: $('#usuario_dist').val(),
        nombre_cuenta: $('#nombre_cuenta').val(),
        password_cuenta:$('#password_cuenta').val(),
        fecha_asig:$('#fecha_asig').val(),
        estado_id:$('#estado_id').val(),
    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
        type: 'PUT',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        myModal.toggle();
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

            myModal.toggle();
            toastr.warning(arr.message);

        }

    });

}

// Función para convertir 'dd/mm/yyyy' a 'yyyy-mm-dd'
function convertDateFormat(dateString) {
    const parts = dateString.split('/');
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
}


function limpiarValidaciones(){
    $('#error_nombre_cuenta').text('');
    $('#error_password_cuenta').text('');
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
            url: 'admin.cuenta.uploadfile',
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
                console.log(xhr.responseJSON);
                $('#spinnerImportar').addClass('d-none');
                $('#spinnerImportar').removeClass('d-block');
                toastr.error(xhr.responseJSON.error);
            }
        });
    }
}

function openModalLoadFile(res)
{

    modalLoadFile.show();
    $('#modalLabel').html('<b>Carga de Archivo</b>');
    $('#lote').html(res.lote);

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
    regCambiar.innerHTML = (res.cantFilesFound).toString();
    if (res.rows.length > 0) {
        body.innerHTML = '';
        res.rows.forEach((el, index) => {
            if(el.dato1.split('(')[0].trim()=='Usuario distribuidor no existe' || el.dato2.split('(')[0].trim()=='Correo no valido' || el.dato2.split('(')[0].trim()=='Correo no existe' || el.fecha.split('(')[0].trim()=='Fecha no valida'){
                cantRegNoValidos++;
            }

            let row = `
                        <tr>
                                 <td> ${ index+1 } </td>
                                 <td> ${ el.dato1 } </td>
                                 <td> ${ el.dato2  } </td>
                                 <td> ${ el.dato3 } </td>
                                 <td> ${ el.fecha } </td>
                                 <td> ${ el.dato4 } </td>
                                 ${ el.dato1.split('(')[0].trim()=='Usuario distribuidor no existe' || (el.dato2).substring(0,16)=='Correo no valido' || el.dato2.split('(')[0].trim()=='Correo no existe' || (el.fecha).substring(0,15)=='Fecha no valida'?'<td> <span class="badge badge-danger">No validos</span>':'<td> <span class="badge badge-primary">Valido</span> </td>'}
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

    console.log('lote', loteNo.innerText);
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
        url: 'admin.cuenta.saveLote',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Manejar la respuesta del servidor en caso de éxito
            // location.reload(response)
            $('#spinnerCambiarEstado').addClass('d-none');
            $('#spinnerCambiarEstado').removeClass('d-block');

            toastr.success(response.msg);

            Cargar();
        },
        error: function(xhr, status, error) {
            // Manejar los errores de la solicitud AJAX
            console.error(xhr.responseJSON.msg);
            $('#spinnerCambiarEstado').addClass('d-none');
            $('#spinnerCambiarEstado').removeClass('d-block');

        }
    });
}
