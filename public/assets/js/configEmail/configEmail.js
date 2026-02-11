$(function() {

    $('#accounts-table').DataTable({
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
        ajax: '/admin/admin.configemail.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'correo', name: 'correo'},
            { data: 'token', name: 'token', className:'text-break'},
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
        console.log('body', body);
        $('#email-subject').text(subject);
        $('#email-from').html(from);
        $('#email-received').html(received);
        $('#email-content').text(body);
    });

});

function Cargar()
{
    let table = $('#cppal-table').DataTable();
    table.ajax.reload();
}

function abrirModal(titulo)
{

    const myModal = new bootstrap.Modal(document.getElementById('myModal'), {
        keyboard: false
      })
    myModal.modal('show')
    $('#exampleModalLabel').html(titulo);
    $('#spinner-email').modal('show');
    let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';

    $(".modal-footer").html(r);

}


function configToken(email){

    const route = "/admin/auth/redirect/"+ email;

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
        type: 'GET',
        dataType: 'json',
    }).then(response => {
        localStorage.setItem('email_consulta',response.email_consulta);
        window.location.href = response.redirectUrl;
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

            $('#myModal').modal('toggle');
            toastr.warning(arr.message);

        }

    });




    // $.get("/admin/auth/redirect/" + email, (response) => {

    //     const messages = response;

    //     console.log('messages', messages);
        // const emailList = $('#email-list');
        // emailList.empty(); // Limpiar la lista de correos electrónicos

        // // Iterar sobre los correos electrónicos recibidos
        // messages.forEach(function(email) {
        //     var listItem = $('<li class="list-group-item email-preview" data-body="' + email.bodyPreview + '">');
        //     listItem.append('<h5>' + email.subject + '</h5>');
        //     listItem.append('<p class="info"><strong>De:</strong> ' + email.from.emailAddress.address + '<br><strong>Recibido:</strong> ' + email.receivedDateTime + '</p>');

        //     if (email.hasAttachments) {
        //         listItem.append('<span class="badge badge-danger">Adjuntos</span>');
        //     }

        //     emailList.append(listItem);
        // });

        // // Agregar evento de clic para mostrar el cuerpo del correo electrónico
        // $('.email-preview').on('click', function() {
        //     var subject = $(this).find('h5').text();
        //     var from = $(this).find('.info').first().html();
        //     var received = $(this).find('.info').last().html();
        //     var body = $(this).data('body');

        //     $('#email-subject').text(subject);
        //     $('#email-from').html(from);
        //     $('#email-received').html(received);
        //     $('#email-content').text(body);
        // });

    //});

}


function consultarCorreosCodigoAcceso(cuentaId){

    abrirModal('Código de acceso temporal');
    const emailList = $('#email-list');
    emailList.empty(); // Limpiar la lista de correos electrónicos
    $('#email-content').html('');//limpiar el contenido del correo
    $('#email-from').html('');
    $('#email-received').html('');
    $.get("/admin/admin.emails.getemail/" + cuentaId+'/codigo', (response) => {

        const messages = response.messages;

        // Iterar sobre los correos electrónicos recibidos
        messages.forEach(function(email) {

            // Iterar sobre los correos electrónicos recibidos

                const listItem = $('<li class="list-group-item email-preview" data-body="' + email.bodyPreview + '">');
                listItem.append('<p class="info">' + email.subject + '</p>');
                listItem.append('<p class="info"><strong>De:</strong> ' + email.from + '<br><strong>Recibido:</strong> ' + email.receivedDateTime + '</p>');

                if (email.hasAttachments) {
                    listItem.append('<span class="badge badge-danger">Adjuntos</span>');
                }

                $('#spinner-email').modal('hide');
                emailList.append(listItem);

            // Agregar evento de clic para mostrar el cuerpo del correo electrónico
            listItem.on('click', function() {
                showEmailDetails(email);
            });
        });

    });
}


function consultarCorreosReestablecimiento(cuentaId){

    abrirModal('Restablecimiento de contraseña');
    const emailList = $('#email-list');
    emailList.empty(); // Limpiar la lista de correos electrónicos
    $('#email-content').html('');//limpiar el contenido del correo
    $('#email-from').html('');
    $('#email-received').html('');
    $.get("/admin/admin.emails.getemail/" + cuentaId+'/reset', (response) => {

        const messages = response.messages;

        // Iterar sobre los correos electrónicos recibidos
        messages.forEach(function(email) {

            // Iterar sobre los correos electrónicos recibidos

                const listItem = $('<li class="list-group-item email-preview" data-body="' + email.bodyPreview + '">');
                listItem.append('<p class="info">' + email.subject + '</p>');
                listItem.append('<p class="info"><strong>De:</strong> ' + email.from + '<br><strong>Recibido:</strong> ' + email.receivedDateTime + '</p>');

                if (email.hasAttachments) {
                    listItem.append('<span class="badge badge-danger">Adjuntos</span>');
                }

                $('#spinner-email').modal('hide');
                emailList.append(listItem);

            // Agregar evento de clic para mostrar el cuerpo del correo electrónico
            listItem.on('click', function() {
                showEmailDetails(email);
            });
        });

    });
}

function showEmailDetails(email) {
    $('#email-subject').text(email.subject);
    $('#email-from').html('<strong>De:</strong> ' + email.from);
    $('#email-received').html('<strong>Recibido:</strong> ' + email.receivedDateTime);
    $('#email-content').html(email.body);
}
