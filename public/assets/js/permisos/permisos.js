$(function() {

    $('#perm-table').DataTable({
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
        ajax: '/admin/admin.permission.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'nombre', name: 'nombre'},
            { data: 'descripcion', name: 'descripcion'},
            { data: 'guard_name', name: 'guard_name'},
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
});

function Cargar()
{
    let table = $('#perm-table').DataTable();
    table.ajax.reload();
}

// Limpiar inputs
function cleanInput() {

    // Campos usuario
    const rolesFields = ['name', 'description','guard_name'];
    rolesFields.forEach(field => {
        $('#' + field).val('');
    });

}

function showPerm(btn) {

     // LIMPIAR CAMPOS
    cleanInput();

    $.get("/admin/admin.permission/" + btn, (response) => {

        const permisos = response.permission;

        const rolesFields = ['name', 'description', 'guard_name'];

        rolesFields.forEach(field => {
            $('#' + field).val(permisos[field]);

        });

    });

}

//Registrar usuario
function regPerm()
{
    $('#modalpermiso').modal();
    $('#exampleModalLabel').html('Crear permiso');

    // LIMPIAR CAMPOS
    cleanInput();

    // FIN LIMPIAR CAMPOS
    limpiarValidaciones();
    let r = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
            '<button id="registro" class="btn btn-primary" onclick="registerPerm()">Agregar</button>';

    $(".modal-footer").html(r);

}

function registerPerm()
{
    const route = "/admin/admin.permission";

    let ajax_data = {

        // Datos usuario
        name: $('#name').val(),
        description:$('#description').val(),
        guard_name: $('#guard_name').val(),

    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        $('#modalpermiso').modal('toggle');
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

            $('#modalpermiso').modal('toggle');
            toastr.warning(arr.error);

        }

    });

}

// Actualizar usuario
function upPerm(btn)
{
    const myModal = new bootstrap.Modal(document.getElementById('modalpermiso'), {
        keyboard: false
      })
    myModal.show()
    $('#exampleModalLabel').html('Editar permiso');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showPerm(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>'+
            '<button id="editar" class="btn btn-primary" onclick="updatePerm('+btn+')">Guardar</button>';

    $(".modal-footer").html(u);

}

function updatePerm(btn)
{
    const route = "/admin/admin.permission/"+btn;

    let ajax_data = {

        // Datos usuario
        name: $('#name').val(),
        description:$('#description').val(),
        guard_name: $('#guard_name').val(),

    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        $('#modalpermiso').modal('toggle');
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

            $('#modalpermiso').modal('toggle');
            toastr.warning(arr.message);

        }

    });

}


function limpiarValidaciones(){
    $('#error_name').text('');
    $('#error_description').text('');
    $('#error_guard_name').text('');
}
