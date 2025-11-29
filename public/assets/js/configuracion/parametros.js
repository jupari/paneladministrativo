$(function () {
    confDatatable()

    // Toast
    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: false,
        progressBar: false,
        positionClass: 'toast-bottom-right',
        preventDuplicates: false,
        onclick: null,
        showDuration: '300',
        hideDuration: '1000',
        timeOut: '5000',
        extendedTimeOut: '1000',
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    }

    // Escucha el evento de cierre de la ventana modal
    $('#ModalParametros').on('hidden.bs.modal', function () {})
})

function confDatatable () {
    $('#parametros-table').DataTable({
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
        ajax: '/admin/admin.elementos.index',
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                className: 'exclude',
                orderable: false,
                searchable: false
            },
            { data: 'id', name: 'id' },
            { data: 'codigo', name: 'codigo' },
            { data: 'nombre', name: 'nombre' },
            { data: 'valor', name: 'valor' },
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
    })
}

//variable para instanciar el modal
const myModal = new bootstrap.Modal(
    document.getElementById('ModalParametros'),
    {
        keyboard: false
    }
)

function Cargar () {
    let table = $('#parametros-table').DataTable()
    table.ajax.reload()
}

// Limpiar inputs
function cleanInput () {
    // Campos usuario
    const Fields = ['elemento_id', 'codigo', 'nombre', 'valor', 'active']
    Fields.forEach(field => {
        $('#' + field).val('')
    })
}

function showParametro (btn) {
    // LIMPIAR CAMPOS
    cleanInput()

    $.get('/admin/admin.elementos.show/' + btn, response => {
        const parametro = response

        const parametros = [
            'elemento_id',
            'codigo',
            'nombre',
            'valor',
            'active'
        ]
        parametros.forEach(parametroField => {
            if (parametroField === 'active') {
                if (parametro[parametroField] == 1) {
                    $('#' + parametroField).prop('checked', true)
                } else {
                    $('#' + parametroField).prop('checked', false)
                }
            }
            if (parametroField === 'elemento_id') {
                $('#' + parametroField).val(parametro['id'])
                return // Saltar al siguiente campo
            }
            $('#' + parametroField).val(parametro[parametroField])
        })
        confTablaDet(parametro) // Configura la tabla de detalles con el ID de elemento actual
    })
}

//Registrar usuario
function regParametro () {
    myModal.show()
    $('#exampleModalLabel').html('Crear Parámetro')

    // LIMPIAR CAMPOS
    cleanInput()
    limpiarValidaciones()
    confTablaDet([]); // Destruye la tabla existente si ya está creada
    // FIN LIMPIAR CAMPOS
    $('#btnGuardarElemento').attr('onclick', 'registerParametro()')

    let r =
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'

    $('.modal-footer').html(r)
}

function registerParametro () {
    const route = '/admin/admin.elementos.store'
    let ajax_data = {
        // Datos formulario
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        valor: $('#valor').val(),
        estado: $('#estado').is(':checked') ? 1 : 0
    }
    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        dataType: 'json',
        data: ajax_data
    })
        .then(response => {
            $('#elemento_id').val(response.data.id) // Guarda el ID del nuevo elemento
            confTablaDet([]) // Recarga la tabla de detalles con el ID de elemento actual
            toastr.success(response.message)
        })
        .catch(e => {
            limpiarValidaciones()
            const arr = e.responseJSON
            const toast = arr.errors

            if (e.status == 422) {
                $.each(toast, function (key, value) {
                    $('#error_' + key).text(value[0])
                })
                // for (const key in toast) {
                //     if (toast.hasOwnProperty(key) && toast[key] != null) {
                //         toastr.error(toast[key][0]);
                //     }
                // }
            } else if (e.status == 403) {
                toastr.warning(arr.error)
            }
        })
}

// Actualizar usuario
function upParametro (btn) {
    myModal.show()
    $('#exampleModalLabel').html('Editar Parámetro')
    // LIMPIAR CAMPOS
    cleanInput()
    limpiarValidaciones()
    showParametro(btn)
    // FIN LIMPIAR CAMPOS

    $('#btnGuardarElemento').attr('onclick', 'updateParametro(' + btn + ')')

    let u =
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'
    $('.modal-footer').html(u)
}

function updateParametro (btn) {
    const route = '/admin/admin.elementos/' + btn
    let ajax_data = {
        // Datos formulario
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        valor: $('#valor').val(),
        active: $('#active').is(':checked') ? 1 : 0
    }

    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
        type: 'PUT',
        dataType: 'json',
        data: ajax_data
    })
        .then(response => {
            toastr.success(response.message)
        })
        .catch(e => {
            limpiarValidaciones()
            const arr = e.responseJSON
            const toast = arr.errors

            if (e.status == 422) {
                $.each(toast, function (key, value) {
                    $('#error_' + key).text(value[0])
                })
                // for (const key in toast) {
                //     if (toast.hasOwnProperty(key) && toast[key] != null) {
                //         toastr.error(toast[key][0]);
                //     }
                // }
            } else if (e.status == 403) {
                myModal.toggle()
                toastr.warning(arr.message)
            }
        })
}

// Función para convertir 'dd/mm/yyyy' a 'yyyy-mm-dd'
function convertDateFormat (dateString) {
    const parts = dateString.split('/')
    return `${parts[2]}-${parts[1]}-${parts[0]}`
}

function limpiarValidaciones () {
    $('#error_codigo').text('')
    $('#error_nombre').text('')
    $('#error_valor').text('')
}

function desactivarParametro (btn) {
    if (confirm('¿Desactivar este parámetro?')) {
        const route = '/admin/admin.elementos.destroy/' + btn
        $.ajax({
            url: route,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'DELETE'
            },
            type: 'DELETE',
            dataType: 'json'
        })
            .then(response => {
                toastr.success(response.message)
                Cargar()
            })
            .catch(e => {
                const arr = e.responseJSON
                if (e.status == 403) {
                    toastr.warning(arr.message)
                } else {
                    toastr.error('Error al desactivar parámetro')
                }
            })
    }
}

//detalles de parametros

//crea un id local único
const genRidN = () =>
    'r' + Math.random().toString(36).slice(2) + Date.now().toString(36)

const withRowIdN = (arr = []) =>
    arr.map((r, i) => ({
        __rid:
            r.__rid ??
            r.id ??
            `${r.item}-${r.unidad_medida}-${i}-${Date.now()}`, // algo único y estable
        ...r
    }))

function nuevoParametroDet () {
    const codigoElemento = $('#codigo').val()
    if (
        codigoElemento == '' ||
        codigoElemento == '0' ||
        codigoElemento == null
    ) {
        toastr.warning(
            'Debe ingresar un código para el parámetro antes de agregar subelementos.'
        )
        return
    }
    elemento_id = $('#elemento_id').val()
    if (elemento_id == '' || elemento_id == '0' || elemento_id == null) {
        toastr.warning(
            'Debe guardar el parámetro antes de agregar subelementos.'
        )
        return
    }
    window.tableParamsDet.addRow({
        __rid: genRidN(), // id local único
        id: elemento_id,
        codigo: '',
        nombre: '',
        codigo_padre: '',
        valor: ''
    })
}

function actualizarParametroDet () {
    window.tableParamsDet.replaceData()
}

const columnasTabulatorParametrosDet = [
    {
        title: 'id',
        field: 'id',
        editor: 'input',
        visible: false,
        mutator: () => $('#elemento_id').val() ?? 0,
    },
    {
        title: 'Código',
        field: 'codigo',
        editor: false,
        mutator: () => $('#codigo').val() ?? '',
        formatter: () => $('#codigo').val() ?? ''
    },
    {
        title: 'Nombre',
        field: 'nombre',
        editor: 'input'
    },
    {
        title: 'Codigo-Padre',
        field: 'codigo_padre',
        editor: 'input'
    },
    {
        title: 'Valor',
        field: 'valor',
        editor: 'input'
    },
    {
        title: 'Contiene Propiedad',
        field: 'contiene_prop',
        editor: 'list',
        editorParams: {
            values: [
                { value: 'SI', label: 'Sí' },
                { value: 'NO', label: 'No' }
            ]
        }
    }
]

const columnaAccionesTabulatorParametrosDet = [
    {
        title: 'Acciones',
        hozAlign: 'center',
        formatter: () =>
            `
                    <button class="btn btn-success btn-sm guardar" >
                        <i class="fas fa-save"></i>
                    </button>
                    <button class="btn btn-danger btn-sm eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                    `,
        width: 100,
        cellClick: async function (e, cell) {
            const row = cell.getRow()
            const el = e.target

            if (!el) return

            if (el.closest('.guardar')) {
                await saveDataParametrosDet(row)
            } else if (el.closest('.eliminar')) {
                await deleteDataParametrosDet(row);
            }
        }
    }
]

let tableParamsDet = null
function confTablaDet (datos) {
    console.log(datos)

    if (window.tableParamsDet) window.tableParamsDet.destroy() // Destruye la tabla existente si ya está creada
    const data = Array.isArray(datos.sub_elementos)
        ? withRowIdN(datos.sub_elementos)
        : []

    tableParamsDet = new Tabulator('#parametrosdet-table', {
        index: '__rid', // Campo de índice único
        height: '300px',
        layout: 'fitColumns',
        data: data,
        // reactiveData: true,
        pagination: 'local',
        paginationSize: 10,
        columns: [
            ...columnasTabulatorParametrosDet,
            ...columnaAccionesTabulatorParametrosDet
        ]
    })

    window.tableParamsDet = tableParamsDet // Guarda la instancia en una variable global

    tableParamsDet?.on('dataChanged', rows => {
        const ids = new Set(),
            dup = []
        ;(rows || []).forEach(r => {
            if (ids.has(r.__rid)) dup.push(r.__rid)
            ids.add(r.__rid)
        })
        if (dup.length) console.warn('Duplicados __rid:', dup)
    })
    // Maneja eventos de botones dentro de la tabla
    // document.getElementById("parametrosdet-table").addEventListener("click", function (e) {
    //     if (e.target.closest(".guardar")) {
    //         let id = e.target.closest(".guardar").dataset.id;
    //         let row = tableParamsDet.getRow(id).getData();

    //         $.ajax({
    //             url: "admin.subelementos.store",
    //             type: "POST",
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    //             },
    //             contentType: "application/json",
    //             data: JSON.stringify(row),
    //             success: function (response) {
    //                 toastr.success("Subelemento guardado correctamente");
    //                 tableParamsDet.replaceData(); // refresca tabla
    //             },
    //             error: function (xhr) {
    //                 console.error(xhr.responseText);
    //                 toastr.error("Error al guardar subelemento");
    //             }
    //         });
    //     }

    //     if (e.target.closest(".eliminar")) {
    //         let id = e.target.closest(".eliminar").dataset.id;
    //         if (confirm("¿Eliminar este subelemento?")) {
    //             $.ajax({
    //                 url: `admin.subelementos.destroy/${id}`,
    //                 type: "DELETE",
    //                 headers: {
    //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    //                 },
    //                 success: function () {
    //                     toastr.success("Subelemento eliminado correctamente");
    //                     tableParamsDet.replaceData();
    //                 },
    //                 error: function (xhr) {
    //                     console.error(xhr.responseText);
    //                     toastr.error("Error al eliminar subelemento");
    //                 }
    //             });
    //         }
    //     }
    // });
}

async function saveDataParametrosDet (row = null) {
    const isSingle = !!row

    const rows = (() => {
        if (!isSingle) {
            return tableParamsDet.getData()
        }
        // row puede ser RowComponent o un objeto literal de datos
        if (row && typeof row.getData === 'function') return [row.getData()]
        return [row] // ya es objeto de datos
    })()

    // 3) Limpiar campos efímeros (no enviar __rid ni columnas de acción)
    const payload = rows.map(({ __rid, acciones, ...r }) => r)

    // 4) Validación básica
    const invalida = payload.find(r => !r?.codigo || !r?.nombre)
    if (invalida) {
        await Swal.fire('Atención', 'Hay filas sin código o nombre.', 'warning')
        return false
    }

    // 5) Enviar al backend (siempre como array)
    try {
        $.ajax({
            url: 'admin.subelementos.store',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            data: JSON.stringify(payload[0] ?? {}),
            success: function (response) {
                toastr.success('Subelemento guardado correctamente')
                //tableParamsDet.replaceData([response.data]) // refresca tabla
            },
            error: function (xhr) {
                console.error(xhr.responseText)
                toastr.error('Error al guardar subelemento')
            }
        }).catch(err => {
            throw err
        })

        // (Opcional) usar la respuesta si necesitas actualizar la fila
        // const dataResp = await res.json().catch(() => ({}));
        //actulizamos la variable parametrizacion
        parametrizacion = tableParamsDet.getData()

        // await Swal.fire(
        //     'OK',
        //     isSingle ? 'Fila guardada correctamente' : 'Guardado correctamente',
        //     'success'
        // )
        return true
    } catch (err) {
        console.error(err)
        await Swal.fire('Error', err.message || 'No se pudo guardar', 'error')
        return false
    }
}

async function deleteDataParametrosDet (row = null) {
    if (!row) return false
    const id = row.getData().__rid;
    console.log(id);

    if (!id) {
        row.delete()
        return true
    }
    if (
        !(await Swal.fire({
            title: 'Confirmar',
            text: '¿Eliminar este subelemento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(res => res.value))
    )
        return false

    try {
        $.ajax({
            url: `admin.subelementos.destroy/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                toastr.success('Subelemento eliminado correctamente')
                row.delete()
                //tableParamsDet.replaceData();
            },
            error: function (xhr) {
                console.error(xhr.responseText)
                toastr.error('Error al eliminar subelemento')
            }
        }).catch(err => {
            throw err
        })

        // await Swal.fire('OK', 'Eliminado correctamente', 'success');
        return true
    } catch (err) {
        console.error(err)
        await Swal.fire('Error', err.message || 'No se pudo eliminar', 'error')
        return false
    }
}
