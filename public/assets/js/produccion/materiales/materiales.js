$(function () {
    toastr.options = { positionClass: "toast-bottom-right" }
    Cargar();
});

var myModal = $('#ModalMaterial');

function Cargar() {
    if ($.fn.DataTable.isDataTable('#materiales-table')) {
        $('#materiales-table').DataTable().destroy();
    }
    $('#materiales-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        ajax: '/admin/admin.materiales.index',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'id' },
            { data: 'codigo' },
            { data: 'nombre' },
            { data: 'descripcion' },
            { data: 'unidad_medida' },
            { data: 'active', className:'text-center' },
            { data: 'acciones', orderable: false, searchable: false }
        ],
        order: [[1, "asc"]],
        pageLength: 10,
    });
}

function cleanInput() {
    $('#id').val('');
    $('#nombre').val('');
    $('#descripcion').val('');
    $('#unidad_medida').val('');
    $('#active').prop('checked', false);
    limpiarValidaciones();
}

function regMaterial() {
    myModal.modal('show');
    $('.modal-title').html('Registrar Material');
    cleanInput();
    let footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="storeMaterial()">Agregar</button>
    `;
    $(".modal-footer").html(footer);
}

function storeMaterial() {
    let activo = $('#active').is(':checked') ? 1 : 0;
    let data = {
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        descripcion: $('#descripcion').val(),
        unidad_medida: $('#unidad_medida').val(),
        active: activo,
    };
    $.ajax({
        url: '/admin/admin.materiales.store',
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(response => {
        toastr.success(response.message)
        myModal.modal('hide')
        Cargar()
    })
    .fail(e => handleErrors(e))

}

function upMaterial(id) {
    myModal.modal('show');
    $('.modal-title').html('Editar Material');
    cleanInput();

    $.get("/admin/admin.materiales.edit/" + id, (response) => {
        let mat = response.data;
        $('#id').val(mat.id);
        $('#nombre').val(mat.nombre);
        $('#descripcion').val(mat.descripcion);
        $('#unidad_medida').val(mat.unidad_medida);
        $('#active').prop('checked', mat.active == 1);

        let footer = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="updateMaterial(${id})">Guardar</button>
        `;
        $(".modal-footer").html(footer);
    });
}

function updateMaterial(id) {
    let activo = $('#active').is(':checked') ? 1 : 0;
    let data = {
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        descripcion: $('#descripcion').val(),
        unidad_medida: $('#unidad_medida').val(),
        active: activo,
    };

    $.ajax({
        url: "/admin/admin.materiales/" + id,
        method: 'PUT',
        data: data,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })
    .done(response => {
        toastr.success(response.message);
        myModal.modal('hide');
        Cargar();
    })
    .fail(e => handleErrors(e));
}

function deleteMaterial(id) {
    if (!confirm("¿Eliminar material?")) return;

    $.ajax({
        url: "/admin/admin.materiales.destroy/" + id,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })
    .done(response => {
        toastr.success(response.message);
        Cargar();
    })
    .fail(e => toastr.error("Error eliminando material"));
}

function limpiarValidaciones() {
    $('#error_nombre').text('');
    $('#error_descripcion').text('');
    $('#error_unidad_medida').text('');
    $('#error_active').text('');
}

function handleErrors(e) {
    limpiarValidaciones();
    if (e.status == 422) {
        $.each(e.responseJSON.errors, function (key, value) {
            $('#error_' + key).text(value[0]);
        });
        toastr.warning("Revisa los errores en los campos.");
    } else {
        toastr.error("Error inesperado");
    }
}


$(document).on('input', 'input[type="text"], textarea', function() {
    this.value = this.value.toUpperCase();
});
