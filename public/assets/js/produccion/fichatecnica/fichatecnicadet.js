
document.addEventListener("DOMContentLoaded", function () {
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

    let container = document.getElementById('bocetos-container');

    // Botón para agregar un nuevo bloque de boceto
    // document.getElementById('add-boceto').addEventListener('click', function () {
    //     let newItem = document.querySelector('.boceto-item').cloneNode(true);

    //     // Limpiar valores al clonar
    //     newItem.querySelector('input[name="nombre[]"]').value = "";
    //     newItem.querySelector('textarea[name="observacion[]"]').value = "";
    //     newItem.querySelector('input[name="archivo[]"]').value = "";
    //     newItem.querySelector('.preview').innerHTML = "";

    //     container.appendChild(newItem);
    // });

    document.getElementById('add-boceto').addEventListener('click', function () {
    const container = document.getElementById('bocetos-container');
    const fichaTecnicaId = document.querySelector('input[name="fichatecnica_id[]"]').value;
    const codigo = document.querySelector('input[name="codigo[]"]').value;

    const newHtml = `
        <div class="boceto-item border p-3 mb-3 rounded">
            <input type="hidden" name="fichatecnicaboceto_id[]" value="0">
            <input type="hidden" name="fichatecnica_id[]" value="${fichaTecnicaId}">
            <input type="hidden" name="codigo[]" value="${codigo}">

            <div class="mb-3">
                <label class="form-label">Nombre del Boceto</label>
                <input type="text" class="form-control" name="nombre[]" placeholder="Nombre">
            </div>

            <div class="col-12">
                <label class="form-label">Observación</label>
                <textarea class="form-control" name="observacion[]"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Archivo</label>
                <input type="file" class="form-control boceto-file" name="archivo[]">
                <div class="preview mt-2"></div>
            </div>

            <button type="button" class="btn btn-danger btn-sm remove-boceto">Eliminar</button>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', newHtml);
    });

    // Delegación de eventos para eliminar bocetos del formulario
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-boceto')) {
            if (document.querySelectorAll('.boceto-item').length > 1) {
                e.target.closest('.boceto-item').remove();
            } else {
                let item = e.target.closest('.boceto-item');
                item.querySelector('input[name="nombre[]"]').value = "";
                item.querySelector('textarea[name="observacion[]"]').value = "";
                item.querySelector('input[name="archivo[]"]').value = "";
                item.querySelector('.preview').innerHTML = "";
            }
        }
    });

    // Preview de imágenes
    container.addEventListener('change', function (e) {
        if (e.target.classList.contains('boceto-file')) {
            let previewDiv = e.target.closest('.boceto-item').querySelector('.preview');
            previewDiv.innerHTML = "";

            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function (ev) {
                    previewDiv.innerHTML = `
                        <img src="${ev.target.result}"
                             class="img-thumbnail mt-2"
                             style="max-height:150px; width:auto;">
                    `;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        }
    });

    // Guardar bocetos
    const form = document.querySelector('#bocetos form');
        form.addEventListener('submit', function (e) {
        e.preventDefault();
        // registerFichaBoceto(form);
        const route = '/admin/admin.fichas-tecnicas-bocetos.store'
        // Capturamos TODO el formulario (incluyendo archivos)
        let formData = new FormData(this);
        // Se pueden agregar extras si no están en el form
        // formData.append('fichatecnica_id', $("#fichatecnica_id").val());
        // formData.append('codigo', $('#codigo').val());

        formData.forEach((value, key) => {
            console.log(key, value);
        });
        $.ajax({
            url: route,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data: formData,
            processData: false,   // obligatorio para enviar archivos
            contentType: false,
        })
            .then(response => {
                $('input[name="fichatecnicaboceto_id[]"]').val('');
                toastr.success(response.message)
                // $('#bocetos-container').html($('.boceto-item').first().prop('outerHTML'));
            })
            .catch(e => {
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
        });
});

function registerFichaBoceto(form) {
    const route = '/admin/admin.fichas-tecnicas-bocetos.store'

    // Capturamos TODO el formulario (incluyendo archivos)
    let formData = new FormData(form);
    console.log('form', form.data);

    // Se pueden agregar extras si no están en el form
    formData.append('fichatecnica_id', $("#fichatecnica_id").val());
    formData.append('codigo', $('#codigo').val());

    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        data: formData,
        processData: false,   // obligatorio para enviar archivos
        contentType: false,
    })
        .then(response => {
            toastr.success(response.message)
            // $('#bocetos-container').html($('.boceto-item').first().prop('outerHTML'));
        })
        .catch(e => {
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

function deleteImagen(id){
    route = '/admin/admin.fichas-tecnicas-bocetos/'+id;
    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'DELETE',
        processData: false,   // obligatorio para enviar archivos
        contentType: false,
    })
        .then(response => {
            toastr.success(response.message)
        })
        .catch(e => {
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

