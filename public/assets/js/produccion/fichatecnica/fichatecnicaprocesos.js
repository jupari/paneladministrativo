const fichatecnica_id_p = $('#fichatecnica_id').val()
const codigoBoceto_p = $('#codigo').val()

let TableProcesos=null;

async function confTableProcesos() {
    let procesos = await  $.getJSON("/admin/admin.procesos/list");

    return {
        procesos: procesos,
    };
}

confTableProcesos().then(catalogos => {
    const fichaid = fichatecnica_id_p=='' || fichatecnica_id_p==undefined?'0':fichatecnica_id;
    TableProcesos = $('#procesos-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `/admin/admin.fichas-tecnicas-procesos.index/${fichaid}`,
        language: {
                emptyTable: "No hay materiales registrados aún."
            },
        columns: [
            { data: 'DT_RowIndex',  width: "5rem", name: 'DT_RowIndex' },
            {
                data: 'codigo_proceso',
                 width: "8rem",
                render: function(data, type, row) {
                    let options = catalogos.procesos.map(m =>
                        `<option value="${m.codigo}" ${m.codigo === data ? 'selected' : ''}>${m.nombre}</option>`
                    ).join('');
                    return `<select class="form-select codigo_proceso" data-id="${row.id}">${options}</select>`;
                }
            },
            {
                data: 'observacion',
                width: "12rem",
                render: function(data, type, row) {
                    // Mostrar input con valor actual
                    return `<input type="text"
                                class="form-control observacion"
                                style="text-align: start;"
                                data-id="${row.id}"
                                value="${data ?? ''}">`;
                }
            },
            {
                data: 'costo',
                width: "9rem",
                render: function(data, type, row) {
                    // Mostrar input con valor actual
                    return `<input type="number" step="0.01" min="0"
                                class="form-control costo"
                                style="text-align: end;"
                                data-id="${row.id}"
                                value="${data ?? 0}">`;
                }
            },
            {
                data: 'acciones',
                width: "4rem",
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-success btn-sm guardarproceso" data-id="${row.id}">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-danger btn-sm eliminarproceso" data-id="${row.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Guardar cambios de la fila
    $('#procesos-table').on('click', '.guardarproceso', function () {
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        // Leer valores de los inputs/selects en la fila
        let payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            fichatecnica_id: fichatecnica_id_p,
            codigo_proceso: row.find('.codigo_proceso').val() ?? row.find('td:eq(1)').text(),
            observacion: row.find('.observacion').val(),
            // costo: fmtMiles.format(row.find('.costo').val()??0),
            costo: row.find('.costo').val()??0,
            codigo:codigoBoceto_p
        };

        $.post(`/admin/admin.fichas-tecnicas-procesos/${id}`, payload)
            .done(response => {
                toastr.success(response.message);
                TableProcesos.ajax.reload(null, true); // recargar solo data
            })
            .fail(xhr => {
                toastr.error("Error al guardar los procesos");
                console.error(xhr.responseText);
            });
    });

    // Eliminar fila
    $('#procesos-table').on('click', '.eliminarproceso', function () {
        let id = $(this).data('id');

        if (confirm("¿Eliminar este proceso?")) {
            $.ajax({
                url: `/admin/admin.fichas-tecnicas-procesos/${id}`,
                type: "DELETE",
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    toastr.success(response.message);
                    TableProcesos.ajax.reload(null, true);
                },
                error: function (xhr) {
                    toastr.error("Error al eliminar el proceso");
                }
            });
        }
    });

});



const nuevoRegProceso = $('#add-proceso').on('click', function () {

    if(fichatecnica_id_p=='' || fichatecnica_id_p==undefined){
        toastr.error('Antes de adicionar un proceso debe crear la ficha técnica.');
    }else{
        let nuevoMaterial = {
            fichatecnica_id: fichatecnica_id_p, // este lo debes tener definido
            codigo_proceso: '',  // vacío para que se edite en la tabla
            observacion:'',
            costo: 0,
            codigo: codigoBoceto_p
        };

        $.ajax({
            url: "/admin/admin.fichas-tecnicas-procesos.store",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') },
            data: nuevoMaterial,
            success: function (response) {
                toastr.success("Proceso agregado correctamente");
                TableProcesos.ajax.reload(null, true);
            },
            error: function (xhr) {
                toastr.error("Error al agregar el proceso");
                console.error(xhr.responseText);
            }
        });
    }


});
