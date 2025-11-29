$(async function () {
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
    // fichaTecnica_id=$('#fichatecnica_id').val();
    // showFichaTecnica(fichaTecnica_id);
    const guardar = document.getElementById('guardar-ficha-tecnica')?.addEventListener('click', registerFichaTecnica);

    //Llamar a los materiales
    if(statusFT!='create'){
        await confTableMateriales();
        await confTableProcesos();
    }
})



// Limpiar inputs
function cleanInput () {
    // Campos usuario
    const Fields = [
        'fichatecnica_id',
        'codigo',
        'nombre',
        'observacion',
        'coleccion',
        'fecha'
    ]
    Fields.forEach(field => {
        $('#' + field).val('')
    })
}

// function showFichaTecnica (btn) {
//     // LIMPIAR CAMPOS
//     console.log('btn', btn);

//     // cleanInput()

//     $.get('/admin/admin.fichas-tecnicas.getDataById/' + btn, response => {
//         const fichaTecnica = response.data;

//         // ficha.forEach(field => {
//         //     if (field == 'fecha') {
//         //         const date = new Date(fichaTecnica[field])
//         //         const options = {
//         //             day: '2-digit',
//         //             month: '2-digit',
//         //             year: 'numeric'
//         //         }
//         //         const fecha = date.toLocaleDateString('es-ES', options)
//         //         const fechaConver = convertDateFormat(fecha)
//         //         $('#' + field).val(fechaConver)
//         //     } else {
//         //         $('#' + field).val(fichaTecnica[field])
//         //     }
//         // })
//     })
// }

function registerFichaTecnica () {
    const fichatecnica_id = $('#fichatecnica_id').val();
    if (fichatecnica_id == '' || fichatecnica_id == undefined) {
        createFichaTecnica();
    }else{
        updateFichaTecnica(fichatecnica_id);
    }
}

function createFichaTecnica () {
    const route = 'admin.fichas-tecnicas.store'

    let ajax_data = {
        // Datos formulario
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        coleccion: $('#coleccion').val(),
        fecha: $('#fecha').val(),
        observacion: $('#observacion').val()
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
            $('#fichatecnica_id').val(response.data.id);
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
            } else if (e.status == 403) {
                toastr.warning(arr.error)
            }
        })
}


function updateFichaTecnica (btn) {
    const route = '/admin/admin.fichas-tecnicas/' + btn

    let ajax_data = {
        // Datos formulario
        codigo: $('#codigo').val(),
        nombre: $('#nombre').val(),
        coleccion: $('#coleccion').val(),
        fecha: $('#fecha').val(),
        observacion: $('#observacion').val()
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
    $('#error_coleccion').text('')
    $('#error_fecha').text('')
    $('#error_observacion').text('')
}

