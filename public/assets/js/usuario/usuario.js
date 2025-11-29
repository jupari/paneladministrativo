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

    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        toggleIcon.classList.toggle('fa-eye');
        toggleIcon.classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggleRPassword').addEventListener('click', function () {
        const passwordField = document.getElementById('rpassword');
        const toggleIcon = document.getElementById('toggleIconR');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        toggleIcon.classList.toggle('fa-eye');
        toggleIcon.classList.toggle('fa-eye-slash');
    });
});

//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('myModal'), {
    keyboard: false
})

const myModalpss = new bootstrap.Modal(document.getElementById('myModalPss'), {
    keyboard: false
})


let oldFiles = [];

function Cargar() {
    if ($.fn.DataTable.isDataTable('#user-table')) {
        $('#user-table').DataTable().destroy();
    }
    let table = $('#user-table').DataTable(
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
            ajax: '/admin/admin.users.index',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
                { data: 'nombres', name: 'nombres'},
                { data: 'email', name: 'correo'},
                { data: 'identificacion', name: 'identificacion'},
                { data: 'rol', name: 'rol'},
                { data: 'fecha_cr', name: 'fecha_cr'},
                { data: 'active', name: 'active',className:'text-center'},
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

    // Campos usuario
    const usuarioFields = ['name','email', 'role','identificacion','password','password-confirm','reset-password'];
    usuarioFields.forEach(field => {
        $('#' + field).val('');
    });


}

function showCustomUser(btn) {

    $.get("/admin/admin.users.edit/" + btn, (response) => {

        const usr = response.usuario;
        const roles = usr.roles

        // Roles
        roles.map(data => {
            $('#role').val(data.name)
        });


        // Mapear los campos del usuario
        const usuarioFields = ['id','name','email','active','identificacion'];
        usuarioFields.forEach(field => {
            if(field!='active'){
                $('#' + field).val(usr[field]);
            }else{
                $('#' + field).prop('checked',usr[field]==1?true:false);
            }
        });

        // Limpiar oldFiles
        oldFiles = [];
    });

}

// //Registrar usuario
function regUsr() {
    myModal.show()
    $('#exampleModalLabel').html('Registrar usuario');
    $('#panelpassword').show();

    // LIMPIAR CAMPOS
    cleanInput();
     // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
    let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button type="button" class="btn btn-primary" onclick="registerUsr()" >Agregar</button>';

    $(".modal-footer").html(r);

}

function registerUsr() {
    const route = "/admin/admin.users.store";

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    let activo=$('#active').is(':checked')?1:0;
    $('#active').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    ajax_data.append('name', $('#name').val());
    ajax_data.append('email', $('#email').val());
    ajax_data.append('role', $('#role').val());
    ajax_data.append('active', activo.toString());
    ajax_data.append('password', $('#password').val());
    ajax_data.append('identificacion', $('#identificacion').val());

    const verificarContraseña =  validacionPassword();
    if(!verificarContraseña){
        $('#repetir-password').text('La contraseña no coincide, por favor verifique');
        return;
    }

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMAGENES POR AJAX
        processData: false,
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
                //$('#myModal').data('bs.modal')._config.backdrop = 'static';
                //$('#myModal').find('[data-dismiss="modal"]').prop('disabled', true);

                // for (const key in toast) {
                //     if (toast.hasOwnProperty(key) && toast[key] != null) {
                //         toastr.error(toast[key][0]);
                //     }
                // }

            }

            else if (e.status == 403) {

                $('#myModal').modal('toggle');
                toastr.warning(arr.error);

            }

        });

}

// // Actualizar usuario
function upUsr(btn) {
    myModal.show()
    $('#exampleModalLabel').html('Editar usuario');
    $('#panelpassword').hide();
    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showCustomUser(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
        '<button id="editar" class="btn btn-primary" onclick="updateUsr(' + btn + ')">Guardar</button>';




    $(".modal-footer").html(u);

}

function updateUsr(btn) {
    const route = "/admin/admin.users.update/" + btn;


    let ajax_data = new FormData();

    const fields = ['name', 'email', 'role','identificacion'];
    let activo=$('#active').is(':checked')?1:0;
    $('#active').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })


    const verificarContraseña =  validacionPassword();
    if(!verificarContraseña){
        $('#repetir-password').text('La contraseña no coincide, por favor verifique');
        return;
    }

    ajax_data.append('name', $('#name').val());
    ajax_data.append('email', $('#email').val());
    ajax_data.append('role', $('#role').val());
    ajax_data.append('active', activo.toString());
    ajax_data.append('password', $('#password').val());
    ajax_data.append('identificacion', $('#identificacion').val());


    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'POST' },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMAGENES POR AJAX
        processData: false,
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

                $('#myModal').data('bs.modal')._config.backdrop = 'static';

                // $('#myModal').find('[data-dismiss="modal"]').prop('disabled', true);
                $.each(toast, function(key, value) {
                    $('#error_'+key).text(value[0]);
                });

                // for (const key in toast) {
                //     if (toast.hasOwnProperty(key) && toast[key] != null) {
                //         toastr.error(toast[key][0]);
                //     }
                // }

            }

            else if (e.status == 403) {

                myModal.toggle();
                toastr.warning(arr.message);

            }

        });

}

// // Cambiar password
function changep(btn) {


    myModalpss.show()

    $('#exampleModalLabelPss').html('<b>Cambiar contraseña</b>');

    // LIMPIAR CAMPOS
    cleanInput(btn);
    limpiarValidaciones();

    let shw = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>' +
        '<button type="button" class="btn btn-primary" onclick="usrChange(' + btn + ')" >Agregar</button>';


    $(".pass").html(shw);

}

function usrChange(btn) {
    const route = "/admin/admin.users.changepass/" + btn;

    if($('#reset-password').val()!==$('#password-confirm').val()){
        $('#error_cpassword').text('Las contraseñas son diferentes');
        return;
    }else{
        $('#error_cpassword').text('');
    }
    let ajax_data = {

        // Datos usuario
        current_password: $('#current_password').val(),
        password: $('#reset-password').val(),
        password_confirmation: $('#password-confirm').val(),

    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        myModalpss.toggle();
        toastr.success(response.message);

    })
        .catch(e => {

            const arr = e.responseJSON;
            const toast = arr.errors;

            if (e.status == 422) {
                $.each(toast, function(key, value) {
                    $('#error_reset_'+key).text(value[0]);
                });

                // for (const key in toast) {
                //     if (toast.hasOwnProperty(key) && toast[key] != null) {
                //         toastr.error(toast[key][0]);
                //     }
                // }
            }

            else if (e.status == 403) {

                myModalpss.toggle();
                toastr.warning(arr.message);

            }

        });

}


function limpiarValidaciones(){
    $('#error_email').text('');
    $('#error_name').text('');
    $('#error_password').text('');
    $('#repetir-password').text('');
    $('#error_reset_password').text('');
    $('#error_cpassword').text('');

}


function validacionPassword(){
    const pass1 =  $('#password').val();
    const pass2 =  $('#rpassword').val();
    let resp=true
    if(pass1!==pass2){
        resp =  false;
    }

    return resp;
}

// // Quitar archivo
// function quitarArchivo(btn, id, nameFile) {

//     const route = "/auth/quitar/archivo/" + id;
//     const idArch = $(this).data('id');

//     const index = oldFiles.indexOf(nameFile);

//     if (index !== -1) {
//         oldFiles[index] = null;
//     }

//     Swal.fire({
//         title: "¿Desea quitar este archivo?",
//         showDenyButton: true,
//         confirmButtonText: "Sí",
//         denyButtonText: "No",
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.ajax({
//                 url: route,
//                 headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
//                 method: 'DELETE',
//                 dataType: 'json',
//             }).then(response => {
//                 //Swal.fire(response.message, "", "success");

//                 $(btn).hide();

//                 $(btn).siblings('.showFile').hide();

//                 $(btn).closest('.form-group').find('.custom-file').show();

//                 $(btn).closest('.form-group').find('.arch' + idArch).show();

//                 $('.lb_' + (idArch)).text('');

//             }).catch(e => {
//                 const arr = e.responseJSON;
//                 if (e.status == 403) {
//                     Swal.fire(arr.error, "", "warning");
//                 }
//             });
//         }
//     });

// }
