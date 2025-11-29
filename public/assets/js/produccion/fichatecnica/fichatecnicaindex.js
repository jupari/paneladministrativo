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
    document.getElementById('guardar-ficha-tecnica')?.addEventListener('click', registerFichaTecnica);
})

let fichatecnicaTable = null;

function confDatatable () {
    fichatecnicaTable = $('#fichastecnicas-table').DataTable({
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
        ajax: '/admin/admin.fichas-tecnicas.index',
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
            { data: 'coleccion', name: 'coleccion' },
            { data: 'fecha', name: 'fecha' },
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


function Cargar () {
    let table = fichatecnicaTable;
    table.ajax.reload();

}

function cambiarEstado(btn){
     $.get('/admin/admin.fichas-tecnicas.change/' + btn, response => {
        if(response.status =='Ok'){
            Cargar();
            toastr.success(response.message);
        }

    })
}
