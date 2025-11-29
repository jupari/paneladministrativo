
$(async function () {
    let productosTable = $('#productos-table').DataTable({
        language: {
            url: '/assets/js/spanish.json'
        },
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
        ajax: '/admin/admin.productos.index',
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                className: 'exclude',
                orderable: false,
                searchable: false
            },
            { data: 'id', name: 'id' },
            { data: 'tipo_producto', name: 'tipo_producto' },
            { data: 'codigo', name: 'codigo' },
            { data: 'nombre', name: 'nombre' },
            { data: 'unidad', name: 'unidad' },
            { data: 'marca', name: 'marca' },
            { data: 'categoria', name: 'categoria' },
            { data: 'subcategoria', name: 'subcategoria' },
            { data: 'estado', name: 'estado' },
            {
                data: 'acciones',
                name: 'acciones',
                className: 'exclude text-center'
            }
        ],
        columnDefs: [
            {
                targets: 1,
                visible: false
            }
        ],
        order: [[1, 'desc']],
        pageLength: 8,
        lengthMenu: [
            [2, 4, 6, 8, 10, -1],
            [2, 4, 6, 8, 10, 'Todo(s)']
        ]
    });

    $('#btn-nuevo').click(() => {
        $('#form-producto')[0].reset();
        $('#producto_id').val('');
        $('#ModalProducto').modal('show');
    });

    $('#btn-nuevo-propiedad').click(() => {
        window.tableProductosDet.addRow(
            {
                __rid: genRid(), // id local único
                id: '',
                propiedad_id: $('#producto_id').val() ?? '0',
                codigo: $('#codigo').val() ?? '',
                propiedad1: '',
                propiedad2: '',
            },
            false
        )
    })

    $('#form-producto').submit(function (e) {
        e.preventDefault();
        let id = $('#producto_id').val();
        let url = id ? `/admin/admin.productos/${id}` : "/admin/admin.productos.store";
        let method = id ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                codigo: $('#codigo').val(),
                nombre: $('#nombre').val(),
                tipo_producto: $('#tipo_producto').val(),
                descripcion: $('#descripcion').val(),
                unidad_medida: $('#unidad_medida').val(),
                marca: $('#marca').val(),
                categoria: $('#categoria').val(),
                subcategoria: $('#subcategoria').val(),
                precio: $('#precio').val(),
                stock_minimo: $('#stock').val(),
                active: $('#active').is(':checked') ? 1 : 0
            },
            success: function (res) {
                $('#ModalProducto').modal('hide');
                $('#guardar-modal').prop('hidden', true);
                productosTable.ajax.reload();
                toastr.success(res.message);
                limpiarValidaciones();
            },
            error: function (xhr) {
                limpiarValidaciones();
                $('#guardar-modal').prop('hidden', false);
                toastr.error(xhr.responseJSON.message);
                if (xhr.status == 422) {

                $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#error_'+key).text(value[0]);
                    });
                }
            }
        });
    });

    $('#productos-table').on('click', '.editar', async function () {
        // 1. Mostrar skeleton
        $('#ModalProductoSkeleton').modal('show');
        $('#ModalProducto').modal('hide');
        limpiarValidaciones();
        let id = $(this).data('id');
        cargarUnidades();
        $.get(`/admin/admin.productos.show/${id}`, async function (data) {


            let row = data.data;
            $('#producto_id').val(row.id);
            $('#codigo').val(row.codigo);
            $('#codigo').prop('readonly', true);
            $('#nombre').val(row.nombre);
            $('#descripcion').val(row.descripcion);
            const precio = (row.precio !== null && row.precio !== undefined) ? Number(row.precio) : 0;
            const stock  = (row.stock_minimo  !== null && row.stock_minimo  !== undefined) ? Number(row.stock_minimo)  : 0;
            $('#precio').val(precio.toFixed(2));
            $('#stock').val(stock);
            $('#active').prop('checked', !!row.active);
            cargarTipoProducto(row.tipo_producto);
            cargarUnidades(row.unidad_medida);
            cargarMarcas(row.marca);
            cargarCategorias(row.categoria, row.subcategoria);
            await confTablaDet();
            $('#ModalProducto').modal('show');

            // 4. Alternar modales
            setTimeout(() => {
                $('#ModalProducto').modal('show');
                $('#ModalProductoSkeleton').modal('hide');
            }, 500); // delay para que se note el skeleton


        });
    });

    $('#productos-table').on('click', '.eliminar', function () {
        if (!confirm("¿Eliminar este producto?")) return;
        let id = $(this).data('id');
        $.ajax({
            url: `/inventario/productos/${id}`,
            type: 'DELETE',
            data: {_token: "{{ csrf_token() }}"},
            success: function (res) {
                productosTable.ajax.reload();
                toastr.success(res.message);
            }
        });
    });
});

async function cargarUnidades(unidad_medida = null) {
    $.ajax({
        url: "/admin/admin.elementos.codigo/UM",
        type: "GET",
        success: function (data) {
            let $select = $('#unidad_medida');
            $select.empty().append('<option value="">Seleccione...</option>');
            data[0].sub_elementos.forEach(function (u) {
                if (u.valor === unidad_medida) {
                    $select.append(`<option value="${u.valor}" selected>${u.valor}</option>`);
                } else {
                    $select.append(`<option value="${u.valor}">${u.valor}</option>`);
                }
            });
        },
        error: function () {
            toastr.error("Error al cargar las unidades de medida");
        }
    });
}

async function cargarMarcas(marca = null) {
    $.ajax({
        url: "/admin/admin.elementos.codigo/MARCA",
        type: "GET",
        success: function (data) {
            let $select = $('#marca');
            $select.empty().append('<option value="">Seleccione...</option>');
            data[0].sub_elementos.forEach(function (u) {
                if (u.valor === marca) {
                    $select.append(`<option value="${u.valor}" selected>${u.valor}</option>`);
                } else {
                    $select.append(`<option value="${u.valor}">${u.valor}</option>`);
                }
            });
        },
        error: function () {
            toastr.error("Error al cargar las marcas");
        }
    });
}

async function cargarCategorias(categoria = null, subcategoria = null) {
    $.ajax({
        url: "/admin/admin.elementos.codigo/CATEGORIA",
        type: "GET",
        success: function (data) {
            let $select = $('#categoria');
            $select.empty().append('<option value="">Seleccione...</option>');
            data[0].sub_elementos.forEach(async function (u) {
                if (u.valor === categoria) {
                    $select.append(`<option value="${u.valor}" selected>${u.valor}</option>`);
                    await cargarSubcategorias(subcategoria); // Cargar subcategorías si hay una categoría seleccionada
                } else {
                    $select.append(`<option value="${u.valor}">${u.valor}</option>`);
                }
            });
        },
        error: function () {
            toastr.error("Error al cargar las subcategorías");
        }
    });
}

async function cargarSubcategorias(subcategoria = null) {
    const categoria = $('#categoria').val();
    const $select = $('#subcategoria');

    // Validación inicial
    if (!categoria) {
        $select.empty().append('<option value="">Seleccione una categoría primero</option>');
        return;
    }

    try {
        const response = await $.ajax({
            url: "/admin/admin.elementos.codigo/SUBCATEGORIA",
            type: "GET",
            dataType: "json"
        });

        $select.empty().append('<option value="">Seleccione...</option>');

        if (response && response[0] && Array.isArray(response[0].sub_elementos)) {
            response[0].sub_elementos
                .filter(u => u.codigo_padre === categoria)
                .forEach(u => {
                    $select.append(
                        `<option value="${u.valor}" ${u.valor === subcategoria ? 'selected' : ''}>${u.valor}</option>`
                    );
                });
        }

        // Si no se asignó en el append, forzamos el valor después
        if (subcategoria) {
            $select.val(subcategoria);
        }

    } catch (error) {
        console.error(error);
        toastr.error("Error al cargar las subcategorías");
    }
}

async function cargarTipoProducto(tipo_producto = null) {

    $.ajax({
        url: "/admin/admin.elementos.codigo/TIPOP",
        type: "GET",
        success: function (data) {
            let $select = $('#tipo_producto');
            $select.empty().append('<option value="">Seleccione...</option>');
            data[0].sub_elementos.forEach(function (u) {
                if (u.valor === tipo_producto) {
                    $select.append(`<option value="${u.valor}" selected>${u.valor}</option>`);
                }else {
                    $select.append(`<option value="${u.valor}">${u.valor}</option>`);
                }
            });
        },
        error: function () {
            toastr.error("Error al cargar las subcategorías");
        }
    });
}

async function cargarPropiedad1(propiedad1 = null) {
    try {
        const res = await $.ajax({
            url: "/admin/admin.elementos.codigo/Prop1",
            type: "GET",
            dataType: "json"
        });
        return res[0].sub_elementos; // <-- IMPORTANTE: retornar el array
    } catch (err) {
        console.error("Error cargando propiedad1", err);
        return [];
    }
}

async function cargarPropiedad2(propiedad2 = null) {
    try {
        const res = await $.ajax({
            url: "/admin/admin.elementos.codigo/Prop2",
            type: "GET",
            dataType: "json"
        });
        return res[0].sub_elementos??[]; // <-- aquí también
    } catch (err) {
        console.error("Error cargando propiedad2", err);
        return [];
    }

}

async function cargarTipoProducto(tipo_producto = null) {

    $.ajax({
        url: "/admin/admin.elementos.codigo/TIPOP",
        type: "GET",
        success: function (data) {
            let $select = $('#tipo_producto');
            $select.empty().append('<option value="">Seleccione...</option>');
            data[0].sub_elementos.forEach(function (u) {
                if (u.valor === tipo_producto) {
                    $select.append(`<option value="${u.valor}" selected>${u.valor}</option>`);
                }else {
                    $select.append(`<option value="${u.valor}">${u.valor}</option>`);
                }
            });
        },
        error: function () {
            toastr.error("Error al cargar las subcategorías");
        }
    });
}

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


async function confTablaDet () {
    if (window.tableProductosDet) window.tableProductosDet.destroy(); // Destruye la tabla existente si ya está creada
    // Cargar las propiedades
    const propiedad1 = await cargarPropiedad1();
    const propiedad2 = await cargarPropiedad2();
    const res = await iniTableDet(propiedad1, propiedad2);
}


async function iniTableDet(propiedad1 = [], propiedad2 = []) {
    const producto_id = $('#producto_id').val() ?? '0';
    const codigo = $('#codigo').val() ?? '';
    let tableProductosDet = new Tabulator("#productosdet-table", {
        layout: "fitColumns",
        width: "100%",
        height: "300px",
        ajaxURL: "admin.productospropiedades.index/" + (producto_id==''?'0':producto_id), // URL para obtener los datos
        ajaxConfig: "GET",
        reactiveData: true,
        pagination: "local",
        paginationSize: 10,
        columns: [
            {
                title: "id",
                field: "id",
                editor: "input",
                visible: false,
            },
            {
                title: "producto_id",
                field: "producto_id",
                editor: "input",
                mutator: function () { return producto_id; },
                visible: false,
            },
            {
                title: "codigo",
                field: "codigo",
                editor: "input",
                mutator: function () { return codigo; },
                visible: false,
            },
            {
                title: "Propiedad1",
                field: "propiedad1",
                editor: "list",
                editorParams: {
                    values: Array.isArray(propiedad2) ? propiedad2.map(item => ({ value: item.valor, label: item.valor })) : [],
                    autocomplete: true,
                    listOnEmpty: true
                },
                formatter: cell => {
                    const prop1 = cell.getValue()
                    return prop1 ? prop1 : ''
                }
            },
            {
                title: "Propiedad2",
                field: "propiedad2",
                editor: "list",
                editorParams: {
                    values: Array.isArray(propiedad1) ? propiedad1.map(item => ({ value: item.valor, label: item.valor })) : [],
                    autocomplete: true,
                    listOnEmpty: true
                },
                formatter: cell => {
                    const prop2 = cell.getValue()
                    return prop2 ? prop2 : ''
                }
            },
            {
                title: "Acciones",
                hozAlign: "center",
                formatter: function (cell) {
                    let id = cell.getRow().getData().id ?? '';
                    return `
                        <button type="button" class="btn btn-success btn-sm guardar" data-id="${id}">
                            <i class="fas fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm eliminar" data-id="${id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            },
        ],
    });

    // Asignar la tabla a una variable global para acceder desde otros ámbitos
    window.tableProductosDet = tableProductosDet;

    document.getElementById("productosdet-table").addEventListener("click", function (e) {
        if (e.target.closest(".guardar")) {
            let id = e.target.closest(".guardar").dataset.id;
            let row = tableProductosDet.getRow(id).getData();

            $.ajax({
                url: "admin.productospropiedades.store",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                contentType: "application/json",
                data: JSON.stringify(row),
                success: function (response) {
                    toastr.success(response.message);
                    tableProductosDet.replaceData(); // refresca tabla
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    toastr.error("Error al guardar Actividad");
                }
            });
        }

        if (e.target.closest(".eliminar")) {
            let id = e.target.closest(".eliminar").dataset.id;
            if (confirm("¿Eliminar esta actividad?")) {
                $.ajax({
                    url: `admin.productospropiedades.destroy/${id}`,
                    type: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function (success) {
                        toastr.success(success.message);
                        tableProductosDet.replaceData();
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        toastr.error("Error al eliminar Actividad");
                    }
                });
            }
        }
    });
}

function limpiarValidaciones() {
    $('#error_codigo').text('');
    $('#error_nombre').text('');
    $('#error_tipo_producto').text('');
    $('#error_descripcion').text('');
    $('#error_unidad_medida').text('');
    $('#error_marca').text('');
    $('#error_categoria').text('');
    $('#error_subcategoria').text('');
    $('#error_precio').text('');
    $('#error_stock').text('');
}

$('#btn-nuevo').on('click', async function () {
    $('#ModalProductoSkeleton').modal('show');
    $('#ModalProducto').modal('hide');
    $('#codigo').prop('readonly', false);
    cargarUnidades();
    cargarMarcas();
    cargarCategorias();
    cargarSubcategorias();
    cargarTipoProducto();
    await confTablaDet();
    // 4. Alternar modales
    setTimeout(() => {
        $('#ModalProductoSkeleton').modal('hide');
        $('#ModalProducto').modal('show');
    }, 500);

});

$('#categoria').on('change', function () {
    cargarSubcategorias();
});
