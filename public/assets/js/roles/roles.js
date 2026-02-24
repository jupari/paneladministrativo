$(function() {

    $('#rol-table').DataTable({
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
        ajax: '/admin/admin.roles.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'nombre', name: 'nombre'},
            { data: 'permisos', name: 'permisos'},
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
});

function Cargar()
{
    let table = $('#rol-table').DataTable();
    table.ajax.reload();
}

// Limpiar inputs
function cleanInput() {

    // Campos usuario
    const rolesFields = ['name', 'guard_name'];
    rolesFields.forEach(field => {
        $('#' + field).val('');
    });

    $('.permissions').prop('checked', false);

}

function showRol(btn) {

     // LIMPIAR CAMPOS
    cleanInput();

    $.get("/admin/admin.roles/" + btn, (response) => {

        const roles = response.roles;

        const rolesFields = ['name',  'guard_name'];

        rolesFields.forEach(field => {
            $('#' + field).val(roles[field]);

        });

        const permisosSeleccionados = response.roles.permissions;

        // Marcar los checkboxes basados en los permisos obtenidos de la respuesta
        permisosSeleccionados.forEach((permiso) => {

            $(".permissions[value='" + permiso.name + "']").prop('checked', true);
        });

    });

}

//Registrar usuario
function regRol()
{

    $('#myModal').modal('show')
    $('#exampleModalLabel').html('Crear rol');

    // LIMPIAR CAMPOS
    cleanInput();

    // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
    let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
            '<button id="registro" class="btn btn-primary" onclick="registerRol()">Agregar</button>';

    $(".modal-footer").html(r);

}

function registerRol()
{

    const route = "/admin/admin.roles";

    const selectedPermissions = [];

    // Recorre los checkboxes y agrega los seleccionados al array
    $('.permissions:checked').each(function() {
        selectedPermissions.push($(this).val());
    });

    let ajax_data = {

        // Datos usuario
        name: $('#name').val(),
        guard_name: $('#guard_name').val(),
        permissions: selectedPermissions,

    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        $('#myModal').modal('toggle');
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

            $('#myModal').modal('toggle');
            toastr.warning(arr.error);

        }

    });

}

// Actualizar usuario
function upRol(btn)
{
    $('#myModal').modal('show')
    $('#exampleModalLabel').html('Asignar o quitar permisos');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showRol(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
            '<button id="editar" class="btn btn-primary" onclick="updateRol('+btn+')">Guardar</button>';

    $(".modal-footer").html(u);

}

function updateRol(btn)
{
    $('#myModal').modal('show');
    const route = "/admin/admin.roles/"+btn;

    const selectedPermissions = [];

    // Recorre los checkboxes y agrega los seleccionados al array
    $('.permissions:checked').each(function() {
        selectedPermissions.push($(this).val());
    });

    let ajax_data = {

        // Datos usuario
        name: $('#name').val(),
        guard_name: $('#guard_name').val(),
        permissions: selectedPermissions

    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
        type: 'PUT',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        $('#myModal').modal('toggle');
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

            $('#myModal').modal('toggle');
            toastr.warning(arr.message);

        }

    });

}


function limpiarValidaciones(){
    $('#error_name').text('');
    $('#error_guard_name').text('');
}
