$(function () {

    configTablaMovimientos();


    // Resetear formulario
    function resetForm() {
        $('#formMovimiento')[0].reset();
        $('#movimiento_id').val('');
    }

    // Nuevo movimiento
    $('#btn-nuevo-movimiento').on('click', function () {
        $('#num_doc').prop('readonly', false);
        $('#tipo').prop('readonly', false);
        $('#doc_ref').prop('readonly', false);
        limpiarValidaciones();
        resetForm();
        $('.modal-title').text('Registrar Movimiento');
        $('#btn-guardar-movimiento').text('Guardar');
        $('#modalMovimiento').modal('show');
        confTablaDet();
    });

    // Guardar o actualizar
    $('#btn-guardar-movimiento').on('click', function (e) {
        e.preventDefault();
        let id = $('#movimiento_id').val();
        let url = id ? `/admin/admin.movimientos.update/${id}` : "/admin/admin.movimientos.store";
        let method = id ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            data: $('#formMovimiento').serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#movimiento_id').val(response.data.id);
                toastr.success(response.message);
                limpiarValidaciones();
                tableMovimientos.ajax.reload();
            },
            error: function (xhr) {
                limpiarValidaciones();
                toastr.error(xhr.responseJSON?.error || 'Error al guardar el movimiento');
                if (xhr.status == 422) {

                $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#error_'+key).text(value[0]);
                    });
                }
            }
        });
    });

    // Editar movimiento
    $('#movimientos-table').on('click', '.editar-movimiento', function () {
        limpiarValidaciones();
        $('#formMovimiento')[0].reset();
        $('#num_doc').prop('readonly', true);
        $('#tipo').prop('readonly', true);
        $('#doc_ref').prop('readonly', true);
        let id = $(this).data('id');

        $.get(`/admin/admin.movimientos.edit/${id}`, async function (res) {
            if (res.success) {
                let mov = res.data;
                $('#movimiento_id').val(mov.id);
                $('#num_doc').val(mov.num_doc);
                $('#tipo').val(mov.tipo);
                $('#observacion').val(mov.observacion);
                $('#doc_ref').val(mov.doc_ref);
                $('.modal-title').text('Editar Movimiento');
                $('#btn-guardar-movimiento').text('Actualizar');
                await confTablaDet();
                $('#modalMovimiento').modal('show');
            }
        }).fail(function () {
            toastr.error("No se pudo cargar el movimiento.");
        });
    });

    // Eliminar movimiento
    $('#movimientos-table').on('click', '.eliminar-movimiento', function () {
        let id = $(this).data('id');
        if (confirm("¿Seguro que deseas eliminar este movimiento?")) {
            $.ajax({
                url: `/admin/admin.movimientos.destroy/${id}`,
                type: "DELETE",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    toastr.success(response.message);
                    tableMovimientos.ajax.reload();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.error || "Error al eliminar movimiento");
                }
            });
        }
    });
});

const genRid = () =>
    'r' + Math.random().toString(36).slice(2) + Date.now().toString(36)
// crea un id local estable para cada fila
const withRowId = (arr = []) =>
    arr.map((r, i) => ({
        __rid:
            r.__rid ??
            r.id ??
            `${r.item}-${r.unidad_medida}-${i}-${Date.now()}`, // algo único y estable
        ...r
    }))

function limpiarValidaciones() {
  $('#error_num_doc').text('');
  $('#error_tipo').text('');
  $('#error_observacion').text('');
  $('#error_doc_ref').text('');
}

let tableMovimientos = null;
function configTablaMovimientos() {
    tableMovimientos = $('#movimientos-table').DataTable({
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
        ajax: "/admin/admin.movimientos.index",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'id', name: 'id'},
            {data: 'numero_documento', name: 'numero_documento'},
            {data: 'created_at', name: 'created_at'},
            {data: 'usuario', name: 'usuario'},
            {data: 'tipo', name: 'tipo'},
            {data: 'observacion', name: 'observacion'},
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

function cargarSelects() {


    $.get("/admin/admin.productos/list", function (res) {
        let select = $("#producto_id").empty();
        res.forEach(item => {
            select.append(new Option(item.nombre, item.id));
        });
    });

    $.get("/admin/admin.elementos.codigo/Prop1", function (res) {
        let select = $("#talla_id").empty();
        res.forEach(item => {
            select.append(new Option(item.nombre, item.id));
        });
    });

    $.get("/admin/admin.elementos.codigo/Prop2", function (res) {
        let select = $("#color_id").empty();
        res.forEach(item => {
            select.append(new Option(item.nombre, item.id));
        });
    });

    $.get("/admin/admin.bodegas/list", function (res) {
        let select = $("#bodega_id").empty();
        res.forEach(item => {
            select.append(new Option(item.nombre, item.id));
        });
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

async function confTablaDet () {
    if (window.tableProductosDet) window.tableProductosDet.destroy(); // Destruye la tabla existente si ya está creada
    // Cargar las propiedades
    const numeroDocumento = $('#num_doc').val();
    if (numeroDocumento!='') {
        const res = await initTablaDetalles(numeroDocumento);
    }else{
        const res = await initTablaDetalles(null);
    }
}

let tablaDetalles = null;
// async function initTablaDetalles(numeroDocumento) {
//         if (window.tablaDetalles) window.tablaDetalles.destroy();

//         let productos = await $.get('/admin/admin.productos/list');
//         let bodegas   = await $.get('/admin/admin.bodegas/list');
//         let tallas    = await $.get('/admin/admin.elementos.codigo/Prop1');
//         let colores   = await $.get('/admin/admin.elementos.codigo/Prop2');

//         // Formatear para Tabulator (value, label)
//         let productosOpts = productos.data.map(p => ({ value: p.id, label: `${p.codigo} - ${p.nombre}` }));
//         let bodegasOpts   = bodegas.data.map(b => ({ value: b.id, label: b.nombre }));
//         let tallasOpts    = tallas[0]?.sub_elementos.map(t => ({ value: t.valor, label: t.valor }));
//         let coloresOpts   = colores[0]?.sub_elementos.map(c => ({ value: c.valor, label: c.valor }));
//         console.log({ productosOpts, bodegasOpts, tallasOpts, coloresOpts });

//         tablaDetalles = new Tabulator("#tabla-detalles", {
//             layout: "fitColumns",
//             width: "100%",
//             height: "250px",
//             ajaxURL: `/admin/admin.movimientosdetalles.index/${numeroDocumento || 0}`,
//             ajaxConfig: "GET",
//             pagination: "local",
//             paginationSize: 10,
//             columns: [
//                 { title: "ID", field: "id", visible: false },
//                 {
//                     title: "Código Producto",
//                     field: "codigo_producto",
//                     editor: "list",
//                     editorParams: {
//                         values: productosOpts,
//                         autocomplete: true,
//                         listOnEmpty: true,
//                     },
//                     formatter: function (cell) {
//                         let row = cell.getRow().getData();
//                         let producto = productosOpts.find(p => p.value == row.codigo_producto);
//                         return producto ? producto.label : '';
//                     },
//                     cellEdited: function (cell) {
//                         let codigo = cell.getValue();
//                         let producto = productosOpts.find(p => p.value == codigo);
//                         if (producto) {
//                             let row = cell.getRow();
//                             row.update({ producto_id: producto.value });
//                         }
//                     },
//                 },
//                 {
//                     title: "Producto ID",
//                     field: "producto_id",
//                     visible: false, //
//                 },
//                 { title: "Talla", field: "talla", editor: "list", editorParams: { values: tallasOpts, autocomplete: true, listOnEmpty: true } },
//                 { title: "Color", field: "color", editor: "list", editorParams: { values: coloresOpts, autocomplete: true, listOnEmpty: true } },
//                 {
//                     title: "Bodega",
//                     field: "bodega_id",
//                     editor: "list",
//                     editorParams: {
//                         values: bodegasOpts,
//                         autocomplete: true,
//                         listOnEmpty: true,
//                     },
//                     formatter: function (cell) {
//                         let val = cell.getValue();
//                         let item = bodegasOpts.find(b => b.value == val);
//                         return item ? item.label : '';
//                     }
//                 },
//                 {
//                     title: 'Cantidad',
//                     field: 'cantidad',
//                     hozAlign: 'right',
//                     headerHozAlign: 'right',
//                     editor: 'input',
//                     editorParams: {
//                         elementAttributes: {
//                             inputmode: 'decimal',
//                             pattern: '[0-9.]*',
//                             style: 'text-align:right;'
//                         }
//                     },
//                     validator: ['numeric'],
//                     formatter: cell => {
//                         const v = toNumber(cell.getValue())
//                         return Number.isFinite(v) ? fmtMiles.format(v) : ''
//                     },
//                 },
//                 // { title: "Tipo", field: "tipo", editor: "list", editorParams: { values: { entrada: 'Entrada', salida: 'Salida', ajuste: 'Ajuste' } } },
//                 { title: "Tipo", field: "tipo", editor: "input", visible: false },
//                 { title: "movimiento_id", field: "movimiento_id", visible: false },
//                 { title: "num_doc", field: "num_doc", visible: false },
//                 { title: "costo_unitario", field: "costo_unitario", visible: false },
//                 {
//                     title: "Acciones",
//                     formatter: function (cell) {
//                         let id = cell.getRow().getData().id ?? '';
//                         return `
//                             <button type="button" class="btn btn-success btn-sm guardar-detalle" data-id="${id}">
//                                 <i class="fas fa-save"></i>
//                             </button>
//                             <button type="button" class="btn btn-danger btn-sm eliminar-detalle" data-id="${id}">
//                                 <i class="fas fa-trash"></i>
//                             </button>
//                         `;
//                         }
//                 }
//             ],
//         });

//         window.tablaDetalles = tablaDetalles;


//         document.getElementById("tabla-detalles").addEventListener("click", function (e) {
//             if (e.target.closest(".guardar-detalle")) {
//                 let id = e.target.closest(".guardar-detalle").dataset.id;
//                 console.log('di', {id});

//                 let row = window.tablaDetalles.getRow(id).getData();
//                 console.log('row', row);
//                 let url = id ? `/admin/admin.movimientosdetalles.update/${id}` : "/admin/admin.movimientosdetalles.store";
//                 let method = id ? "PUT" : "POST";

//                 $.ajax({
//                     url: url,
//                     type: method,
//                     headers: {
//                         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
//                     },
//                     contentType: "application/json",
//                     data: JSON.stringify(row),
//                     success: function (response) {
//                         toastr.success(response.message);
//                         window.tablaDetalles.replaceData();
//                     },
//                     error: function (xhr) {
//                         console.log(xhr.responseText);
//                         toastr.error("Error al guardar Actividad");
//                     }
//                 });
//             }

//             if (e.target.closest(".eliminar-detalle")) {
//                 let id = e.target.closest(".eliminar-detalle").dataset.id;
//                 if (confirm("¿Eliminar esta actividad?")) {
//                     $.ajax({
//                         url: `/admin/admin.movimientosdetalles.destroy/${id}`,
//                         type: "DELETE",
//                         headers: {
//                             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
//                         },
//                         success: function (success) {
//                             toastr.success(success.message);
//                             window.tablaDetalles.replaceData();
//                         },
//                         error: function (xhr) {
//                             console.log(xhr.responseText);
//                             toastr.error("Error al eliminar Actividad");
//                         }
//                     });
//                 }
//             }
//         });

// }

async function initTablaDetalles(numeroDocumento) {
    // 🧹 Si ya existe una tabla, destrúyela
    const numDoc = numeroDocumento || $('#num_doc').val() || '0';
    window.numDocActual = numDoc;

    console.log('Iniciando tabla detalles para numDoc:', numDoc);

    if (window.tablaDetalles) {
        console.log('Destruyendo tabla existente...');

        window.tablaDetalles.destroy();
        $('#tabla-detalles').empty();
    }

    // 🔹 1️⃣ Cargar catálogos en paralelo
    const [productos, bodegas, tallas, colores] = await Promise.all([
        $.get('/admin/admin.productos/list'),
        $.get('/admin/admin.bodegas/list'),
        $.get('/admin/admin.elementos.codigo/Prop1'),
        $.get('/admin/admin.elementos.codigo/Prop2')
    ]);

    // 🔹 2️⃣ Formatear opciones
    const productosOpts = productos.data.map(p => ({ value: p.id, label: `${p.codigo} - ${p.nombre}` }));
    const bodegasOpts   = bodegas.data.map(b => ({ value: b.id, label: b.nombre }));
    const tallasOpts    = tallas[0]?.sub_elementos.map(t => ({ value: t.valor, label: t.valor })) ?? [];
    const coloresOpts   = colores[0]?.sub_elementos.map(c => ({ value: c.valor, label: c.valor })) ?? [];



    // 🔹 3️⃣ Crear tabla
    window.tablaDetalles = new Tabulator("#tabla-detalles", {
        layout: "fitColumns",
        height: "300px",
        ajaxURL: `/admin/admin.movimientosdetalles.index/${window.numDocActual || 0}`,
        ajaxConfig: "GET",
        reactiveData: true,
        pagination: "local",
        paginationSize: 10,
        columns: [
            { title: "ID", field: "id", visible: false },
            {
                title: "Producto",
                field: "producto_id",
                editor: "list",
                // headerFilter: "input",
                editorParams: {
                    values: productosOpts,
                    autocomplete: true,
                    listOnEmpty: true,
                },
                formatter: cell => {
                    const val = cell.getValue();
                    const item = productosOpts.find(p => p.value == val);
                    return item ? item.label : '';
                },
                cellEdited: function (cell) {
                    const producto = productosOpts.find(p => p.value == cell.getValue());
                    if (producto) {
                        const codigo = producto.label.split(' - ')[0];
                        cell.getRow().update({ codigo_producto: codigo });
                    }
                }
            },
            { title: "Código Producto", field: "codigo_producto", visible: false },
            { title: "Talla", field: "talla", editor: "list", editorParams: { values: tallasOpts, autocomplete: true, listOnEmpty: true } },
            { title: "Color", field: "color", editor: "list", editorParams: { values: coloresOpts, autocomplete: true, listOnEmpty: true } },
            {
                title: "Bodega",
                field: "bodega_id",
                editor: "list",
                editorParams: {
                    values: bodegasOpts,
                    autocomplete: true,
                    listOnEmpty: true,
                },
                formatter: cell => {
                    const val = cell.getValue();
                    const item = bodegasOpts.find(b => b.value == val);
                    return item ? item.label : '';
                }
            },
            {
                title: 'Cantidad',
                field: 'cantidad',
                hozAlign: 'right',
                headerHozAlign: 'right',
                editor: 'input',
                validator: ['numeric'],
                editorParams: {
                    elementAttributes: {
                        inputmode: 'decimal',
                        pattern: '[0-9.]*',
                    }
                },
                formatter: cell => {
                    const v = parseFloat(cell.getValue()) || 0;
                    return v.toLocaleString('es-CO', { minimumFractionDigits: 2 });
                },
            },
            { title: "Tipo", field: "tipo", visible: false },
            { title: "movimiento_id", field: "movimiento_id", visible: false },
            { title: "num_doc", field: "num_doc", visible: false },
            { title: "costo_unitario", field: "costo_unitario", visible: false },
            {
                title: "Acciones",
                hozAlign: "center",
                formatter: () => `
                    <button type="button" class="btn btn-success btn-sm guardar-detalle">
                        <i class="fas fa-save"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm eliminar-detalle">
                        <i class="fas fa-trash"></i>
                    </button>
                `
            }
        ],
    });

    const tabla = window.tablaDetalles;

    // 🔹 4️⃣ Filtro global tipo DataTables
    // $('#buscar-detalles').off('keyup').on('keyup', function () {
    //     const value = $(this).val().trim();
    //     if (value) {
    //         tabla.setFilter([
    //             [
    //                 { field: "producto_id", type: "like", value },
    //                 { field: "codigo_producto", type: "like", value },
    //                 { field: "talla", type: "like", value },
    //                 { field: "color", type: "like", value },
    //                 { field: "cantidad", type: "like", value },
    //             ]
    //         ]);
    //     } else {
    //         tabla.clearFilter();
    //     }
    // });
    $('#buscar-detalles').off('keyup').on('keyup', function () {
        const value = $(this).val().trim().toLowerCase();
        if (value) {
            tabla.setFilter(function (data) {
                const producto = productosOpts.find(p => p.value == data.producto_id);
                const label = producto ? producto.label.toLowerCase() : '';
                return (
                    label.includes(value) ||
                    (data.talla ?? '').toLowerCase().includes(value) ||
                    (data.color ?? '').toLowerCase().includes(value) ||
                    (data.cantidad ?? '').toString().includes(value) ||
                    (data.codigo_producto ?? '').toLowerCase().includes(value)
                );
            });
        } else {
            tabla.clearFilter();
        }
    });

    // 🔹 4️⃣ Botón "Agregar detalle"
    $('#btnAddDetalle').off('click').on('click', () => {
        tabla.addRow({
            id: null,
            producto_id: null,
            codigo_producto: null,
            talla: null,
            color: null,
            bodega_id: null,
            cantidad: 0,
            tipo:  $('#tipo').val() || 'entrada',
            movimiento_id: $('#movimiento_id').val() || null,
            num_doc: $('#num_doc').val() || '',
            costo_unitario: 0,
        }, false);
    });

    // 🔹 5️⃣ Manejo interno de clicks en celdas (sin jQuery)
    tabla.on("cellClick", function (e, cell) {
        const target = e.target;

        // ✅ Guardar
        if (target.closest(".guardar-detalle")) {
            const row = cell.getRow().getData();
            const id = row.id;
            const url = id
                ? `/admin/admin.movimientosdetalles.update/${id}`
                : "/admin/admin.movimientosdetalles.store";
            const method = id ? "PUT" : "POST";

            $.ajax({
                url,
                type: method,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                contentType: "application/json",
                data: JSON.stringify(row),
                success: function (response) {
                    toastr.success(response.message);
                    tabla.replaceData(response.data); // refrescar tabla
                },
                error: function (xhr) {
                    console.error(xhr.responseJSON.message);
                    toastr.error(xhr.responseJSON.message|| "Error al guardar detalle");
                }
            });
        }

        // ❌ Eliminar
        if (target.closest(".eliminar-detalle")) {
            const rowComponent = cell.getRow();
            const row = rowComponent.getData();

            if (!row.id) {
                // Si es nuevo sin guardar, simplemente eliminarlo
                rowComponent.delete();
                return;
            }

            if (confirm("¿Eliminar este detalle?")) {
                $.ajax({
                    url: `/admin/admin.movimientosdetalles.destroy/${row.id}`,
                    type: "DELETE",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        toastr.success(res.message);
                        tabla.replaceData();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        toastr.error("Error al eliminar detalle");
                    }
                });
            }
        }
    });
}
