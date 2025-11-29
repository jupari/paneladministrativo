$(function () {

    configTablaBodegas();

    $('#btn-nuevo-bodega').click(() => {
        $('#form-bodega')[0].reset();
        $('#bodega_id').val('');
        $('#ModalBodega').modal('show');
    });

    $('#form-bodega').submit(function (e) {
        e.preventDefault();
        let id = $('#bodega_id').val();
        let url = id ? `/admin/admin.bodegas.update/${id}` : `/admin/admin.bodegas.store`;
        let method = id ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            data: {
                codigo: $('#codigo').val(),
                nombre: $('#nombre').val(),
                ubicacion: $('#ubicacion').val(),
                active: $('#activo').is(':checked') ? 1 : 0
            },
            success: function (res) {
                $('#ModalBodega').modal('hide');
                tableBodegas.ajax.reload();
                toastr.success(res.message);
                limpiarValidaciones();
            },
            error: function (xhr) {
                limpiarValidaciones();
                toastr.error(xhr.responseJSON.message);
                if (xhr.status == 422) {

                $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#error_'+key).text(value[0]);
                    });
                }
            }
        });
    });

    $('#bodegas-table').on('click', '.editar-bodega', function () {
        limpiarValidaciones();
        let id = $(this).data('id');
        $.get(`/admin/admin.bodegas.edit/${id}`, function (data) {
            let row = data.data;
            $('#bodega_id').val(row.id);
            $('#codigo').val(row.codigo);
            $('#nombre').val(row.nombre);
            $('#ubicacion').val(row.ubicacion);
            $('#activo').prop('checked', row.active == 1);
            $('#ModalBodega').modal('show');
        });
    });

    $('#bodegas-table').on('click', '.eliminar-bodega', function () {
        if (!confirm("¿Eliminar esta bodega?")) return;
        let id = $(this).data('id');
        $.ajax({
            url: `/admin/admin.bodegas.destroy/${id}`,
            type: 'DELETE',
            data: {_token: "{{ csrf_token() }}"},
            success: function (res) {
                table.ajax.reload();
                toastr.success(res.message);
            }
        });
    });
});

function limpiarValidaciones(){
    $('#error_codigo').text('');
    $('#error_nombre').text('');
    $('#error_ubicacion').text('');
}


let tableBodegas=null;
function configTablaBodegas() {
    tableBodegas = $('#bodegas-table').DataTable({
        processing: false,
        responsive: true,
        serverSide: true,
        dom:
            "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
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
            }
        ],
        ajax: "/admin/admin.bodegas.index",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'id', name: 'id'},
            {data: 'codigo', name: 'codigo'},
            {data: 'nombre', name: 'nombre'},
            {data: 'ubicacion', name: 'ubicacion'},
            {data: 'estado', name: 'estado'},
            {data: 'acciones', name: 'acciones', orderable: false, searchable: false}
        ],
        language: {
            url: '/assets/js/spanish.json'
        },
        columnDefs: [
            {
                targets: 1,
                visible: false
            }
        ],
        order: [[1, 'asc']],
        pageLength: 8,
        lengthMenu: [
            [2, 4, 6, 8, 10, -1],
            [2, 4, 6, 8, 10, 'Todo(s)']
        ]
    });
}
