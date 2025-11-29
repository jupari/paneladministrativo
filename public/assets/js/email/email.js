$(function() {

    $('#cuenatamadre-table').DataTable({
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
        ajax: '/admin/admin.emails.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'correo', name: 'correo'},
            { data: 'token', name: 'token'},
            { data: 'expiracion', name: 'expiracion'},
            { data: 'acciones', name: 'acciones', className: 'exclude text-center'},
        ],
        order: [[ 1, "asc" ]],
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


    //configuracion para ver la informacion del correo
    $('.email-preview').on('click', function() {
        var subject = $(this).find('h5').text();
        var from = $(this).find('.info').first().html();
        var received = $(this).find('.info').last().html();
        var body = $(this).data('body');

        $('#email-subject').text(subject);
        $('#email-from').html(from);
        $('#email-received').html(received);
        $('#email-content').text(body);
    });

});

// function Cargar()
// {
//     let table = $('#cppal-table').DataTable();
//     table.ajax.reload();
// }

// // Limpiar inputs
// function cleanInput() {

//     // Campos usuario
//     const rolesFields = ['nombre', 'email', 'password', 'usuario_reg'];
//     rolesFields.forEach(field => {
//         $('#' + field).val('');
//     });

//     $('.permissions').prop('checked', false);

// }

// function showCppal(btn) {

//      // LIMPIAR CAMPOS
//     cleanInput();

//     $.get("/admin/admin.cuentappal/" + btn, (response) => {

//         const cuentaPpal = response.cuentappal;

//         console.log('cuentaPpal', cuentaPpal);
//         const cuentaPpalFields = ['nombre', 'email', 'password', 'usuario_reg'];

//         cuentaPpalFields.forEach(field => {
//             $('#' + field).val(cuentaPpal[field]);
//         });
//         // Select the option with a value of '1'
//         $('#usuario_reg').trigger('change');

//     });

// }

// //Registrar usuario
// function regCuentaPpal()
// {
//     $('#myModal').modal();
//     $('#exampleModalLabel').html('Crear Cuenta Principal');

//     // LIMPIAR CAMPOS
//     cleanInput();

//     // FIN LIMPIAR CAMPOS

//     let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
//             '<button id="registro" class="btn btn-primary" onclick="registerCuentaPpal()">Agregar</button>';

//     $(".modal-footer").html(r);

// }

// function registerCuentaPpal()
// {

//     const route = "/admin/admin.cuentappal";

//     let ajax_data = {

//         // Datos formulario
//         nombre: $('#nombre').val(),
//         email: $('#email').val(),
//         password:$('#password').val(),
//         usuario_reg:$('#usuario_reg').val(),
//     };

//     $.ajax({
//         url: route,
//         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
//         type: 'POST',
//         dataType: 'json',
//         data: ajax_data,
//     }).then(response => {

//         Cargar();

//         $('#myModal').modal('toggle');
//         toastr.success(response.message);

//     })
//     .catch(e => {

//         const arr = e.responseJSON;
//         const toast = arr.errors;

//         if (e.status == 422) {

//             for (const key in toast) {
//                 if (toast.hasOwnProperty(key) && toast[key] != null) {
//                     toastr.error(toast[key][0]);
//                 }
//             }

//         }

//         else if(e.status == 403){

//             $('#myModal').modal('toggle');
//             toastr.warning(arr.error);

//         }

//     });

// }

// // Actualizar usuario
// function upCuentaPpal(btn)
// {
//     $('#myModal').modal();
//     $('#exampleModalLabel').html('Editar Cuenta Principal');

//     // LIMPIAR CAMPOS
//     cleanInput();

//     showCppal(btn);
//     // FIN LIMPIAR CAMPOS

//     let u = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
//             '<button id="editar" class="btn btn-primary" onclick="updateCuentaPpal('+btn+')">Guardar</button>';

//     $(".modal-footer").html(u);

// }

// function updateCuentaPpal(btn)
// {
//     $('#myModal').modal();

//     const route = "/admin/admin.cuentappal/"+btn;

//     // const selectedPermissions = [];

//     // // Recorre los checkboxes y agrega los seleccionados al array
//     // $('.permissions:checked').each(function() {
//     //     selectedPermissions.push($(this).val());
//     // });

//     let ajax_data = {

//         // Datos formulario
//         nombre: $('#nombre').val(),
//         email: $('#email').val(),
//         password:$('#password').val(),
//         usuario_reg:$('#usuario_reg').val(),
//     };

//     $.ajax({
//         url: route,
//         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
//         type: 'POST',
//         dataType: 'json',
//         data: ajax_data,
//     }).then(response => {

//         Cargar();

//         $('#myModal').modal('toggle');
//         toastr.success(response.message);

//     })
//     .catch(e => {

//         const arr = e.responseJSON;
//         const toast = arr.errors;

//         if (e.status == 422) {

//             for (const key in toast) {
//                 if (toast.hasOwnProperty(key) && toast[key] != null) {
//                     toastr.error(toast[key][0]);
//                 }
//             }

//         }

//         else if(e.status == 403){

//             $('#myModal').modal('toggle');
//             toastr.warning(arr.message);

//         }

//     });

// }


function upVerCorreos(accountId){

    $.get("/admin/admin.emails.getemail/" + accountId, (response) => {

        const messages = response.messages;

        const emailList = $('#email-list');
        emailList.empty(); // Limpiar la lista de correos electrónicos

        // Iterar sobre los correos electrónicos recibidos
        messages.forEach(function(email) {
            var listItem = $('<li class="list-group-item email-preview" data-body="' + email.bodyPreview + '">');
            listItem.append('<h5>' + email.subject + '</h5>');
            listItem.append('<p class="info"><strong>De:</strong> ' + email.from.emailAddress.address + '<br><strong>Recibido:</strong> ' + email.receivedDateTime + '</p>');

            if (email.hasAttachments) {
                listItem.append('<span class="badge badge-danger">Adjuntos</span>');
            }

            emailList.append(listItem);
        });

        // Agregar evento de clic para mostrar el cuerpo del correo electrónico
        $('.email-preview').on('click', function() {
            var subject = $(this).find('h5').text();
            var from = $(this).find('.info').first().html();
            var received = $(this).find('.info').last().html();
            var body = $(this).data('body');

            $('#email-subject').text(subject);
            $('#email-from').html(from);
            $('#email-received').html(received);
            $('#email-content').text(body);
        });

     });

}
