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
    $('#ModalProcesos').on('hidden.bs.modal', function () {
    })
})

function confDatatable () {
    $('#procesos-table').DataTable({
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
        ajax: '/admin/admin.procesos.index',
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
            { data: 'descripcion', name: 'descripcion' },
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
const myModal = new bootstrap.Modal(document.getElementById('ModalProcesos'), {
    keyboard: false
})


function Cargar () {
    let table = $('#procesosdet-table').DataTable()
    table.ajax.reload()
}

// Limpiar inputs
function cleanInput () {
    // Campos usuario
    const Fields = [
        'proceso_id',
        'codigo',
        'nombre',
        'descripcion',
        'active'
    ]
    Fields.forEach(field => {
        $('#' + field).val('')
    })
}

function showProceso (btn) {
    // LIMPIAR CAMPOS
    cleanInput()

    $.get('/admin/admin.procesos.show/' + btn, response => {
        const parametro = response
        console.log(parametro);

        const parametros = [
            'proceso_id',
            'codigo',
            'nombre',
            'descripcion',
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
            if (parametroField === 'proceso_id') {
                $('#' + parametroField).val(parametro['id'])
                return; // Saltar al siguiente campo
            }
            $('#' + parametroField).val(parametro[parametroField])
        })
        confTablaDet() // Configura la tabla de detalles con el ID de elemento actual
    })
}

//Registrar usuario
function regProceso () {
    myModal.modal('show')
    $('#exampleModalLabel').html('Crear Proceso')

    // LIMPIAR CAMPOS
    cleanInput()
    limpiarValidaciones()
    confTablaDet() // Configura la tabla de detalles vacía
    // FIN LIMPIAR CAMPOS
    $('#btnGuardarElemento').attr('onclick', 'registerProceso()');

    let r =
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'

    $('.modal-footer').html(r)

}

function registerProceso () {
    const route = '/admin/admin.procesos.store'
    let ajax_data = {
        // Datos formulario
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        descripcion: $('#descripcion').val(),
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
            $('#proceso_id').val(response.data.id); // Guarda el ID del nuevo proceso
            confTablaDet() // Recarga la tabla de detalles con el ID de proceso actual
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
function upProceso (btn) {
    myModal.modal('show')
    $('#exampleModalLabel').html('Editar Proceso')
    // LIMPIAR CAMPOS
    cleanInput()
    limpiarValidaciones()
    showProceso(btn)
    // FIN LIMPIAR CAMPOS

    $('#btnGuardarElemento').attr('onclick', 'updateProceso(' + btn + ')')

    let u =
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'
    $('.modal-footer').html(u)
}

function updateProceso (btn) {
    const route = '/admin/admin.proceso/' + btn
    let ajax_data = {
        // Datos formulario
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        valor: $('#descripcion').val(),
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
                myModal.modal('toggle')
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
    $('#error_descripcion').text('')
}

function desactivarProceso (btn) {
    if (confirm('¿Desactivar este proceso?')) {
        const route = '/admin/admin.procesos.destroy/' + btn
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
                Cargar();
            })
            .catch(e => {
                const arr = e.responseJSON
                if (e.status == 403) {
                    toastr.warning(arr.message)
                } else {
                    toastr.error('Error al desactivar proceso')
                }
            })
    }
}

//detalles de procesos

function nuevoProcesoDet () {
    const codigoProceso = $('#codigo').val();
    if (codigoProceso=='' || codigoProceso=='0' || codigoProceso==null) {
        toastr.warning('Debe ingresar un código para el proceso antes de agregar subelementos.');
        return;
    }
    proceso_id = $('#proceso_id').val();
    if (proceso_id=='' || proceso_id=='0' || proceso_id==null) {
        toastr.warning('Debe guardar el proceso antes de agregar subelementos.');
        return;
    }
    window.tableProcesosDet.addRow({
        id: proceso_id,
        actividad: "",
        descripcion: "",
        tiempo: "",
        costo: "",
    });
}

function actualizarProcesoDet () {
    window.tableProcesosDet.replaceData();
}

function confTablaDet () {
    if (window.tableProcesosDet) window.tableProcesosDet.destroy(); // Destruye la tabla existente si ya está creada
    const proceso_id = $('#proceso_id').val() ?? '0';

    let tableProcesosDet = new Tabulator("#procesosdet-table", {
        layout: "fitColumns",
        ajaxURL: "admin.procesosdet.index/" + (proceso_id==''?'0':proceso_id), // URL para obtener los datos
        ajaxConfig: "GET",
        reactiveData: true,
        pagination: "local",
        paginationSize: 5,
        columns: [
            {
                title: "id",
                field: "id",
                editor: "input",
                visible: false,
            },
            {
                title: "Actividad",
                field: "actividad",
                editor: "input",
                mutator: function (value) {
                        return value ? value.toUpperCase() : "";
                    }
            },
            {
                title: "Descripción",
                field: "descripcion",
                editor: "input",
                mutator: function (value) {
                    return value ? value.toUpperCase() : "";
                }
            },
            {
                title: "Tiempo Estimado",
                field: "tiempo",
                editor: "number",
                // mutator: function (value) {
                //     let minutos = parseInt(value) || 0;
                //     return minutos;
                // }
                mutator: function (value) {
                    if (!value) return 0;

                    // value llega como "HH:MM:SS"
                    let partes = value.split(":");
                    if (partes.length === 3) {
                        let horas = parseInt(partes[0]) || 0;
                        let minutos = parseInt(partes[1]) || 0;
                        return (horas * 60) + minutos;
                    }
                    return parseInt(value) || 0;
                },
                formatter: "plaintext"
            },
            {
                title: "Costo Estimado",
                field: "costo",
                editor: "number",
                hozAlign: "right",
                validator: ["numeric"],
                mutator: function (value) {
                    return value ? parseFloat(value).toFixed(2) : 0.00;
                }
            },
            {
                title: "Acciones",
                hozAlign: "center",
                formatter: function (cell) {
                    let id = cell.getRow().getData().id ?? '';
                    return `
                        <button class="btn btn-success btn-sm guardar" data-id="${id}">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-danger btn-sm eliminar" data-id="${id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            },
        ],

    });

    // Asignar la tabla a una variable global para acceder desde otros ámbitos
    window.tableProcesosDet = tableProcesosDet;

    document.getElementById("procesosdet-table").addEventListener("click", function (e) {
        if (e.target.closest(".guardar")) {
            let id = e.target.closest(".guardar").dataset.id;
            let row = tableProcesosDet.getRow(id).getData();

            $.ajax({
                url: "admin.procesosdet.store",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                contentType: "application/json",
                data: JSON.stringify(row),
                success: function (response) {
                    toastr.success("Actividad guardada correctamente");
                    tableProcesosDet.replaceData(); // refresca tabla
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    toastr.error("Error al guardar Actividad");
                }
            });
        }

        if (e.target.closest(".eliminar")) {
            let id = e.target.closest(".eliminar").dataset.id;
            if (confirm("¿Eliminar esta actividad?")) {
                $.ajax({
                    url: `admin.procesosdet.destroy/${id}`,
                    type: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function () {
                        toastr.success("Actividad eliminada correctamente");
                        tableProcesosDet.replaceData();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        toastr.error("Error al eliminar Actividad");
                    }
                });
            }
        }
    });


}


