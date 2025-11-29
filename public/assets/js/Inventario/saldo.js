// let saldosproductosTable = null;
$(function () {
    configTablaSaldos();

    // Ver saldos
    $('#saldos-table').on('click', '.ver-saldos', async function () {
        console.log('ver saldos');

        const codigo = $(this).data('codigo');
        const bodega = $(this).data('bodega');

        console.log('Código:', codigo);
        console.log('Bodega:', bodega);

        $('#codigo').val(codigo);
        $('#bodega').val(bodega);
        $('#bodega').prop('readonly', true);
        $('#codigo').prop('readonly', true);
        let producto_id = $(this).data('producto-id');
        let bodega_id = $(this).data('bodega-id');
        await initTablaDetalles(producto_id, bodega_id);
        $('#ModalSaldoProducto').modal('show');
    });
});


function limpiarValidaciones() {
}


let tableSaldos = null;
function configTablaSaldos() {
    tableSaldos = $('#saldos-table').DataTable({
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
        ajax: "/admin/admin.saldos.index",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'id', name: 'id'},
            {data: 'producto', name: 'producto'},
            {data: 'talla', name: 'talla'},
            {data: 'color', name: 'color'},
            {data: 'bodega', name: 'bodega'},
            {data: 'saldo_cantidad', name: 'saldo_cantidad'},
            {data: 'ultimo_costo', name: 'ultimo_costo'},
            {data: 'acciones', name: 'acciones', orderable: false, searchable: false},

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

const fmtMiles = new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})

const toNumber = v => {
    if (v === null || v === undefined || v === '') return NaN
    if (typeof v === 'number') return v
    const n = Number(String(v).replace(',', '.'))
    return Number.isFinite(n) ? n : NaN
}


async function initTablaDetalles(producto_id, bodega_id) {
    // 🧹 Si ya existe una tabla, destrúyela
    if (window.saldosproductosTable) {
        window.saldosproductosTable.destroy();
        $('#saldosproductos-table').empty();
    }

    // 🔹 3️⃣ Crear tabla
    window.saldosproductosTable = new Tabulator("#saldosproductos-table", {
        layout: "fitColumns",
        height: "300px",
        ajaxURL: `/admin/admin.saldos/list/${producto_id || 0}/${bodega_id || 0}`,
        ajaxConfig: "GET",
        pagination: "local",
        paginationSize: 20,
        columns: [
            { title: "ID", field: "id", visible: false },
            { title: "num_doc", field: "num_doc", visible: true },
            { title: "FechaCreción", field: "created_at" },
            {
                title: "Código Producto",
                field: "codigo_producto",
                formatter: function (cell) {
                    const data = cell.getRow().getData();
                    return data.codigo_producto ? data.codigo_producto +'-'+ data.producto.nombre : '-';
                }
            },
            { title: "Talla", field: "talla"},
            { title: "Color", field: "color"},
            {
                title: "Bodega",
                field: "bodega_id",
                formatter: function (cell) {
                    const data = cell.getRow().getData();
                    return data.bodega ? data.bodega.nombre : '-';
                }
            },
            { title: "Tipo", field: "tipo"},
            {
                title: 'Cantidad',
                field: 'cantidad',
                hozAlign: 'right',
                headerHozAlign: 'right',
                formatter: cell => {
                    const v = parseFloat(cell.getValue()) || 0;
                    return v.toLocaleString('es-CO', { minimumFractionDigits: 2 });
                },
            },
            { title: "costo_unitario", field: "costo_unitario"},

        ],
    });

    const tabla = window.saldosproductosTable;

    $('#buscar-detalles').off('keyup').on('keyup', function () {
    const value = $(this).val().trim().toLowerCase();

        if (value) {
            tabla.setFilter(function (data) {
                // Combina código + nombre (de la etiqueta o concatenado)
                const codigoNombre = `${data.codigo_producto ?? ''}-${data.producto?.nombre ?? ''}`.toLowerCase();
                // Filtro flexible: busca por código, nombre o campos relacionados
                return (
                    codigoNombre.includes(value) ||
                    (data.num_doc ?? '').toString().toLowerCase().includes(value) ||
                    (data.tipo ?? '').toLowerCase().includes(value) ||
                    (data.talla ?? '').toLowerCase().includes(value) ||
                    (data.color ?? '').toLowerCase().includes(value) ||
                    (data.cantidad ?? '').toString().includes(value)
                );
            });
        } else {
            tabla.clearFilter();
        }
    });

}
