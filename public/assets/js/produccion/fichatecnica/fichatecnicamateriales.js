const fichatecnica_id = $('#fichatecnica_id').val()
const codigoBoceto = $('#codigo').val()

let tableMateriales = null

async function confTableMateriales() {
    let materiales = await $.getJSON("/admin/admin.materiales/list");
    let unidades = await $.getJSON("/admin/admin.elementos.codigo/UM");
    let prop1 = await $.getJSON("/admin/admin.elementos.codigo/Prop1");
    let prop2 = await $.getJSON("/admin/admin.elementos.codigo/Prop2");

    return {
        materiales: materiales,
        unidades: unidades[0]?.sub_elementos,
        prop1: prop1[0]?.sub_elementos,
        prop2: prop2[0]?.sub_elementos
    };
}

confTableMateriales().then(catalogos => {
    const fichaid = fichatecnica_id=='' || fichatecnica_id==undefined?'0':fichatecnica_id;
    let table = $('#materiales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `/admin/admin.fichas-tecnicas-materiales.index/${fichaid}`,
        language: {
                emptyTable: "No hay materiales registrados aún."
            },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            {
                data: 'referencia_codigo',
                render: function(data, type, row) {
                    let options = catalogos.materiales.map(m =>
                        `<option value="${m.codigo}" ${m.codigo === data ? 'selected' : ''}>${m.nombre}</option>`
                    ).join('');
                    return `<select class="form-select referencia_codigo" data-id="${row.id}">${options}</select>`;
                }
            },
            {
                data: 'unidad_medida',
                render: function(data, type, row) {
                    let options = catalogos.unidades.map(u =>
                        `<option value="${u.valor}" ${u.valor === data ? 'selected' : ''}>${u.valor}</option>`
                    ).join('');
                    return `<select class="form-select unidad_medida" data-id="${row.id}">${options}</select>`;
                }
            },
            {
                data: 'prop_1',
                render: function(data, type, row) {
                    let options = catalogos.prop1.map(p =>
                        `<option value="${p.valor}" ${p.valor === data ? 'selected' : ''}>${p.valor}</option>`
                    ).join('');
                    return `<select class="form-select prop_1" data-id="${row.id}">${options}</select>`;
                }
            },
            {
                data: 'prop_2',
                render: function(data, type, row) {
                    let options = catalogos.prop2.map(p =>
                        `<option value="${p.valor}" ${p.valor === data ? 'selected' : ''}>${p.valor}</option>`
                    ).join('');
                    return `<select class="form-select prop_2" data-id="${row.id}">${options}</select>`;
                }
            },
            {
                data: 'cantidad',
                width: "9rem",
                render: function(data, type, row) {
                    // Mostrar input con valor actual
                    return `<input type="number" step="0.01" min="0"
                                class="form-control cantidad"
                                style="text-align: end;"
                                data-id="${row?.id??''}"
                                value="${data ?? 0}">`;
                } },
            // { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            {
                data: 'acciones',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-success btn-sm guardarmaterial" data-id="${row.id}">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-danger btn-sm eliminarmaterial" data-id="${row.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // 🔹 Evento para actualizar select
    // $('#materiales-table').on('change', 'select', function () {
    //     let id = $(this).data('id');
    //     let row = table.row($(this).parents('tr')).data();

    //     let payload = {
    //         _token: $('meta[name="csrf-token"]').attr('content'),
    //         referencia_codigo: $(this).hasClass('referencia_codigo') ? $(this).val() : row.referencia_codigo,
    //         unidad_medida: $(this).hasClass('unidad_medida') ? $(this).val() : row.unidad_medida,
    //         prop_1: $(this).hasClass('prop_1') ? $(this).val() : row.prop_1,
    //         prop_2: $(this).hasClass('prop_2') ? $(this).val() : row.prop_2,
    //         cantidad: row.cantidad
    //     };

    //     $.post(`/admin/admin.fichas-tecnicas-materiales/${id}`, payload, function (response) {
    //         toastr.success(response.message);
    //         table.ajax.reload(null, false);
    //     });
    // });

    // // Detectar cambios en cantidad
    // $('#materiales-table').on('change', '.cantidad', function () {
    //     let id = $(this).data('id');
    //     let valor = parseFloat($(this).val());

    //     if (isNaN(valor)) {
    //         toastr.error("El valor debe ser numérico con punto decimal.");
    //         return;
    //     }

    //     $.post(`/admin/admin.fichas-tecnicas-materiales/${id}`, {
    //         _token: $('meta[name="csrf-token"]').attr('content'),
    //         cantidad: valor
    //     }, function (response) {
    //         toastr.success("Cantidad actualizada correctamente");
    //     }).fail(function () {
    //         toastr.error("Error al actualizar la cantidad");
    //     });
    // });

    // Guardar cambios de la fila
    $('#materiales-table').on('click', '.guardarmaterial', function () {
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        // Leer valores de los inputs/selects en la fila
        let payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            referencia_codigo: row.find('.referencia_codigo').val() ?? row.find('td:eq(1)').text(),
            unidad_medida: row.find('.unidad_medida').val() ?? row.find('td:eq(2)').text(),
            prop_1: row.find('.prop_1').val() ?? row.find('td:eq(3)').text(),
            prop_2: row.find('.prop_2').val() ?? row.find('td:eq(4)').text(),
            cantidad: row.find('.cantidad').val(),
            codigo:codigoBoceto
        };

        $.post(`/admin/admin.fichas-tecnicas-materiales/${id}`, payload)
            .done(response => {
                toastr.success(response.message);
                table.ajax.reload(null, false); // recargar solo data
            })
            .fail(xhr => {
                toastr.error("Error al guardar el material");
                console.error(xhr.responseText);
            });
    });

    // Eliminar fila
    $('#materiales-table').on('click', '.eliminarmaterial', function () {
        let id = $(this).data('id');

        if (confirm("¿Eliminar este material?")) {
            $.ajax({
                url: `/admin/admin.fichas-tecnicas-materiales/${id}`,
                type: "DELETE",
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    toastr.success(response.message);
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    toastr.error("Error al eliminar el material");
                }
            });
        }
    });

});



async function confTableMateriales_old () {
    if (tableMateriales) tableMateriales.destroy() // Destruye la tabla existente si ya está creada
    // Cargar catálogos desde backend
    let materiales = await fetch('/admin/admin.materiales/list').then(res =>
        res.json()
    )
    let propiedades1 = await fetch(
        '/admin/admin.elementos.codigo/' + 'Prop1'
    ).then(res => res.json())
    let propiedades2 = await fetch(
        '/admin/admin.elementos.codigo/' + 'Prop2'
    ).then(res => res.json())
    let unidadesMedidas = await fetch(
        '/admin/admin.elementos.codigo/' + 'UM'
    ).then(res => res.json())

    // Mapear a formato Tabulator {label, value}
    let materialesOptions = materiales.map(m => ({
        label: m.nombre,
        value: m.id
    }))
    let propiedades1Options = propiedades1[0].sub_elementos.map(p => ({
        label: p.valor,
        value: p.valor
    }))
    let propiedades2Options = propiedades2[0].sub_elementos.map(p => ({
        label: p.valor,
        value: p.valor
    }))
    let unidadesMedidasOptions = unidadesMedidas[0].sub_elementos.map(u => ({
        label: u.valor,
        value: u.valor
    }))

    // Inicializar tabla
    // tableMateriales = new Tabulator("#materiales-table", {
    //     layout: "fitColumns",
    //     ajaxURL: "/admin/admin.fichas-tecnicas-materiales.index/" + fichatecnica_id,
    //     ajaxConfig: "GET",
    //     reactiveData: true,
    //     pagination: "local",
    //     paginationSize: 5,
    //     columns: [
    //         {
    //             title: "Referencia Material",
    //             field: "referencia_codigo",
    //             editor: "list",
    //             editorParams: { values: materialesOptions },
    //             formatter: function (cell) {
    //                 let value = cell.getValue();
    //                 let option = materialesOptions.find(m => m.value === value);
    //                 return option ? option.label : value;
    //             }
    //         },
    //         {
    //             title: "Unidad de Medida",
    //             field: "unidad_medida",
    //             editor: "list",
    //             editorParams: { values: unidadesMedidasOptions },
    //             formatter: function (cell) {
    //                 let value = cell.getValue();
    //                 let option = unidadesMedidasOptions.find(u => u.value === value);
    //                 return option ? option.label : value;
    //             }
    //         },
    //         {
    //             title: "Propiedad 1",
    //             field: "prop_1",
    //             editor: "list",
    //             editorParams: { values: propiedades1Options },
    //             formatter: function (cell) {
    //                 let value = cell.getValue();
    //                 let option = propiedades1Options.find(p => p.value === value);
    //                 return option ? option.label : value;
    //             }
    //         },
    //         {
    //             title: "Propiedad 2",
    //             field: "prop_2",
    //             editor: "list",
    //             editorParams: { values: propiedades2Options },
    //             formatter: function (cell) {
    //                 let value = cell.getValue();
    //                 let option = propiedades2Options.find(p => p.value === value);
    //                 return option ? option.label : value;
    //             }
    //         },
    //         {
    //             title: 'Cantidad',
    //             field: 'cantidad',
    //             hozAlign: 'right',
    //             editor: 'input',
    //             editorParams: {
    //                 elementAttributes: {
    //                     inputmode: 'decimal',
    //                     pattern: '[0-9.]*'
    //                 }
    //             },
    //             validator: ['numeric'],
    //             formatter: cell => {
    //                 const v = Number(cell.getValue());
    //                 return Number.isFinite(v) ? new Intl.NumberFormat().format(v) : '';
    //             }
    //         },
    //         {
    //             title: "Acciones",
    //             hozAlign: "center",
    //             formatter: function (cell) {
    //                 let row = cell.getRow();
    //                 let id = row.getData().id || row.getIndex(); // fallback si no hay id
    //                 return `
    //                     <button class="btn btn-success btn-sm guardar" data-id="${id}">
    //                         <i class="fas fa-save"></i>
    //                     </button>
    //                     <button class="btn btn-danger btn-sm eliminar" data-id="${id}">
    //                         <i class="fas fa-trash"></i>
    //                     </button>
    //                 `;
    //             }
    //         },
    //     ],
    // });

    tableMateriales = new Tabulator('#materiales-table', {
        layout: 'fitColumns',
        ajaxURL:
            '/admin/admin.fichas-tecnicas-materiales.index/' + fichatecnica_id,
        ajaxConfig: 'GET',
        reactiveData: false, // 👈 importante para evitar bucles
        pagination: 'local',
        paginationSize: 5,
        columns: [
            {
                title: 'Referencia Material',
                field: 'referencia_codigo',
                editor: 'list',
                editorParams: { values: materialesOptions },
                formatter: cell => {
                    const v = cell.getValue()
                    const opt = materialesOptions.find(o => o.value === v)
                    return opt ? opt.label : v ?? ''
                }
            },
            {
                title: 'Unidad de Medida',
                field: 'unidad_medida',
                editor: 'list',
                editorParams: { values: unidadesMedidasOptions },
                formatter: cell => {
                    const v = cell.getValue()
                    const opt = unidadesMedidasOptions.find(o => o.value === v)
                    return opt ? opt.label : v ?? ''
                }
            },
            {
                title: 'Propiedad 1',
                field: 'prop_1',
                editor: 'list',
                editorParams: { values: propiedades1Options },
                formatter: cell => {
                    const v = cell.getValue()
                    const opt = propiedades1Options.find(o => o.value === v)
                    return opt ? opt.label : v ?? ''
                }
            },
            {
                title: 'Propiedad 2',
                field: 'prop_2',
                editor: 'list',
                editorParams: { values: propiedades2Options },
                formatter: cell => {
                    const v = cell.getValue()
                    const opt = propiedades2Options.find(o => o.value === v)
                    return opt ? opt.label : v ?? ''
                }
            },
            {
                title: 'Cantidad',
                field: 'cantidad',
                hozAlign: 'right',
                editor: 'input',
                validator: ['numeric'],
                formatter: cell => {
                    const n = Number(cell.getValue())
                    return Number.isFinite(n)
                        ? new Intl.NumberFormat().format(n)
                        : ''
                }
            },
            {
                title: 'Acciones',
                hozAlign: 'center',
                formatter: function () {
                    // Solo pintamos HTML plano
                    return `
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-success btn-sm guardar">
                            <i class="fas fa-save"></i>
                            </button>
                            <button class="btn btn-danger btn-sm eliminar">
                            <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        `
                },
                cellClick: function (e, cell) {
                    const row = cell.getRow()
                    const data = row.getData()

                    if (e.target.closest('.guardar')) {
                        // Guardar fila
                        const payload = {
                            ...data,
                            fichatecnica_id: fichatecnica_id
                        }

                        $.ajax({
                            url: '/admin/admin.fichas-tecnicas-materiales.store',
                            type: 'POST',
                            data: JSON.stringify(payload),
                            contentType: 'application/json',
                            headers: {
                                'X-CSRF-TOKEN': $(
                                    'meta[name="csrf-token"]'
                                ).attr('content')
                            },
                            success: function (resp) {
                                toastr.success(
                                    'Material guardado correctamente'
                                )
                                if (resp?.data) row.update(resp.data)
                            },
                            error: function (xhr) {
                                toastr.error('Error al guardar material')
                                console.error(xhr.responseText)
                            }
                        })
                    }

                    if (e.target.closest('.eliminar')) {
                        if (!confirm('¿Eliminar este material?')) return

                        $.ajax({
                            url: `/admin/admin.fichas-tecnicas-materiales/${data.id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $(
                                    'meta[name="csrf-token"]'
                                ).attr('content')
                            },
                            success: function () {
                                toastr.success('Material eliminado')
                                row.delete()
                            },
                            error: function (xhr) {
                                toastr.error('Error al eliminar material')
                                console.error(xhr.responseText)
                            }
                        })
                    }
                }
            }
        ]
    })

    // Delegación Guardar/Eliminar - solo se registra una vez
    if (!window.listenerMaterialesAttached) {
        document
            .getElementById('materiales-table')
            .addEventListener('click', function (e) {
                // Guardar
                if (e.target.closest('.guardar')) {
                    let id = e.target.closest('.guardar').dataset.id
                    let row = tableMateriales.getRow(id).getData()
                    row.fichatecnica_id = fichatecnica_id

                    $.ajax({
                        url: '/admin/admin.fichas-tecnicas-materiales.store',
                        type: 'POST',
                        data: JSON.stringify(row),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content'
                            )
                        },
                        success: function () {
                            toastr.success('Material guardado correctamente')
                            // tableMateriales.replaceData();
                            tableMateriales.updateData([response.data])
                        },
                        error: function (xhr) {
                            toastr.error('Error al guardar el material')
                            console.error(xhr.responseText)
                        }
                    })
                }

                // Eliminar
                if (e.target.closest('.eliminar')) {
                    let id = e.target.closest('.eliminar').dataset.id
                    if (confirm('¿Eliminar este material?')) {
                        $.ajax({
                            url: `/admin/admin.fichas-tecnicas-materiales/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $(
                                    'meta[name="csrf-token"]'
                                ).attr('content')
                            },
                            success: function () {
                                toastr.success(
                                    'Material eliminado correctamente'
                                )
                                tableMateriales.updateData([response.data])
                            },
                            error: function (xhr) {
                                toastr.error('Error al eliminar el material')
                                console.error(xhr.responseText)
                            }
                        })
                    }
                }
            })

        window.listenerMaterialesAttached = true // evitar múltiples listeners
    }
}

async function confTableMateriales_oldnew(){

    await loadOptions(); // primero cargamos las listas

    let table = $('#materiales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `/admin/admin.fichas-tecnicas-materiales.index/${fichatecnica_id}`,
        columns: [
            { data: 'id', visible: false },
            { data: 'referencia_codigo' },
            { data: 'unidad_medida' },
            { data: 'prop_1' },
            { data: 'prop_2' },
            { data: 'cantidad' },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return `<button class="btn btn-danger btn-sm eliminar" data-id="${data.id}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                }
            }
        ]
    });

    // 🔽 Edición inline con listas desplegables
    $('#materiales-table').on('click', 'td', function () {
        let cell = table.cell(this);
        let rowData = table.row(this).data();
        let columnIndex = table.column(this).index();

        // evitamos editar columna de Acciones
        if (columnIndex === 6) return;

        let columnName = table.column(this).dataSrc();

        let selectOptions = [];
        if (columnName === "referencia_codigo") {
            selectOptions = materialesOptions.map(m => `<option value="${m.id}" ${m.id == cell.data() ? 'selected' : ''}>${m.nombre}</option>`);
        }
        if (columnName === "unidad_medida") {
            selectOptions = unidadesMedidasOptions.map(u => `<option value="${u.valor}" ${u.valor == cell.data() ? 'selected' : ''}>${u.valor}</option>`);
        }
        if (columnName === "prop_1") {
            selectOptions = propiedades1Options.map(p => `<option value="${p.valor}" ${p.valor == cell.data() ? 'selected' : ''}>${p.valor}</option>`);
        }
        if (columnName === "prop_2") {
            selectOptions = propiedades2Options.map(p => `<option value="${p.valor}" ${p.valor == cell.data() ? 'selected' : ''}>${p.valor}</option>`);
        }

        if (selectOptions.length > 0) {
            let select = $(`<select class="form-control">${selectOptions.join('')}</select>`);

            $(this).html(select);

            select.focus().on('change blur', function () {
                let value = $(this).val();

                $.ajax({
                    url: `/admin/admin.fichas-tecnicas-materiales/${rowData.id}`,
                    type: "PUT",
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') },
                    data: { [columnName]: value },
                    success: function () {
                        toastr.success("Actualizado correctamente");
                        cell.data(value).draw(false);
                    },
                    error: function (xhr) {
                        toastr.error("Error al actualizar");
                        console.error(xhr.responseText);
                    }
                });
            });
        }
    });

    // 🔽 Eliminar registro
    $('#materiales-table').on('click', '.eliminar', function () {
        let id = $(this).data('id');
        if (confirm("¿Eliminar este material?")) {
            $.ajax({
                url: `/admin/admin.fichas-tecnicas-materiales/${id}`,
                method: "DELETE",
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    toastr.success("Material eliminado correctamente");
                    table.ajax.reload();
                },
                error: function (xhr) {
                    toastr.error("Error al eliminar material");
                }
            });
        }
    });


}

const nuevoReg = $('#add-material').on('click', function () {

    if(fichatecnica_id=='' || fichatecnica_id==undefined){
        toastr.error('Antes de adicionar un material debe crear la ficha técnica.');
    }else{
        let nuevoMaterial = {
            fichatecnica_id: fichatecnica_id, // este lo debes tener definido
            referencia_codigo: '',  // vacío para que se edite en la tabla
            unidad_medida: '',
            prop_1: '',
            prop_2: '',
            cantidad: 0,
            codigo: codigoBoceto
        };

        $.ajax({
            url: "/admin/admin.fichas-tecnicas-materiales.store",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') },
            data: nuevoMaterial,
            success: function (response) {
                toastr.success("Material agregado correctamente");
                $('#materiales-table').DataTable().ajax.reload();
            },
            error: function (xhr) {
                toastr.error("Error al agregar material");
                console.error(xhr.responseText);
            }
        });
    }


});


// function nuevoMaterial () {
//     const nuevoM = document
//         .getElementById('nuevoMaterial')
//         .addEventListener('click', function () {
//             table.addRow({
//                 id:
//                     'BOC-' +
//                     Math.random().toString(36).substring(2, 8).toUpperCase(),
//                 referencia_codigo: '',
//                 cantidad: 0,
//                 prop_1: '',
//                 prop_2: '',
//                 codigo: codigoBoceto
//             })
//         })
// }

// const actualizar = document
//     .getElementById('actualizarMaterial')
//     .addEventListener('click', function () {
//         table.replaceData()
//     })



const toNumber = v => {
    if (v === null || v === undefined || v === '') return NaN
    if (typeof v === 'number') return v
    const n = Number(String(v).replace(',', '.'))
    return Number.isFinite(n) ? n : NaN
}

// Formateador reutilizable (miles con punto y decimales con coma)
const fmtMiles = new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})
