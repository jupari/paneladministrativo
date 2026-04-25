// Funciones para controlar el skeleton loader
function showSkeleton() {
    const skeleton = document.getElementById('skeleton-loader');
    const content = document.getElementById('main-content');
    if (skeleton) skeleton.style.display = 'block';
    if (content) content.style.display = 'none';
}

// function hideSkeleton() {
//     const skeleton = document.getElementById('skeleton-loader');
//     const content = document.getElementById('main-content');
//     if (skeleton) skeleton.style.display = 'none';
//     if (content) content.style.display = 'block';
// }

function hideSkeleton() {
    document.getElementById('skeleton-loader').style.display = 'none';
    document.getElementById('main-content').style.display = 'block';
}

function showLoadingState() {
    const content = document.getElementById('main-content');
    if (content) content.classList.add('loading');
}

function hideLoadingState() {
    const content = document.getElementById('main-content');
    if (content) content.classList.remove('loading');
}

// Función principal de inicialización (anteriormente IIFE)
// Ahora es llamada por el coordinador en el momento adecuado
async function initializeCoreFeatures() {
    try {
        await initEstadosSelect('estado_id', estados);
        await initClientesSelect('cliente_id', clientes);
        await initImpuestosDescuentos();
        await initItems();

        const observacionTextarea = document.getElementById('observacion');
        const contadorSpan = document.getElementById('observacion_count');
        const agregarCotizacion = document.getElementById('agregarCotizacion');
        const botonesAgregarProductos = document.getElementById('botonesAgregarProductos');
        const documentoLabel = document.getElementById('document-label');

        if (botonesAgregarProductos) {
            botonesAgregarProductos.classList.add('d-none');
        }

        const accordionElement = document.getElementById('accordionCotizacionDetails');
        if (accordionElement) {
            accordionElement.style.display = 'none';
        }

    if (variable === 'ver') {
        // Deshabilitar todos los campos del formulario en modo "ver"
        document.querySelectorAll('#cotizacionForm input, #cotizacionForm select, #cotizacionForm textarea, #cotizacionForm button').forEach(element => {
            element.disabled = true;
        });
        agregarCotizacion.style.display = 'none';
        await cargarDocumento(variable, cotizacion);
        await cargarObservacionesExistentes(cotizacion.id);
        await cargarCondicionesExistentes(cotizacion.id);
        await cargarItemsExistentes(cotizacion.id);
        await cargarViaticosExistentes(cotizacion.id);
        document.getElementById('accordionCotizacionDetails').style.display = 'block';
        botonesAgregarProductos.classList.add('d-none');
    }

    if (variable === 'editar') {
        agregarCotizacion.removeEventListener('click', guardarCotizacion)
        agregarCotizacion.addEventListener('click', function() {
            if (validateForm()) {
                // actualizarCotizacion(document.getElementById('id').value);
                guardarCotizacion();
            }
        });
        await cargarDocumento(variable, cotizacion);
        await cargarObservacionesExistentes(cotizacion.id);
        await cargarCondicionesExistentes(cotizacion.id);
        await cargarItemsExistentes(cotizacion.id);
        await cargarViaticosExistentes(cotizacion.id);
        document.getElementById('accordionCotizacionDetails').style.display = 'block';
        botonesAgregarProductos.classList.remove('d-none');
    } else {
        agregarCotizacion.addEventListener('click', guardarCotizacion);
        await cargarDocumento(variable, cotizacion);
        document.getElementById('accordionCotizacionDetails').style.display = 'none';
        botonesAgregarProductos.classList.add('d-none');
    }

    if (observacionTextarea && contadorSpan) {
        observacionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            contadorSpan.textContent = length;

            if (length > 1000) {
                this.classList.add('is-invalid');
                document.getElementById('error_observacion').textContent = 'La observación no puede tener más de 1000 caracteres';
            } else {
                this.classList.remove('is-invalid');
                document.getElementById('error_observacion').textContent = '';
            }
        });
    }

    // Validación en tiempo real para otros campos
    document.getElementById('proyecto').addEventListener('input', function() {
        if (this.value.length > 255) {
            this.classList.add('is-invalid');
            document.getElementById('error_proyecto').textContent = 'El proyecto no puede tener más de 255 caracteres';
        } else {
            this.classList.remove('is-invalid');
            document.getElementById('error_proyecto').textContent = '';
        }
    });

    // Limpiar errores cuando se selecciona un valor
    document.getElementById('estado_id').addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            document.getElementById('error_estado_id').textContent = '';
        }
    });

    document.getElementById('cliente_id').addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            document.getElementById('error_cliente_id').textContent = '';
        }
    });

    // Event listeners para los campos de totales
    const camposTotales = ['subtotal', 'descuento', 'total_impuesto'];

    camposTotales.forEach(function(campo) {
        const input = document.getElementById(campo);
        if (input) {
            // Calcular totales al cambiar
            input.addEventListener('change', calcularTotales);
            input.addEventListener('input', calcularTotales);

            // Formatear al perder el foco
            input.addEventListener('blur', function() {
                formatearMoneda(this);
                calcularTotales();
            });

            // Validar valores negativos
            input.addEventListener('input', function() {
                if (parseFloat(this.value) < 0) {
                    this.classList.add('is-invalid');
                    document.getElementById(`error_${campo}`).textContent = `El ${campo.replace('_', ' ')} no puede ser negativo`;
                } else {
                    this.classList.remove('is-invalid');
                    document.getElementById(`error_${campo}`).textContent = '';
                }
            });
        }
        });

        // Inicializar cálculo de totales
        calcularTotales();
    } catch (error) {
        console.error('âŒ Error al inicializar caracterí­sticas principales:', error);
        throw error;
    }
}

async function initClientesSelect(selectId, clientes) {
    const select = document.getElementById(selectId);
    const optionDefault = document.createElement('option');
    optionDefault.value = '';
    optionDefault.text = 'Seleccione un cliente';
    select.appendChild(optionDefault);

    clientes.forEach(cliente => {
        const option = document.createElement('option');
        option.value = cliente.id;

        option.text = cliente.nombres ? cliente.nombres + ' ' + cliente.apellidos : cliente.nombre_establecimiento;
        select.appendChild(option);
    });
}

async function initEstadosSelect(selectId, estados) {
    const select = document.getElementById(selectId);
    const optionDefault = document.createElement('option');
    optionDefault.value = '';
    optionDefault.text = 'Seleccione un estado';
    select.appendChild(optionDefault);

    estados.forEach(estado => {
        const option = document.createElement('option');
        option.value = estado.id;
        option.text = estado.estado;
        select.appendChild(option);
    });

    // Si es una cotización nueva, establecer estado "Borrador" por defecto
    const cotizacionId = document.getElementById('id')?.value;
    const isNewCotizacion = !cotizacionId || cotizacionId === '' || cotizacionId === 'null';

    if (isNewCotizacion && estados.length > 0) {
        // Buscar especí­ficamente el estado "Borrador" (ID: 1)
        const estadoBorrador = estados.find(estado => estado.id === 1 || estado.estado.toLowerCase() === 'borrador');
        if (estadoBorrador) {
            select.value = estadoBorrador.id;
        } else {
            // Fallback: usar el primer estado disponible
            select.value = estados[0].id;
        }

        // Actualizar el badge de estado despuí©s de establecer el valor por defecto
        setTimeout(() => {
            if (typeof actualizarProgresoCompletion === 'function') {
                actualizarProgresoCompletion();
            }
        }, 100);
    }
}

async function fetchSucursales(terceroId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.getSucursales/${terceroId}`);
        const data = await response.json();

        const sedeSelect = document.getElementById('tercero_sucursal_id');
        sedeSelect.innerHTML = ''; // Limpiar opciones existentes

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = 'Seleccione una sede';
        sedeSelect.appendChild(defaultOption);

        data.data.forEach(sede => {
            const option = document.createElement('option');
            option.value = sede.id;
            option.text = sede.nombre_sucursal ? sede.nombre_sucursal : (sede.nombres ? sede.nombres +' '+ (sede.direccion ? sede.direccion : '') : sede.direccion);
            sedeSelect.appendChild(option);
        });
        fetchContactos(terceroId);
        return data.data; // Retornar los datos para uso posterior
    } catch (error) {
        console.error('Error al cargar las sucursales:', error);
        throw error;
    }
}


async function fetchContactos(terceroId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.getContactos/${terceroId}`);
        const data = await response.json();

        const contactoSelect = document.getElementById('tercero_contacto_id');
        contactoSelect.innerHTML = ''; // Limpiar opciones existentes

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = 'Seleccione un contacto';
        contactoSelect.appendChild(defaultOption);

        data.data.forEach(contacto => {
            const option = document.createElement('option');
            option.value = contacto.id;
            option.text = contacto.nombres+' '+contacto.apellidos + ' ('+contacto.correo+')' + ' ('+contacto.cargo+')';
            contactoSelect.appendChild(option);
        });

        return data.data; // Retornar los datos para uso posterior
    } catch (error) {
        console.error('Error al cargar las sucursales:', error);
        throw error;
    }
}


//Gurdar
async function guardarCotizacion() {

    $('#spinnerRegister').removeClass('d-none');

    //botones para agregar productos
    const botonesAgregarProductos = document.getElementById('botonesAgregarProductos');
    // Determinar si es creación o actualización
    const isEdit = variable === 'editar';
    const cotizacionId = document.getElementById('id')?.value;
    const route = isEdit ? `/admin/admin.cotizaciones.update/${cotizacionId}` : "/admin/admin.cotizaciones.store";
    const method = isEdit ? 'PUT' : 'POST';

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    if (isEdit) {
        ajax_data.append('_method', 'PUT');
    }
    // Agregar los nuevos campos del formulario
    // Para estado_id, usar el valor seleccionado o Borrador como defecto
    let estadoId = $('#estado_id').val();
    if (!estadoId || estadoId === '') {
        // Para cotizaciones nuevas, usar siempre el estado Borrador (ID: 1)
        const cotizacionId = document.getElementById('id')?.value;
        const isNewCotizacion = !cotizacionId || cotizacionId === '' || cotizacionId === 'null';

        if (isNewCotizacion) {
            estadoId = 1; // Borrador por defecto
        } else {
            // Para edición, buscar el primer estado disponible
            const estadoSelect = document.getElementById('estado_id');
            if (estadoSelect && estadoSelect.options.length > 1) {
                estadoId = estadoSelect.options[1].value;
            } else {
                estadoId = 1; // Fallback
            }
        }
    }

    ajax_data.append('estado_id', estadoId);
    ajax_data.append('num_documento', $('#num_documento').val());
    ajax_data.append('fecha', $('#fecha').val());
    ajax_data.append('tipo', $('#tipo').val()=='' ? 'COT' : $('#tipo').val());
    ajax_data.append('proyecto', $('#proyecto').val());
    ajax_data.append('autorizacion_id', $('#autorizacion_id').val()=='Pendiente por autorización' ? 1 : 2);
    ajax_data.append('doc_origen', $('#doc_origen').val());
    ajax_data.append('version', $('#version').val()=='' ? '1' : $('#version').val());
    ajax_data.append('tercero_id', $('#cliente_id').val());
    ajax_data.append('tercero_sucursal_id', $('#tercero_sucursal_id').val());
    ajax_data.append('tercero_contacto_id', $('#tercero_contacto_id').val());
    ajax_data.append('observacion', $('#observacion').val());
    ajax_data.append('subtotal', $('#subtotal').val()==''?0:$('#subtotal').val());
    ajax_data.append('descuento', $('#descuento').val()==''?0:$('#descuento').val());
    ajax_data.append('total_impuesto', $('#total_impuesto').val()==''?0:$('#total_impuesto').val());
    ajax_data.append('total', $('#total').val()==''?0:$('#total').val());

    ajax_data.append('impuestos_descuentos', JSON.stringify(impuestosDescuentos));

    // Mostrar estado de carga
    showLoadingState();

    // Realizar la solicitud AJAX
    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMíGENES O ARCHIVOS POR AJAX
        processData: false,
    }).then(async response => {
        if (response.success) {
            const cotizacionGuardadaId = isEdit ? cotizacionId : response.data?.id;
            let id = document.getElementById('id');
            let cotizacionId = document.getElementById('cotizacionId');
            id.value = cotizacionGuardadaId;
            cotizacionId.value = cotizacionGuardadaId;

            // Mostrar botones de PDF
            if (typeof mostrarBotonesPdf === 'function' && cotizacionGuardadaId) {
                mostrarBotonesPdf(cotizacionGuardadaId);
            }

            // Siempre guardar conceptos, observaciones y condiciones comerciales
            if (isEdit) {
                toastr.success('Cotización actualizada exitosamente');
                document.getElementById('accordionCotizacionDetails').style.display = 'block';
                botonesAgregarProductos.classList.remove('d-none');
                if (cotizacionGuardadaId) {
                    await guardarConceptosCotizacion(cotizacionGuardadaId);
                    await guardarObservacionesCotizacion(cotizacionGuardadaId);
                    await guardarCondicionesCotizacion(cotizacionGuardadaId);
                }
            } else {
                toastr.success('Cotización creada exitosamente');
                document.getElementById('accordionCotizacionDetails').style.display = 'block';
                botonesAgregarProductos.classList.remove('d-none');
                variable='editar';
            }
            //parar el spinner
            $('#spinnerRegister').addClass('d-none');
            hideLoadingState();
        }
    }).catch(e => {
        $('#spinnerRegister').addClass('d-none');
        hideLoadingState();
        showValidationErrors(e.responseJSON?.errors || {});
        const arr = e.responseJSON;
        toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
    });
}


//Actualizar
async function actualizarCotizacion(btn) {

    const route = `/admin/admin.cotizaciones.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Lista de campos del formulario
    const fields = [
        'id',
        'estado_id',
        'num_documento',
        'fecha',
        'proyecto',
        'cliente_id',
        'tercero_sucursal_id',
        'tercero_contacto_id',
        'observacion',
        'subtotal',
        'descuento',
        'total_impuesto',
        'total'
    ];
    // Recorrer los campos y agregarlos al FormData
    fields.forEach(field => {
        ajax_data.append(field, $('#' + field).val());
    });

    ajax_data.append('tercero_id', $('#cliente_id').val()=='' ? null : $('#cliente_id').val());

    // Mostrar estado de carga
    showLoadingState();

    // Enviar la solicitud AJAX
    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false,
        processData: false,
    })
    .then(response => {
        hideLoadingState();
        toastr.success(response.message);
        CargarCotizaciones();
    })
    .catch(e => {
        hideLoadingState();
        clearValidationErrors();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status === 422) {
            // Errores de validación
            showValidationErrors(toast);
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            toastr.warning(arr.message);
        }
    });
}

// Función para calcular totales automáticamente
function calcularTotales() {
    try {
        // Detectar si tenemos el sistema nuevo de totales
        const tieneElementosNuevos = !!(
            document.getElementById('display-subtotal-valor') ||
            document.getElementById('display-total-valor') ||
            document.getElementById('hidden_subtotal')
        );

        // Detectar si tenemos elementos tradicionales
        const subtotalEl = document.getElementById('subtotal');
        const descuentoEl = document.getElementById('descuento');
        const totalImpuestoEl = document.getElementById('total_impuesto');

        if (tieneElementosNuevos) {
            actualizarTotalesCompletos();
            return;
        }

        if (!subtotalEl || !descuentoEl || !totalImpuestoEl) {
            if (typeof actualizarTotalesCompletos === 'function') {
                actualizarTotalesCompletos();
                return;
            } else {
                return;
            }
        }

        // Calcular subtotal automáticamente
        actualizarSubtotal();

        const subtotal = parseFloat(subtotalEl.value) || 0;
        const descuento = parseFloat(descuentoEl.value) || 0;
        const totalImpuesto = parseFloat(totalImpuestoEl.value) || 0;

        // Validar descuento de forma segura
        const errorDescuentoEl = document.getElementById('error_descuento');
        if (descuento > subtotal) {
            if (descuentoEl) descuentoEl.classList.add('is-invalid');
            if (errorDescuentoEl) errorDescuentoEl.textContent = 'El descuento no puede ser mayor al subtotal';
            return;
        } else {
            if (descuentoEl) descuentoEl.classList.remove('is-invalid');
            if (errorDescuentoEl) errorDescuentoEl.textContent = '';
        }

        // Calcular totales de forma segura
        const subtotalMenosDescuentoEl = document.getElementById('subtotal_menos_descuento');
        const totalEl = document.getElementById('total');

        const subtotalMenosDescuento = subtotal - descuento;
        if (subtotalMenosDescuentoEl) {
            subtotalMenosDescuentoEl.value = subtotalMenosDescuento.toFixed(2);
        }

        const totalFinal = subtotalMenosDescuento + totalImpuesto;
        if (totalEl) {
            totalEl.value = totalFinal.toFixed(2);
        }

    } catch (error) {
        console.error('ðŸ’¥ Error en calcularTotales:', error);
        // Fallback al sistema nuevo si está disponible
        if (typeof actualizarTotalesCompletos === 'function') {
            actualizarTotalesCompletos();
        }
    }
}

/**
 * Actualizar subtotal basado en items, productos y salarios
 */
function actualizarSubtotal() {
    let subtotal = 0;

    // Sumar items existentes (los que ya estaban en la cotización)
    if (typeof itemsCotizacion !== 'undefined' && Array.isArray(itemsCotizacion)) {
        subtotal += itemsCotizacion.reduce((sum, item) => {
            return sum + (item.valor_total || 0);
        }, 0);
    }

    document.getElementById('subtotal').value = subtotal.toFixed(2);
}

// Formatear números como moneda
function formatearMoneda(input) {
    let value = parseFloat(input.value) || 0;
    input.value = value.toFixed(2);
}

// Funciones de validación
function clearValidationErrors() {
    // Remover clases de error
    document.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });

    // Limpiar mensajes de error
    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
}

function showValidationErrors(errors) {
    clearValidationErrors();

    for (const field in errors) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(`error_${field}`);

        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = errors[field][0];
        }
    }
}

function validateForm() {
    let isValid = true;
    const errors = {};

    // Validar estado
    const estado = document.getElementById('estado_id');
    if (!estado.value) {
        errors.estado_id = ['Debe seleccionar un estado'];
        isValid = false;
    }

    // Validar número de documento
    const numDocumento = document.getElementById('num_documento');
    if (!numDocumento.value) {
        errors.num_documento = ['El número de documento es obligatorio'];
        isValid = false;
    }

    // Validar cliente
    const cliente = document.getElementById('cliente_id');
    if (!cliente.value) {
        errors.cliente_id = ['Debe seleccionar un cliente'];
        isValid = false;
    }

    // Validar proyecto (máximo 255 caracteres)
    const proyecto = document.getElementById('proyecto');
    if (proyecto.value && proyecto.value.length > 255) {
        errors.proyecto = ['El proyecto no puede tener más de 255 caracteres'];
        isValid = false;
    }

    // Validar observación (máximo 1000 caracteres)
    const observacion = document.getElementById('observacion');
    if (observacion.value && observacion.value.length > 1000) {
        errors.observacion = ['La observación no puede tener más de 1000 caracteres'];
        isValid = false;
    }

    // Validar subtotal y descuento
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;

    if (descuento > subtotal) {
        errors.descuento = ['El descuento no puede ser mayor al subtotal'];
        isValid = false;
    }

    // Validar que los montos sean positivos
    if (subtotal < 0) {
        errors.subtotal = ['El subtotal no puede ser negativo'];
        isValid = false;
    }

    if (descuento < 0) {
        errors.descuento = ['El descuento no puede ser negativo'];
        isValid = false;
    }

    const totalImpuesto = parseFloat(document.getElementById('total_impuesto').value) || 0;
    if (totalImpuesto < 0) {
        errors.total_impuesto = ['El total de impuesto no puede ser negativo'];
        isValid = false;
    }

    if (!isValid) {
        showValidationErrors(errors);
    }

    return isValid;
}


async function cargarDocumento(variable, cotizacion) {
    try {

        document.getElementById('document-label').textContent = variable === 'editar' ? cotizacion.num_documento +' - Editar' : variable === 'ver' ? cotizacion.num_documento + ' - Ver' : ' - Nuevo';

        if (variable === 'editar' || variable === 'ver') {
            // Cambiar texto del botón
            const botonGuardar = document.getElementById('agregarCotizacion');
            if (botonGuardar && variable === 'editar') {
                botonGuardar.innerHTML = '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span> Actualizar Cotización';
            }

            // Asignar el número de documento al campo correspondiente
            const id=document.getElementById('id');
            id.value=cotizacion.id;

            const estado = document.getElementById('estado_id');
            estado.value = cotizacion.estado_id;

            const documento = document.getElementById('num_documento');
            documento.value = consecutivo;
            documento.readOnly = true;

            const proyecto = document.getElementById('proyecto');
            proyecto.value = cotizacion.proyecto;

            const tipo = document.getElementById('tipo');
            tipo.value = cotizacion.tipo;

            const autorizacion = document.getElementById('autorizacion_id');
            autorizacion.readOnly= true;
            autorizacion.value = cotizacion.autorizacion ? cotizacion.autorizacion.nombre : 'Pendiente por autorización';

            const docOrigen = document.getElementById('doc_origen');
            docOrigen.readOnly= true;
            docOrigen.value = cotizacion.doc_origen;

            const version = document.getElementById('version');
            version.readOnly= true;
            version.value = cotizacion.version;

            const fecha = document.getElementById('fecha');
            fecha.value = cotizacion.fecha?cotizacion.fecha.split('T')[0]:'';

            const cliente = document.getElementById('cliente_id');
            cliente.value = cotizacion.tercero_id;

            await fetchSucursales(cotizacion.tercero_id);
            const sede = document.getElementById('tercero_sucursal_id');
            // Establecer el valor despuí©s de que las opciones se hayan cargado
            setTimeout(() => {
                sede.value = cotizacion.tercero_sucursal_id;
            }, 100);

            await fetchContactos(cotizacion.tercero_id);
            const contacto = document.getElementById('tercero_contacto_id');
            // Establecer el valor despuí©s de que las opciones se hayan cargado
            setTimeout(() => {
                contacto.value = cotizacion.tercero_contacto_id;
            }, 100);

            const observacion = document.getElementById('observacion');
            observacion.value = cotizacion.observacion;

            const contadorSpan = document.getElementById('observacion_count');
            contadorSpan.textContent = observacion.value.length;

            const subtotal = document.getElementById('subtotal');
            if (variable === 'ver') {
                subtotal.readOnly= true;
            }
            subtotal.value = parseFloat(cotizacion.subtotal).toFixed(2);

            const descuento = document.getElementById('descuento');
            if (variable === 'ver') {
                descuento.readOnly= true;
            }
            descuento.value = parseFloat(cotizacion.descuento).toFixed(2);

            const totalImpuesto = document.getElementById('total_impuesto');
            if (variable === 'ver') {
                totalImpuesto.readOnly= true;
            }
            totalImpuesto.value = parseFloat(cotizacion.total_impuesto).toFixed(2);

            const total = document.getElementById('total');
            if (variable === 'ver') {
                total.readOnly= true;
            }
            total.value = parseFloat(cotizacion.total).toFixed(2);

            hideSkeleton();
            cargarProductosGuardados();


        }else{
            // Asignar el número de documento al campo correspondiente
            const id=document.getElementById('id');
            id.value = null;

            const documento = document.getElementById('num_documento');
            documento.value = consecutivo;
            documento.readOnly = true;

            document.getElementById('doc_origen').readOnly= true;

            document.getElementById('version').value = '1';
            document.getElementById('version').readOnly= true;

            const autorizacion = document.getElementById('autorizacion_id');
            autorizacion.readOnly= true;
            autorizacion.value ='Pendiente por autorización';
        }
    } catch (error) {
        console.error('Error al cargar los datos iniciales:', error);
    }
}

// Variables globales para impuestos y descuentos
let impuestosDescuentos = [];
let contadorRegistros = 0;
let conceptosDisponibles = [];


// Función para cargar conceptos disponibles desde el backend
async function cargarConceptosDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.conceptos.getConceptos', {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            conceptosDisponibles = data.data;
            poblarSelectConceptos();
        } else {
            console.error('Error al cargar conceptos:', data.message);
            toastr.error('Error al cargar los conceptos disponibles');
        }
    } catch (error) {
        console.error('Error en la solicitud:', error);
        toastr.error('Error de conexión al cargar conceptos');
    }
}

// Función para poblar los selects con los conceptos
function poblarSelectConceptos() {
    const selectImpuesto = document.getElementById('concepto_impuesto');
    const selectDescuento = document.getElementById('concepto_descuento');

    if (selectImpuesto) {
        selectImpuesto.innerHTML = '<option value="">Seleccione concepto</option>';
        conceptosDisponibles
            .filter(concepto => concepto.tipo === 'IVA' || concepto.tipo === 'IMP' ||  concepto.tipo === 'IMPUESTO')
            .forEach(concepto => {
                const option = document.createElement('option');
                option.value = concepto.id;
                option.textContent = concepto.nombre;
                option.setAttribute('data-porcentaje', concepto.porcentaje_defecto || '0');
                selectImpuesto.appendChild(option);
            });
    }

    if (selectDescuento) {
        selectDescuento.innerHTML = '<option value="">Seleccione concepto</option>';
        conceptosDisponibles
            .filter(concepto => concepto.tipo === 'DES')
            .forEach(concepto => {
                const option = document.createElement('option');
                option.value = concepto.id;
                option.textContent = concepto.nombre;
                option.setAttribute('data-porcentaje', concepto.porcentaje_defecto || '0');
                selectDescuento.appendChild(option);
            });
    }
}

// Función para auto-completar valores según el concepto seleccionado
function autoCompletarValorConcepto(tipo, conceptoId) {
    if (!conceptoId) return;

    const concepto = conceptosDisponibles.find(c => c.id == conceptoId);
    if (concepto && concepto.porcentaje_defecto > 0) {
        const valorInput = document.getElementById(`valor_${tipo}`);
        const tipoSelect = document.getElementById(`tipo_${tipo}`);
        const simbolo = document.getElementById(`simbolo_${tipo}`);

        if (valorInput && tipoSelect && simbolo) {
            valorInput.value = concepto.porcentaje_defecto;
            tipoSelect.value = 'porcentaje';
            simbolo.textContent = '%';
        }
    }
}

// Función para inicializar la funcionalidad de impuestos y descuentos
async function initImpuestosDescuentos() {
    // Cargar conceptos disponibles y observaciones disponibles
    Promise.all([
        cargarConceptosDisponibles(),
        cargarObservacionesDisponibles()
    ]).then(() => {
        // Si estamos en modo edición y hay una cotización, cargar datos existentes
        if (variable === 'editar' && cotizacion?.id) {
            cargarConceptosExistentes(cotizacion.id);
            document.getElementById('agregar_descuento').disabled = false;
            document.getElementById('agregar_impuesto').disabled = false;
        }else if (variable === 'ver' && cotizacion?.id) {
            cargarConceptosExistentes(cotizacion.id);
            document.getElementById('agregar_descuento').disabled = true;
            document.getElementById('agregar_impuesto').disabled = true;
        }else{
            document.getElementById('agregar_descuento').disabled = false;
            document.getElementById('agregar_impuesto').disabled = false;
        }
    });

    // Cambiar sí­mbolo según el tipo seleccionado para descuentos
    const tipoDescuentoSelect = document.getElementById('tipo_descuento');
    if (tipoDescuentoSelect) {
        tipoDescuentoSelect.addEventListener('change', function() {
            const simbolo = document.getElementById('simbolo_descuento');
            if (simbolo) {
                simbolo.textContent = this.value === 'porcentaje' ? '%' : '$';
                document.getElementById('valor_descuento').placeholder = this.value === 'porcentaje' ? '0.00' : '0.00';
            }
        });
    }

    // Cambiar sí­mbolo según el tipo seleccionado para impuestos
    const tipoImpuestoSelect = document.getElementById('tipo_impuesto');
    if (tipoImpuestoSelect) {
        tipoImpuestoSelect.addEventListener('change', function() {
            const simbolo = document.getElementById('simbolo_impuesto');
            if (simbolo) {
                simbolo.textContent = this.value === 'porcentaje' ? '%' : '$';
                document.getElementById('valor_impuesto').placeholder = this.value === 'porcentaje' ? '0.00' : '0.00';
            }
        });
    }

    // Auto-completar valores desde base de datos
    const conceptoImpuestoSelect = document.getElementById('concepto_impuesto');
    if (conceptoImpuestoSelect) {
        conceptoImpuestoSelect.addEventListener('change', function() {
            autoCompletarValorConcepto('impuesto', this.value);
        });
    }

    const conceptoDescuentoSelect = document.getElementById('concepto_descuento');
    if (conceptoDescuentoSelect) {
        conceptoDescuentoSelect.addEventListener('change', function() {
            autoCompletarValorConcepto('descuento', this.value);
        });
    }

    // Agregar descuento
    const btnAgregarDescuento = document.getElementById('agregar_descuento');
    if (btnAgregarDescuento) {
        btnAgregarDescuento.addEventListener('click', function() {
            agregarImpuestoDescuento('descuento');
        });
    }

    // Agregar impuesto
    const btnAgregarImpuesto = document.getElementById('agregar_impuesto');
    if (btnAgregarImpuesto) {
        btnAgregarImpuesto.addEventListener('click', function() {
            agregarImpuestoDescuento('impuesto');
        });
    }

    // Seleccionar/Deseleccionar todos
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#tbody_impuestos_descuentos input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleEliminarSeleccionados();
        });
    }

    // Eliminar seleccionados
    const btnEliminarSeleccionados = document.getElementById('eliminar_seleccionados');
    if (btnEliminarSeleccionados) {
        btnEliminarSeleccionados.addEventListener('click', function() {
            eliminarSeleccionados();
        });
    }

    // Limpiar todo
    const btnLimpiarTodo = document.getElementById('limpiar_todo');
    if (btnLimpiarTodo) {
        btnLimpiarTodo.addEventListener('click', function() {
            if (confirm('Â¿Está seguro de eliminar todos los impuestos y descuentos?')) {
                limpiarTodoImpuestosDescuentos();
            }
        });
    }
}

// Función para agregar impuesto o descuento
async function agregarImpuestoDescuento(tipo) {
    const concepto = document.getElementById(`concepto_${tipo}`)?.value;
    const tipoCalculo = document.getElementById(`tipo_${tipo}`)?.value;
    const valor = parseFloat(document.getElementById(`valor_${tipo}`)?.value) || 0;

    if (!concepto) {
        toastr.warning('Por favor seleccione un concepto');
        return;
    }

    if (valor <= 0) {
        toastr.warning('El valor debe ser mayor a cero');
        return;
    }

    contadorRegistros++;
    const registro = {
        id: contadorRegistros,
        tipo: tipo,
        concepto: concepto,
        tipoCalculo: tipoCalculo,
        valor: valor,
        valorCalculado: calcularValorImpuestoDescuento(valor, tipoCalculo, tipo)
    };

    impuestosDescuentos.push(registro);
    actualizarTablaImpuestosDescuentos();
    limpiarFormularioImpuestoDescuento(tipo);
    actualizarTotalesConImpuestosDescuentos();
    const cotizacionGuardadaId = document.getElementById('id')?.value;
    await guardarConceptosCotizacion(cotizacionGuardadaId);

    toastr.success(`${tipo.charAt(0).toUpperCase() + tipo.slice(1)} agregado correctamente`);
}

// Función para calcular el valor del impuesto/descuento
function calcularValorImpuestoDescuento(valor, tipoCalculo, tipo) {
    const subtotal = parseFloat(document.getElementById('subtotal')?.value) || 0;

    if (tipoCalculo === 'porcentaje') {
        return (subtotal * valor) / 100;
    } else {
        return valor;
    }
}

// Función para actualizar la tabla
function actualizarTablaImpuestosDescuentos() {
    const tbody = document.getElementById('tbody_impuestos_descuentos');
    const noItemsRow = document.getElementById('no_items_row');

    if (!tbody || !noItemsRow) return;

    // Limpiar tabla excepto la fila "no items"
    const existingRows = tbody.querySelectorAll('tr:not(#no_items_row)');
    existingRows.forEach(row => row.remove());

    if (impuestosDescuentos.length === 0) {
        noItemsRow.style.display = 'table-row';
        return;
    }

    noItemsRow.style.display = 'none';

    impuestosDescuentos.forEach(item => {
        const row = document.createElement('tr');
        const conceptoTexto = getConceptoTexto(item.concepto);

        // row.innerHTML = `
        //     <td>
        //         <input type="checkbox" class="item-checkbox" data-id="${item.id}" onchange="toggleEliminarSeleccionados()">
        //     </td>
        //     <td>${item.id.toString().padStart(5, '0')}</td>
        //     <td>
        //         <span class="badge bg-${item.tipo === 'impuesto' ? 'success' : 'primary'}">${item.tipo}</span>
        //         ${conceptoTexto}
        //     </td>
        //     <td>${item.tipoCalculo === 'porcentaje' ? 'Porcentaje' : 'Valor Fijo'}</td>
        //     <td>${item.tipoCalculo === 'porcentaje' ? item.valor + '%' : '$' + numberFormat(item.valor, 2)}</td>
        //     <td class="${item.tipo === 'impuesto' ? 'text-success' : 'text-primary'}">
        //         $${numberFormat(item.valorCalculado, 2)}
        //     </td>
        //     <td>
        //         <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarImpuestoDescuento(${item.id})" title="Eliminar">
        //             <i class="fas fa-trash"></i>
        //         </button>
        //     </td>
        // `;

        row.innerHTML = `
            <td>
                <input type="checkbox" class="item-checkbox" data-id="${item.id}" onchange="toggleEliminarSeleccionados()">
            </td>
            <td>${item.id.toString().padStart(5, '0')}</td>
            <td>
                <span class="badge bg-${item.tipo === 'impuesto' ? 'success' : 'primary'}">${item.tipo}</span>
                ${conceptoTexto}
            </td>
            <td>${item.tipoCalculo === 'porcentaje' ? 'Porcentaje' : 'Valor Fijo'}</td>
            <td>${item.tipoCalculo === 'porcentaje' ? item.valor + '%' : '$' + numberFormat(item.valor, 2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarImpuestoDescuento(${item.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.insertBefore(row, noItemsRow);
    });
}

// Función para obtener el texto del concepto
function getConceptoTexto(conceptoId) {
    const concepto = conceptosDisponibles.find(c => c.id == conceptoId);
    return concepto ? concepto.nombre : `Concepto ${conceptoId}`;
}

// Función para formatear números
function numberFormat(number, decimals) {
    return parseFloat(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para limpiar formulario
function limpiarFormularioImpuestoDescuento(tipo) {
    const conceptoElement = document.getElementById(`concepto_${tipo}`);
    const valorElement = document.getElementById(`valor_${tipo}`);

    if (conceptoElement) conceptoElement.value = '';
    if (valorElement) valorElement.value = '';
}

// Función para eliminar un impuesto/descuento especí­fico
async function eliminarImpuestoDescuento(id) {
    const initialLength = impuestosDescuentos.length;
    impuestosDescuentos = impuestosDescuentos.filter(item => item.id !== id);

    // Debug: verificar que se eliminó correctamente

    actualizarTablaImpuestosDescuentos();
    actualizarTotalesConImpuestosDescuentos();
    const cotizacionGuardadaId = document.getElementById('id')?.value;
    if (cotizacionGuardadaId) {
        await guardarConceptosCotizacion(cotizacionGuardadaId);
    }
    // Limpiar selección
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    toggleEliminarSeleccionados();

    toastr.info('Elemento eliminado');
}

// Función para eliminar seleccionados
async function eliminarSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_impuestos_descuentos .item-checkbox:checked');
    const idsEliminar = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-id')));

    if (idsEliminar.length === 0) {
        toastr.warning('No hay elementos seleccionados');
        return;
    }

    impuestosDescuentos = impuestosDescuentos.filter(item => !idsEliminar.includes(item.id));
    actualizarTablaImpuestosDescuentos();
    actualizarTotalesConImpuestosDescuentos();
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    toggleEliminarSeleccionados();
    const cotizacionGuardadaId = document.getElementById('id')?.value;
    if (cotizacionGuardadaId) {
        await guardarConceptosCotizacion(cotizacionGuardadaId);
    }
    toastr.info(`${idsEliminar.length} elemento(s) eliminado(s)`);
}

// Función para habilitar/deshabilitar botón de eliminar seleccionados
function toggleEliminarSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_impuestos_descuentos .item-checkbox:checked');
    const btnEliminar = document.getElementById('eliminar_seleccionados');
    if (btnEliminar) {
        btnEliminar.disabled = checkboxes.length === 0;
    }
}

// Función para limpiar todo
function limpiarTodoImpuestosDescuentos() {
    impuestosDescuentos = [];
    contadorRegistros = 0;
    actualizarTablaImpuestosDescuentos();
    actualizarTotalesConImpuestosDescuentos();
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    toggleEliminarSeleccionados();
    toastr.info('Todos los impuestos y descuentos han sido eliminados');
}

// Función para actualizar totales considerando impuestos y descuentos del accordion
function actualizarTotalesConImpuestosDescuentos() {
    const subtotal = parseFloat(document.getElementById('subtotal')?.value) || 0;

    let totalDescuentos = 0;
    let totalImpuestos = 0;

    impuestosDescuentos.forEach(item => {
        const valorCalculado = calcularValorImpuestoDescuento(item.valor, item.tipoCalculo, item.tipo);

        if (item.tipo === 'descuento') {
            totalDescuentos += valorCalculado;
        } else {
            totalImpuestos += valorCalculado;
        }
    });

    // Actualizar campos automáticamente
    const descuentoField = document.getElementById('descuento');
    const totalImpuestoField = document.getElementById('total_impuesto');

    if (descuentoField) descuentoField.value = totalDescuentos.toFixed(2);
    if (totalImpuestoField) totalImpuestoField.value = totalImpuestos.toFixed(2);

    // Usar nuestro sistema nuevo de totales
    actualizarTotalesCompletos();

    // Actualizar valores en la tabla
    actualizarTablaImpuestosDescuentos();
}

// Función para guardar conceptos en el backend
async function guardarConceptosCotizacion(cotizacionId) {
    try {
        const conceptosParaGuardar = impuestosDescuentos.map(item => ({
            concepto_id: item.concepto,
            porcentaje: item.tipoCalculo === 'porcentaje' ? item.valor : 0,
            valor: item.valorCalculado
        }));

        const response = await fetch('/admin/admin.cotizaciones.conceptos.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                conceptos: conceptosParaGuardar
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'No se pudieron guardar los conceptos');
        }

        return true;
    } catch (error) {
        console.error('Error al enviar conceptos:', error);
        toastr.error('Error al guardar los impuestos y descuentos: ' + error.message);
        return false;
    }
}

// Guardar impuestos/descuentos directamente en el backend desde el paso 1
async function guardarImpuestosDescuentosPaso() {
    const cotizacionId = document.getElementById('id')?.value;
    if (!cotizacionId) {
        toastr.error('Debe guardar la información básica de la cotización primero.');
        return;
    }

    const ok = await guardarConceptosCotizacion(cotizacionId);
    if (ok) {
        toastr.success('Impuestos y descuentos guardados en la cotización');
    }
}

// Función para cargar conceptos existentes en modo edición
async function cargarConceptosExistentes(cotizacionId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.conceptos.getCotizacionConceptos/${cotizacionId}`, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const data = await response.json();

        if (data.success && data.data.length > 0) {
            // Limpiar conceptos actuales
            impuestosDescuentos = [];
            contadorRegistros = 0;

            // Cargar conceptos existentes
            data.data.forEach(item => {
                contadorRegistros++;
                const concepto = conceptosDisponibles.find(c => c.id == item.concepto_id);
                if (concepto) {
                    impuestosDescuentos.push({
                        id: contadorRegistros,
                        tipo: concepto.tipo,
                        concepto: item.concepto_id,
                        tipoCalculo: item.porcentaje ? 'porcentaje' : 'valor',
                        valor: item.porcentaje || item.valor,
                        valorCalculado: item.valor
                    });
                }
            });

            actualizarTablaImpuestosDescuentos();
        }
    } catch (error) {
        console.error('Error al cargar conceptos existentes:', error);
    }
}

// ========================================
// FUNCIONES PARA OBSERVACIONES ADICIONALES
// ========================================

let observaciones = [];
let observacionesDisponibles = [];
let contadorObservaciones = 0;

/**
 * Cargar observaciones disponibles
 */
async function cargarObservacionesDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.observaciones.getObservaciones');
        const data = await response.json();

        if (data.success) {
            observacionesDisponibles = data.data;

            // Llenar el select
            const select = document.getElementById('observacionSelect');
            select.innerHTML = '<option value="">Seleccione una observación...</option>';

            observacionesDisponibles.forEach(obs => {
                const option = document.createElement('option');
                option.value = obs.id;
                option.textContent = obs.texto;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar observaciones disponibles:', error);
        toastr.error('Error al cargar las observaciones disponibles');
    }
}

/**
 * Agregar una nueva observación
 */
async function agregarObservacion() {
    const selectElement = document.getElementById('observacionSelect');
    const observacionId = selectElement.value;

    // Validaciones
    if (!observacionId) {
        toastr.error('Debe seleccionar una observación');
        selectElement.focus();
        return;
    }

    // Verificar si ya existe esa observación
    const observacionExistente = observaciones.find(obs => obs.observacion_id == observacionId);
    if (observacionExistente) {
        toastr.error('Esta observación ya ha sido agregada');
        selectElement.focus();
        return;
    }

    // Obtener el texto de la observación
    const observacionData = observacionesDisponibles.find(obs => obs.id == observacionId);
    if (!observacionData) {
        toastr.error('Observación no encontrada');
        return;
    }

    // Crear nueva observación
    contadorObservaciones++;
    const nuevaObservacion = {
        id: contadorObservaciones,
        observacion_id: parseInt(observacionId),
        texto: observacionData.texto
    };

    observaciones.push(nuevaObservacion);
    actualizarTablaObservaciones();
    const cotizacionGuardadaId = document.getElementById('id')?.value;
    await guardarObservacionesCotizacion(cotizacionGuardadaId);

    // Limpiar selector
    selectElement.value = '';
    selectElement.focus();

    toastr.success('Observación agregada correctamente');
}

/**
 * Eliminar una observación
 */
async function eliminarObservacion(id) {
    observaciones = observaciones.filter(obs => obs.id !== id);
    actualizarTablaObservaciones();
    const cotizacionGuardadaId = document.getElementById('id')?.value;
    await guardarObservacionesCotizacion(cotizacionGuardadaId);
    toastr.success('Observación eliminada correctamente');
}

/**
 * Actualizar la tabla de observaciones
 */
function actualizarTablaObservaciones() {
    const tbody = document.getElementById('observacionesTableBody');
    const tabla = document.getElementById('tablaObservaciones');
    const mensaje = document.getElementById('noObservacionesMessage');

    // Limpiar tabla
    tbody.innerHTML = '';

    if (observaciones.length === 0) {
        tabla.style.display = 'none';
        mensaje.style.display = 'block';
        return;
    }

    observaciones.forEach((observacion, index) => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${index + 1}</td>
            <td>${observacion.texto}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarObservacion(${observacion.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(fila);
    });

    tabla.style.display = 'table';
    mensaje.style.display = 'none';
}

/**
 * Cargar observaciones existentes de la cotización
 */
async function cargarObservacionesExistentes(cotizacionId) {
    if (!cotizacionId) return;

    try {
        const response = await fetch(`/admin/admin.cotizaciones.observaciones.getCotizacionObservaciones/${cotizacionId}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            observaciones = [];
            contadorObservaciones = 0;

            data.data.forEach(item => {
                contadorObservaciones++;
                observaciones.push({
                    id: contadorObservaciones,
                    observacion_id: item.id,
                    texto: item.texto
                });
            });

            actualizarTablaObservaciones();
        }

        if (variable === 'ver') {
            // Deshabilitar controles de observaciones en modo ver
            const botonesEliminar = document.querySelectorAll('#tablaObservaciones .btn-danger');
            botonesEliminar.forEach(btn => btn.disabled = true);
            document.getElementById('observacionSelect').disabled = true;
            document.getElementById('agregar_observacion').style.display = 'none';
        }else if (variable === 'editar') {
            // Habilitar controles de observaciones en modo editar
            const botonesEliminar = document.querySelectorAll('#tablaObservaciones .btn-danger');
            botonesEliminar.forEach(btn => btn.disabled = false);
            document.getElementById('observacionSelect').disabled = false;
            document.getElementById('agregar_observacion').style.display = 'inline-block';
        }else{
            // Habilitar controles de observaciones en modo crear
            const botonesEliminar = document.querySelectorAll('#tablaObservaciones .btn-danger');
            botonesEliminar.forEach(btn => btn.disabled = false);
            document.getElementById('observacionSelect').disabled = false;
            document.getElementById('agregar_observacion').style.display = 'inline-block';
        }

    } catch (error) {
        console.error('Error al cargar observaciones existentes:', error);
    }
}

/**
 * Guardar observaciones en el backend
 */
async function guardarObservacionesCotizacion(cotizacionId) {
    try {
        const observacionesParaGuardar = observaciones.map(item => ({
            observacion_id: item.observacion_id
        }));

        //     cotizacion_id: cotizacionId,
        //     observaciones: observacionesParaGuardar,
        //     cantidad: observacionesParaGuardar.length
        // });

        const response = await fetch('/admin/admin.cotizaciones.observaciones.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                observaciones: observacionesParaGuardar
            })
        });

        const data = await response.json();

        if (data.success) {
            if (observacionesParaGuardar.length === 0) {
            } else {
            }
        } else {
            console.error('Error al guardar observaciones:', data.message);
            toastr.warning('Cotización guardada, pero hubo problemas con las observaciones');
        }
    } catch (error) {
        console.error('Error al enviar observaciones:', error);
        toastr.warning('Cotización guardada, pero hubo problemas con las observaciones');
    }
}

// ========================================
// FUNCIONES PARA CONDICIONES COMERCIALES
// ========================================

let condicionesComerciales = {};

/**
 * Guardar condiciones comerciales
 */
async function guardarCondicionesComerciales() {
    try {
        const form = document.getElementById('formCondicionesComerciales');
        const formData = new FormData(form);

        // Crear objeto con los datos
        const condiciones = {
            tiempo_entrega: formData.get('tiempo_entrega')?.trim() || null,
            lugar_obra: formData.get('lugar_obra')?.trim() || null,
            duracion_oferta: formData.get('duracion_oferta')?.trim() || null,
            garantia: formData.get('garantia')?.trim() || null,
            forma_pago: formData.get('forma_pago')?.trim() || null
        };

        // Validar que al menos un campo tenga contenido
        const tieneContenido = Object.values(condiciones).some(valor => valor && valor.length > 0);

        if (!tieneContenido) {
            toastr.warning('Debe completar al menos un campo de las condiciones comerciales');
            return;
        }

        // Guardar en variable local
        condicionesComerciales = condiciones;

        const cotizacionId = document.getElementById('id')?.value;
        if (!cotizacionId) {
            toastr.error('Debe guardar la información básica de la cotización primero.');
            return;
        }

        // Guardar en backend inmediatamente
        const ok = await guardarCondicionesCotizacion(cotizacionId);

        // Mostrar resumen
        mostrarResumenCondiciones(condiciones);

        if (ok) {
            toastr.success('Condiciones comerciales guardadas en la cotización');
        }

    } catch (error) {
        console.error('Error al guardar condiciones comerciales:', error);
        toastr.error('Error al guardar las condiciones comerciales');
    }
}

/**
 * Guardar condiciones comerciales en el backend
 */
async function guardarCondicionesCotizacion(cotizacionId) {
    try {
        // Solo enviar si hay condiciones comerciales definidas
        if (!condicionesComerciales || Object.keys(condicionesComerciales).length === 0) {
            return;
        }

        //           cotizacion_id: cotizacionId,
        //    condiciones: condicionesComerciales
        //});

        const response = await fetch('/admin/admin.cotizaciones.condiciones.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                ...condicionesComerciales
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'No se pudieron guardar las condiciones comerciales');
        }

        return true;
    } catch (error) {
        console.error('Error al enviar condiciones comerciales:', error);
        toastr.error('Error al guardar las condiciones comerciales: ' + error.message);
        return false;
    }
}

/**
 * Cargar condiciones comerciales existentes
 */
async function cargarCondicionesExistentes(cotizacionId) {
    if (!cotizacionId) return;

    try {
        const response = await fetch(`/admin/admin.cotizaciones.condiciones.getCotizacionCondiciones/${cotizacionId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const condiciones = data.data;

            // Llenar el formulario
            document.getElementById('tiempo_entrega').value = condiciones.tiempo_entrega || '';
            document.getElementById('lugar_obra').value = condiciones.lugar_obra || '';
            document.getElementById('duracion_oferta').value = condiciones.duracion_oferta || '';
            document.getElementById('garantia').value = condiciones.garantia || '';
            document.getElementById('forma_pago').value = condiciones.forma_pago || '';

            // Guardar en variable local
            condicionesComerciales = {
                tiempo_entrega: condiciones.tiempo_entrega,
                lugar_obra: condiciones.lugar_obra,
                duracion_oferta: condiciones.duracion_oferta,
                garantia: condiciones.garantia,
                forma_pago: condiciones.forma_pago
            };

            // Mostrar resumen si hay datos
            const tieneContenido = Object.values(condicionesComerciales).some(valor => valor && valor.length > 0);
            if (tieneContenido) {
                mostrarResumenCondiciones(condicionesComerciales);
            }

            if (variable === 'ver') {
                // Deshabilitar formulario en modo ver
                document.getElementById('formCondicionesComerciales').querySelectorAll('input, textarea, select, button').forEach(elem => {
                    elem.disabled = true;
                });

                document.getElementById('btnGuardarCondiciones').style.display = 'none';

            } else if (variable === 'editar') {
                // Habilitar formulario en modo editar
                document.getElementById('formCondicionesComerciales').querySelectorAll('input, textarea, select, button').forEach(elem => {
                    elem.disabled = false;
                });
                document.getElementById('btnGuardarCondiciones').style.display = 'inline-block';
            } else {
                // Habilitar formulario en modo crear
                document.getElementById('formCondicionesComerciales').querySelectorAll('input, textarea, select, button').forEach(elem => {
                    elem.disabled = false;
                });
                document.getElementById('btnGuardarCondiciones').style.display = 'inline-block';
            }
        }
    } catch (error) {
        console.error('Error al cargar condiciones comerciales existentes:', error);
    }
}

/**
 * Mostrar resumen de condiciones comerciales
 */
function mostrarResumenCondiciones(condiciones) {
    const resumenDiv = document.getElementById('resumenCondiciones');
    if (!resumenDiv) return;

    let html = '<div class="alert alert-info"><h6><i class="fas fa-info-circle"></i> Resumen de Condiciones Comerciales</h6><ul class="mb-0">';

    if (condiciones.tiempo_entrega) {
        html += `<li><strong>Tiempo de Entrega:</strong> ${condiciones.tiempo_entrega}</li>`;
    }

    if (condiciones.lugar_obra) {
        html += `<li><strong>Lugar de Obra:</strong> ${condiciones.lugar_obra}</li>`;
    }

    if (condiciones.duracion_oferta) {
        html += `<li><strong>Duración de Oferta:</strong> ${condiciones.duracion_oferta}</li>`;
    }

    if (condiciones.garantia) {
        html += `<li><strong>Garantí­a:</strong> ${condiciones.garantia}</li>`;
    }

    if (condiciones.forma_pago) {
        html += `<li><strong>Forma de Pago:</strong> ${condiciones.forma_pago}</li>`;
    }

    html += '</ul></div>';

    resumenDiv.innerHTML = html;
    resumenDiv.style.display = 'block';
}

/**
 * Limpiar formulario de condiciones comerciales
 */
function limpiarCondicionesComerciales() {
    document.getElementById('formCondicionesComerciales').reset();
    condicionesComerciales = {};
    document.getElementById('resumenCondiciones').style.display = 'none';
    toastr.info('Formulario de condiciones comerciales limpio');
}

// ========================================
// FUNCIONES PARA ITEMS DE COTIZACIí“N
// ========================================

let itemsCotizacion = [];
let subitemsDisponibles = [];
let unidadesMedida = [];
let contadorItems = 0;

/**
 * Inicializar funcionalidad de items
 */
async function initItems() {
    try {
        // Cargar datos necesarios
        await Promise.all([
            cargarSubitemsDisponibles(),
            cargarUnidadesMedida()
        ]);

        // Configurar event listeners
        setupItemsEventListeners();

        // Si estamos en modo edición, cargar items existentes
        if ((variable === 'editar' || variable === 'ver') && cotizacion?.id) {
            // await cargarItemsExistentes(cotizacion.id);
        }

        // Configurar permisos según el modo
        configurarPermisosItems();

    } catch (error) {
        console.error('Error al inicializar items:', error);
        toastr.error('Error al cargar los datos de items');
    }
}

/**
 * Cargar subitems disponibles
 */
async function cargarSubitemsDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.items.getSubitems');
        const data = await response.json();

        if (data.success) {
            subitemsDisponibles = data.data;
            poblarSelectSubitems();
        }
    } catch (error) {
        console.error('Error al cargar subitems:', error);
    }
}

/**
 * Cargar unidades de medida
 */
async function cargarUnidadesMedida() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.items.getUnidadesMedida');
        const data = await response.json();

        if (data.success) {
            unidadesMedida = data.data;
            poblarSelectUnidadesMedida();
        }
    } catch (error) {
        console.error('Error al cargar unidades de medida:', error);
    }
}

/**
 * Poblar select de subitems
 */
function poblarSelectSubitems() {
    const select = document.getElementById('item_subitem');
    if (!select) return;

    select.innerHTML = '<option value="">Seleccione item...</option>';

    subitemsDisponibles.forEach(subitem => {
        const option = document.createElement('option');
        option.value = subitem.id;
        option.textContent = `${subitem.codigo} - ${subitem.nombre}`;
        option.setAttribute('data-unidad', subitem.unidad_medida?.simbolo || '');
        option.setAttribute('data-cantidad', subitem.cantidad || 1);
        select.appendChild(option);
    });
}

/**
 * Poblar select de unidades de medida
 */
function poblarSelectUnidadesMedida() {
    const select = document.getElementById('subitem_unidad_medida');
    if (!select) return;

    select.innerHTML = '<option value="">Seleccione unidad...</option>';

    unidadesMedida.forEach(unidad => {
        const option = document.createElement('option');
        option.value = unidad.id;
        option.textContent = `${unidad.nombre} (${unidad.sigla})`;
        select.appendChild(option);
    });
}

/**
 * Configurar event listeners para items
 */
function setupItemsEventListeners() {
    // Cálculo automático del valor total
    const cantidadInput = document.getElementById('item_cantidad');
    const valorUnitarioInput = document.getElementById('item_valor_unitario');

    if (cantidadInput && valorUnitarioInput) {
        cantidadInput.addEventListener('input', calcularValorTotalItem);
        valorUnitarioInput.addEventListener('input', calcularValorTotalItem);
    }

    // Botones principales
    const btnAgregarItem = document.getElementById('btn_agregar_item');
    if (btnAgregarItem) {
        btnAgregarItem.addEventListener('click', agregarItem);
    }

    const btnLimpiarItem = document.getElementById('btn_limpiar_item');
    if (btnLimpiarItem) {
        btnLimpiarItem.addEventListener('click', limpiarFormularioItem);
    }

    // Botones de gestión de tabla
    const btnEliminarSeleccionados = document.getElementById('btn_eliminar_items_seleccionados');
    if (btnEliminarSeleccionados) {
        btnEliminarSeleccionados.addEventListener('click', eliminarItemsSeleccionados);
    }

    const btnLimpiarTodos = document.getElementById('btn_limpiar_todos_items');
    if (btnLimpiarTodos) {
        btnLimpiarTodos.addEventListener('click', function() {
            if (confirm('Â¿Está seguro de eliminar todos los items?')) {
                limpiarTodosItems();
            }
        });
    }

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select_all_items');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#tbody_items .item-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleEliminarItemsSeleccionados();
        });
    }

    // Modal para crear subitem
    const btnCrearSubitem = document.getElementById('btn_crear_subitem');
    if (btnCrearSubitem) {
        btnCrearSubitem.addEventListener('click', abrirModalCrearSubitem);
    }

    // // Formulario de crear subitem
    // const formCrearSubitem = document.getElementById('formCrearSubitem');
    // if (formCrearSubitem) {
    //     formCrearSubitem.addEventListener('submit', guardarSubitem);
    // }

    // Contador de caracteres para observación de subitem
    const observacionTextarea = document.getElementById('subitem_observacion');
    const contadorSpan = document.getElementById('subitem_observacion_count');
    if (observacionTextarea && contadorSpan) {
        observacionTextarea.addEventListener('input', function() {
            contadorSpan.textContent = this.value.length;
        });
    }
}

/**
 * Calcular valor total del item
 */
function calcularValorTotalItem() {
    const cantidad = parseFloat(document.getElementById('item_cantidad')?.value) || 0;
    const valorUnitario = parseFloat(document.getElementById('item_valor_unitario')?.value) || 0;
    const valorTotal = cantidad * valorUnitario;

    const valorTotalInput = document.getElementById('item_valor_total');
    if (valorTotalInput) {
        valorTotalInput.value = valorTotal.toFixed(2);
    }
}

/**
 * Agregar nuevo item
 */
async function agregarItem() {
    try {
        // Validar formulario
        if (!validarFormularioItem()) {
            return;
        }

        const cotizacionId = document.getElementById('id')?.value;
        // Verificar si ya tenemos una cotización guardada
        if (!cotizacionId) {
            toastr.error('Debe guardar la cotización primero antes de agregar items');
            return;
        }

        // Obtener datos del formulario
        const nombre = document.getElementById('item_nombre').value.trim();

        // Crear objeto para enviar al backend
        const itemData = {
            cotizacion_id: cotizacionId,
            nombre: nombre,
            active: true
        };

        // Enviar al backend inmediatamente
        const response = await fetch('/admin/admin.cotizaciones.items.createItem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(itemData)
        });

        const result = await response.json();

        if (result.success) {
            // Agregar el item con ID del backend a la lista local
            const nuevoItem = {
                id: result.data.id,
                nombre: result.data.nombre,
                active: result.data.active,
                subitems: result.data.subitems || []
            };

            itemsCotizacion.push(nuevoItem);

            // Actualizar tabla
            actualizarTablaItems();

            // Recalcular totales
            calcularTotales();

            // Limpiar formulario
            limpiarFormularioItem();

            toastr.success('Item creado correctamente');
        } else {
            throw new Error(result.message || 'Error al crear item');
        }

    } catch (error) {
        console.error('Error al agregar item:', error);
        toastr.error('Error al crear el item: ' + error.message);
    }
}

/**
 * Validar formulario de item
 */
function validarFormularioItem() {
    let esValido = true;
    const errores = {};

    // Validar nombre
    const nombre = document.getElementById('item_nombre').value.trim();
    if (!nombre) {
        errores.item_nombre = 'El nombre del item es obligatorio';
        esValido = false;
    } else if (nombre.length > 255) {
        errores.item_nombre = 'El nombre no puede exceder 255 caracteres';
        esValido = false;
    }

    // Mostrar errores
    if (!esValido) {
        mostrarErroresItems(errores);
    } else {
        limpiarErroresItems();
    }
    return esValido;
}

/**
 * Obtener subitem por ID
 */
function obtenerSubitemPorId(subitemId) {
    return subitemsDisponibles.find(s => s.id == subitemId) || null;
}

/**
 * Actualizar tabla de items
 */
function actualizarTablaItems() {
    const tbody = document.getElementById('tbody_items');
    let noItemsRow = document.getElementById('no_items_row_items');

    if (!tbody) return;

    // Limpiar tabla excepto la fila "no items"
    const existingRows = tbody.querySelectorAll('tr:not(#no_items_row_items)');
    existingRows.forEach(row => row.remove());

    if (itemsCotizacion.length == 0) {
        // Crear o mostrar la fila de "no items"
        if (!noItemsRow) {
            noItemsRow = document.createElement('tr');
            noItemsRow.id = 'no_items_row_items';
            noItemsRow.innerHTML = `
                <td colspan="6" class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No hay items agregados
                </td>
            `;
            tbody.appendChild(noItemsRow);
        }
        noItemsRow.style.display = 'table-row';
        return;
    }

    if (noItemsRow) {
        noItemsRow.style.display = 'none';
    }


    itemsCotizacion.forEach((item, index) => {
        const row = document.createElement('tr');
        // Generar HTML para lista de subitems

        const subitemsHtml = generarListaSubitems(item.id || index, item.subitems || []);
        row.innerHTML = `
            <td class="text-center">
                <input type="checkbox" class="item-checkbox" data-id="${item.id || index}" onchange="toggleEliminarItemsSeleccionados()">
            </td>
            <td class="text-center">${(index + 1).toString().padStart(3, '0')}</td>
            <td>${item.nombre}</td>
            <td>
                ${subitemsHtml}
            </td>
            <td class="text-center"><span class="badge ${item.active ? 'bg-success' : 'bg-secondary'}">${item.active ? 'Activo' : 'Inactivo'}</span></td>
            <td class="text-center">
                    ${item.id ? `
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editarItem(${item.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="abrirModalCrearSubitem(${item.id})" title="Crear item">
                        <i class="fas fa-list"></i>
                    </button>` : ''}
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarItem(${item.id || index})" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
            </td>
        `;

        // Agregar al final del tbody
        tbody.appendChild(row);
    });

    // Solo cargar subitems para items que no los tengan ya cargados
    itemsCotizacion.forEach(item => {
        if (item.id && (!item.subitems || item.subitems.length === 0)) {
            cargarSubitemsDelItem(item.id);
        }
    });

    // Actualizar tambií©n la tabla del modal
    actualizarTablaItemsAcordeon();
}

/**
 * Actualizar tabla de items en el modal de productos
 */
function actualizarTablaItemsAcordeon() {
    const tbody = document.getElementById('tbodyItemsAcordeon');
    if (!tbody) return; // Si no existe el modal, no hacer nada

    // Limpiar la tabla
    tbody.innerHTML = '';

    if (itemsCotizacion.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay items agregados en el acordeón
                </td>
            </tr>
        `;
        return;
    }
    // Agregar filas para cada item y sus subitems
    itemsCotizacion.forEach((item, itemIndex) => {
        // Fila del item principal (NO seleccionable)
        const itemRow = document.createElement('tr');
        itemRow.classList.add('table-light');
        itemRow.innerHTML = `
            <td class="text-center">
                <i class="fas fa-folder text-secondary" title="Items principales no seleccionables"></i>
            </td>
            <td><strong><i class="fas fa-folder mr-1 text-secondary"></i>${item.nombre}</strong></td>
            <td class="text-muted"><em>Capitulación - No seleccionable</em></td>
            <td><span class="badge bg-secondary">Capitulación</span></td>
        `;
        tbody.appendChild(itemRow);

        // Agregar filas para cada subitem (Sí seleccionables)
        if (item.subitems && item.subitems.length > 0) {
            item.subitems.forEach((subitem, subitemIndex) => {
                const subitemRow = document.createElement('tr');
                subitemRow.innerHTML = `
                    <td class="text-center">
                        <input type="radio" name="itemSelected" class="item-select" data-type="subitem" data-item-id="${item.id || itemIndex}" data-id="${subitem.id}" data-index="${itemIndex}-${subitemIndex}" onchange="seleccionarUnicoItem(this)">
                    </td>
                    <td class="ps-4"><i class="fas fa-cubes mr-1 text-info"></i>${subitem.codigo || subitem.nombre || 'SUB-' + (subitemIndex + 1).toString().padStart(3, '0')}</td>
                    <td>${subitem.descripcion || subitem.observacion || subitem.nombre || 'Sin descripción'}</td>
                    <td>
                        <span class="badge bg-info">Subitem</span>
                        ${subitem.cantidad ? `<span class="badge bg-secondary ml-1">${subitem.cantidad}${subitem.unidadMedida && subitem.unidadMedida.sigla ? ` ${subitem.unidadMedida.sigla}` : ''}</span>` : ''}
                    </td>
                `;
                tbody.appendChild(subitemRow);
            });
        } else {
            // Mostrar mensaje si el item no tiene subitems
            const noSubitemsRow = document.createElement('tr');
            noSubitemsRow.innerHTML = `
                <td class="text-center">
                    <i class="fas fa-info-circle text-muted"></i>
                </td>
                <td class="ps-4 text-muted"><em>Sin items disponibles</em></td>
                <td class="text-muted">-</td>
                <td><span class="badge bg-light text-dark">Sin items</span></td>
            `;
            tbody.appendChild(noSubitemsRow);
        }
    });

    // Aplicar filtro si hay texto de búsqueda
    const searchInput = document.getElementById('buscarItemsAcordeon');
    if (searchInput && searchInput.value.trim() !== '') {
        filtrarItemsAcordeon();
    }
}


/**
 * Manejar selección única de subitem
 */
function seleccionarUnicoItem(radioButton) {
    // Limpiar selección previa en la tabla de productos seleccionados
    // (No limpiamos automáticamente para preservar items ya agregados)

    // Actualizar interfaz visual
    const tipo = radioButton.dataset.type;
    const nombre = radioButton.closest('tr').querySelector('td:nth-child(2)').textContent.trim();

    // Solo procesar subitems
    if (tipo === 'subitem') {

        // Destacar visualmente la fila seleccionada
        document.querySelectorAll('input[name="itemSelected"]').forEach(radio => {
            const row = radio.closest('tr');
            if (radio.checked) {
                row.style.backgroundColor = 'rgba(13, 202, 240, 0.1)';
                row.classList.add('table-info');
            } else {
                row.style.backgroundColor = '';
                row.classList.remove('table-info');
            }
        });
    }
}

/**
 * Obtener item seleccionado del acordeón
 */
function obtenerItemSeleccionado() {
    const radioSeleccionado = document.querySelector('input[name="itemSelected"]:checked');

    if (!radioSeleccionado) {
        return null;
    }

    const tipo = radioSeleccionado.dataset.type;
    const id = radioSeleccionado.dataset.id;
    const index = radioSeleccionado.dataset.index;

    if (tipo === 'item') {
        const itemIndex = parseInt(index);
        const item = itemsCotizacion[itemIndex];
        if (item) {
            return {
                tipo: 'item',
                id: item.id,
                nombre: item.nombre,
                descripcion: item.descripcion || item.nombre,
                data: item
            };
        }
    } else if (tipo === 'subitem') {
        const [itemIndex, subitemIndex] = index.split('-').map(i => parseInt(i));
        const item = itemsCotizacion[itemIndex];
        if (item && item.subitems && item.subitems[subitemIndex]) {
            const subitem = item.subitems[subitemIndex];
            return {
                tipo: 'subitem',
                id: subitem.id,
                itemId: item.id,
                itemNombre: item.nombre,
                nombre: subitem.codigo || subitem.descripcion || subitem.nombre,
                descripcion: subitem.descripcion || subitem.nombre,
                data: subitem
            };
        }
    }

    return null;
}

/**
 * Usar item seleccionado del acordeón como producto
 */
function usarItemSeleccionado() {

    // Buscar item seleccionado en el acordeón
    const itemSeleccionado = document.querySelector('input[name="itemSelected"]:checked');

    if (!itemSeleccionado) {
        Swal.fire({
            type: 'warning',
            title: 'Sin selección',
            text: 'Por favor seleccione un item o subitem del acordeón para agregarlo como producto.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const tipo = itemSeleccionado.dataset.type;
    const itemId= itemSeleccionado.dataset.item-id;
    const subitemId= itemSeleccionado.dataset.id;
    const indexData = itemSeleccionado.dataset.index;


    // Guardar temporalmente el item seleccionado
    if (tipo === 'item') {
        const itemIndex = parseInt(indexData);
        window.subitemTemporal = {
            item_id:itemId,
            subitem_id: subitemId,
            tipo: 'item',
            item: itemsCotizacion[itemIndex],
            index: itemIndex
        };
    } else if (tipo === 'subitem') {
        const [itemIndex, subitemIndex] = indexData.split('-').map(i => parseInt(i));
        const item = itemsCotizacion[itemIndex];
        const subitem = item.subitems[subitemIndex];

        window.subitemTemporal = {
            item_id:itemId,
            subitem_id: subitemId,
            tipo: 'subitem',
            item: item,
            subitem: subitem,
            itemIndex: itemIndex,
            subitemIndex: subitemIndex
        };
    }

    // Abrir modal de categorí­as
    abrirModalSeleccionCategorias();
}

/**
 * Abrir modal para selección de categorí­as
 */
function abrirModalSeleccionCategorias() {
    // Crear modal dinámico para seleccionar categorí­as
    const modalHtml = `
        <div class="modal fade" id="modalSeleccionCategorias" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-tags mr-2"></i>Seleccionar una o más categorí­as para cargar los productos.
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Subitem seleccionado:</strong> ${window.subitemTemporal?.subitem?.codigo || window.subitemTemporal?.subitem?.nombre}
                            <br><small>Seleccione una o más categorí­as para cargar los items propios correspondientes.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Categorí­as disponibles:</strong></label>
                            <div id="categoriasContainer" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                <div class="text-center p-3">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Cargando categorí­as...
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleTodasCategorias()">
                                <i class="fas fa-check-double mr-1"></i>Seleccionar todas
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="cargarItemsPorCategorias()" disabled id="btnCargarItems">
                            <i class="fas fa-arrow-right mr-1"></i>Cargar Items Propios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalSeleccionCategorias');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Verificar que el modal se creó correctamente
    const nuevoModal = document.getElementById('modalSeleccionCategorias');
    if (!nuevoModal) {
        console.error('No se pudo crear el modal de selección de categorí­as');
        return;
    }

    // Mostrar modal con timeout para asegurar que se renderice
    setTimeout(() => {
        try {
            $('#modalSeleccionCategorias').modal('show');
        } catch (error) {
            console.error('Error al mostrar modal:', error);
        }
    }, 100);

    // Cargar categorí­as disponibles
    cargarCategoriasPorSeleccionar();
}

/**
 * Cargar categorí­as disponibles para selección
 */
async function cargarCategoriasPorSeleccionar() {
    try {
        const container = document.getElementById('categoriasContainer');

        if (!container) {
            console.error('No se encontró el contenedor de categorí­as');
            return;
        }

        // Usar categorí­as desde variable global o cargar desde backend
        let categorias = [];

        // Intentar obtener de variable global primero
        if (window.categoriasDisponibles && window.categoriasDisponibles.length > 0) {
            categorias = window.categoriasDisponibles;
        } else {
            // Cargar desde backend
            const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
            const response = await fetch(`${baseUrl}/admin/admin.cotizaciones.categorias.obtener`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data) {
                    categorias = result.data;
                }
            }
        }

        // Si no hay categorí­as, usar datos simulados
        if (categorias.length === 0) {
            categorias = [
                { id: 1, nombre: 'Materiales de Construcción' },
                { id: 2, nombre: 'Herramientas' },
                { id: 3, nombre: 'Equipos' },
                { id: 4, nombre: 'Servicios' },
                { id: 5, nombre: 'Mano de Obra' }
            ];
        }

        // Generar HTML de categorí­as
        let categoriasHtml = '';
        categorias.forEach(categoria => {
            const esNomina = categoria.tipo === 'nomina';
            categoriasHtml += `
                <div class="form-check mb-2">
                    <input class="form-check-input categoria-checkbox" type="checkbox" value="${categoria.id}" id="categoria_${categoria.id}"
                           data-tipo="${categoria.tipo || 'estandar'}" onchange="validarSeleccionCategorias()">
                    <label class="form-check-label" for="categoria_${categoria.id}">
                        <strong>${categoria.nombre}</strong>
                        ${esNomina ? '<span class="badge badge-warning ml-2"><i class="fas fa-users mr-1"></i>Nómina</span>' : ''}
                        <small class="text-muted d-block">Tipo: ${esNomina ? 'Cargo/Personal' : 'Productos/Insumos'}</small>
                    </label>
                </div>
            `;
        });

        container.innerHTML = categoriasHtml;

        // Guardar categorí­as en variable temporal
        window.categoriasTemporal = categorias;

    } catch (error) {
        console.error('Error al cargar categorí­as:', error);
        document.getElementById('categoriasContainer').innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Error al cargar categorí­as. Se usarán categorí­as por defecto.
            </div>
        `;
    }
}

/**
 * Validar selección de categorí­as y habilitar botón
 */
function validarSeleccionCategorias() {
    const todas = document.querySelectorAll('.categoria-checkbox');
    const seleccionadas = document.querySelectorAll('.categoria-checkbox:checked');
    const btnCargarItems = document.getElementById('btnCargarItems');

    // Determinar si alguna seleccionada es nómina y si alguna seleccionada es estándar
    let nominaSeleccionada = false;
    let estandarSeleccionada = false;
    seleccionadas.forEach(cb => {
        if (cb.dataset.tipo === 'nomina') nominaSeleccionada = true;
        else estandarSeleccionada = true;
    });

    // Aplicar bloqueo bidireccional
    todas.forEach(cb => {
        const esNomina = cb.dataset.tipo === 'nomina';
        let disabled = false;
        if (nominaSeleccionada && !esNomina) disabled = true;   // nómina seleccionada → bloquear estándar
        if (estandarSeleccionada && esNomina) disabled = true;  // estándar seleccionada → bloquear nómina
        cb.disabled = disabled;
        const wrapper = cb.closest('.form-check');
        if (wrapper) wrapper.style.opacity = disabled ? '0.4' : '1';
    });

    if (seleccionadas.length > 0) {
        btnCargarItems.disabled = false;
        btnCargarItems.innerHTML = `<i class="fas fa-arrow-right mr-1"></i>Cargar Items Propios (${seleccionadas.length} categorí­a${seleccionadas.length !== 1 ? 's' : ''})`;
    } else {
        btnCargarItems.disabled = true;
        btnCargarItems.innerHTML = '<i class="fas fa-arrow-right mr-1"></i>Cargar Items Propios';
    }
}

/**
 * Toggle todas las categorías (excluye nómina si hay estándar seleccionada y viceversa)
 */
function toggleTodasCategorias() {
    const checkboxes = document.querySelectorAll('.categoria-checkbox:not(:disabled)');
    const todasSeleccionadas = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !todasSeleccionadas;
    });

    validarSeleccionCategorias();
}

/**
 * Cargar items propios basándose en categorí­as seleccionadas
 */
async function cargarItemsPorCategorias() {
    const categoriasSeleccionadas = document.querySelectorAll('.categoria-checkbox:checked');

    if (categoriasSeleccionadas.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin selección',
            text: 'Debe seleccionar al menos una categorí­a.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Mostrar loading en el botón
    const btnCargarItems = document.getElementById('btnCargarItems');
    const textoOriginal = btnCargarItems.innerHTML;
    btnCargarItems.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Cargando...';
    btnCargarItems.disabled = true;

    try {
        // Obtener IDs de categorí­as seleccionadas
        const categoriaIds = Array.from(categoriasSeleccionadas).map(cb => parseInt(cb.value));


        // Preparar datos para la petición
        const requestData = {
            categoria_ids: categoriaIds
        };

        // Obtener la URL base
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.items-categoria.obtener`;

        // Hacer petición al backend
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        let itemsPropios = [];

        if (result.success && result.data && result.data.length > 0) {
            // Procesar y validar los datos del backend
            itemsPropios = result.data.map(item => {
                // Asegurar que todos los campos son del tipo correcto
                const tipoItem = String(item.tipo || 'item_propio');
                const esCargoParam = tipoItem === 'parametrizacion' || tipoItem === 'cargo_tabla';

                return {
                    id: String(item.id || ''),
                    nombre: String(item.nombre || 'Sin nombre'),
                    codigo: String(item.codigo || 'Sin código'),
                    descripcion: String(item.descripcion || 'Sin descripción'),
                    categoria_id: item.categoria_id || null,
                    categoria: {
                        id: item.categoria?.id || item.categoria_id || null,
                        nombre: String(item.categoria?.nombre || 'Sin categoría')
                    },
                    unidad_medida: String(item.unidad_medida || ''),
                    dias_laborales: Number(item.dias_laborales || 0),
                    precio: item.precio || 0,
                    tipo: tipoItem,
                    // Datos específicos de parametrización / cargos tabla
                    ...(esCargoParam && {
                        cargo_id: item.cargo_id || item.cargo?.id || null,
                        cargo: item.cargo ? {
                            id: item.cargo.id || item.cargo_id || null,
                            nombre: String(item.cargo.nombre || 'Sin cargo')
                        } : null,
                    }),
                    ...(tipoItem === 'parametrizacion' && {
                        valor_porcentaje: Number(item.valor_porcentaje || 0),
                        valor_admon: Number(item.valor_admon || 0),
                        valor_obra: Number(item.valor_obra || 0)
                    }),
                    ...(tipoItem === 'cargo_tabla' && {
                        tabla_precios_id: item.tabla_id || null,
                        costo_hora: Number(item.costo_hora || 0),
                        costo_dia: Number(item.costo_dia || 0),
                        base_costo_hora: Number(item.base_costo_hora || 0),
                        base_costo_dia: Number(item.base_costo_dia || 0)
                    })
                };
            });

            // Guardar items en variable global para uso posterior
            window.itemsPropiosDisponibles = itemsPropios;
        } else {
            // Datos simulados como fallback
            // itemsPropios = generarItemsPropiosSimulados(categoriaIds);
            itemsPropios = [];
        }

        // Separar items por tipo: nómina (cargo_tabla) vs estándar
        const itemsNomina = itemsPropios.filter(i => i.tipo === 'cargo_tabla');
        const itemsEstandar = itemsPropios.filter(i => i.tipo !== 'cargo_tabla');

        // Cerrar modal de categorí­as y enrutar al flujo correcto
        $('#modalSeleccionCategorias').modal('hide');

        // Dar tiempo a que se cierre el modal anterior
        setTimeout(() => {
            if (itemsNomina.length > 0 && itemsEstandar.length === 0) {
                // Solo nómina → flujo dedicado de Cargo/Perfil
                abrirModalNominaConfig(itemsNomina);
            } else if (itemsNomina.length === 0) {
                // Solo estándar → flujo existente
                abrirModalSeleccionItemsPropios(itemsPropios, categoriaIds);
            } else {
                // Mixto: estándar primero, nómina después
                window.itemsNominaPendientes = itemsNomina;
                abrirModalSeleccionItemsPropios(itemsEstandar, categoriaIds);
            }
        }, 500);

    } catch (error) {
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: `No se pudieron cargar los items propios: ${error.message}`,
            confirmButtonText: 'Entendido'
        });
    } finally {
        // Restaurar botón
        btnCargarItems.innerHTML = textoOriginal;
        btnCargarItems.disabled = false;
    }
}

/**
 * Generar items propios simulados para categorí­as seleccionadas
 */
function generarItemsPropiosSimulados(categoriaIds) {
    const itemsSimulados = [];

    // Más variedad de nombres por categorí­a
    const itemsPorCategoria = {
        1: ['Cemento Portland', 'Arena de Rí­o', 'Grava Triturada', 'Ladrillos Cerámicos', 'Blocks de Concreto', 'Cal Hidratada', 'Yeso Fresco'],
        2: ['Varillas de Acero', 'Alambre Galvanizado', 'Clavos 2.5"', 'Tornillos Autoroscantes', 'Tuercas y Arandelas', 'Pernos de Anclaje'],
        3: ['Taladro Percutor', 'Sierra Circular', 'Amoladora Angular', 'Martillo Demoledor', 'Pistola de Calor', 'Compresor de Aire'],
        4: ['Soldadura Profesional', 'Consultorí­a Tí©cnica', 'Supervisión de Obra', 'Control de Calidad', 'Diseí±o Estructural'],
        5: ['Maestro de Obra', 'Ayudante General', 'Operario Especializado', 'Tí©cnico Electricista', 'Plomero Industrial', 'Soldador Certificado']
    };

    categoriaIds.forEach(categoriaId => {
        const categoria = window.categoriasTemporal?.find(c => c.id === categoriaId);
        const nombreCategoria = categoria?.nombre || `Categorí­a ${categoriaId}`;

        // Obtener items especí­ficos para la categorí­a o usar gení©ricos
        const itemsDisponibles = itemsPorCategoria[categoriaId] ||
            ['Item Gení©rico A', 'Item Gení©rico B', 'Item Gení©rico C', 'Item Gení©rico D', 'Item Gení©rico E'];

        // Generar 5-8 items por categorí­a (más cantidad)
        const cantidadItems = Math.floor(Math.random() * 4) + 5;

        for (let i = 1; i <= cantidadItems; i++) {
            const nombreItem = itemsDisponibles[(i-1) % itemsDisponibles.length];
            const variante = i > itemsDisponibles.length ? ` V${Math.ceil(i/itemsDisponibles.length)}` : '';

            itemsSimulados.push({
                id: `sim_${categoriaId}_${i}`,
                nombre: `${nombreItem}${variante}`,
                codigo: `${nombreCategoria.substring(0, 3).toUpperCase()}-${categoriaId}${i.toString().padStart(3, '0')}`,
                descripcion: `Item propio especializado de la categorí­a ${nombreCategoria}. Calidad profesional.`,
                categoria: { id: categoriaId, nombre: nombreCategoria },
                unidad_medida: ['Kg', 'MÂ³', 'Unidad', 'MÂ²', 'Litros', 'Metros', 'Piezas'][Math.floor(Math.random() * 7)],
                precio: Math.floor(Math.random() * 15000) + 2000
            });
        }
    });

    return itemsSimulados;
}

/**
 * Abrir modal para selección de items propios
 */
function abrirModalSeleccionItemsPropios(itemsPropios, categoriaIds) {

    const categoriasTexto = categoriaIds.map(id => {
        const cat = window.categoriasTemporal?.find(c => c.id === id);
        return cat?.nombre || `Categorí­a ${id}`;
    }).join(', ');

    const modalHtml = `
        <div class="modal fade" id="modalSeleccionItemsPropios" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cubes mr-2"></i>Seleccionar Items Propios
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Subitem:</strong> ${window.subitemTemporal?.subitem?.codigo || window.subitemTemporal?.subitem?.nombre}
                            <br><strong>Categorí­as:</strong> ${categoriasTexto}
                            <br><small>Seleccione los items propios que desea asociar al subitem.</small>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="buscarItemsPropios"
                                           placeholder="Buscar items propios por nombre, código o descripción..."
                                           onkeyup="filtrarItemsPropios()"
                                           onpaste="setTimeout(filtrarItemsPropios, 10)">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusquedaItemsPropios()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleTodosItemsPropios()">
                                    <i class="fas fa-check-double mr-1"></i>Seleccionar todos
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>Items propios disponibles (<span id="totalItemsPropios">${itemsPropios.length}</span>) -
                            Mostrando: <span id="itemsVisibles">${itemsPropios.length}</span></strong>
                        </div>

                        <div id="itemsPropiosContainer" class="border rounded" style="max-height: 400px; overflow-y: auto;">
                            ${generarHtmlItemsPropios(itemsPropios)}
                        </div>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <span id="contadorSeleccionados">0</span> items propios seleccionados
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="finalizarSeleccionItemsPropios()" id="btnFinalizarSeleccion">
                            <i class="fas fa-check mr-1"></i>Agregar Selección
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalSeleccionItemsPropios');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Guardar items propios temporalmente
    window.itemsPropiosTemporal = itemsPropios;

    // Mostrar modal
    $('#modalSeleccionItemsPropios').modal('show');

    // Actualizar contador inicial
    actualizarContadorSeleccionados();
}

/**
 * Generar HTML para mostrar items propios
 */
function generarHtmlItemsPropios(itemsPropios) {
    if (itemsPropios.length === 0) {
        return `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h5>No hay items propios disponibles</h5>
                <p>Las categorí­as seleccionadas no contienen items propios.</p>
            </div>
        `;
    }

    let html = '<div class="p-3">';

    // Agrupar por categorí­a
    const itemsPorCategoria = {};
    itemsPropios.forEach(item => {
        const categoriaId = item.categoria?.id || 'sin_categoria';
        const categoriaNombre = item.categoria?.nombre || 'Sin categorí­a';

        if (!itemsPorCategoria[categoriaId]) {
            itemsPorCategoria[categoriaId] = {
                nombre: categoriaNombre,
                items: []
            };
        }
        itemsPorCategoria[categoriaId].items.push(item);
    });

    // Generar HTML por categorí­a
    Object.entries(itemsPorCategoria).forEach(([categoriaId, data]) => {

        html += `
            <div class="mb-4">
                <h6 class="text-primary border-bottom pb-2">
                    <i class="fas fa-tag mr-2"></i>${data.nombre}
                </h6>
                <div class="row">
        `;

        data.items.forEach(item => {
            // Determinar el tipo de item y su información específica
            const esCargoTabla = item.tipo === 'cargo_tabla';
            const esParametrizacion = item.tipo === 'parametrizacion' || esCargoTabla;
            const icono = esParametrizacion ? 'fas fa-user-tie' : 'fas fa-cube';
            const tipoClass = esParametrizacion ? 'border-warning' : 'border-primary';

            // Asegurar que todos los valores son strings
            const itemNombre = String(item.nombre || 'Sin nombre');
            const itemCodigo = String(item.codigo || 'No definido');
            const itemId = String(item.id || '');
            const itemTipo = String(item.tipo || 'item_propio');
            const unidadMedida = item.unidad_medida ? String(item.unidad_medida) : '';
            const precio = item.precio ? String(item.precio) : '';

            // Descripción mejorada para parametrización con información del cargo
            let descripcion = String(item.descripcion || 'Sin descripción');
            if (item.tipo === 'parametrizacion' && item.cargo && item.cargo.nombre) {
                const cargoNombre = String(item.cargo.nombre);
                const valorPorcentaje = Number(item.valor_porcentaje || 0);
                const valorAdmon = Number(item.valor_admon || 0);
                const valorObra = Number(item.valor_obra || 0);

                descripcion = `👤 ${cargoNombre} | ${valorPorcentaje}% | Admin: $${valorAdmon.toLocaleString()} | Obra: $${valorObra.toLocaleString()}`;
            }

            if (esCargoTabla) {
                const cargoNombre = item.cargo?.nombre || item.nombre;
                const costoHora = Number(item.costo_hora || 0);
                const costoDia = Number(item.costo_dia || 0);
                descripcion = `👤 ${cargoNombre} | Hora: $${costoHora.toLocaleString()} | Día: $${costoDia.toLocaleString()}`;
            }

            // Debug logging
            if (typeof descripcion !== 'string' || descripcion.includes('[object Object]')) {
                console.error('Problema con descripción:', {
                    item: item,
                    descripcion: descripcion,
                    tipo: typeof descripcion,
                    esParametrizacion: esParametrizacion,
                    cargo: item.cargo
                });
                descripcion = 'Error en descripción';
            }

            html += `
                <div class="col-md-6 mb-3">
                    <div class="card ${tipoClass} item-propio-card" data-item-id="${itemId}">
                        <div class="card-body p-3">
                            <div class="form-check">
                                <input class="form-check-input item-propio-checkbox"
                                       type="checkbox"
                                       value="${itemId}"
                                       id="item_${itemId}"
                                       onchange="actualizarContadorSeleccionados()"
                                       data-tipo="${itemTipo}">
                                <label class="form-check-label" for="item_${itemId}">
                                    <div>
                                        <strong class="item-nombre">
                                            <i class="${icono} mr-1 ${esParametrizacion ? 'text-warning' : 'text-primary'}"></i>
                                            ${itemNombre}
                                        </strong>
                                        <br><small class="text-muted item-codigo">
                                            <i class="fas fa-tag mr-1"></i>Código: ${itemCodigo}
                                        </small>
                                        <br><small class="${esParametrizacion ? 'text-warning' : 'text-info'} item-descripcion">
                                            ${descripcion}
                                        </small>
                                        <br><small class="text-secondary">
                                            ${unidadMedida=='' ? `<span class="badge ${esParametrizacion ? 'bg-warning' : 'bg-info'}">${unidadMedida}</span>` : ''}
                                            ${precio ? `<span class="badge bg-success ml-1">$${precio}</span>` : ''}
                                            ${esParametrizacion ? `<span class="badge text-white ml-1" style="background-color: #fd7e14;">📊 ${esCargoTabla ? 'Tabla precios' : 'Parametrización'}</span>` : ''}
                                            ${esParametrizacion && item.cargo && item.cargo.nombre ? `<span class="badge bg-secondary ml-1">👤 ${String(item.cargo.nombre)}</span>` : ''}
                                        </small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

/**
 * Toggle todos los items propios
 */
function toggleTodosItemsPropios() {
    const checkboxes = document.querySelectorAll('.item-propio-checkbox');
    const todosSeleccionados = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !todosSeleccionados;
    });

    actualizarContadorSeleccionados();
}

/**
 * Actualizar contador de items propios seleccionados
 */
function actualizarContadorSeleccionados() {
    const seleccionados = document.querySelectorAll('.item-propio-checkbox:checked');
    const contador = document.getElementById('contadorSeleccionados');
    const btnFinalizar = document.getElementById('btnFinalizarSeleccion');

    if (contador) {
        contador.textContent = seleccionados.length;
    }

    if (btnFinalizar) {
        if (seleccionados.length > 0) {
            btnFinalizar.innerHTML = `<i class="fas fa-check mr-1"></i>Agregar Selección (${seleccionados.length})`;
        } else {
            btnFinalizar.innerHTML = '<i class="fas fa-check mr-1"></i>Agregar Selección';
        }
    }
}

/**
 * Finalizar selección de items propios y pasar a configuración de costos
 */
async function finalizarSeleccionItemsPropios() {
    const itemsPropiosSeleccionados = document.querySelectorAll('.item-propio-checkbox:checked');

    if (itemsPropiosSeleccionados.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin selección',
            text: 'Debe seleccionar al menos un item propio.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    try {
        // Obtener los datos de los items propios seleccionados
        const itemsPropiosData = Array.from(itemsPropiosSeleccionados).map(checkbox => {
            const itemId = checkbox.value;
            return window.itemsPropiosTemporal.find(item => item.id == itemId);
        }).filter(item => item !== undefined);


        // Guardar items propios seleccionados para el siguiente paso
        window.itemsPropiosSeleccionadosTemporal = itemsPropiosData;

        // Cerrar modal actual
        $('#modalSeleccionItemsPropios').modal('hide');

        // Abrir modal de configuración de costos
        await abrirModalConfiguracionCostos(itemsPropiosData);

    } catch (error) {
        console.error('Error al finalizar selección:', error);
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: error.message || 'No se pudo completar la selección. Intente nuevamente.',
            confirmButtonText: 'Entendido'
        });
    }
}

/**
 * Agregar subitem con items propios seleccionados por el usuario
 */
async function agregarSubitemConItemsPropiosSeleccionados(subitem, itemParent, itemsPropiosSeleccionados) {
    try {


        const tbody = document.getElementById('tbody_items');
        const subitemId = `subitem_${subitem.id || subitem.codigo}`;

        // Verificar si ya existe en la tabla
        const existeSubitem = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);
        if (existeSubitem) {
            throw new Error(`El subitem "${subitem.codigo || subitem.nombre}" ya está agregado.`);
        }

        // Limpiar tabla si tiene mensaje de "no hay elementos"
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }

        // Crear sección del subitem
        const subitemRow = document.createElement('tr');
        subitemRow.setAttribute('data-subitem-id', subitemId);
        subitemRow.classList.add('table-info', 'subitem-header');
        subitemRow.innerHTML = `
            <td colspan="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cubes text-info fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><strong>${subitem.codigo || subitem.nombre}</strong></h6>
                            <small class="text-muted">
                                <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                            </small>
                            <br><small class="text-info">
                                ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info mr-2">${itemsPropiosSeleccionados.length} Item(s) Propio(s)</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemCompleto('${subitemId}')" title="Quitar subitem completo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(subitemRow);

        // Agregar items propios seleccionados por el usuario
        if (itemsPropiosSeleccionados && itemsPropiosSeleccionados.length > 0) {
            itemsPropiosSeleccionados.forEach((itemPropio, index) => {
                const itemPropioRow = document.createElement('tr');
                itemPropioRow.setAttribute('data-parent-subitem', subitemId);
                itemPropioRow.classList.add('item-propio-row');
                itemPropioRow.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center ps-4">
                            <div class="me-3">
                                <i class="fas fa-cube text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div>
                                    <strong>${itemPropio.nombre}</strong>
                                    <br><small class="text-muted">
                                        <i class="fas fa-tag mr-1"></i>Código: ${itemPropio.codigo || 'No definido'}
                                    </small>
                                    <br><small class="text-success">
                                        ${itemPropio.descripcion || 'Sin descripción'}
                                    </small>
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-success">Item Propio</span>
                                    ${itemPropio.categoria?.nombre ? `<span class="badge bg-secondary ml-1">${itemPropio.categoria.nombre}</span>` : ''}
                                    ${itemPropio.unidad_medida ? `<span class="badge bg-info ml-1">${itemPropio.unidad_medida}</span>` : ''}
                                    ${itemPropio.precio ? `<span class="badge bg-primary ml-1">$${itemPropio.precio}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="quitarItemPropio('${subitemId}', '${itemPropio.id}')" title="Quitar item propio">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(itemPropioRow);
            });
        }

        // Actualizar variable global
        if (!window.subitemsSeleccionados) {
            window.subitemsSeleccionados = [];
        }

        window.subitemsSeleccionados.push({
            id: subitemId,
            subitem: subitem,
            itemParent: itemParent,
            itemsPropios: itemsPropiosSeleccionados,
            fechaAgregado: new Date().toISOString()
        });

        // Actualizar contador
        actualizarContadorProductosSeleccionados();


        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem con items propios seleccionados:', error);
        return Promise.reject(error);
    }
}

/**
 * Abrir modal para configuración de costos de items propios
 */
async function abrirModalConfiguracionCostos(itemsPropiosSeleccionados) {
    const subitem = window.subitemTemporal.subitem;

    const modalHtml = `
        <div class="modal fade" id="modalConfiguracionCostos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content shadow-lg border-0">
                    <!-- Header con diseí±o moderno -->
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                                <i class="fas fa-calculator text-primary" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title text-white mb-0 font-weight-bold">Configuración de Costos</h5>
                                <small class="text-white-50">Configure los tipos de costo para cada item seleccionado</small>
                            </div>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="text-shadow: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Progress indicator -->
                    <div class="bg-light px-4 py-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="step-indicator completed mr-3">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-indicator completed mr-3">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-indicator active mr-3">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div class="step-indicator">
                                        <i class="fas fa-save"></i>
                                    </div>
                                </div>
                                <div class="d-flex mt-2">
                                    <small class="text-muted mr-4">Categorí­as</small>
                                    <small class="text-muted mr-4">Items</small>
                                    <small class="text-primary mr-4 font-weight-bold">Costos</small>
                                    <small class="text-muted">Finalizar</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="bg-primary text-white px-3 py-1 rounded-pill d-inline-block">
                                    <i class="fas fa-cube mr-1"></i>
                                    <strong>${itemsPropiosSeleccionados.length}</strong> items seleccionados
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subitem info card -->
                    <div class="p-4 bg-light border-bottom">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="media">
                                    <div class="bg-info text-white rounded-circle p-2 mr-3 shadow-sm">
                                        <i class="fas fa-cubes"></i>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="mb-1 font-weight-bold text-dark">${subitem.codigo || subitem.nombre}</h6>
                                        <p class="mb-0 text-muted small">${subitem.descripcion || 'Configure el tipo de costo y parámetros para cada item'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="badge badge-outline-info mb-1">Paso 3 de 4</span>
                                    <small class="text-muted">Configuración de costos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main content area -->
                    <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Items cost configuration area -->
                        <div class="container-fluid p-4">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3 font-weight-bold text-uppercase">
                                        <i class="fas fa-cog mr-2"></i>Items a Configurar
                                    </h6>
                                    ${generarTarjetasItemsCostos(itemsPropiosSeleccionados)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer con botones mejorados -->
                    <div class="modal-footer bg-light border-top-0 p-4">
                        <div class="row w-100">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary btn-block" onclick="volverASeleccionItemsPropios()">
                                    <i class="fas fa-arrow-left mr-2"></i>Volver a Selección de Items
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-block btn-lg shadow" onclick="finalizarConfiguracionCostos()" id="btnFinalizarCostos">
                                    <i class="fas fa-check mr-2"></i>Finalizar Configuración
                                </button>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="w-100 mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"
                                     aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CSS adicional para el modal -->
        <style>
            .step-indicator {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #dee2e6;
                background: #f8f9fa;
                color: #6c757d;
                font-size: 0.8rem;
            }

            .step-indicator.completed {
                background: #28a745;
                border-color: #28a745;
                color: white;
            }

            .step-indicator.active {
                background: #007bff;
                border-color: #007bff;
                color: white;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
                70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
                100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
            }

            .item-cost-card {
                border: 2px solid #e3f2fd;
                border-radius: 12px;
                transition: all 0.3s ease;
                background: white;
                overflow: hidden;
            }

            .item-cost-card:hover {
                border-color: #2196f3;
                box-shadow: 0 4px 20px rgba(33, 150, 243, 0.1);
            }

            .item-cost-card.configured {
                border-color: #4caf50;
                background: #f8fff8;
            }

            .cost-type-selector {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .cost-field-group {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .price-display {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                border-radius: 10px;
                padding: 1rem;
                text-align: center;
                font-size: 1.1rem;
                font-weight: bold;
                box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            }

            .collapse-toggle {
                background: none;
                border: none;
                color: #007bff;
                text-decoration: none;
                font-size: 0.9rem;
                padding: 0;
            }

            .collapse-toggle:hover {
                color: #0056b3;
                text-decoration: underline;
            }

            .form-control:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            .btn-outline-secondary:hover {
                background-color: #6c757d;
                border-color: #6c757d;
            }
        </style>
    `;

    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalConfiguracionCostos');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Mostrar modal
    $('#modalConfiguracionCostos').modal('show');
}

/**
 * Generar tarjetas modernas de items propios para configuración de costos
 */
function generarTarjetasItemsCostos(itemsPropios) {
    let html = '';

    itemsPropios.forEach((item, index) => {
        const itemId = item.id;

        // Detectar si la categoría es NOMINA
        const esNomina = item.categoria && (item.categoria.nombre === 'NOMINA' || item.categoria.nombre === 'Nomina' || item.categoria.nombre === 'nómina');

        html += `
            <div class="item-cost-card mb-4" id="cardItem_${itemId}" data-es-nomina="${esNomina}">
                <!-- Header de la tarjeta -->
                <div class="card-header bg-primary text-white py-3" style="border-radius: 12px 12px 0 0;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-white text-primary rounded-circle p-2 mr-3 shadow-sm">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">${item.nombre}</h6>
                                    <small class="text-white-50">
                                        <i class="fas fa-tag mr-1"></i>${item.codigo || 'Sin código'}
                                        ${item.categoria?.nombre ? `â€¢ ${item.categoria.nombre}` : ''}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-light text-primary" id="statusBadge_${itemId}">
                                <i class="fas fa-clock mr-1"></i>Pendiente
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contenido de configuración -->
                <div class="card-body p-4">
                    ${item.descripcion ? `
                        <div class="alert alert-light border-0 mb-3" style="background-color: #f8f9ff;">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>${item.descripcion}
                            </small>
                        </div>
                    ` : ''}

                    <!-- Selector de tipo de costo -->
                    <div class="cost-type-selector mb-4">
                        <label class="form-label font-weight-bold text-primary mb-3">
                            <i class="fas fa-tag mr-2"></i>Seleccione el Tipo de Costo
                        </label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                     <input type="radio" id="tipoUnitario_${itemId}" name="tipoCosto_${itemId}"
                                         value="unitario" class="custom-control-input" onchange="cambiarTipoCostoVisual('${itemId}', 'unitario')" ${esNomina ? 'disabled' : ''}>
                                    <label class="custom-control-label" for="tipoUnitario_${itemId}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calculator text-info mr-2"></i>
                                            <div>
                                                <strong>Costo Unitario</strong>
                                                <br><small class="text-muted">Precio por unidad</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tipoHora_${itemId}" name="tipoCosto_${itemId}"
                                           value="hora" class="custom-control-input" onchange="cambiarTipoCostoVisual('${itemId}', 'hora')">
                                    <label class="custom-control-label" for="tipoHora_${itemId}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-warning mr-2"></i>
                                            <div>
                                                <strong>Costo Hora</strong>
                                                <br><small class="text-muted">Precio por hora</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tipoDia_${itemId}" name="tipoCosto_${itemId}"
                                           value="dia" class="custom-control-input" onchange="cambiarTipoCostoVisual('${itemId}', 'dia')">
                                    <label class="custom-control-label" for="tipoDia_${itemId}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-day text-success mr-2"></i>
                                            <div>
                                                <strong>Costo Dí­a</strong>
                                                <br><small class="text-muted">Precio por dí­a</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- írea de configuración de campos (inicialmente oculta) -->
                    <div id="camposCosto_${itemId}" class="d-none">
                        ${esNomina ? generarCamposConfiguracion(itemId) : generarCamposConfiguracionSimple(itemId)}
                    </div>

                    ${esNomina ? `
                    <!-- Panel de Novedades Operativas (exclusivo NOMINA) -->
                    <div id="panelNovedades_${itemId}" class="d-none mt-3">
                        <div class="card border-warning shadow-sm">
                            <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-invoice-dollar mr-2"></i><strong>Novedades Operativas</strong></span>
                                <small class="text-dark opacity-75">Costos adicionales al salario base</small>
                            </div>
                            <div class="card-body p-2" id="tablaNovedades_${itemId}">
                                <div class="text-center py-3 text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Seleccione el tipo de costo para ver las novedades
                                </div>
                            </div>
                            <div class="card-footer py-2 text-right bg-light border-0">
                                <strong>Total Novedades:
                                    <span id="totalNovedades_${itemId}" class="text-warning ml-1">$0</span>
                                </strong>
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Display del precio calculado -->
                    <div class="price-display d-none" id="precioDisplay_${itemId}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave mr-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="mb-0">Precio Total</h6>
                                        <small style="opacity: 0.9;">Cálculo automático</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <h4 class="mb-0" id="valorPrecio_${itemId}">$0.00</h4>
                            </div>
                        </div>

                        <!-- Input oculto para compatibilidad -->
                        <input type="hidden" id="precio_${itemId}" value="0">
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

/**
 * Generar campos SIMPLIFICADOS para items NO-NOMINA (maquinaria, insumos, servicios, etc.)
 * Solo muestra: unidad de medida, cantidad, valor del costo, precio total.
 */
function generarCamposConfiguracionSimple(itemId) {
    return `
        <div class="cost-field-group mb-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-ruler mr-1 text-secondary"></i>Unidad de Medida
                        </label>
                        <input type="text" class="form-control" id="unidadMedida_${itemId}"
                               placeholder="Ej: UND, DIA, HRS, KG"
                               onchange="actualizarPrecioVisual('${itemId}')">
                        <small class="form-text text-muted">Se precarga automáticamente desde la parametrización</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-sort-numeric-up mr-1 text-secondary"></i><span id="labelCantidad_${itemId}">Cantidad</span>
                        </label>
                        <input type="number" class="form-control" id="cantidadOperarios_${itemId}"
                               placeholder="0" step="0.5" min="0"
                               onchange="actualizarPrecioVisual('${itemId}')">
                        <small class="form-text text-muted" id="helpCantidad_${itemId}">Ingrese la cantidad requerida</small>
                    </div>
                </div>
            </div>
            <div id="camposEspecificos_${itemId}">
                <!-- Costo Unitario -->
                <div class="d-none" id="camposCostoUnitario_${itemId}">
                    <div class="form-group">
                        <label class="form-label font-weight-bold text-info">
                            <i class="fas fa-calculator mr-1"></i>Valor Unitario
                            <span id="badgeSugerido_unitario_${itemId}" class="badge badge-secondary border ml-1 d-none" style="font-size:.75rem;">
                                <i class="fas fa-magic mr-1"></i>Valor sugerido
                            </span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-info text-white">$</span>
                            </div>
                            <input type="number" class="form-control" id="costoUnitario_${itemId}"
                                   placeholder="0.00" step="0.01" min="0"
                                   onchange="actualizarPrecioVisual('${itemId}')">
                        </div>
                        <small class="form-text text-muted">Costo por unidad — puede editarlo si difiere del estándar</small>
                    </div>
                </div>
                <!-- Costo Hora -->
                <div class="d-none" id="camposCostoHora_${itemId}">
                    <div class="form-group">
                        <label class="form-label font-weight-bold text-warning">
                            <i class="fas fa-clock mr-1"></i>Valor por Hora
                            <span id="badgeSugerido_hora_${itemId}" class="badge badge-secondary border ml-1 d-none" style="font-size:.75rem;">
                                <i class="fas fa-magic mr-1"></i>Valor sugerido
                            </span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-warning text-dark">$</span>
                            </div>
                            <input type="number" class="form-control" id="costoHora_${itemId}"
                                   placeholder="0.00" step="0.01" min="0"
                                   onchange="actualizarPrecioVisual('${itemId}')">
                        </div>
                        <small class="form-text text-muted">Calculado como costo/día ÷ 8 horas — puede editarlo</small>
                    </div>
                </div>
                <!-- Costo Día -->
                <div class="d-none" id="camposCostoDia_${itemId}">
                    <div class="form-group">
                        <label class="form-label font-weight-bold text-success">
                            <i class="fas fa-calendar-day mr-1"></i>Valor por Día
                            <span id="badgeSugerido_dia_${itemId}" class="badge badge-secondary border ml-1 d-none" style="font-size:.75rem;">
                                <i class="fas fa-magic mr-1"></i>Valor sugerido
                            </span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-success text-white">$</span>
                            </div>
                            <input type="number" class="form-control" id="costoDia_${itemId}"
                                   placeholder="0.00" step="0.01" min="0"
                                   onchange="actualizarPrecioVisual('${itemId}')">
                        </div>
                        <small class="form-text text-muted">Costo por día de trabajo — puede editarlo</small>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Generar campos de configuración para un item
 */
function generarCamposConfiguracion(itemId) {
    return `
        <!-- Campos básicos -->
        <div class="cost-field-group mb-3">
            <h6 class="text-muted font-weight-bold mb-3">
                <i class="fas fa-cogs mr-2"></i>Configuración Básica
            </h6>
            <div class="row">
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div id="detalleCalculo_${itemId}" class="alert alert-info p-2 mb-0" style="font-size: 0.95em; display: none;"></div>
                                </div>
                            </div>
                <div class="col-12 mb-2">
                    <label class="form-label font-weight-bold">Tipo de Día</label>
                    <select class="form-control" id="tipoDia_${itemId}">
                        <option value="normal">Normal</option>
                        <option value="festivo">Festivo/Dominical</option>
                    </select>
                    <small class="form-text text-muted">Seleccione si el turno es en día normal o festivo/dominical</small>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-ruler mr-1"></i>Unidad de Medida
                        </label>
                        <input type="text" class="form-control" id="unidadMedida_${itemId}"
                               placeholder="Ej: UND, M2, KG, ML" onchange="actualizarPrecioVisual('${itemId}')">
                        <small class="form-text text-muted">Especifique la unidad de medida del item</small>
                    </div>
                </div>
                <div class="col-md-6" id="campoCantidadOperarios_${itemId}">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-users mr-1"></i><span id="labelCantidad_${itemId}">Cantidad</span>
                        </label>
                        <input type="number" class="form-control" id="cantidadOperarios_${itemId}"
                               placeholder="1" step="1" min="1" onchange="actualizarPrecioVisual('${itemId}')">
                        <small class="form-text text-muted" id="helpCantidad_${itemId}">Ingrese la cantidad</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campos especí­ficos por tipo de costo -->
        <div class="cost-field-group mb-3 d-none" id="camposEspecificos_${itemId}">
            <h6 class="text-muted font-weight-bold mb-3">
                <i class="fas fa-dollar-sign mr-2"></i>Configuración de Costos
            </h6>

            <!-- Costo Unitario -->
            <div class="d-none" id="camposCostoUnitario_${itemId}">
                <div class="form-group">
                    <label class="form-label font-weight-bold text-info">
                        <i class="fas fa-calculator mr-1"></i>Valor del Costo Unitario
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white">$</span>
                        </div>
                        <input type="number" class="form-control" id="costoUnitario_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                    </div>
                    <small class="form-text text-muted">Ingrese el costo por unidad individual</small>
                </div>
            </div>

            <!-- Costo Hora -->
            <div class="d-none" id="camposCostoHora_${itemId}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold text-warning">
                                <i class="fas fa-clock mr-1"></i>Valor del Costo por Hora
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-dark">$</span>
                                </div>
                                <input type="number" class="form-control" id="costoHora_${itemId}"
                                       placeholder="0.00" step="0.01" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                            </div>
                            <small class="form-text text-muted">Costo por cada hora de trabajo</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-sun mr-1 text-warning"></i>Turno (Hora Inicial y Final)
                            </label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" class="form-control" id="horaInicial_${itemId}" value="07:00" onchange="calcularHorasTurno('${itemId}')" step="60">
                                    <small class="form-text text-muted">Hora Inicial</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" class="form-control" id="horaFinal_${itemId}" value="14:00" onchange="calcularHorasTurno('${itemId}')" step="60">
                                    <small class="form-text text-muted">Hora Final</small>
                                </div>
                            </div>
                            <small class="form-text text-danger font-weight-bold">Máximo 7 horas normales y 2 extras</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold text-success">
                                <i class="fas fa-check mr-1"></i>Horas Normales
                            </label>
                            <input type="number" class="form-control" id="horasNormales_${itemId}" value="7" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold text-danger">
                                <i class="fas fa-plus mr-1"></i>Horas Extras
                            </label>
                            <input type="number" class="form-control" id="horasExtras_${itemId}" value="0" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Costo Dí­a -->
            <div class="d-none" id="camposCostoDia_${itemId}">
                <div class="form-group">
                    <label class="form-label font-weight-bold text-success">
                        <i class="fas fa-calendar-day mr-1"></i>Valor del Costo por Día
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-success text-white">$</span>
                        </div>
                        <input type="number" class="form-control" id="costoDia_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                    </div>
                    <small class="form-text text-muted">Costo por cada día de trabajo</small>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-sun mr-1 text-warning"></i>Dí­as Diurnos
                            </label>
                            <input type="number" class="form-control" id="diasDiurnos_${itemId}"
                                   placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemuneradosVisual('${itemId}')">
                            <small class="form-text text-muted">Cantidad de dí­as en horario diurno</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-moon mr-1 text-info"></i>Dí­as Nocturnos
                            </label>
                            <input type="number" class="form-control" id="diasNocturnos_${itemId}"
                                   placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemuneradosVisual('${itemId}')">
                            <small class="form-text text-muted">Cantidad de dí­as en horario nocturno</small>
                        </div>
                    </div>
                </div>

                <!-- Campos de dí­as remunerados -->
                <div id="seccionDiasRemunerados_${itemId}" class="d-none">
                    <div class="alert alert-info border-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-calculator mr-2"></i>Configuración de Dí­as Remunerados
                        </h6>
                        <div class="row">
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosDiurnos_${itemId}">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-sun mr-1 text-warning"></i>Dí­as Remunerados (Diurnos)
                                    </label>
                                    <input type="number" class="form-control" id="diasRemuneradosDiurnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                    <small class="form-text text-muted">Cantidad de dí­as diurnos que serán remunerados</small>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosNocturnos_${itemId}">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-moon mr-1 text-info"></i>Dí­as Remunerados (Nocturnos)
                                    </label>
                                    <input type="number" class="form-control" id="diasRemuneradosNocturnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                    <small class="form-text text-muted">Cantidad de dí­as nocturnos que serán remunerados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dí­as dominicales -->
                <div class="alert alert-secondary border-0" style="background-color: #f8f9fa;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="incluirDominicales_${itemId}"
                               onchange="toggleDiasDominicalesVisual('${itemId}')">
                        <label class="custom-control-label font-weight-bold" for="incluirDominicales_${itemId}">
                            <i class="fas fa-calendar-week mr-2 text-purple"></i>Â¿Desea incluir dí­as dominicales?
                        </label>
                    </div>

                    <div class="d-none mt-3" id="camposDominicales_${itemId}">
                        <div class="row">
                            <div class="col-md-6 d-none" id="campoDominicalDiurno_${itemId}">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-sun mr-1 text-warning"></i>Dominicales Diurnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesDiurnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="campoDominicalNocturno_${itemId}">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-moon mr-1 text-info"></i>Dominicales Nocturnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesNocturnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}


/**
 * Calcular horas normales y extras según hora inicial y final
 */
function calcularHorasTurno(itemId) {
    const horaInicial = document.getElementById(`horaInicial_${itemId}`).value;
    const horaFinal = document.getElementById(`horaFinal_${itemId}`).value;
    const inputNormales = document.getElementById(`horasNormales_${itemId}`);
    const inputExtras = document.getElementById(`horasExtras_${itemId}`);

    if (!horaInicial || !horaFinal) {
        inputNormales.value = 0;
        inputExtras.value = 0;
        return;
    }

    // Convertir a minutos
    const [hIni, mIni] = horaInicial.split(":").map(Number);
    const [hFin, mFin] = horaFinal.split(":").map(Number);
    let minutos = (hFin * 60 + mFin) - (hIni * 60 + mIni);
    if (minutos < 0) minutos += 24 * 60; // Soporta turnos que cruzan medianoche
    let horas = minutos / 60;

    // Lógica: máximo 7 normales, hasta 2 extras
    let normales = Math.min(7, Math.max(0, Math.floor(horas)));
    let extras = Math.max(0, Math.floor(horas) - 7);
    let total = normales + extras;
    if (total > 9) {
        // Mostrar alerta y limitar valores
        Swal.fire({
            icon: 'warning',
            title: 'Selección de horas no permitida',
            text: 'Solo puede liquidar hasta 7 horas normales y 2 extras (máximo 9 horas por turno).',
            confirmButtonText: 'Entendido',
            toast: true,
            position: 'top-end',
            timer: 4000
        });
        normales = 7;
        extras = 2;
    } else if (extras > 2) {
        extras = 2;
        normales = Math.min(7, Math.floor(horas) - (Math.floor(horas) - 9));
    }
    inputNormales.value = normales;
    inputExtras.value = extras;
    // Si se requiere, actualizar el precio visual
    if (typeof actualizarPrecioVisual === 'function') {
        actualizarPrecioVisual(itemId);
    }
}

/**
 * Cambiar tipo de costo en la nueva interfaz visual
 */
function cambiarTipoCostoVisual(itemId, tipoCosto) {
    const camposCosto = document.getElementById(`camposCosto_${itemId}`);
    const camposEspecificos = document.getElementById(`camposEspecificos_${itemId}`);
    const camposCostoUnitario = document.getElementById(`camposCostoUnitario_${itemId}`);
    const camposCostoHora = document.getElementById(`camposCostoHora_${itemId}`);
    const camposCostoDia = document.getElementById(`camposCostoDia_${itemId}`);
    const labelCantidad = document.getElementById(`labelCantidad_${itemId}`);
    const helpCantidad = document.getElementById(`helpCantidad_${itemId}`);
    const statusBadge = document.getElementById(`statusBadge_${itemId}`);
    const cardItem = document.getElementById(`cardItem_${itemId}`);

    // Mostrar campos de configuración
    camposCosto.classList.remove('d-none');
    camposEspecificos.classList.remove('d-none');

    // Ocultar todos los campos especí­ficos primero
    camposCostoUnitario.classList.add('d-none');
    camposCostoHora.classList.add('d-none');
    camposCostoDia.classList.add('d-none');

    // Mostrar campos según tipo seleccionado
    const _esNominaSwitch = cardItem?.dataset?.esNomina === 'true';
    switch (tipoCosto) {
        case 'unitario':
            camposCostoUnitario.classList.remove('d-none');
            labelCantidad.textContent = _esNominaSwitch ? 'Número de Operarios' : 'Cantidad de Unidades';
            helpCantidad.textContent  = _esNominaSwitch ? 'Ingrese la cantidad de operarios' : 'Ingrese la cantidad de unidades';
            statusBadge.innerHTML = '<i class="fas fa-calculator mr-1"></i>Configurando Unitario';
            statusBadge.className = 'badge badge-info';
            cardItem.style.borderColor = '#17a2b8';
            break;
        case 'hora':
            camposCostoHora.classList.remove('d-none');
            labelCantidad.textContent = _esNominaSwitch ? 'Número de Operarios' : 'Cantidad de Horas';
            helpCantidad.textContent  = _esNominaSwitch ? 'Ingrese la cantidad de operarios' : 'Ingrese la cantidad de horas';
            statusBadge.innerHTML = '<i class="fas fa-clock mr-1"></i>Configurando por Hora';
            statusBadge.className = 'badge badge-warning';
            cardItem.style.borderColor = '#ffc107';
            break;
        case 'dia':
            camposCostoDia.classList.remove('d-none');
            labelCantidad.textContent = _esNominaSwitch ? 'Número de Operarios' : 'Cantidad de Días';
            helpCantidad.textContent  = _esNominaSwitch ? 'Ingrese la cantidad de operarios' : 'Ingrese la cantidad de días';
            statusBadge.innerHTML = '<i class="fas fa-calendar-day mr-1"></i>Configurando por Día';
            statusBadge.className = 'badge badge-success';
            cardItem.style.borderColor = '#28a745';
            break;
    }

    // Limpiar campos y mostrar precio display
    limpiarCamposCostoVisual(itemId);
    const precioDisplay = document.getElementById(`precioDisplay_${itemId}`);
    precioDisplay.classList.remove('d-none');

    // Cargar valores por defecto desde backend
    cargarValoresDefectoPorTipo(itemId, tipoCosto);

    // Mostrar/ocultar panel de novedades (solo para items NOMINA)
    const _cardNom = document.getElementById(`cardItem_${itemId}`);
    const _esNomina = _cardNom?.dataset?.esNomina === 'true';
    const _panelNov = document.getElementById(`panelNovedades_${itemId}`);
    if (_esNomina && _panelNov) {
        if (tipoCosto === 'hora' || tipoCosto === 'dia') {
            _panelNov.classList.remove('d-none');
            cargarNovedadesEnPanel(itemId);
        } else {
            _panelNov.classList.add('d-none');
        }
    }
}

// ============================================================
// FUNCIONES NOVEDADES OPERATIVAS (NOMINA)
// ============================================================

let _cacheNovedadesOperativas = null;

async function cargarNovedadesEnPanel(itemId) {
    const contenedor = document.getElementById(`tablaNovedades_${itemId}`);
    if (!contenedor) return;

    // Si ya está renderizada la tabla (no es el placeholder inicial) no recargar
    if (contenedor.querySelector('table')) return;

    contenedor.innerHTML = '<div class="text-center py-2 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando novedades...</div>';

    try {
        if (!_cacheNovedadesOperativas) {
            const resp = await fetch('/admin/admin.cotizaciones.novedades-grupo-cotiza');
            const data = await resp.json();
            if (data.success) {
                _cacheNovedadesOperativas = data.data;
            } else {
                contenedor.innerHTML = '<div class="alert alert-warning py-2 mb-0">No se pudieron cargar las novedades.</div>';
                return;
            }
        }
        renderizarTablaNovedades(itemId, _cacheNovedadesOperativas);
    } catch (e) {
        console.error('Error cargando novedades:', e);
        contenedor.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error al cargar novedades operativas.</div>';
    }
}

function renderizarTablaNovedades(itemId, novedades) {
    const contenedor = document.getElementById(`tablaNovedades_${itemId}`);
    if (!contenedor) return;

    let filas = '';
    novedades.forEach(novedad => {
        if (!novedad.detalles || novedad.detalles.length === 0) return;
        novedad.detalles.forEach(detalle => {
            const valorFmt = Number(detalle.valor_operativo).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            filas += `
            <tr>
                <td class="py-1 align-middle">
                    <small class="text-dark"><strong>${novedad.nombre}</strong> · ${detalle.nombre}</small>
                </td>
                <td class="py-1 align-middle text-right">
                    <small>$${valorFmt}</small>
                </td>
                <td class="py-1 align-middle" style="width:90px">
                    <input type="number" min="0" step="0.5" value="0"
                           class="form-control form-control-sm text-center"
                           id="novCant_${itemId}_${detalle.id}"
                           data-detalle-id="${detalle.id}"
                           data-valor="${detalle.valor_operativo}"
                           oninput="recalcularFilaNovedad('${itemId}', '${detalle.id}', ${detalle.valor_operativo})">
                </td>
                <td class="py-1 align-middle text-right" id="novSub_${itemId}_${detalle.id}">
                    <small class="text-muted">$0</small>
                </td>
            </tr>`;
        });
    });

    if (!filas) {
        contenedor.innerHTML = '<div class="text-center text-muted py-2"><i class="fas fa-info-circle mr-1"></i>No hay novedades con grupo cotiza activo.</div>';
        return;
    }

    contenedor.innerHTML = `
        <div class="table-responsive">
        <table class="table table-sm table-borderless mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Novedad / Detalle</th>
                    <th class="text-right">Valor Unit.</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>${filas}</tbody>
        </table>
        </div>`;
}

function recalcularFilaNovedad(itemId, detalleId, valorUnitario) {
    const cantInput = document.getElementById(`novCant_${itemId}_${detalleId}`);
    const subCell  = document.getElementById(`novSub_${itemId}_${detalleId}`);
    if (!cantInput || !subCell) return;

    const cantidad = parseFloat(cantInput.value) || 0;
    const subtotal = cantidad * parseFloat(valorUnitario);
    const fmt = subtotal.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    subCell.innerHTML = `<small class="${subtotal > 0 ? 'text-success font-weight-bold' : 'text-muted'}">$${fmt}</small>`;
    actualizarTotalNovedadesPanel(itemId);
}

function actualizarTotalNovedadesPanel(itemId) {
    const inputs = document.querySelectorAll(`[id^="novCant_${itemId}_"]`);
    let total = 0;
    inputs.forEach(inp => {
        total += (parseFloat(inp.value) || 0) * (parseFloat(inp.dataset.valor) || 0);
    });
    const el = document.getElementById(`totalNovedades_${itemId}`);
    if (el) {
        el.textContent = '$' + total.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        el.style.color = total > 0 ? '#e67e00' : '';
    }
    // Recalcular Precio Total para reflejar el nuevo total de novedades
    actualizarPrecioVisual(itemId);
}

function recolectarNovedadesDeItem(itemId) {
    const inputs = document.querySelectorAll(`[id^="novCant_${itemId}_"]`);
    const novedades = [];
    inputs.forEach(inp => {
        const cantidad = parseFloat(inp.value) || 0;
        if (cantidad > 0) {
            novedades.push({
                novedad_detalle_id: parseInt(inp.dataset.detalleId),
                valor: parseFloat(inp.dataset.valor),
                cantidad: cantidad,
            });
        }
    });
    return novedades;
}

// ============================================================

/**
 * Validar horas y actualizar en la nueva interfaz
 */
function validarHorasYActualizar(itemId) {
    const horasDiurnas = document.getElementById(`horasDiurnas_${itemId}`);
    const campoHorasRemuneradas = document.getElementById(`campoHorasRemuneradas_${itemId}`);

    const horas = parseInt(horasDiurnas.value) || 0;

    if (horas > 7) {
        horasDiurnas.value = 7;
        // Mostrar alerta con SweetAlert2
        Swal.fire({
            type: 'warning',
            title: 'Lí­mite de Horas Excedido',
            text: 'El máximo de horas diurnas permitido es 7. Se ha ajustado automáticamente.',
            confirmButtonText: 'Entendido',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
    }

    // Mostrar campo de horas remuneradas si hay horas configuradas
    if (horas > 0) {
        campoHorasRemuneradas.classList.remove('d-none');
    } else {
        campoHorasRemuneradas.classList.add('d-none');
        document.getElementById(`horasRemuneradas_${itemId}`).value = '';
    }

    actualizarPrecioVisual(itemId);
}

/**
 * Mostrar campos de dí­as remunerados en la nueva interfaz
 */
function mostrarCamposDiasRemuneradosVisual(itemId) {
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;

    const seccionDiasRemunerados = document.getElementById(`seccionDiasRemunerados_${itemId}`);
    const campoDiurnos = document.getElementById(`campoDiasRemuneradosDiurnos_${itemId}`);
    const campoNocturnos = document.getElementById(`campoDiasRemuneradosNocturnos_${itemId}`);

    // Mostrar sección si hay al menos un tipo de dí­a configurado
    if (diasDiurnos > 0 || diasNocturnos > 0) {
        seccionDiasRemunerados.classList.remove('d-none');
    } else {
        seccionDiasRemunerados.classList.add('d-none');
    }

    // Mostrar campos especí­ficos según configuración
    if (diasDiurnos > 0) {
        campoDiurnos.classList.remove('d-none');
    } else {
        campoDiurnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value = '';
    }

    if (diasNocturnos > 0) {
        campoNocturnos.classList.remove('d-none');
    } else {
        campoNocturnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value = '';
    }

    // Actualizar campos dominicales
    actualizarCamposDominicalesVisual(itemId);
    actualizarPrecioVisual(itemId);
}

/**
 * Toggle dí­as dominicales en la nueva interfaz
 */
function toggleDiasDominicalesVisual(itemId) {
    const checkbox = document.getElementById(`incluirDominicales_${itemId}`);
    const camposDominicales = document.getElementById(`camposDominicales_${itemId}`);

    if (checkbox.checked) {
        camposDominicales.classList.remove('d-none');
        actualizarCamposDominicalesVisual(itemId);
    } else {
        camposDominicales.classList.add('d-none');
        // Limpiar campos
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }

    actualizarPrecioVisual(itemId);
}

/**
 * Actualizar campos dominicales según configuración de dí­as
 */
function actualizarCamposDominicalesVisual(itemId) {
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;
    const campoDiurno = document.getElementById(`campoDominicalDiurno_${itemId}`);
    const campoNocturno = document.getElementById(`campoDominicalNocturno_${itemId}`);

    // Solo mostrar campos dominicales si hay dí­as configurados del tipo correspondiente
    if (diasDiurnos > 0) {
        campoDiurno.classList.remove('d-none');
    } else {
        campoDiurno.classList.add('d-none');
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
    }

    if (diasNocturnos > 0) {
        campoNocturno.classList.remove('d-none');
    } else {
        campoNocturno.classList.add('d-none');
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }
}

/**
 * Actualizar precio en la nueva interfaz visual
 */
function actualizarPrecioVisual(itemId) {
    // Obtener tipo de costo seleccionado
    const tipoRadios = document.querySelectorAll(`input[name="tipoCosto_${itemId}"]:checked`);
    if (tipoRadios.length === 0) return;

    const tipoCosto = tipoRadios[0].value;
    const cantidadOperarios = parseFloat(document.getElementById(`cantidadOperarios_${itemId}`).value) || 0;
    const cardItem = document.getElementById(`cardItem_${itemId}`);
    const esNomina = cardItem?.dataset?.esNomina === 'true';
    let precio = 0;

    if (!esNomina) {
        // ── Cálculo simplificado para items NO-NÓMINA ──
        // Fórmula: costo × cantidad (sin días remunerados, dominicales, horas extras)
        switch (tipoCosto) {
            case 'unitario': {
                const costo = parseFloat(document.getElementById(`costoUnitario_${itemId}`)?.value) || 0;
                precio = costo * cantidadOperarios;
                break;
            }
            case 'hora': {
                const costo = parseFloat(document.getElementById(`costoHora_${itemId}`)?.value) || 0;
                precio = costo * cantidadOperarios;
                break;
            }
            case 'dia': {
                const costo = parseFloat(document.getElementById(`costoDia_${itemId}`)?.value) || 0;
                precio = costo * cantidadOperarios;
                break;
            }
        }
    } else {
        // ── Cálculo completo para items NÓMINA ──
        switch (tipoCosto) {
            case 'unitario': {
                const costoUnitarioInput = document.getElementById(`costoUnitario_${itemId}`);
                const costoUnitario = costoUnitarioInput ? parseFloat(costoUnitarioInput.value) || 0 : 0;
                precio = costoUnitario * cantidadOperarios;
                break;
            }
            case 'hora': {
                const costoHoraInput = document.getElementById(`costoHora_${itemId}`);
                const horasNormalesInput = document.getElementById(`horasNormales_${itemId}`);
                const horasExtrasInput = document.getElementById(`horasExtras_${itemId}`);
                const costoHora = costoHoraInput ? parseFloat(costoHoraInput.value) || 0 : 0;
                const horasNormales = horasNormalesInput ? parseFloat(horasNormalesInput.value) || 0 : 0;
                const horasExtras = horasExtrasInput ? parseFloat(horasExtrasInput.value) || 0 : 0;
                const factorExtra = 1.5;
                precio = (costoHora * horasNormales + costoHora * factorExtra * horasExtras) * cantidadOperarios;
                break;
            }
            case 'dia': {
                const costoDiaInput = document.getElementById(`costoDia_${itemId}`);
                const diasRemuneradosDiurnosInput = document.getElementById(`diasRemuneradosDiurnos_${itemId}`);
                const diasRemuneradosNocturnosInput = document.getElementById(`diasRemuneradosNocturnos_${itemId}`);
                const dominicalesDiurnosInput = document.getElementById(`dominicalesDiurnos_${itemId}`);
                const dominicalesNocturnosInput = document.getElementById(`dominicalesNocturnos_${itemId}`);
                const costoDia = costoDiaInput ? parseFloat(costoDiaInput.value) || 0 : 0;
                const diasRemuneradosDiurnos = diasRemuneradosDiurnosInput ? parseFloat(diasRemuneradosDiurnosInput.value) || 0 : 0;
                const diasRemuneradosNocturnos = diasRemuneradosNocturnosInput ? parseFloat(diasRemuneradosNocturnosInput.value) || 0 : 0;
                const dominicalesDiurnos = dominicalesDiurnosInput ? parseFloat(dominicalesDiurnosInput.value) || 0 : 0;
                const dominicalesNocturnos = dominicalesNocturnosInput ? parseFloat(dominicalesNocturnosInput.value) || 0 : 0;
                const totalDias = diasRemuneradosDiurnos + diasRemuneradosNocturnos + dominicalesDiurnos + dominicalesNocturnos;
                precio = costoDia * totalDias * cantidadOperarios;
                break;
            }
        }

        // Sumar Total Novedades × Operarios para items NOMINA
        const _novInputs = document.querySelectorAll(`[id^="novCant_${itemId}_"]`);
        let _totalNov = 0;
        _novInputs.forEach(inp => {
            _totalNov += (parseFloat(inp.value) || 0) * (parseFloat(inp.dataset.valor) || 0);
        });
        precio += _totalNov * cantidadOperarios;
    }

    // Actualizar display del precio
    const valorPrecio = document.getElementById(`valorPrecio_${itemId}`);
    const statusBadge = document.getElementById(`statusBadge_${itemId}`);

    valorPrecio.textContent = `$${precio.toFixed(2)}`;

    // Actualizar estado visual según precio
    if (precio > 0) {
        statusBadge.innerHTML = '<i class="fas fa-check mr-1"></i>Configurado';
        statusBadge.className = 'badge badge-success';
        cardItem.classList.add('configured');
        cardItem.style.borderColor = '#28a745';
    } else {
        const tipoTexto = tipoCosto.charAt(0).toUpperCase() + tipoCosto.slice(1);
        statusBadge.innerHTML = `<i class="fas fa-edit mr-1"></i>Configurando ${tipoTexto}`;
        cardItem.classList.remove('configured');
    }

    // Tambií©n actualizar el input oculto para compatibilidad
    const inputPrecio = document.getElementById(`precio_${itemId}`);
    if (inputPrecio) {
        inputPrecio.value = precio.toFixed(2);
    }
}

/**
 * Limpiar campos en la nueva interfaz visual
 */
function limpiarCamposCostoVisual(itemId) {
    // Limpiar todos los campos de entrada
    const campos = [
        `unidadMedida_${itemId}`,
        `cantidadOperarios_${itemId}`,
        `costoUnitario_${itemId}`,
        `costoHora_${itemId}`,
        `costoDia_${itemId}`,
        `horasDiurnas_${itemId}`,
        `horasRemuneradas_${itemId}`,
        `diasDiurnos_${itemId}`,
        `diasNocturnos_${itemId}`,
        `diasRemuneradosDiurnos_${itemId}`,
        `diasRemuneradosNocturnos_${itemId}`,
        `dominicalesDiurnos_${itemId}`,
        `dominicalesNocturnos_${itemId}`
    ];

    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo && campo.id !== `unidadMedida_${itemId}` && campo.id !== `cantidadOperarios_${itemId}`) {
            campo.value = '';
        }
    });

    // Desmarcar checkbox
    const checkbox = document.getElementById(`incluirDominicales_${itemId}`);
    if (checkbox) checkbox.checked = false;

    // Ocultar campos condicionales
    const camposCondicionales = [
        `campoHorasRemuneradas_${itemId}`,
        `seccionDiasRemunerados_${itemId}`,
        `campoDiasRemuneradosDiurnos_${itemId}`,
        `campoDiasRemuneradosNocturnos_${itemId}`,
        `camposDominicales_${itemId}`,
        `campoDominicalDiurno_${itemId}`,
        `campoDominicalNocturno_${itemId}`
    ];

    camposCondicionales.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) campo.classList.add('d-none');
    });

    // Actualizar precio
    actualizarPrecioVisual(itemId);
}

/**
 * Generar acordeón de items propios para configuración de costos
 */
function generarAccordionItemsCostos(itemsPropios) {
    let html = '';

    itemsPropios.forEach((item, index) => {
        const itemId = `item_${item.id}`;
        const isFirst = index === 0;

        html += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading_${itemId}">
                    <button class="accordion-button ${!isFirst ? 'collapsed' : ''}" type="button"
                            data-toggle="collapse" data-target="#collapse_${itemId}"
                            aria-expanded="${isFirst ? 'true' : 'false'}" aria-controls="collapse_${itemId}">
                        <i class="fas fa-cube mr-2"></i>
                        <strong>${item.nombre}</strong>
                        <small class="text-muted ml-2">(${item.codigo || 'Sin código'})</small>
                    </button>
                </h2>
                <div id="collapse_${itemId}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}"
                     aria-labelledby="heading_${itemId}" data-parent="#accordionCostos">
                    <div class="accordion-body">
                        ${generarFormularioCosto(item)}
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

/**
 * Generar formulario de configuración de costo para un item
 */
function generarFormularioCosto(item) {
    const itemId = item.id;

    return `
        <div class="row g-3" id="formCosto_${itemId}">
            <!-- Tipo de Costo -->
            <div class="col-md-12">
                <label class="form-label fw-bold">
                    <i class="fas fa-tag mr-1"></i>Tipo de Costo *
                </label>
                <select class="form-select" id="tipoCosto_${itemId}" onchange="cambiarTipoCosto(${itemId}, this.value)" required>
                    <option value="">Seleccione el tipo de costo</option>
                    <option value="unitario">Costo Unitario</option>
                    <option value="hora">Costo Hora</option>
                    <option value="dia">Costo Dí­a</option>
                </select>
            </div>

            <!-- Campos de configuración (inicialmente ocultos) -->
            <div id="camposCosto_${itemId}" class="col-md-12 d-none">
                <div class="row g-3">
                    <!-- Unidad de Medida -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-ruler mr-1"></i>Unidad de Medida *
                        </label>
                        <input type="text" class="form-control" id="unidadMedida_${itemId}"
                               placeholder="Ej: UND, M2, KG" required>
                    </div>

                    <!-- Costo Unitario -->
                    <div class="col-md-6" id="campoCostoUnitario_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign mr-1"></i>Costo Unitario *
                        </label>
                        <input type="number" class="form-control" id="costoUnitario_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="calcularPrecioItem(${itemId})" required>
                    </div>

                    <!-- Costo Hora -->
                    <div class="col-md-6 d-none" id="campoCostoHora_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-clock mr-1"></i>Costo por Hora *
                        </label>
                        <input type="number" class="form-control" id="costoHora_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="calcularPrecioItem(${itemId})" required>
                    </div>

                    <!-- Costo Dí­a -->
                    <div class="col-md-6 d-none" id="campoCostoDia_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-calendar-day mr-1"></i>Costo por Dí­a *
                        </label>
                        <input type="number" class="form-control" id="costoDia_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="calcularPrecioItem(${itemId})" required>
                    </div>

                    <!-- Cantidad / Operarios -->
                    <div class="col-md-6" id="campoCantidadOperarios_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-users mr-1"></i>
                            <span id="labelCantidad_${itemId}">Cantidad</span> *
                        </label>
                        <input type="number" class="form-control" id="cantidadOperarios_${itemId}"
                               placeholder="1" step="1" min="1" onchange="calcularPrecioItem(${itemId})" required>
                        <small class="form-text text-muted" id="helpCantidad_${itemId}">Cantidad de unidades/operarios</small>
                    </div>

                    <!-- Campos especí­ficos para tipo HORA -->
                    <div class="col-md-12 d-none" id="camposHoras_${itemId}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-sun mr-1"></i>Cantidad de horas diurnas
                                </label>
                                <input type="number" class="form-control" id="horasDiurnas_${itemId}"
                                       placeholder="0" step="1" min="0" max="7" onchange="validarHorasYCalcular(${itemId})">
                                <small class="form-text text-muted">Máximo 7 horas</small>
                            </div>
                            <div class="col-md-6" id="campoHorasRemuneradas_${itemId}" style="display: none;">
                                <label class="form-label">
                                    <i class="fas fa-calculator mr-1"></i>Ingrese la cantidad de horas remuneradas
                                </label>
                                <input type="number" class="form-control" id="horasRemuneradas_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                            </div>
                        </div>
                    </div>

                    <!-- Campos especí­ficos para tipo DIA -->
                    <div class="col-md-12 d-none" id="camposDias_${itemId}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-sun mr-1"></i>Cantidad de dí­as diurnos
                                </label>
                                <input type="number" class="form-control" id="diasDiurnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemunerados(${itemId}, 'diurnos')">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-moon mr-1"></i>Cantidad de dí­as nocturnos
                                </label>
                                <input type="number" class="form-control" id="diasNocturnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemunerados(${itemId}, 'nocturnos')">
                            </div>
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosDiurnos_${itemId}">
                                <label class="form-label">
                                    <i class="fas fa-calculator mr-1"></i>Ingrese la cantidad de dí­as remunerados (diurnos)
                                </label>
                                <input type="number" class="form-control" id="diasRemuneradosDiurnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                            </div>
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosNocturnos_${itemId}">
                                <label class="form-label">
                                    <i class="fas fa-calculator mr-1"></i>Ingrese la cantidad de dí­as remunerados (nocturnos)
                                </label>
                                <input type="number" class="form-control" id="diasRemuneradosNocturnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                            </div>
                        </div>

                        <!-- Dí­as dominicales -->
                        <div class="col-md-12 mt-3" id="seccionDominicales_${itemId}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="incluirDominicales_${itemId}"
                                       onchange="toggleDiasDominicales(${itemId})">
                                <label class="form-check-label" for="incluirDominicales_${itemId}">
                                    <i class="fas fa-calendar-week mr-1"></i>Â¿Desea ingresar dí­as dominicales?
                                </label>
                            </div>

                            <div class="row g-3 mt-2 d-none" id="camposDominicales_${itemId}">
                                <div class="col-md-6 d-none" id="campoDominicalDiurno_${itemId}">
                                    <label class="form-label">
                                        <i class="fas fa-sun mr-1"></i>Dí­as dominicales diurnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesDiurnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                                </div>
                                <div class="col-md-6 d-none" id="campoDominicalNocturno_${itemId}">
                                    <label class="form-label">
                                        <i class="fas fa-moon mr-1"></i>Dí­as dominicales nocturnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesNocturnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Precio calculado -->
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-success">
                            <i class="fas fa-money-bill-wave mr-1"></i>Precio Total
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control fw-bold" id="precio_${itemId}" readonly
                                   style="background-color: #e8f5e8; color: #28a745;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Cambiar tipo de costo y mostrar/ocultar campos
 */
function cambiarTipoCosto(itemId, tipoCosto) {
    const camposCosto = document.getElementById(`camposCosto_${itemId}`);
    const campoCostoUnitario = document.getElementById(`campoCostoUnitario_${itemId}`);
    const campoCostoHora = document.getElementById(`campoCostoHora_${itemId}`);
    const campoCostoDia = document.getElementById(`campoCostoDia_${itemId}`);
    const camposHoras = document.getElementById(`camposHoras_${itemId}`);
    const camposDias = document.getElementById(`camposDias_${itemId}`);
    const labelCantidad = document.getElementById(`labelCantidad_${itemId}`);
    const helpCantidad = document.getElementById(`helpCantidad_${itemId}`);

    // Mostrar campos de configuración
    if (tipoCosto) {
        camposCosto.classList.remove('d-none');
    } else {
        camposCosto.classList.add('d-none');
        return;
    }

    // Ocultar todos los campos de costo especí­ficos primero
    campoCostoUnitario.classList.add('d-none');
    campoCostoHora.classList.add('d-none');
    campoCostoDia.classList.add('d-none');
    camposHoras.classList.add('d-none');
    camposDias.classList.add('d-none');

    // Mostrar campos según tipo de costo
    switch (tipoCosto) {
        case 'unitario':
            campoCostoUnitario.classList.remove('d-none');
            labelCantidad.textContent = 'Cantidad';
            helpCantidad.textContent = 'Cantidad de unidades';
            break;
        case 'hora':
            campoCostoHora.classList.remove('d-none');
            camposHoras.classList.remove('d-none');
            labelCantidad.textContent = 'Número de Operarios';
            helpCantidad.textContent = 'Cantidad de operarios';
            break;
        case 'dia':
            campoCostoDia.classList.remove('d-none');
            camposDias.classList.remove('d-none');
            labelCantidad.textContent = 'Número de Operarios';
            helpCantidad.textContent = 'Cantidad de operarios';
            break;
    }

    // Limpiar campos al cambiar tipo
    limpiarCamposCosto(itemId);

    // Cargar valores por defecto desde backend
    cargarValoresDefectoPorTipo(itemId, tipoCosto);
}

/**
 * Validar horas y calcular precio para tipo HORA
 */
function validarHorasYCalcular(itemId) {
    const horasDiurnas = document.getElementById(`horasDiurnas_${itemId}`);
    const campoHorasRemuneradas = document.getElementById(`campoHorasRemuneradas_${itemId}`);

    const horas = parseInt(horasDiurnas.value) || 0;

    if (horas > 7) {
        horasDiurnas.value = 7;
        Swal.fire({
            type: 'warning',
            title: 'Lí­mite de horas',
            text: 'El máximo de horas diurnas permitido es 7.',
            confirmButtonText: 'Entendido'
        });
    }

    // Mostrar campo de horas remuneradas si hay horas diurnas
    if (horas > 0) {
        campoHorasRemuneradas.style.display = 'block';
    } else {
        campoHorasRemuneradas.style.display = 'none';
        document.getElementById(`horasRemuneradas_${itemId}`).value = '';
    }

    calcularPrecioItem(itemId);
}

/**
 * Mostrar campos de dí­as remunerados según tipo
 */
function mostrarCamposDiasRemunerados(itemId, tipo) {
    const campoDiurnos = document.getElementById(`campoDiasRemuneradosDiurnos_${itemId}`);
    const campoNocturnos = document.getElementById(`campoDiasRemuneradosNocturnos_${itemId}`);
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;

    // Mostrar campo de dí­as remunerados diurnos si hay dí­as diurnos
    if (diasDiurnos > 0) {
        campoDiurnos.classList.remove('d-none');
    } else {
        campoDiurnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value = '';
    }

    // Mostrar campo de dí­as remunerados nocturnos si hay dí­as nocturnos
    if (diasNocturnos > 0) {
        campoNocturnos.classList.remove('d-none');
    } else {
        campoNocturnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value = '';
    }

    // Actualizar visibilidad de campos dominicales
    actualizarCamposDominicales(itemId);
    calcularPrecioItem(itemId);
}

/**
 * Toggle dí­as dominicales
 */
function toggleDiasDominicales(itemId) {
    const checkbox = document.getElementById(`incluirDominicales_${itemId}`);
    const camposDominicales = document.getElementById(`camposDominicales_${itemId}`);

    if (checkbox.checked) {
        camposDominicales.classList.remove('d-none');
        actualizarCamposDominicales(itemId);
    } else {
        camposDominicales.classList.add('d-none');
        // Limpiar campos dominicales
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }
    calcularPrecioItem(itemId);
}

/**
 * Actualizar visibilidad de campos dominicales según dí­as configurados
 */
function actualizarCamposDominicales(itemId) {
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;
    const campoDominicalDiurno = document.getElementById(`campoDominicalDiurno_${itemId}`);
    const campoDominicalNocturno = document.getElementById(`campoDominicalNocturno_${itemId}`);

    // Mostrar campo dominical diurno solo si hay dí­as diurnos configurados
    if (diasDiurnos > 0) {
        campoDominicalDiurno.classList.remove('d-none');
    } else {
        campoDominicalDiurno.classList.add('d-none');
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
    }

    // Mostrar campo dominical nocturno solo si hay dí­as nocturnos configurados
    if (diasNocturnos > 0) {
        campoDominicalNocturno.classList.remove('d-none');
    } else {
        campoDominicalNocturno.classList.add('d-none');
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }
}

/**
 * Calcular precio del item según configuración
 */
function calcularPrecioItem(itemId) {
    // Obtener tipo de costo de radio buttons
    const tipoRadios = document.querySelectorAll(`input[name="tipoCosto_${itemId}"]:checked`);
    if (tipoRadios.length === 0) return 0;

    const tipoCosto = tipoRadios[0].value;
    const cantidadOperarios = parseFloat(document.getElementById(`cantidadOperarios_${itemId}`).value) || 0;

    let precio = 0;

    switch (tipoCosto) {
        case 'unitario': {
            const costoUnitarioInput = document.getElementById(`costoUnitario_${itemId}`);
            const costoUnitario = costoUnitarioInput ? parseFloat(costoUnitarioInput.value) || 0 : 0;
            precio = costoUnitario * cantidadOperarios;
            break;
        }
        case 'hora': {
            // Usar los nuevos campos si existen, si no, fallback a horasRemuneradas
            const costoHoraInput = document.getElementById(`costoHora_${itemId}`);
            const horasNormalesInput = document.getElementById(`horasNormales_${itemId}`);
            const horasExtrasInput = document.getElementById(`horasExtras_${itemId}`);
            const horasRemuneradasInput = document.getElementById(`horasRemuneradas_${itemId}`);
            const costoHora = costoHoraInput ? parseFloat(costoHoraInput.value) || 0 : 0;
            let precioHora = 0;
            if (horasNormalesInput && horasExtrasInput) {
                const horasNormales = parseFloat(horasNormalesInput.value) || 0;
                const horasExtras = parseFloat(horasExtrasInput.value) || 0;
                // Determinar tipo de día
                const tipoDiaSelect = document.getElementById(`tipoDia_${itemId}`);
                const tipoDia = tipoDiaSelect ? tipoDiaSelect.value : 'normal';
                // Determinar si las horas extras son diurnas o nocturnas
                const horaInicialInput = document.getElementById(`horaInicial_${itemId}`);
                const horaFinalInput = document.getElementById(`horaFinal_${itemId}`);
                let detalle = [];
                let precioHoraDetalle = 0;
                if (horaInicialInput && horaFinalInput && horasExtras > 0) {
                    const hIni = parseInt(horaInicialInput.value.split(":")[0], 10);
                    let horaExtraIni = hIni + horasNormales;
                    for (let i = 0; i < horasExtras; i++) {
                        let horaActual = (horaExtraIni + i) % 24;
                        let esNocturna = (horaActual >= 21 || horaActual < 6);
                        let esDiurna = !esNocturna;
                        let recargo = 1.5; // default
                        let tipo = '';
                        if (tipoDia === 'normal') {
                            recargo = esDiurna ? 1.25 : 1.75;
                            tipo = esDiurna ? 'Extra Diurna' : 'Extra Nocturna';
                        } else if (tipoDia === 'festivo') {
                            recargo = esDiurna ? 2.0 : 2.5;
                            tipo = esDiurna ? 'Extra Diurna Festiva/Dominical' : 'Extra Nocturna Festiva/Dominical';
                        }
                        precioHoraDetalle += costoHora * recargo;
                        detalle.push(`<b>${tipo}</b> (${horaActual}:00): $${(costoHora * recargo).toFixed(2)} (Recargo x${recargo})`);
                    }
                    // Horas normales siempre pagan 1x
                    if (horasNormales > 0) {
                        precioHoraDetalle += costoHora * horasNormales;
                        detalle.unshift(`<b>Horas Normales</b>: ${horasNormales} x $${costoHora.toFixed(2)} = $${(costoHora * horasNormales).toFixed(2)}`);
                    }
                    precioHoraDetalle = precioHoraDetalle * cantidadOperarios;
                    detalle.push(`<b>Total x Operarios:</b> $${precioHoraDetalle.toFixed(2)} (${cantidadOperarios} operario(s))`);
                    // Mostrar detalle en interfaz
                    const detalleDiv = document.getElementById(`detalleCalculo_${itemId}`);
                    if (detalleDiv) {
                        detalleDiv.innerHTML = detalle.join('<br>');
                        detalleDiv.style.display = '';
                    }
                } else {
                    // Fallback si no hay info de hora
                    let factorExtra = 1.5;
                    let tipo = '';
                    if (tipoDia === 'normal') {
                        factorExtra = 1.25;
                        tipo = 'Extra Diurna';
                    } else if (tipoDia === 'festivo') {
                        factorExtra = 2.0;
                        tipo = 'Extra Diurna Festiva/Dominical';
                    }
                    precioHoraDetalle = (costoHora * horasNormales + costoHora * factorExtra * horasExtras) * cantidadOperarios;
                    detalle.push(`<b>Horas Normales</b>: ${horasNormales} x $${costoHora.toFixed(2)} = $${(costoHora * horasNormales).toFixed(2)}`);
                    if (horasExtras > 0) {
                        detalle.push(`<b>${tipo}</b>: ${horasExtras} x $${(costoHora * factorExtra).toFixed(2)} (Recargo x${factorExtra})`);
                    }
                    detalle.push(`<b>Total x Operarios:</b> $${precioHoraDetalle.toFixed(2)} (${cantidadOperarios} operario(s))`);
                    const detalleDiv = document.getElementById(`detalleCalculo_${itemId}`);
                    if (detalleDiv) {
                        detalleDiv.innerHTML = detalle.join('<br>');
                        detalleDiv.style.display = '';
                    }
                }
                precio = precioHoraDetalle;
            } else if (horasRemuneradasInput) {
                const horasRemuneradas = parseFloat(horasRemuneradasInput.value) || 0;
                precioHora = costoHora * horasRemuneradas * cantidadOperarios;
                const detalleDiv = document.getElementById(`detalleCalculo_${itemId}`);
                if (detalleDiv) {
                    detalleDiv.innerHTML = `<b>Horas Remuneradas</b>: ${horasRemuneradas} x $${costoHora.toFixed(2)} = $${precioHora.toFixed(2)}`;
                    detalleDiv.style.display = '';
                }
                precio = precioHora;
            }
            break;
        }
        case 'dia': {
            const costoDiaInput = document.getElementById(`costoDia_${itemId}`);
            const diasRemuneradosDiurnosInput = document.getElementById(`diasRemuneradosDiurnos_${itemId}`);
            const diasRemuneradosNocturnosInput = document.getElementById(`diasRemuneradosNocturnos_${itemId}`);
            const dominicalesDiurnosInput = document.getElementById(`dominicalesDiurnos_${itemId}`);
            const dominicalesNocturnosInput = document.getElementById(`dominicalesNocturnos_${itemId}`);
            const costoDia = costoDiaInput ? parseFloat(costoDiaInput.value) || 0 : 0;
            const diasRemuneradosDiurnos = diasRemuneradosDiurnosInput ? parseFloat(diasRemuneradosDiurnosInput.value) || 0 : 0;
            const diasRemuneradosNocturnos = diasRemuneradosNocturnosInput ? parseFloat(diasRemuneradosNocturnosInput.value) || 0 : 0;
            const dominicalesDiurnos = dominicalesDiurnosInput ? parseFloat(dominicalesDiurnosInput.value) || 0 : 0;
            const dominicalesNocturnos = dominicalesNocturnosInput ? parseFloat(dominicalesNocturnosInput.value) || 0 : 0;
            const totalDias = diasRemuneradosDiurnos + diasRemuneradosNocturnos + dominicalesDiurnos + dominicalesNocturnos;
            precio = costoDia * totalDias * cantidadOperarios;
            break;
        }
    }

    // Actualizar campo de precio
    const campoPrecio = document.getElementById(`precio_${itemId}`);
    if (campoPrecio) {
        campoPrecio.value = precio.toFixed(2);
    }
}

/**
 * Limpiar campos de costo al cambiar tipo
 */
function limpiarCamposCosto(itemId) {
    // Limpiar todos los campos de entrada
    const campos = [
        `costoUnitario_${itemId}`,
        `costoHora_${itemId}`,
        `costoDia_${itemId}`,
        `cantidadOperarios_${itemId}`,
        `horasDiurnas_${itemId}`,
        `horasRemuneradas_${itemId}`,
        `diasDiurnos_${itemId}`,
        `diasNocturnos_${itemId}`,
        `diasRemuneradosDiurnos_${itemId}`,
        `diasRemuneradosNocturnos_${itemId}`,
        `dominicalesDiurnos_${itemId}`,
        `dominicalesNocturnos_${itemId}`,
        `precio_${itemId}`
    ];

    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.value = '';
        }
    });

    // Desmarcar checkbox dominicales
    const checkboxDominicales = document.getElementById(`incluirDominicales_${itemId}`);
    if (checkboxDominicales) {
        checkboxDominicales.checked = false;
    }

    // Ocultar campos condicionales
    const camposCondicionales = [
        `campoHorasRemuneradas_${itemId}`,
        `campoDiasRemuneradosDiurnos_${itemId}`,
        `campoDiasRemuneradosNocturnos_${itemId}`,
        `camposDominicales_${itemId}`,
        `campoDominicalDiurno_${itemId}`,
        `campoDominicalNocturno_${itemId}`
    ];

    camposCondicionales.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.classList.add('d-none');
            campo.style.display = 'none';
        }
    });
}

/**
 * Volver al modal de selección de items propios
 */
function volverASeleccionItemsPropios() {
    $('#modalConfiguracionCostos').modal('hide');

    // Restaurar modal de items propios con selecciones previas
    const itemsPropios = window.itemsPropiosTemporal;
    const categoriaIds = window.categoriasTemporal?.map(c => c.id) || [];

    abrirModalSeleccionItemsPropios(itemsPropios, categoriaIds);

    // Restaurar selecciones previas
    setTimeout(() => {
        window.itemsPropiosSeleccionadosTemporal?.forEach(item => {
            const checkbox = document.getElementById(`item_${item.id}`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
        actualizarContadorSeleccionados();
    }, 300);
}

/**
 * Finalizar configuración de costos y agregar todo a la tabla
 */
async function finalizarConfiguracionCostos() {
    const itemsPropiosSeleccionados = window.itemsPropiosSeleccionadosTemporal;

    const errores = [];
    const itemsConCostos = [];

    // Validar configuración de cada item
    for (const item of itemsPropiosSeleccionados) {
        const itemId = item.id;

        // Obtener tipo de costo de radio buttons
        const tipoRadios = document.querySelectorAll(`input[name="tipoCosto_${itemId}"]:checked`);
        const tipoCosto = tipoRadios.length > 0 ? tipoRadios[0].value : '';

        const unidadMedidaInput = document.getElementById(`unidadMedida_${itemId}`);
        const cantidadOperariosInput = document.getElementById(`cantidadOperarios_${itemId}`);
        const unidadMedida = unidadMedidaInput ? unidadMedidaInput.value.trim() : '';
        const cantidadOperarios = cantidadOperariosInput ? cantidadOperariosInput.value : '';

        // Obtener precio del display visual o input oculto
        let precio = document.getElementById(`valorPrecio_${itemId}`)?.textContent?.replace('$', '') ||
                document.getElementById(`precio_${itemId}`)?.value || '0';

        if (!tipoCosto) {
            errores.push(`Debe seleccionar el tipo de costo para "${item.nombre}"`);
            continue;
        }

        if (!unidadMedida) {
            errores.push(`Debe ingresar la unidad de medida para "${item.nombre}"`);
            continue;
        }

        if (!cantidadOperarios || cantidadOperarios <= 0) {
            errores.push(`Debe ingresar una cantidad válida para "${item.nombre}"`);
            continue;
        }

        if (!precio || precio <= 0) {
            errores.push(`El precio calculado debe ser mayor a 0 para "${item.nombre}"`);
            continue;
        }

        // Validaciones específicas por tipo de costo
        const _esNominaItem2 = item.categoria && (
            item.categoria.nombre === 'NOMINA' ||
            item.categoria.nombre === 'Nomina' ||
            item.categoria.nombre === 'nómina'
        );
        let costoEspecifico = 0;
        switch (tipoCosto) {
            case 'unitario': {
                const costoUnitarioInput = document.getElementById(`costoUnitario_${itemId}`);
                costoEspecifico = costoUnitarioInput ? parseFloat(costoUnitarioInput.value) : 0;
                if (!costoEspecifico || costoEspecifico <= 0) {
                    errores.push(`Debe ingresar un costo unitario válido para "${item.nombre}"`);
                    continue;
                }
                break;
            }
            case 'hora': {
                const costoHoraInput = document.getElementById(`costoHora_${itemId}`);
                costoEspecifico = costoHoraInput ? parseFloat(costoHoraInput.value) : 0;
                if (!costoEspecifico || costoEspecifico <= 0) {
                    errores.push(`Debe ingresar un costo por hora válido para "${item.nombre}"`);
                    continue;
                }
                // Solo para Nómina exigir horas normales/extras
                if (_esNominaItem2) {
                    const horasNormales = parseFloat(document.getElementById(`horasNormales_${itemId}`)?.value || 0);
                    const horasExtras   = parseFloat(document.getElementById(`horasExtras_${itemId}`)?.value || 0);
                    if (horasNormales + horasExtras <= 0) {
                        errores.push(`Debe ingresar al menos una hora remunerada para "${item.nombre}"`);
                        continue;
                    }
                }
                break;
            }
            case 'dia': {
                const costoDiaInput = document.getElementById(`costoDia_${itemId}`);
                costoEspecifico = costoDiaInput ? parseFloat(costoDiaInput.value) : 0;
                if (!costoEspecifico || costoEspecifico <= 0) {
                    errores.push(`Debe ingresar un costo por día válido para "${item.nombre}"`);
                    continue;
                }
                // Solo para Nómina exigir días remunerados
                if (_esNominaItem2) {
                    const diasR = parseFloat(document.getElementById(`diasRemuneradosDiurnos_${itemId}`)?.value || 0)
                                + parseFloat(document.getElementById(`diasRemuneradosNocturnos_${itemId}`)?.value || 0);
                    if (diasR <= 0) {
                        errores.push(`Debe ingresar al menos un día remunerado para "${item.nombre}"`);
                        continue;
                    }
                }
                break;
            }
        }

        item.cotizacion_item_id = window.subitemTemporal.item.id;
        item.cotizacion_subitem_id = window.subitemTemporal.subitem.id;
        if(item.categoria.nombre=='NOMINA'){
            item.item_propio_id = null;
            item.cargo_id = item.cargo?.id || item.cargo_id || null;
            item.parametrizacion_id = item.tipo === 'parametrizacion' ? item.id : null;
            item.tabla_precios_id = item.tipo === 'cargo_tabla' ? (item.tabla_precios_id || item.tabla_id || item.id) : null;
        }else{
            // Si proviene de parametrización de costos o es de tipo parametrizacion, conservar su id
            const esParametrizacion = item.tipo === 'parametrizacion' || item.fuente === 'parametrizacion_costos';
            item.parametrizacion_id = item.parametrizacion_id || (esParametrizacion ? item.id : null);
            item.item_propio_id = esParametrizacion ? null : item.id;
        }

        // Recolectar novedades si el item es NOMINA
        const _esNominaItem = _esNominaItem2;
        const _novedadesItem = _esNominaItem ? recolectarNovedadesDeItem(itemId) : [];

        // Para no-Nómina: los campos de nómina (días, horas) quedan en null
        const configuracionCosto = {
            tipoCosto,
            unidadMedida,
            cantidadOperarios: parseFloat(cantidadOperarios),
            precio: parseFloat(precio),
            costoUnitario: tipoCosto === 'unitario' ? costoEspecifico : null,
            costoHora: tipoCosto === 'hora' ? costoEspecifico : null,
            costoDia: tipoCosto === 'dia' ? costoEspecifico : null,
            novedades: _novedadesItem,
        };

        // Agregar campos de días/horas solo para nómina
        if (_esNominaItem) {
            if (tipoCosto === 'hora') {
                configuracionCosto.horasDiurnas   = parseFloat(document.getElementById(`horasDiurnas_${itemId}`)?.value || 0);
                configuracionCosto.horasRemuneradas = parseFloat(document.getElementById(`horasRemuneradas_${itemId}`)?.value || 0);
            }
            if (tipoCosto === 'dia') {
                configuracionCosto.diasDiurnos             = parseFloat(document.getElementById(`diasDiurnos_${itemId}`)?.value || 0);
                configuracionCosto.diasNocturnos            = parseFloat(document.getElementById(`diasNocturnos_${itemId}`)?.value || 0);
                configuracionCosto.diasRemuneradosDiurnos  = parseFloat(document.getElementById(`diasRemuneradosDiurnos_${itemId}`)?.value || 0);
                configuracionCosto.diasRemuneradosNocturnos= parseFloat(document.getElementById(`diasRemuneradosNocturnos_${itemId}`)?.value || 0);
                configuracionCosto.dominicalesDiurnos      = parseFloat(document.getElementById(`dominicalesDiurnos_${itemId}`)?.value || 0);
                configuracionCosto.dominicalesNocturnos    = parseFloat(document.getElementById(`dominicalesNocturnos_${itemId}`)?.value || 0);
                configuracionCosto.incluirDominicales      = document.getElementById(`incluirDominicales_${itemId}`)?.checked || false;
            }
        }

        // Recopilar toda la configuración del item
        const itemConCosto = {
            ...item,
            configuracionCosto,
        };

        itemsConCostos.push(itemConCosto);
    }
    // Mostrar errores si los hay
    if (errores.length > 0) {
        Swal.fire({
            type: 'error',
            title: 'Errores de validación',
            html: errores.map(error => `${error}`).join('<br>'),
            confirmButtonText: 'Entendido'
        });
        return;
    }

    try {
         // Actualizar contador
        actualizarContadorProductosSeleccionados();

        // Tambií©n agregar a la tabla del modal "Items Propios Seleccionados"
        actualizarTablaItemsPropiosSeleccionados(itemsConCostos, window.subitemTemporal.subitem, window.subitemTemporal.item);
        // Limpiar variables temporales
        window.subitemTemporal = null;
        window.itemsPropiosTemporal = null;
        window.itemsPropiosSeleccionadosTemporal = null;
        window.categoriasTemporal = null;

        // Limpiar selección del acordeón
        const itemSeleccionado = document.querySelector('input[name="itemSelected"]:checked');
        if (itemSeleccionado) {
            itemSeleccionado.checked = false;
        }

        // Cerrar modal
        $('#modalConfiguracionCostos').modal('hide');

        // Mostrar mensaje de í©xito
        Swal.fire({
            icon: 'success',
            title: 'Configuración completada',
            text: `Subitem agregado con ${itemsConCostos.length} item(s) propio(s) configurado(s).`,
            confirmButtonText: 'Entendido',
            timer: 3000
        });

        // SINCRONIZAR: Convertir items de la tabla a productosSeleccionados
        sincronizarItemsTablaConProductosSeleccionados(itemsConCostos);
        actualizarTotalGeneral();

        // Si hay items de nómina pendientes (flujo mixto), abrir su modal
        if (window.itemsNominaPendientes && window.itemsNominaPendientes.length > 0) {
            const nominaPendientes = window.itemsNominaPendientes;
            window.itemsNominaPendientes = null;
            setTimeout(() => abrirModalNominaConfig(nominaPendientes), 800);
        }


    } catch (error) {
        console.error('Error al finalizar configuración:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo completar la configuración. Intente nuevamente.',
            confirmButtonText: 'Entendido'
        });
    }
}

/**
 * Sincronizar items de tbody_items con productosSeleccionados
 */
function sincronizarItemsTablaConProductosSeleccionados(itemsConCostos) {
    // NO limpiar productos seleccionados - agregar a los existentes
    // Buscar datos directamente desde window.subitemsSeleccionados
    if (itemsConCostos && itemsConCostos.length > 0) {

        itemsConCostos.forEach(subitem => {

            // Usar directamente los datos de configuración de costos
            const precioTotal = subitem.configuracionCosto ?
                parseFloat(subitem.configuracionCosto.precio) : 50.0;
            const cantidad = subitem.configuracionCosto ?
                parseFloat(subitem.configuracionCosto.cantidadOperarios) : 1;
            const unidad = subitem.configuracionCosto ?
                subitem.configuracionCosto.unidadMedida : 'Unidad';

            // 🔧 CORRECCIÓN: Calcular precio unitario correcto
            const precioUnitario = cantidad > 0 ? precioTotal / cantidad : precioTotal;
            const id = subitem.id;
            const nuevoProducto = {
                id: id,
                cotizacion_item_id: subitem.cotizacion_item_id,
                cotizacion_subitem_id: subitem.cotizacion_subitem_id,
                item_propio_id: subitem.item_propio_id,
                parametrizacion_id: subitem.parametrizacion_id,
                tabla_precios_id: subitem.tabla_precios_id || null,
                tipo: subitem.tipo || null, // Preservar tipo para resolución posterior
                fuente: subitem.fuente || null, // Preservar fuente para resolución posterior
                nombre: subitem.nombre,
                codigo: subitem.codigo || '',
                precio: precioUnitario, // 🎯 Usar precio unitario calculado
                cantidad: cantidad, // 🎯 Usar cantidad correcta
                total: precioTotal, // 🎯 El total ya está correcto
                unidad: subitem.configuracionCosto ? unidad : 'Unidad',
                categoria: subitem.categoria?.nombre || 'Item Propio',
                descripcion: subitem.descripcion || '',
                esDelAcordeon: true,
                item_parent: subitem.subitem?.nombre || 'Configuración de Costos',
                // Campos de configuración de costos
                categoria_id: subitem.categoria_id || null,
                cargo_id: subitem.cargo_id || null,
                configuracionCosto: subitem.configuracionCosto || null
            };

            // Verificar duplicados por nombre y precio para evitar productos repetidos
            const existeProducto = productosSeleccionados.some(producto =>
                producto.nombre === nuevoProducto.nombre &&
                Math.abs(producto.precio - nuevoProducto.precio) < 0.01
            );

            if (!existeProducto) {
                productosSeleccionados.push(nuevoProducto);
            } else {
                console.log('⚠️ Producto duplicado no agregado:', nuevoProducto.nombre);
            }

        });
    } else {
        console.log('⚠️ No hay items con costos para sincronizar');
    }
    // Actualizar la tabla de productos seleccionados
    //actualizarTablaProductosSeleccionados();
}

/**
 * Agregar subitem con items propios configurados con costos
 */
async function agregarSubitemConItemsPropiosConfigurados(subitem, itemParent, itemsConCostos) {
    try {
        const subitemId = `subitem_${subitem.id || Date.now()}`;
        const tbody = document.getElementById('tbody_items');

        if (!tbody) {
            console.error('Tabla de items no encontrada');
            return Promise.reject(new Error('Tabla de productos no encontrada'));
        }

        // Eliminar mensaje de tabla vací­a
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }

        // Crear sección del subitem con información de configuración
        const subitemRow = document.createElement('tr');
        subitemRow.setAttribute('data-subitem-id', subitemId);
        subitemRow.classList.add('table-info', 'subitem-header');

        // Calcular precio total del subitem
        const precioTotalSubitem = itemsConCostos.reduce((total, item) => total + item.configuracionCosto.precio, 0);

        subitemRow.innerHTML = `
            <td colspan="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cubes text-info fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><strong>${subitem.codigo || subitem.nombre}</strong></h6>
                            <small class="text-muted">
                                <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                            </small>
                            <br><small class="text-info">
                                ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                            </small>
                            <br><small class="text-success">
                                <i class="fas fa-money-bill-wave mr-1"></i><strong>Total: $${precioTotalSubitem.toFixed(2)}</strong>
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info mr-2">${itemsConCostos.length} Item(s) Configurado(s)</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemCompleto('${subitemId}')" title="Quitar subitem completo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(subitemRow);

        // Agregar items propios con configuración de costos
        if (itemsConCostos && itemsConCostos.length > 0) {
            itemsConCostos.forEach((itemPropio, index) => {
                const itemPropioRow = document.createElement('tr');
                itemPropioRow.setAttribute('data-item-propio-id', itemPropio.id);
                itemPropioRow.setAttribute('data-subitem-parent', subitemId);
                itemPropioRow.classList.add('item-propio-row');

                const config = itemPropio.configuracionCosto;

                // Generar resumen de configuración
                let resumenConfig = '';
                switch (config.tipoCosto) {
                    case 'unitario':
                        resumenConfig = `Unitario: $${config.costoUnitario} x ${config.cantidadOperarios}`;
                        break;
                    case 'hora':
                        resumenConfig = `Hora: $${config.costoHora} x ${config.horasRemuneradas}h x ${config.cantidadOperarios} operarios`;
                        break;
                    case 'dia':
                        const totalDias = (config.diasRemuneradosDiurnos || 0) + (config.diasRemuneradosNocturnos || 0) +
                                        (config.dominicalesDiurnos || 0) + (config.dominicalesNocturnos || 0);
                        resumenConfig = `Dí­a: $${config.costoDia} x ${totalDias} dí­as x ${config.cantidadOperarios} operarios`;
                        break;
                }

                itemPropioRow.innerHTML = `
                    <td style="padding-left: 3rem;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-cube text-success mr-2"></i>
                                    <strong class="text-dark">${itemPropio.nombre}</strong>
                                    <span class="badge bg-light text-dark ml-2">${itemPropio.codigo || 'Sin código'}</span>
                                </div>

                                <div class="row g-2 text-sm">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-tag mr-1"></i>Tipo: ${config.tipoCosto.charAt(0).toUpperCase() + config.tipoCosto.slice(1)}
                                        </small>
                                        <br><small class="text-muted">
                                            <i class="fas fa-ruler mr-1"></i>Unidad: ${config.unidadMedida}
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calculator mr-1"></i>${resumenConfig}
                                        </small>
                                    </div>
                                </div>

                                ${itemPropio.descripcion ? `
                                <div class="mt-1">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle mr-1"></i>${itemPropio.descripcion}
                                    </small>
                                </div>
                                ` : ''}

                                <!-- Detalles adicionales colapsables -->
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse"
                                            data-target="#detalles_${itemPropio.id}_${index}" aria-expanded="false">
                                        <i class="fas fa-info-circle mr-1"></i>Ver detalles
                                    </button>
                                </div>

                                <div class="collapse mt-2" id="detalles_${itemPropio.id}_${index}">
                                    <div class="card card-body bg-light">
                                        ${generarDetallesConfiguracion(config)}
                                    </div>
                                </div>
                            </div>

                            <div class="text-end ml-3">
                                <div class="mb-1">
                                    <span class="badge bg-success fs-6">$${config.precio.toFixed(2)}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="quitarItemPropio('${subitemId}', '${itemPropio.id}')" title="Quitar item propio">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                tbody.appendChild(itemPropioRow);
            });
        }

        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem con items configurados:', error);
        return Promise.reject(error);
    }
}

/**
 * Actualizar tabla de Items Propios Seleccionados en el modal
 */
function actualizarTablaItemsPropiosSeleccionados(itemsConCostos, subitem, itemParent) {
    const tbodyModal = document.getElementById('tbodyProductosSeleccionados');
    if (!tbodyModal) {
        console.warn('No se encontró la tabla tbodyProductosSeleccionados en el modal');
        return;
    }

    // Remover mensaje de "no hay items propios seleccionados"
    const mensajeVacio = tbodyModal.querySelector('#noProductosSeleccionados');
    if (mensajeVacio) {
        mensajeVacio.remove();
    }
    // Agregar cada item propio configurado a la tabla del modal
    itemsConCostos.forEach((itemPropio) => {
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', itemPropio.id);

        const config = itemPropio.configuracionCosto;
        const resumenConfig = `${config.tipoCosto.toUpperCase()}: $${config.precio.toFixed(2)}`;

        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="fas fa-cube text-success"></i>
                    </div>
                    <div>
                        <strong class="text-dark">Nombre: ${itemPropio.nombre} código: ${itemPropio.codigo || 'Sin código'}</strong>
                        <br><small class="text-danger">${resumenConfig}</small>
                        <br><small class="text-muted">Item: ${itemParent.nombre} del subitem: ${subitem.codigo || subitem.nombre}</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="quitarItemPropioDelModal('${itemPropio.id}')" title="Quitar">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbodyModal.appendChild(row);
    });

}

/**
 * Quitar item propio de la tabla del modal
 */
function quitarItemPropioDelModal(itemId) {
    const tbodyModal = document.getElementById('tbodyProductosSeleccionados');
    if (!tbodyModal) return;

    const row = tbodyModal.querySelector(`tr[data-item-id="${itemId}"]`);
    if (row) {
        row.remove();

        // Si no quedan items, mostrar mensaje
        const remainingRows = tbodyModal.querySelectorAll('tr[data-item-id]');
        if (remainingRows.length === 0) {
            const mensajeVacio = document.createElement('tr');
            mensajeVacio.id = 'noProductosSeleccionados';
            mensajeVacio.innerHTML = `
                <td colspan="2" class="text-center text-muted">
                    No hay items propios seleccionados
                </td>
            `;
            tbodyModal.appendChild(mensajeVacio);
        }
    }
}

/**
 * Generar detalles de configuración para mostrar en el colapso
 */
function generarDetallesConfiguracion(config) {
    let detalles = `
        <div class="row g-2">
            <div class="col-md-6">
                <small><strong>Tipo de Costo:</strong> ${config.tipoCosto.charAt(0).toUpperCase() + config.tipoCosto.slice(1)}</small>
                <br><small><strong>Unidad de Medida:</strong> ${config.unidadMedida}</small>
                <br><small><strong>Cantidad/Operarios:</strong> ${config.cantidadOperarios}</small>
            </div>
            <div class="col-md-6">
    `;

    switch (config.tipoCosto) {
        case 'unitario':
            detalles += `
                <small><strong>Costo Unitario:</strong> $${config.costoUnitario}</small>
                <br><small><strong>Total:</strong> $${config.costoUnitario} x ${config.cantidadOperarios} = $${config.precio.toFixed(2)}</small>
            `;
            break;

        case 'hora':
            detalles += `
                <small><strong>Costo por Hora:</strong> $${config.costoHora}</small>
                <br><small><strong>Horas Diurnas:</strong> ${config.horasDiurnas || 0}</small>
                <br><small><strong>Horas Remuneradas:</strong> ${config.horasRemuneradas || 0}</small>
                <br><small><strong>Total:</strong> $${config.costoHora} x ${config.horasRemuneradas} x ${config.cantidadOperarios} = $${config.precio.toFixed(2)}</small>
            `;
            break;

        case 'dia':
            const totalDiasRemunerados = (config.diasRemuneradosDiurnos || 0) + (config.diasRemuneradosNocturnos || 0);
            const totalDominicales = (config.dominicalesDiurnos || 0) + (config.dominicalesNocturnos || 0);
            const totalDias = totalDiasRemunerados + totalDominicales;

            detalles += `
                <small><strong>Costo por Dí­a:</strong> $${config.costoDia}</small>
                <br><small><strong>Dí­as Diurnos:</strong> ${config.diasDiurnos || 0}</small>
                <br><small><strong>Dí­as Nocturnos:</strong> ${config.diasNocturnos || 0}</small>
                <br><small><strong>Dí­as Remunerados:</strong> ${totalDiasRemunerados}</small>
                ${totalDominicales > 0 ? `<br><small><strong>Dí­as Dominicales:</strong> ${totalDominicales}</small>` : ''}
                <br><small><strong>Total:</strong> $${config.costoDia} x ${totalDias} x ${config.cantidadOperarios} = $${config.precio.toFixed(2)}</small>
            `;
            break;
    }

    detalles += `
            </div>
        </div>
    `;

    return detalles;
}

/**
 * Cargar items propios de un subitem basándose en su categorí­a
 */
async function cargarItemsPropiosDelSubitem(subitem, itemParent) {
    try {
        // Obtener la categorí­a del subitem (puede estar en el subitem mismo o heredada del item padre)
        let categoriaId = null;

        if (subitem.categoria_id) {
            categoriaId = subitem.categoria_id;
        } else if (itemParent.categoria_id) {
            categoriaId = itemParent.categoria_id;
        } else {
            // Categorí­a por defecto si no se encuentra
            categoriaId = 1;
        }


        // Preparar datos para la petición
        const requestData = {
            categoria_ids: [categoriaId]
        };

        // Obtener la URL base
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.items-categoria.obtener`;

        // Hacer petición al backend
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            return result.data;
        } else {
            console.warn('No se encontraron items propios, usando datos simulados');
            // Datos simulados como fallback
            return [
                {
                    id: `sim_1_${subitem.id}`,
                    nombre: `Item Propio 1 - ${subitem.codigo || subitem.nombre}`,
                    codigo: 'IP-001',
                    categoria: { nombre: 'Categorí­a General' },
                    descripcion: `Item propio relacionado con ${subitem.codigo || subitem.nombre}`,
                    unidad_medida: 'Unidad'
                },
                {
                    id: `sim_2_${subitem.id}`,
                    nombre: `Item Propio 2 - ${subitem.codigo || subitem.nombre}`,
                    codigo: 'IP-002',
                    categoria: { nombre: 'Categorí­a General' },
                    descripcion: `Segundo item propio para ${subitem.codigo || subitem.nombre}`,
                    unidad_medida: 'Kg'
                }
            ];
        }

    } catch (error) {
        console.error('Error al cargar items propios del subitem:', error);
        throw new Error(`No se pudieron cargar los items propios: ${error.message}`);
    }
}

/**
 * Agregar subitem con sus items propios a la tabla de productos seleccionados
 */
async function agregarSubitemConItemsPropios(subitem, itemParent, itemsPropios) {
    try {
        const tbody = document.getElementById('tbody_items');
        const subitemId = `subitem_${subitem.id || subitem.codigo}`;

        // Verificar si ya existe en la tabla
        const existeSubitem = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);
        if (existeSubitem) {
            throw new Error(`El subitem "${subitem.codigo || subitem.nombre}" ya está agregado.`);
        }

        // Limpiar tabla si tiene mensaje de "no hay elementos"
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }

        // Crear sección del subitem
        const subitemRow = document.createElement('tr');
        subitemRow.setAttribute('data-subitem-id', subitemId);
        subitemRow.classList.add('table-info', 'subitem-header');
        subitemRow.innerHTML = `
            <td colspan="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cubes text-info fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><strong>${subitem.codigo || subitem.nombre}</strong></h6>
                            <small class="text-muted">
                                <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                            </small>
                            <br><small class="text-info">
                                ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info mr-2">Subitem</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemCompleto('${subitemId}')" title="Quitar subitem completo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(subitemRow);

        // Agregar items propios del subitem
        if (itemsPropios && itemsPropios.length > 0) {
            itemsPropios.forEach((itemPropio, index) => {
                const itemPropioRow = document.createElement('tr');
                itemPropioRow.setAttribute('data-parent-subitem', subitemId);
                itemPropioRow.classList.add('item-propio-row');
                itemPropioRow.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center ps-4">
                            <div class="me-3">
                                <i class="fas fa-cube text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div>
                                    <strong>${itemPropio.nombre}</strong>
                                    <br><small class="text-muted">
                                        <i class="fas fa-tag mr-1"></i>Código: ${itemPropio.codigo || 'No definido'}
                                    </small>
                                    <br><small class="text-success">
                                        ${itemPropio.descripcion || 'Sin descripción'}
                                    </small>
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-success">Item Propio</span>
                                    ${itemPropio.categoria?.nombre ? `<span class="badge bg-secondary ml-1">${itemPropio.categoria.nombre}</span>` : ''}
                                    ${itemPropio.unidad_medida ? `<span class="badge bg-info ml-1">${itemPropio.unidad_medida}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="quitarItemPropio('${subitemId}', '${itemPropio.id}')" title="Quitar item propio">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(itemPropioRow);
            });
        } else {
            // Mostrar mensaje si no hay items propios
            const noItemsRow = document.createElement('tr');
            noItemsRow.setAttribute('data-parent-subitem', subitemId);
            noItemsRow.classList.add('item-propio-row');
            noItemsRow.innerHTML = `
                <td colspan="2" class="text-center text-muted ps-4">
                    <em>No hay items propios disponibles para este subitem</em>
                </td>
            `;
            tbody.appendChild(noItemsRow);
        }

        // Actualizar variable global
        if (!window.subitemsSeleccionados) {
            window.subitemsSeleccionados = [];
        }

        window.subitemsSeleccionados.push({
            id: subitemId,
            subitem: subitem,
            itemParent: itemParent,
            itemsPropios: itemsPropios,
            fechaAgregado: new Date().toISOString()
        });

        // Actualizar contador
        actualizarContadorProductosSeleccionados();

        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem con items propios:', error);
        return Promise.reject(error);
    }
}

/**
 * Agregar subitem a la tabla de productos seleccionados
 */
async function agregarSubitemAProductosSeleccionados(subitem, itemParent) {
    try {
        // Verificar si ya existe en la tabla
        const tbody = document.getElementById('tbody_items');
        const subitemId = `subitem_${subitem.id || subitem.codigo}`;
        const existeSubitem = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);

        if (existeSubitem) {
            throw new Error(`El subitem "${subitem.codigo || subitem.nombre}" ya está agregado a los productos seleccionados.`);
        }

        // Limpiar tabla si tiene mensaje de "no hay elementos"
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }


        // Crear fila del subitem
        const row = document.createElement('tr');
        row.setAttribute('data-subitem-id', subitemId);
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-cubes text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${subitem.codigo || subitem.nombre}</strong>
                                <br><small class="text-muted">
                                    <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                                </small>
                                <br><small class="text-info">
                                    ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                                </small>
                                ${subitem.cantidad ? `<br><small class="text-secondary">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>Cantidad: ${subitem.cantidad}
                                    ${subitem.unidad_medida.sigla ? ` ${subitem.unidad_medida.sigla}` : ''}
                                </small>` : ''}
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-info">Subitem</span>
                            ${subitem.unidad_medida.sigla ? `<span class="badge bg-secondary ml-1">${subitem.unidad_medida.sigla}</span>` : ''}
                            ${subitem.active ? '<span class="badge bg-success ml-1">Activo</span>' : '<span class="badge bg-warning ml-1">Inactivo</span>'}
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemSeleccionado('${subitemId}')" title="Quitar subitem">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);

        // Actualizar contador de productos seleccionados si existe
        actualizarContadorProductosSeleccionados();

        // Guardar en variable global para posterior uso
        if (!window.subitemsSeleccionados) {
            window.subitemsSeleccionados = [];
        }

        window.subitemsSeleccionados.push({
            id: subitemId,
            subitem: subitem,
            itemParent: itemParent,
            fechaAgregado: new Date().toISOString()
        });

        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem:', error);
        return Promise.reject(error);
    }
}

/**
 * Quitar subitem seleccionado de la tabla
 */
function quitarSubitemSeleccionado(subitemId) {
    const row = document.querySelector(`tr[data-subitem-id="${subitemId}"]`);
    if (row) {
        row.remove();

        // Actualizar variable global
        if (window.subitemsSeleccionados) {
            window.subitemsSeleccionados = window.subitemsSeleccionados.filter(item => item.id !== subitemId);
        }

        // Si no quedan elementos, mostrar mensaje
        const tbody = document.getElementById('tbody_items');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr id="no_items_row_items">
                    <td colspan="2" class="text-center text-muted">
                        No hay subitems seleccionados
                    </td>
                </tr>
            `;
        }

        // Actualizar contador
        actualizarContadorProductosSeleccionados();

        toastr.success('Subitem eliminado correctamente');
    }
}

/**
 * Actualizar contador de productos seleccionados
 */
function actualizarContadorProductosSeleccionados() {
    const contador = document.getElementById('contadorProductosSeleccionados');
    if (contador) {
        const tbody = document.getElementById('tbody_items');
        const cantidadSubitems = tbody ? tbody.querySelectorAll('tr.subitem-header').length : 0;
        contador.textContent = cantidadSubitems;
    }
}

/**
 * Cargar items propios por categorí­a
 */
async function cargarItemsPropiosPorCategoria(itemInfo, tipo) {
    try {
        // Obtener la categorí­a del item/subitem seleccionado
        let categoriaId = null;

        if (tipo === 'item') {
            // Para items principales, intentar obtener categoria_id directamente
            categoriaId = itemInfo.categoria_id || 1; // Default a categorí­a 1 si no existe
        } else if (tipo === 'subitem') {
            // Para subitems, obtener del item padre
            categoriaId = itemInfo.parentItem?.categoria_id || itemInfo.categoria_id || 1;
        }

        // Preparar datos para la petición
        const requestData = {
            categoria_ids: [categoriaId]
        };

        // Obtener la URL base desde la meta tag o construirla
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.items-categoria.obtener`;

        // Hacer petición al backend
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            // Actualizar tabla con los items propios obtenidos
            const categoria = {
                id: categoriaId,
                nombre: result.data[0]?.categoria?.nombre || `Categorí­a ${categoriaId}`
            };

            actualizarTablaItemsPropios(result.data, itemInfo, categoria);
        } else {
            throw new Error(result.message || 'Error al obtener items propios');
        }

    } catch (error) {
        console.error('Error al cargar items propios:', error);

        // Usar datos simulados como fallback
        const itemsPropiosSimulados = [
            {
                id: `sim_1_${itemInfo.id}`,
                nombre: `Item Propio 1 - ${itemInfo.nombre}`,
                codigo: 'IP-001',
                categoria: { nombre: 'Categorí­a General' },
                descripcion: `Item propio relacionado con ${itemInfo.nombre}`
            },
            {
                id: `sim_2_${itemInfo.id}`,
                nombre: `Item Propio 2 - ${itemInfo.nombre}`,
                codigo: 'IP-002',
                categoria: { nombre: 'Categorí­a General' },
                descripcion: `Otro item propio para ${itemInfo.nombre}`
            }
        ];

        const categoriaSimulada = {
            id: 1,
            nombre: 'Categorí­a General (Simulada)'
        };

        actualizarTablaItemsPropios(itemsPropiosSimulados, itemInfo, categoriaSimulada);

        // Mostrar advertencia sobre datos simulados
        console.warn('Se están usando datos simulados para items propios');
    }
}

/**
 * Actualizar tabla de items propios
 */
function actualizarTablaItemsPropios(itemsPropios, itemOriginal, categoria) {
    const tbody = document.getElementById('tbody_items');

    // Limpiar tabla
    tbody.innerHTML = '';

    if (!itemsPropios || itemsPropios.length === 0) {
        tbody.innerHTML = `
            <tr id="no_items_row_items">
                <td colspan="2" class="text-center text-muted py-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay items propios para la categorí­a "${categoria.nombre}"
                    <br><small>Seleccione un item que tenga categorí­a asignada</small>
                </td>
            </tr>
        `;
        return;
    }

    // Agregar items propios a la tabla
    itemsPropios.forEach((itemPropio, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-cube text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${itemPropio.nombre}</strong>
                                <br><small class="text-muted"><i class="fas fa-tag mr-1"></i>Código: ${itemPropio.codigo || 'No definido'}</small>
                                <br><small class="text-info">${itemPropio.descripcion || 'Sin descripción'}</small>
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-secondary">${categoria.nombre}</span>
                            ${itemPropio.unidad_medida ? `<span class="badge bg-info ml-1">${itemPropio.unidad_medida}</span>` : ''}
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarItemPropio(${index})" title="Quitar item propio">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Guardar items propios en variable global para posterior uso
    window.itemsPropiosActuales = {
        items: itemsPropios,
        itemOriginal: itemOriginal,
        categoria: categoria
    };

}

/**
 * Limpiar subitems seleccionados en la tabla de productos
 */
function limpiarItemsPropiosSeleccionados() {
    const tbody = document.getElementById('tbody_items');
    tbody.innerHTML = `
        <tr id="no_items_row_items">
            <td colspan="2" class="text-center text-muted">
                No hay subitems seleccionados
            </td>
        </tr>
    `;

    // Limpiar variable global
    window.subitemsSeleccionados = [];

    // Actualizar contador
    actualizarContadorProductosSeleccionados();
}

/**
 * Quitar item propio individual de un subitem
 */
function quitarItemPropio(subitemId, itemPropioId) {
    const tbody = document.getElementById('tbody_items');

    // Buscar y eliminar el item propio especí­fico
    const itemPropioRows = tbody.querySelectorAll(`tr[data-parent-subitem="${subitemId}"]`);

    itemPropioRows.forEach(row => {
        const button = row.querySelector(`button[onclick*="${itemPropioId}"]`);
        if (button) {
            row.remove();
        }
    });

    // Actualizar variable global
    if (window.subitemsSeleccionados) {
        const subitem = window.subitemsSeleccionados.find(s => s.id === subitemId);
        if (subitem && subitem.itemsPropios) {
            subitem.itemsPropios = subitem.itemsPropios.filter(ip => ip.id != itemPropioId);

            // Si ya no hay items propios, agregar mensaje informativo
            if (subitem.itemsPropios.length === 0) {
                const noItemsRow = document.createElement('tr');
                noItemsRow.setAttribute('data-parent-subitem', subitemId);
                noItemsRow.classList.add('item-propio-row');
                noItemsRow.innerHTML = `
                    <td colspan="2" class="text-center text-muted ps-4">
                        <em>No quedan items propios para este subitem</em>
                    </td>
                `;
                tbody.appendChild(noItemsRow);
            }
        }
    }

    // Actualizar contador
    actualizarContadorProductosSeleccionados();

    toastr.success('Item propio eliminado correctamente');
}

/**
 * Quitar subitem completo con todos sus items propios
 */
function quitarSubitemCompleto(subitemId) {
    const tbody = document.getElementById('tbody_items');

    // Quitar el subitem header
    const subitemRow = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);
    if (subitemRow) {
        subitemRow.remove();
    }

    // Quitar todos los items propios asociados
    const itemsPropio = tbody.querySelectorAll(`tr[data-parent-subitem="${subitemId}"]`);
    itemsPropio.forEach(row => row.remove());

    // Actualizar variable global
    if (window.subitemsSeleccionados) {
        window.subitemsSeleccionados = window.subitemsSeleccionados.filter(item => item.id !== subitemId);
    }

    // Si no quedan elementos, mostrar mensaje
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="no_items_row_items">
                <td colspan="2" class="text-center text-muted">
                    No hay subitems seleccionados
                </td>
            </tr>
        `;
    }

    // Actualizar contador
    actualizarContadorProductosSeleccionados();

    toastr.success('Subitem y sus items propios eliminados correctamente');
}

/**
 * Inicializar estilos CSS para subitems e items propios
 */
function initSubitemsStyles() {
    if (!document.getElementById('subitemsStyles')) {
        const style = document.createElement('style');
        style.id = 'subitemsStyles';
        style.textContent = `
            .subitem-header {
                background-color: rgba(13, 202, 240, 0.1) !important;
                border-left: 4px solid #0dcaf0 !important;
            }

            .item-propio-row {
                background-color: rgba(25, 135, 84, 0.05) !important;
                border-left: 2px solid #198754 !important;
            }

            .item-propio-row:hover {
                background-color: rgba(25, 135, 84, 0.1) !important;
            }

            .subitem-header:hover {
                background-color: rgba(13, 202, 240, 0.15) !important;
            }

            .badge {
                font-size: 0.75rem;
            }

            .table-responsive .table td {
                vertical-align: middle;
            }

            .ps-4 {
                padding-left: 1.5rem !important;
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Generar HTML para lista desplegable de subitems
 */
function generarListaSubitems(itemId, subitems) {


    if (!subitems || subitems.length === 0) {
        return `
            <div class="subitems-container">
                <button class="btn btn-sm btn-outline-secondary" data-item-id="${itemId}" type="button" disabled>
                    <i class="fas fa-cube"></i> Sin Items
                </button>
            </div>
        `;
    }

    // Generar tabla de subitems
    let subitemsTableHtml = `
        <div class="subitems-container">
            <button class="btn btn-sm btn-primary toggle-subitems" type="button" data-item-id="${itemId}" onclick="toggleSubitems(${itemId})">
                <i class="fas fa-eye" id="icon_${itemId}"></i> Ver Items (${subitems.length})
            </button>
            <div class="subitems-table-container mt-2" id="subitems_${itemId}" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Código</th>
                                <th>Nombre</th>
                                <th style="width: 120px;">Cantidad</th>
                                <th>Observación</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    subitems.forEach(subitem => {
        const unidadMedida = subitem.unidad_medida ? subitem.unidad_medida.simbolo || subitem.unidad_medida.sigla || '' : '';
        const observacion = subitem.observacion || 'Sin observaciones';
        const isReadOnly = variable === 'ver';

        subitemsTableHtml += `
            <tr data-subitem-id="${subitem.id}">
                <td><code class="text-primary">${subitem.codigo}</code></td>
                <td><strong>${subitem.nombre}</strong></td>
                <td class="text-center">${subitem.cantidad || 1} ${unidadMedida}</td>
                <td><small class="text-muted">${observacion}</small></td>
                <td class="text-center">
                    ${!isReadOnly ? `
                    <button type="button" class="btn btn-sm btn-primary" onclick="editarSubitem(${subitem.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarSubitem(${subitem.id}, ${itemId})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                    ` : '<span class="text-muted">-</span>'}
                </td>
            </tr>
        `;
    });

    subitemsTableHtml += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    return subitemsTableHtml;
}

/**
 * Cargar subitems de un item especí­fico desde el backend
 */
async function cargarSubitemsDelItem(itemId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.items.getItemSubitems/${itemId}`);
        const data = await response.json();
        if (data.success) {
            // Encontrar el item en la lista local y actualizar sus subitems
            const itemIndex = itemsCotizacion.findIndex(item => item.id === itemId);
            if (itemIndex !== -1) {
                // Actualizar con los subitems recibidos (puede ser un array vacío)
                itemsCotizacion[itemIndex].subitems = data.data || [];

                // Guardar estado de visibilidad antes de reemplazar el DOM
                const oldContainer = document.getElementById(`subitems_${itemId}`);
                const wasVisible = oldContainer && window.getComputedStyle(oldContainer).display !== 'none';

                // Encontrar y actualizar solo el HTML específico del item
                const itemRow = document.querySelector(`tr:has([data-item-id="${itemId}"])`);
                if (itemRow) {
                    const subitemsCell = itemRow.cells[3]; // La celda de subitems
                    if (subitemsCell) {
                        subitemsCell.innerHTML = generarListaSubitems(itemId, data.data || []);

                        // Restaurar visibilidad si el panel estaba abierto y aún hay subitems
                        if (wasVisible && data.data && data.data.length > 0) {
                            const newContainer = document.getElementById(`subitems_${itemId}`);
                            if (newContainer) {
                                newContainer.style.display = 'block';
                            }
                            const newIcon = document.getElementById(`icon_${itemId}`);
                            if (newIcon) {
                                newIcon.classList.remove('fa-eye');
                                newIcon.classList.add('fa-eye-slash');
                            }
                        }
                    }
                }
            }
        } else {
            console.error('Error en la respuesta del servidor:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar subitems del item:', error);
    }
}

/**
 * Filtrar items del acordeón basado en texto de búsqueda
 */
function filtrarItemsAcordeon() {
    const searchTerm = document.getElementById('buscarItemsAcordeon').value.toLowerCase().trim();
    const tbody = document.getElementById('tbodyItemsAcordeon');

    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr:not(.mensaje-filtro)');
    let visibleCount = 0;
    let totalCount = 0;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 4) {
            // Es la fila de "no hay items" o similar
            if (searchTerm === '') {
                row.style.display = itemsCotizacion.length === 0 ? '' : 'none';
            } else {
                row.style.display = 'none';
            }
            return;
        }

        totalCount++;

        // Obtener texto de las celdas importantes (nombre, descripción)
        const itemText = cells[1].textContent.toLowerCase(); // Item/Subitem
        const descripcionText = cells[2].textContent.toLowerCase(); // Descripción
        const tipoText = cells[3].textContent.toLowerCase(); // Tipo

        // Verificar si alguno contiene el tí©rmino de búsqueda
        const matches = itemText.includes(searchTerm) ||
                       descripcionText.includes(searchTerm) ||
                       tipoText.includes(searchTerm);

        if (matches || searchTerm === '') {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Mostrar mensaje si no hay resultados y actualizar contador
    mostrarMensajeFiltroItems(searchTerm, visibleCount, totalCount);
}

/**
 * Mostrar mensaje cuando no hay resultados de filtro
 */
function mostrarMensajeFiltroItems(searchTerm, visibleCount, totalCount) {
    const tbody = document.getElementById('tbodyItemsAcordeon');
    if (!tbody) return;

    // Remover mensaje previo si existe
    const mensajePrevio = tbody.querySelector('.mensaje-filtro');
    if (mensajePrevio) {
        mensajePrevio.remove();
    }

    // Si hay tí©rmino de búsqueda pero no hay resultados visibles
    if (searchTerm !== '' && visibleCount === 0 && totalCount > 0) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.className = 'mensaje-filtro';
        noResultsRow.innerHTML = `
            <td colspan="4" class="text-center text-muted py-3">
                <i class="fas fa-search mr-2"></i>
                No se encontraron items que coincidan con "<strong>${searchTerm}</strong>"
                <br><small>Intente con otros tí©rminos de búsqueda</small>
            </td>
        `;
        tbody.appendChild(noResultsRow);
    }

    // Función de actualización de contador removida por ser innecesaria
}

/**
 * Limpiar filtro de items del acordeón
 */
function limpiarFiltroItems() {
    document.getElementById('buscarItemsAcordeon').value = '';
    filtrarItemsAcordeon();
}

/**
 * Alternar visibilidad de subitems
 */
function toggleSubitems(itemId) {
    const container = document.getElementById(`subitems_${itemId}`);
    const icon = document.getElementById(`icon_${itemId}`);
    const button = document.querySelector(`[data-item-id="${itemId}"]`);


    if (!container || !icon || !button) {
        console.error('Elementos no encontrados:', { container: !!container, icon: !!icon, button: !!button });
        return;
    }

    // Usar getComputedStyle para obtener el estado real de display
    const isHidden = window.getComputedStyle(container).display === 'none';

    if (isHidden) {
        container.style.display = 'block';
        icon.className = 'fas fa-eye-slash';
        button.innerHTML = `<i class="fas fa-eye-slash" id="icon_${itemId}"></i> Ocultar subitems`;
    } else {
        container.style.display = 'none';
        icon.className = 'fas fa-eye';
        const count = container.querySelectorAll('tbody tr').length;
        button.innerHTML = `<i class="fas fa-eye" id="icon_${itemId}"></i> Ver subitems (${count})`;
    }
}

function limpiarFormularioItem() {
    document.getElementById('item_nombre').value = '';
    limpiarErroresItems();
}

/**
 * Mostrar errores de validación
 */
function mostrarErroresItems(errores) {
    // Limpiar errores previos
    limpiarErroresItems();

    // Mostrar nuevos errores
    for (const campo in errores) {
        const input = document.getElementById(campo);
        const errorDiv = document.getElementById(`error_${campo}`);

        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = errores[campo];
        }
    }
}

/**
 * Limpiar errores de validación
 */
function limpiarErroresItems() {
    const inputs = document.querySelectorAll('#formAgregarItem .form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });

    const errorDivs = document.querySelectorAll('#formAgregarItem .invalid-feedback');
    errorDivs.forEach(div => {
        div.textContent = '';
    });
}


/**
 * Eliminar item especí­fico
 */
function eliminarItem(itemId) {
    Swal.fire({
        title: '¿Está seguro de eliminar este item?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (!result.value) {
            return;
        }
        try {
            const response = await fetch(`/admin/admin.cotizaciones.items.destroy/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const resultData = await response.json();

            if (resultData.success) {
                // Eliminar del array en memoria
                itemsCotizacion = itemsCotizacion.filter(item => item.id !== itemId);

                // Eliminar la fila directamente del DOM
                const fila = document.querySelector(`tr[data-item-id="${itemId}"]`);
                if (fila) {
                    fila.remove();
                }

                actualizarTablaItems();
                toggleEliminarItemsSeleccionados();
                toastr.success('Item eliminado exitosamente');
            } else {
                toastr.error('Error al eliminar el item: ' + (resultData.message ?? 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error al eliminar item:', error);
            toastr.error('Ocurrió un error al intentar eliminar el item');
        }
    });
}

/**
 * Editar item (cargar en formulario)
 */
function editarItem(itemId) {
    const item = itemsCotizacion.find(i => i.id === itemId);
    if (!item) return;

    // Cargar datos en el formulario
    document.getElementById('item_nombre').value = item.nombre;

    // Eliminar item de la lista (se re-agregará al guardar)
    itemsCotizacion = itemsCotizacion.filter(i => i.id !== itemId);
    actualizarTablaItems();

    // Scroll al formulario
    document.getElementById('formAgregarItem').scrollIntoView({ behavior: 'smooth' });

    toastr.info('Item cargado para edición');
}

/**
 * Eliminar items seleccionados
 */
function eliminarItemsSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_items .item-checkbox:checked');
    const idsEliminar = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-id')));

    if (idsEliminar.length === 0) {
        toastr.warning('No hay items seleccionados');
        return;
    }

    if (confirm(`Â¿Está seguro de eliminar ${idsEliminar.length} item(s)?`)) {
        itemsCotizacion = itemsCotizacion.filter(item => !idsEliminar.includes(item.id));
        actualizarTablaItems();

        const selectAllCheckbox = document.getElementById('select_all_items');
        if (selectAllCheckbox) selectAllCheckbox.checked = false;

        toggleEliminarItemsSeleccionados();
        toastr.info(`${idsEliminar.length} item(s) eliminado(s)`);
    }
}

/**
 * Limpiar todos los items
 */
function limpiarTodosItems() {
    itemsCotizacion = [];
    contadorItems = 0;
    actualizarTablaItems();

    const selectAllCheckbox = document.getElementById('select_all_items');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;

    toggleEliminarItemsSeleccionados();
    toastr.info('Todos los items han sido eliminados');
}

/**
 * Toggle botón eliminar seleccionados
 */
function toggleEliminarItemsSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_items .item-checkbox:checked');
    const btnEliminar = document.getElementById('btn_eliminar_items_seleccionados');

    if (btnEliminar) {
        btnEliminar.disabled = checkboxes.length === 0;
    }
}

/**
 * Configurar permisos según el modo
 */
function configurarPermisosItems() {
    const isReadOnly = variable === 'ver';

    // Deshabilitar formulario en modo ver
    const formInputs = document.querySelectorAll('#formAgregarItem input, #formAgregarItem select, #formAgregarItem button');
    formInputs.forEach(input => {
        input.disabled = isReadOnly;
    });

    // Ocultar botones de gestión en modo ver
    const botonesGestion = document.querySelectorAll('#btn_eliminar_items_seleccionados, #btn_limpiar_todos_items');
    botonesGestion.forEach(btn => {
        btn.style.display = isReadOnly ? 'none' : 'inline-block';
    });
}

/**
 * Abrir modal para crear subitem
 */
function abrirModalCrearSubitem(cotizacionItemId) {
    // Verificar que el item tenga un ID válido
    if (!cotizacionItemId) {
        toastr.error('La capitulación debe estar guardada antes de poder agregar un Item. Por favor, guarde la capitulación primero.');
        return;
    }

    // Verificar que el item existe en la lista local
    const item = itemsCotizacion.find(item => item.id === cotizacionItemId);
    if (!item) {
        toastr.error('Item no encontrado');
        return;
    }

    // Limpiar formulario
    document.getElementById('formCrearSubitem').reset();
    document.getElementById('cotizacion_item_id').value = cotizacionItemId;
    document.getElementById('subitem_id_edit').value = ''; // Limpiar ID de edición
    limpiarErroresSubitem();

    // Obtener sugerencia de código desde el backend
    let parentCodigo = null;
    // Si hay subitems y el usuario seleccionó un padre, puedes obtener el código padre aquí
    // Por ahora, solo sugerimos el siguiente subitem directo
    fetch(`/admin/admin.cotizaciones.items.sugerirCodigoSubitem?cotizacion_item_id=${cotizacionItemId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.codigo) {
                document.getElementById('subitem_codigo').value = data.codigo;
            } else {
                document.getElementById('subitem_codigo').value = '';
            }
        })
        .catch(() => {
            document.getElementById('subitem_codigo').value = '';
        });

    // Restaurar títulos y botones para modo crear
    const modalTitle = document.querySelector('#modalCrearSubitem .modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Crear Nuevo Item';
    }

    const btnGuardar = document.getElementById('btn_guardar_subitem');
    if (btnGuardar) {
        btnGuardar.textContent = 'Crear item';
    }

    // Mostrar modal usando jQuery (Bootstrap 4)
    $('#modalCrearSubitem').modal('show');
}

/**
 * Guardar/Actualizar subitem
 */
async function guardarSubitem(event) {
    event.preventDefault();

    // Mostrar loading
    const btnGuardar = document.getElementById('btn_guardar_subitem');
    const textoOriginal = btnGuardar.innerHTML;
    try {
        // Validar formulario
        if (!validarFormularioSubitem()) {
            return;
        }

        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btnGuardar.disabled = true;

        // Verificar si es edición o creación
        const subitemIdEdit = document.getElementById('subitem_id_edit').value;
        const isEdit = subitemIdEdit && subitemIdEdit !== '';

        // Obtener datos
        const datos = {
            codigo: document.getElementById('subitem_codigo').value.trim(),
            nombre: document.getElementById('subitem_nombre').value.trim(),
            unidad_medida_id: document.getElementById('subitem_unidad_medida').value,
            cantidad: parseFloat(document.getElementById('subitem_cantidad').value) || 1,
            observacion: document.getElementById('subitem_observacion').value.trim() || null
        };

        // Solo incluir cotizacion_item_id si es creación
        if (!isEdit) {
            datos.cotizacion_item_id = document.getElementById('cotizacion_item_id').value;
        }

        // Determinar URL y mí©todo según operación
        const url = isEdit
            ? `/admin/admin.cotizaciones.items.updateSubitem/${subitemIdEdit}`
            : '/admin/admin.cotizaciones.items.createSubitem';
        const method = isEdit ? 'PUT' : 'POST';

        // Enviar al servidor
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(datos)
        });

        const result = await response.json();

        if (result.success) {
            // Restaurar botón
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;

            // Si es creación, agregar a la lista local de subitems disponibles
            if (!isEdit) {
                subitemsDisponibles.push(result.data);
            }

            // Obtener el cotizacion_item_id correcto
            const cotizacionItemId = isEdit
                ? parseInt(document.getElementById('cotizacion_item_id').value)
                : parseInt(datos.cotizacion_item_id);

            if (cotizacionItemId) {
                // Recargar subitems del item
                await cargarSubitemsDelItem(cotizacionItemId);

                // Fallback: si no se actualizó, manejar manualmente
                const itemIndex = itemsCotizacion.findIndex(item => item.id === cotizacionItemId);
                if (itemIndex !== -1) {
                    // Si el item no tení­a subitems antes, inicializar el array
                    if (!itemsCotizacion[itemIndex].subitems) {
                        itemsCotizacion[itemIndex].subitems = [];
                    }

                    if (!isEdit) {
                        // Para creación: verificar si el subitem ya existe en la lista local
                        const subitemExiste = itemsCotizacion[itemIndex].subitems.find(sub => sub.id === result.data.id);
                        if (!subitemExiste) {
                            // Agregar el nuevo subitem a la lista local
                            itemsCotizacion[itemIndex].subitems.push(result.data);
                        }
                    }
                    // Para edición, cargarSubitemsDelItem ya actualizó la lista
                }
            }

            // Refrescar vistas: acordeón (modal) y tabla principal
            actualizarTablaItems();

            toastr.success(isEdit ? 'Item actualizado exitosamente' : 'Item creado exitosamente');            // Cerrar modal usando jQuery (Bootstrap 4)
            $('#modalCrearSubitem').modal('hide');


        } else {
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;
            throw new Error(result.message || 'Error al crear Item');
        }

    } catch (error) {
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;

        if (error.response?.status === 422) {
            // Mostrar errores de validación
            mostrarErroresSubitem(error.response.data.errors || {});
        }
    } finally {
        // Restaurar botón
        const btnGuardar = document.getElementById('btn_guardar_subitem');
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
    }
}

/**
 * Validar formulario de subitem
 */
function validarFormularioSubitem() {
    let esValido = true;
    const errores = {};

    // Validar código
    const codigo = document.getElementById('subitem_codigo').value.trim();
    if (!codigo) {
        errores.subitem_codigo = 'El código es obligatorio';
        esValido = false;
    } else if (codigo.length > 50) {
        errores.subitem_codigo = 'El código no puede exceder 50 caracteres';
        esValido = false;
    }

    // Validar nombre
    const nombre = document.getElementById('subitem_nombre').value.trim();
    if (!nombre) {
        errores.subitem_nombre = 'El nombre es obligatorio';
        esValido = false;
    } else if (nombre.length > 255) {
        errores.subitem_nombre = 'El nombre no puede exceder 255 caracteres';
        esValido = false;
    }

    // Validar unidad de medida
    const unidadMedida = document.getElementById('subitem_unidad_medida').value;
    if (!unidadMedida) {
        errores.subitem_unidad_medida = 'La unidad de medida es obligatoria';
        esValido = false;
    }

    // Validar cantidad
    const cantidad = parseFloat(document.getElementById('subitem_cantidad').value);
    if (isNaN(cantidad) || cantidad < 0) {
        errores.subitem_cantidad = 'La cantidad debe ser un número válido mayor o igual a 0';
        esValido = false;
    }

    // Mostrar errores
    if (!esValido) {
        mostrarErroresSubitem(errores);
    } else {
        limpiarErroresSubitem();
    }

    return esValido;
}

/**
 * Mostrar errores de validación del subitem
 */
function mostrarErroresSubitem(errores) {
    limpiarErroresSubitem();

    for (const campo in errores) {
        const input = document.getElementById(campo);
        const errorDiv = document.getElementById(`error_${campo}`);

        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = Array.isArray(errores[campo]) ? errores[campo][0] : errores[campo];
        }
    }
}

/**
 * Limpiar errores de validación del subitem
 */
function limpiarErroresSubitem() {
    const inputs = document.querySelectorAll('#formCrearSubitem .form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });

    const errorDivs = document.querySelectorAll('#formCrearSubitem .invalid-feedback');
    errorDivs.forEach(div => {
        div.textContent = '';
    });
}

/**
 * Cargar items existentes de la cotización
 */
async function cargarItemsExistentes(cotizacionId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.items.getCotizacionItems/${cotizacionId}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            itemsCotizacion = [];
            contadorItems = 0;

            // Nueva lógica para preservar subitems
            itemsCotizacion = data.data.map(item => {
                contadorItems++;
                return {
                    id: item.id ? item.id : contadorItems,
                    nombre: item.nombre,
                    active: item.active,
                    subitems: item.subitems || [] // Preservar los subitems que vienen del backend
                };
            });
            actualizarTablaItems();
        }
    } catch (error) {
        console.error('Error al cargar items existentes:', error);
    }
}

/**
 * Guardar items en el backend
 */
async function guardarItemsCotizacion(cotizacionId) {
    try {
        const itemsParaGuardar = itemsCotizacion.map(item => ({
            nombre: item.nombre,
            active: item.active
        }));

        const response = await fetch('/admin/admin.cotizaciones.items.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                items: itemsParaGuardar
            })
        });

        const data = await response.json();

        if (data.success) {
            await cargarItemsExistentes(cotizacionId);
        } else {
            console.error('Error al guardar items:', data.message);
            toastr.warning('Cotización guardada, pero hubo problemas con los items');
        }
    } catch (error) {
        console.error('Error al enviar items:', error);
        toastr.warning('Cotización guardada, pero hubo problemas con los items');
    }
}

/**
 * Editar un subitem
 */
async function editarSubitem(subitemId) {
    try {
        // Cargar datos del subitem
        const response = await fetch(`/admin/admin.cotizaciones.items.getSubitem/${subitemId}`);
        const result = await response.json();

        if (result.success) {
            const subitem = result.data;

            // Llenar el modal de edición con los datos del subitem
            document.getElementById('cotizacion_item_id').value = subitem.cotizacion_item_id;
            document.getElementById('subitem_id_edit').value = subitem.id;
            document.getElementById('subitem_codigo').value = subitem.codigo;
            document.getElementById('subitem_nombre').value = subitem.nombre;
            document.getElementById('subitem_unidad_medida').value = subitem.unidad_medida_id;
            document.getElementById('subitem_cantidad').value = subitem.cantidad;
            document.getElementById('subitem_observacion').value = subitem.observacion || '';

            // Actualizar contador de caracteres
            const contadorSpan = document.getElementById('subitem_observacion_count');
            if (contadorSpan) {
                contadorSpan.textContent = (subitem.observacion || '').length;
            }

            // Cambiar tí­tulo del modal
            const modalTitle = document.querySelector('#modalCrearSubitem .modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Editar Item';
            }

            // Cambiar texto del botón
            const btnGuardar = document.getElementById('btn_guardar_subitem');
            if (btnGuardar) {
                btnGuardar.textContent = 'Actualizar Item';
            }

            // Limpiar errores
            limpiarErroresSubitem();

            // Mostrar modal
            $('#modalCrearSubitem').modal('show');

        } else {
            toastr.error('Error al cargar los datos del item');
        }
    } catch (error) {
        console.error('Error al editar item:', error);
        toastr.error('Error al cargar el item para edición');
    }
}

/**
 * Eliminar un item
 */
async function eliminarSubitem(subitemId, itemId) {
    Swal.fire({
        title: '¿Está seguro de eliminar este Item?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (!result.value) {
            return;
        }
        try {
            const response = await fetch(`/admin/admin.cotizaciones.items.destroySubitem/${subitemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const resultData = await response.json();

            if (resultData.success) {
                // 1. Eliminar la fila directamente del DOM
                const fila = document.querySelector(`tr[data-subitem-id="${subitemId}"]`);
                if (fila) {
                    fila.remove();
                }

                // 2. Actualizar itemsCotizacion en memoria
                const itemIndex = itemsCotizacion.findIndex(i => i.id === itemId);
                if (itemIndex !== -1 && itemsCotizacion[itemIndex].subitems) {
                    itemsCotizacion[itemIndex].subitems = itemsCotizacion[itemIndex].subitems.filter(s => s.id !== subitemId);
                    const restantes = itemsCotizacion[itemIndex].subitems.length;

                    // 3. Actualizar el contador del botón toggle
                    const toggleBtn = document.querySelector(`button.toggle-subitems[data-item-id="${itemId}"]`);
                    if (restantes === 0) {
                        // No quedan subitems: reemplazar todo el contenedor
                        const contenedor = toggleBtn ? toggleBtn.closest('.subitems-container') : null;
                        if (contenedor) {
                            contenedor.outerHTML = `
                                <div class="subitems-container">
                                    <button class="btn btn-sm btn-outline-secondary" data-item-id="${itemId}" type="button" disabled>
                                        <i class="fas fa-cube"></i> Sin Items
                                    </button>
                                </div>`;
                        }
                    } else if (toggleBtn) {
                        // Actualizar el contador en el botón
                        toggleBtn.innerHTML = `<i class="fas fa-eye-slash" id="icon_${itemId}"></i> Ver Items (${restantes})`;
                    }
                }

                toastr.success('Item eliminado exitosamente');
            } else {
                toastr.error('Error al eliminar el item: ' + resultData.message);
            }
        } catch (error) {
            console.error('Error al eliminar item:', error);
        }
    });
}

// ========================================
// FUNCIONES PARA PRODUCTOS Y SALARIOS
// ========================================

// Variables globales para productos y salarios
let productosDisponibles = [];
let cargosDisponibles = [];
let productosSeleccionados = [];
let personalAsignado = [];

/**
 * Inicializar funcionalidad de productos y salarios
 */
function initProductosYSalarios() {
    try {
        setupEventListenersProductos();
        cargarProductosDisponibles();
        agregarEstilosVisuales();
    } catch (error) {
        console.error('Error en initProductosYSalarios:', error);
    }
}

/**
 * Agregar estilos CSS para animaciones y visualizaciones
 */
function agregarEstilosVisuales() {
    if (document.getElementById('estilosProductosVisuales')) return; // Ya agregados

    const estilos = document.createElement('style');
    estilos.id = 'estilosProductosVisuales';
    estilos.innerHTML = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .item-agregado-reciente {
            animation: fadeInUp 0.6s ease-out;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
            border-left: 4px solid #28a745;
        }

        .badge-nuevo {
            animation: pulse 2s infinite;
        }

        .toast-productos {
            border-left: 4px solid #007bff;
        }

        .toast-productos .toast-title {
            color: #007bff;
            font-weight: 600;
        }

        .producto-destacado {
            background: linear-gradient(45deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
            border: 1px solid rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .producto-destacado:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
    `;

    document.head.appendChild(estilos);
}

/**
 * Cargar productos disponibles desde el servidor
 */
async function cargarProductosDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.productos.obtener');
        const data = await response.json();

        if (data.success) {
            productosDisponibles = data.data || [];
            renderizarTablaProductos();
        } else {
            console.error('Error al cargar productos:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}


/**
 * Renderizar tabla de productos disponibles
 */
function renderizarTablaProductos() {
    // En lugar de buscar tbodyProductosDisponibles, trabajar con items propios existentes
    // Verificar si hay items propios cargados
    const itemsPropiosContainer = document.getElementById('itemsPropiosContainer');
    if (itemsPropiosContainer) {
        // Los items propios ya están renderizados por el sistema existente
        return;
    }

    // Si no hay container de items propios, intentar con otras tablas disponibles
    const tablaAlternativas = [
        'tbody_items',
        'tbodyProductosSeleccionados',
        'tbodyElementosAQuitar'
    ];

    let tablaEncontrada = false;
    for (const tablaId of tablaAlternativas) {
        const tabla = document.getElementById(tablaId);
        if (tabla) {
            tablaEncontrada = true;
            break;
        }
    }

    if (!tablaEncontrada) {
        console.warn('No se encontraron tablas disponibles para productos');
        // Crear productos seleccionados desde items propios si existen
        crearProductosDesdeItemsPropios();
    }
}

/**
 * Crear botones de selección rápida para productos de ejemplo
 */
function crearBotonesSeleccionRapida() {
    // Por ahora, simplemente crear una función vací­a para evitar errores
    // Esta funcionalidad se puede implementar más adelante si es necesaria
}

/**
 * Toggle selección rápida de producto
 */
function toggleProductoSelectionRapida(productoId) {
    const isSelected = productosSeleccionados.find(p => p.id == productoId); // Usar == para comparar
    const boton = document.getElementById(`btnProducto_${productoId}`);


    if (isSelected) {
        // Quitar producto
        const lengthAntes = productosSeleccionados.length;
        productosSeleccionados = productosSeleccionados.filter(p => p.id != productoId); // Usar != para comparar

        if (boton) {
            boton.className = 'btn btn-sm w-100 btn-outline-primary';
            boton.innerHTML = '<i class="fas fa-plus-circle"></i> Agregar';
        }
    } else {
        // Agregar producto
        const producto = productosDisponibles.find(p => p.id == productoId); // Usar == para comparar

        if (producto) {
            const nuevoProducto = {
                id: producto.id,
                nombre: producto.nombre,
                codigo: producto.codigo,
                precio: producto.precio,
                cantidad: 1,
                total: producto.precio,
                unidad: producto.unidad,
                categoria: producto.categoria,
                esDelAcordeon: false,
                parametrizacion_id: (producto.tipo === 'parametrizacion' || producto.fuente === 'parametrizacion_costos') ? producto.id : (producto.parametrizacion_id || null),
                item_propio_id: (producto.tipo === 'parametrizacion' || producto.fuente === 'parametrizacion_costos') ? null : (producto.item_propio_id || null)
            };

            const lengthAntes = productosSeleccionados.length;
            productosSeleccionados.push(nuevoProducto);

            if (boton) {
                boton.className = 'btn btn-sm w-100 btn-success';
                boton.innerHTML = '<i class="fas fa-check-circle"></i> Seleccionado';
            }
        } else {
            console.error('Producto no encontrado en productosDisponibles para ID:', productoId);
        }
    }

    // Actualizar tabla de productos seleccionados
    actualizarTablaProductosSeleccionados();
    calcularTotales();

    // Debug final

    // Actualizar contador debug si existe
    const productosCount = document.getElementById('productosCount');
    if (productosCount) {
        productosCount.textContent = productosSeleccionados.length;
    }
}

/**
 * Crear productos seleccionados desde items propios cargados
 */
function crearProductosDesdeItemsPropios() {
    // Buscar items propios en el DOM
    const itemsElements = document.querySelectorAll('.item-propio-card, .item-card, .list-group-item');


    itemsElements.forEach((element, index) => {
        // Extraer información del item desde el DOM
        const nombreElement = element.querySelector('.item-nombre, h6, .font-weight-bold');
        const precioElement = element.querySelector('.item-precio, .text-success, .badge-success');
        const codigoElement = element.querySelector('.item-codigo, .text-muted, small');

        if (nombreElement) {
            const nombre = nombreElement.textContent.trim();
            const codigo = codigoElement ? codigoElement.textContent.trim() : `ITEM-${index + 1}`;

            // Obtener precio - manejar casos especiales donde el precio mostrado es un total calculado
            let precio = 0;
            if (precioElement) {
                const precioText = precioElement.textContent;

                // Método simplificado: buscar diferentes patrones paso a paso
                let precioUnitario = null;

                // PATRÓN 1: Buscar "$X x Y" donde X es unitario
                const patron1 = precioText.match(/\$?([0-9.,]+)\s*x\s*([0-9.,]+)/i);
                if (patron1) {
                    const numero1 = parseFloat(patron1[1].replace(/,/g, '')) || 0;
                    const numero2 = parseFloat(patron1[2].replace(/,/g, '')) || 0;

                    // Si numero1 * numero2 parece ser el total mostrado, entonces numero1 es unitario
                    const totalCalculado = numero1 * numero2;

                    // Buscar si aparece el total en alguna parte del texto
                    const textoSinFormato = precioText.replace(/[^0-9]/g, '');
                    const totalEnTexto = totalCalculado.toString().replace(/[^0-9]/g, '');

                    if (textoSinFormato.includes(totalEnTexto)) {
                        precioUnitario = numero1;
                    } else {
                        // Si no encuentra el total, tomar el menor número como unitario
                        precioUnitario = Math.min(numero1, numero2);
                    }
                }

                // PATRÓN 2: Buscar "($X c/u)" o similar
                if (!precioUnitario) {
                    const patron2 = precioText.match(/\(\$?([0-9.,]+)\s*(?:c\/u|cada|unitario)\)/i);
                    if (patron2) {
                        precioUnitario = parseFloat(patron2[1].replace(/,/g, '')) || 0;
                    }
                }

                // PATRÓN 3: Solo números con $
                if (!precioUnitario) {
                    const patron3 = precioText.match(/\$([0-9.,]+)/i);
                    if (patron3) {
                        const soloNumero = parseFloat(patron3[1].replace(/,/g, '')) || 0;

                        // Si hay "x" en el texto, es probable que sea un total
                        if (precioText.toLowerCase().includes('x') && precioText.match(/x\s*([0-9]+)/)) {
                            const cantidad = parseInt(precioText.match(/x\s*([0-9]+)/)[1]) || 1;
                            precioUnitario = cantidad > 1 ? soloNumero / cantidad : soloNumero;
                        } else {
                            precioUnitario = soloNumero;
                        }
                    }
                }

                // Resultado final
                precio = precioUnitario || 0;
            }

            // Verificar si ya existe en productos disponibles
            if (!productosDisponibles.find(p => p.nombre === nombre)) {
                productosDisponibles.push({
                    id: Date.now() + index,
                    codigo: codigo,
                    nombre: nombre,
                    precio: precio,
                    stock: 100, // Stock por defecto
                    categoria: 'Items Propios',
                    unidad: 'Unidad',
                    descripcion: `Item propio: ${nombre}`,
                    source: 'dom'
                });
            }
        }
    });

}

/**
 * Toggle selección de producto individual (modificado para trabajar con items existentes)
 */
function toggleProductoSelection(productoId) {

    // Buscar checkbox si existe
    const checkbox = document.querySelector(`.producto-checkbox[data-id="${productoId}"], .item-checkbox[data-id="${productoId}"], input[data-id="${productoId}"]`);

    if (checkbox && checkbox.checked) {
        seleccionarProducto(productoId);
    } else if (checkbox) {
        deseleccionarProducto(productoId);
    } else {
        // Si no hay checkbox, asumir selección directa
        const yaSeleccionado = productosSeleccionados.find(p => p.id === productoId);
        if (yaSeleccionado) {
            deseleccionarProducto(productoId);
        } else {
            seleccionarProducto(productoId);
        }
    }

}

/**
 * Seleccionar producto desde items propios o acordeón
 */
function seleccionarProductoDesdeItem(itemElement) {
    try {
        const nombreElement = itemElement.querySelector('.item-nombre, h6, .font-weight-bold');
        const precioElement = itemElement.querySelector('.item-precio, .text-success, .badge-success');
        const codigoElement = itemElement.querySelector('.item-codigo, .text-muted, small');

        if (!nombreElement) {
            console.warn('No se pudo extraer información del item');
            return;
        }

        const nombre = nombreElement.textContent.trim();
        const codigo = codigoElement ? codigoElement.textContent.trim() : `ITEM-${Date.now()}`;

        // Obtener precio - manejar casos especiales donde el precio mostrado es un total calculado
        let precio = 0;
        if (precioElement) {
            const precioText = precioElement.textContent;

            // Método simplificado: buscar diferentes patrones paso a paso
            let precioUnitario = null;

            // PATRÓN 1: Buscar "$X x Y" donde X es unitario
            const patron1 = precioText.match(/\$?([0-9.,]+)\s*x\s*([0-9.,]+)/i);
            if (patron1) {
                const numero1 = parseFloat(patron1[1].replace(/,/g, '')) || 0;
                const numero2 = parseFloat(patron1[2].replace(/,/g, '')) || 0;

                // Si numero1 * numero2 parece ser el total mostrado, entonces numero1 es unitario
                const totalCalculado = numero1 * numero2;


                // Buscar si aparece el total en alguna parte del texto
                const textoSinFormato = precioText.replace(/[^0-9]/g, '');
                const totalEnTexto = totalCalculado.toString().replace(/[^0-9]/g, '');

                if (textoSinFormato.includes(totalEnTexto)) {
                    precioUnitario = numero1;
                } else {
                    // Si no encuentra el total, tomar el menor número como unitario
                    precioUnitario = Math.min(numero1, numero2);
                }
            }

            // PATRÓN 2: Buscar "($X c/u)" o similar
            if (!precioUnitario) {
                const patron2 = precioText.match(/\(\$?([0-9.,]+)\s*(?:c\/u|cada|unitario)\)/i);
                if (patron2) {
                    precioUnitario = parseFloat(patron2[1].replace(/,/g, '')) || 0;
                }
            }

            // PATRÓN 3: Solo números con $
            if (!precioUnitario) {
                const patron3 = precioText.match(/\$([0-9.,]+)/i);
                if (patron3) {
                    const soloNumero = parseFloat(patron3[1].replace(/,/g, '')) || 0;

                    // Si hay "x" en el texto, es probable que sea un total
                    if (precioText.toLowerCase().includes('x') && precioText.match(/x\s*([0-9]+)/)) {
                        const cantidad = parseInt(precioText.match(/x\s*([0-9]+)/)[1]) || 1;
                        precioUnitario = cantidad > 1 ? soloNumero / cantidad : soloNumero;
                    } else {
                        precioUnitario = soloNumero;
                    }
                }
            }

            // Resultado final
            precio = precioUnitario || 0;
        }

        // Verificar si ya está seleccionado
        const yaSeleccionado = productosSeleccionados.find(p => p.nombre === nombre);
        if (yaSeleccionado) {
            return;
        }

        // Agregar a productos seleccionados
        const producto = {
            id: Date.now() + Math.random(),
            nombre: nombre,
            codigo: codigo,
            precio: precio,
            cantidad: 1,
            total: precio,
            unidad: 'Unidad',
            categoria: 'Items Propios',
            source: 'manual'
        };

        productosSeleccionados.push(producto);

        // Actualizar UI
        actualizarTablaProductosSeleccionados();
        calcularTotales();

        // Marcar visualmente como seleccionado
        itemElement.classList.add('selected', 'border-primary');

    } catch (error) {
        console.error('Error al seleccionar producto desde item:', error);
    }
}

/**
 * Agregar evento click a items para selección manual
 */
function configurarSeleccionManualItems() {
    // Buscar todos los items clickeables
    const items = document.querySelectorAll('.item-propio-card, .item-card, .list-group-item');

    items.forEach(item => {
        // Evitar múltiples listeners
        if (!item.hasAttribute('data-click-configurado')) {
            item.style.cursor = 'pointer';
            item.setAttribute('data-click-configurado', 'true');

            item.addEventListener('click', function(e) {
                // Evitar activar si se hizo click en un input o button
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') {
                    return;
                }

                seleccionarProductoDesdeItem(this);
            });
        }
    });

}


function actualizarSelectAllProductos() {
    const selectAllCheckbox = document.getElementById('selectAllProductos');
    const productCheckboxes = document.querySelectorAll('.producto-checkbox');

    if (!selectAllCheckbox || productCheckboxes.length === 0) return;

    const checkedCount = document.querySelectorAll('.producto-checkbox:checked').length;

    if (checkedCount === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCount === productCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    }
}

/**
 * Configurar event listeners para productos y salarios
 */
function setupEventListenersProductos() {

    // Botón Agregar Productos
    const btnAgregarProductos = document.getElementById('agregarProductos');
    if (btnAgregarProductos) {
        btnAgregarProductos.addEventListener('click', function(e) {
            e.preventDefault();
            abrirModalAgregarProductos();
        });
    } else {
        console.error('Botón agregarProductos NO encontrado en el DOM');
    }

    // Botón Quitar Productos
    const btnQuitarProductos = document.getElementById('quitarProductos');
    if (btnQuitarProductos) {
        btnQuitarProductos.addEventListener('click', abrirModalQuitarProductos);
    }

    // Confirmación agregar productos
    const btnConfirmarAgregar = document.getElementById('confirmarAgregarProductos');
    if (btnConfirmarAgregar) {
        btnConfirmarAgregar.addEventListener('click', function(e) {
            e.preventDefault();
            confirmarAgregarProductos();
        });
    } else {
        console.error('Botón confirmarAgregarProductos NO encontrado en el DOM');
    }

    // Confirmación quitar productos
    const btnConfirmarQuitar = document.getElementById('confirmarQuitarProductos');
    if (btnConfirmarQuitar) {
        btnConfirmarQuitar.addEventListener('click', confirmarQuitarProductos);
    }

    // Búsqueda de productos
    const inputBuscar = document.getElementById('buscarProducto');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', filtrarProductos);
    }

    // Select all productos
    const selectAllProductos = document.getElementById('selectAllProductos');
    if (selectAllProductos) {
        selectAllProductos.addEventListener('change', toggleSelectAllProductos);
    }

    // Select all elementos a quitar
    const selectAllElementos = document.getElementById('selectAllElementos');
    if (selectAllElementos) {
        selectAllElementos.addEventListener('change', toggleSelectAllElementos);
    }

    // Form agregar personal
    const formAgregarPersonal = document.getElementById('formAgregarPersonal');
    if (formAgregarPersonal) {
        formAgregarPersonal.addEventListener('submit', agregarPersonal);
    }

    // Cambios en cargo para calcular salario
    const cargoSelect = document.getElementById('cargoSeleccionado');
    if (cargoSelect) {
        cargoSelect.addEventListener('change', calcularCostoPersonal);
    }

    // Cambios en inputs para recalcular
    const inputs = ['cantidadPersonal', 'diasLaborales', 'prestacionesSociales'];
    inputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', calcularCostoPersonal);
        }
    });
}

/**
 * Abrir modal agregar productos
 */
function abrirModalAgregarProductos() {
    // Cargar productos disponibles si no están cargados
    if (productosDisponibles.length != 0) {
        renderizarTablaProductos();
    }
    // Mostrar el modal usando jQuery (Bootstrap 4)
    $('#modalAgregarProductos').modal('show');
    // Pequeí±o delay para que el modal se renderice completamente
    setTimeout(() => {
        const elementoTotal = document.getElementById('totalGeneral');
        if (elementoTotal) {
            elementoTotal.textContent = `Total: $${calcularTotalGeneral().toFixed(2)}`;
        }
        // Agregar instrucciones visuales para el usuario
        mostrarInstruccionesSeleccion();
        configurarSeleccionManualItems();
        // Actualizar contador debug
        const productosCount = document.getElementById('productosCount');
        if (productosCount) {
            productosCount.textContent = productosSeleccionados.length;
        }
    }, 500);

}

/**
 * Mostrar instrucciones de selección al usuario
 */
function mostrarInstruccionesSeleccion() {
    // Buscar si hay un área de instrucciones
    let instruccionesElement = document.getElementById('instruccionesSeleccion');

    if (!instruccionesElement) {
        // Crear área de instrucciones si no existe
        const modalBody = document.querySelector('#modalAgregarProductos .modal-body');
        if (modalBody) {
            instruccionesElement = document.createElement('div');
            instruccionesElement.id = 'instruccionesSeleccion';
            instruccionesElement.className = 'alert alert-info alert-dismissible fade show';
            instruccionesElement.innerHTML = `
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
                <h6><i class="fas fa-info-circle"></i> Cómo seleccionar productos:</h6>
                <ul class="mb-0">
                    <li><strong>Items del acordeón:</strong> Use los botones de selección en la tabla de items existentes</li>
                    <li><strong>Items propios:</strong> Haga clic en cualquier item de la lista para agregarlo</li>
                    <li><strong>Productos seleccionados:</strong> Aparecerán en la tabla de la derecha donde puede ajustar cantidades y precios</li>
                </ul>
            `;
            modalBody.insertBefore(instruccionesElement, modalBody.firstChild);
        }
    }

}

/**
 * Abrir modal quitar productos
 */
function abrirModalQuitarProductos() {
    $('#modalQuitarProductos').modal('show');
    cargarElementosAQuitar();
}

/**
 * Usar items seleccionados del acordeón como productos
 */
function usarItemsSeleccionados() {
    const itemsSeleccionados = obtenerItemsSeleccionados();

    if (itemsSeleccionados.length === 0) {
        Swal.fire({
            title: 'Sin selección',
            text: 'Por favor seleccione al menos un item o subitem para agregar.',
            type: 'warning',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Convertir items seleccionados a productos
    itemsSeleccionados.forEach(item => {
        // Verificar si ya existe en productos seleccionados
        const yaExiste = productosSeleccionados.find(p =>
            p.source === 'acordeon' &&
            p.sourceId === item.id &&
            p.sourceType === item.tipo
        );

        if (!yaExiste) {
            productosSeleccionados.push({
                id: `acordeon_${item.tipo}_${item.id}_${Date.now()}`, // ID único
                source: 'acordeon', // Indicar que viene del acordeón
                sourceId: item.id,
                sourceType: item.tipo,
                sourceItemId: item.itemId, // Para subitems
                nombre: item.nombre,
                descripcion: item.descripcion,
                precio: 0, // Precio inicial, se puede editar
                cantidad: 1,
                total: 0,
                    categoria: item.tipo === 'item' ? 'Capitulación' : 'item',
                    parametrizacion_id: (item.tipo === 'parametrizacion' || item.fuente === 'parametrizacion_costos') ? item.id : null,
                    item_propio_id: (item.tipo === 'parametrizacion' || item.fuente === 'parametrizacion_costos') ? null : item.id
            });
        }
    });

    // Actualizar la tabla de productos seleccionados
    actualizarTablaProductosSeleccionados();
    calcularTotales();

    // Mostrar mensaje de confirmación
    Swal.fire({
        title: 'Items agregados',
        text: `Se agregaron ${itemsSeleccionados.length} items como productos. Puede ajustar precios y cantidades en la tabla de la derecha.`,
        type: 'success',
        confirmButtonText: 'Entendido'
    });

    // Limpiar selección
    const checkboxes = document.querySelectorAll('.item-select:checked');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    // Actualizar estado del checkbox "seleccionar todos"
    const selectAllCheckbox = document.getElementById('selectAllItems');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}

/**
 * Filtrar productos por búsqueda
 */
function filtrarProductos() {
    const termino = document.getElementById('buscarProducto').value.toLowerCase();
    const productosFiltrados = productosDisponibles.filter(producto =>
        producto.nombre.toLowerCase().includes(termino) ||
        producto.codigo.toLowerCase().includes(termino)
    );
}

/**
 * Toggle select all productos
 */
function toggleSelectAllProductos() {
    const selectAll = document.getElementById('selectAllProductos');
    const checkboxes = document.querySelectorAll('.producto-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        const productoId = parseInt(checkbox.dataset.id);

        if (selectAll.checked) {
            seleccionarProducto(productoId);
        } else {
            deseleccionarProducto(productoId);
        }
    });
}

/**
 * Seleccionar producto
 */
function seleccionarProducto(productoId) {
    const producto = productosDisponibles.find(p => p.id === productoId);
    const yaSeleccionado = productosSeleccionados.find(p => p.id === productoId);

    if (producto && !yaSeleccionado) {
        productosSeleccionados.push({
            ...producto,
            cantidad: 1,
            total: producto.precio,
            parametrizacion_id: (producto.tipo === 'parametrizacion' || producto.fuente === 'parametrizacion_costos') ? producto.id : (producto.parametrizacion_id || null),
            item_propio_id: (producto.tipo === 'parametrizacion' || producto.fuente === 'parametrizacion_costos') ? null : (producto.item_propio_id || producto.id || null)
        });
        actualizarTablaProductosSeleccionados();
        calcularTotales();
    }
}

/**
 * Deseleccionar producto
 */
function deseleccionarProducto(productoId) {
    productosSeleccionados = productosSeleccionados.filter(p => p.id !== productoId);
    actualizarTablaProductosSeleccionados();
    calcularTotales();
}

/**
 * Actualizar tabla de productos seleccionados
 */
function actualizarTablaProductosSeleccionados() {

    const tbody = document.getElementById('tbodyProductosSeleccionados');
    const noItemsRow = document.getElementById('noProductosSeleccionados');

    // Verificar si los elementos existen
    if (!tbody) {
        console.warn('Elemento tbodyProductosSeleccionados no encontrado');
        return;
    }
    if (!noItemsRow) {
        console.warn('Elemento noProductosSeleccionados no encontrado');
        return;
    }
    noItemsRow.style.display = 'none';


    if (productosSeleccionados.length === 0) {
        noItemsRow.style.display = 'table-row';
        return;
    }
    // Limpiar filas existentes excepto la de "no items"
    Array.from(tbody.children).forEach(row => {
        if (row.id !== 'noProductosSeleccionados') {
            row.remove();
        }
    });

    productosSeleccionados.forEach(producto => {
        const row = document.createElement('tr');
        const esDelAcordeon = producto.esDelAcordeon || false;
        const badgeColor = esDelAcordeon ? 'bg-info' : 'bg-secondary';
        const badgeText = esDelAcordeon ? (producto.categoria || 'Acordeón') : 'Producto';

        row.innerHTML = `
            <td>
                <div class="d-flex flex-column">
                    <div class="fw-bold">${producto.nombre}</div>
                    ${producto.item_parent ? `<small class="text-muted">De: ${producto.item_parent}</small>` : ''}
                    ${producto.descripcion ? `<small class="text-muted">${producto.descripcion}</small>` : ''}
                    <div class="mt-1">
                        <span class="badge ${badgeColor}">${badgeText}</span>
                        <small class="text-muted ms-2">
                            <i class="fas fa-tag"></i> ${producto.codigo || 'S/C'}
                        </small>
                    </div>
                </div>
            </td>
            <td>
                <div class="row">
                    <div class="col-4">
                        <label class="form-label-sm">Cantidad:</label>
                        <input type="number" class="form-control form-control-sm cantidad-producto"
                               value="${producto.cantidad}" min="1" data-id="${producto.id}"
                               onchange="actualizarCantidadProducto('${producto.id}', this.value)">
                    </div>
                    <div class="col-4">
                        <label class="form-label-sm">Precio:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control precio-producto"
                                   value="${producto.precio}" min="0" step="0.01" data-id="${producto.id}"
                                   onchange="actualizarPrecioProducto('${producto.id}', this.value)">
                        </div>
                    </div>
                    <div class="col-4">
                        <label class="form-label-sm">Total:</label>
                        <div class="fw-bold text-success">$${producto.total.toFixed(2)}</div>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="quitarProductoSeleccionado('${producto.id}')"
                        title="Quitar producto">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });

    // Calcular y actualizar el total general
    actualizarTotalGeneral();
}

/**
 * Actualizar el total general de productos seleccionados
 */
function actualizarTotalGeneral() {

    const totalGeneral = calcularTotalGeneral();
    productosSeleccionados.forEach(producto => {
        const productoTotal = producto.total || 0;
    });
    const elementoTotalGeneral = document.getElementById('totalGeneral');
    if (elementoTotalGeneral) {
        const totalFormateado = totalGeneral.toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        elementoTotalGeneral.textContent = totalFormateado;
    } else {
        console.error('❌ No se encontró el elemento #totalGeneral en el DOM');
    }
}

/**
 * Calcular total general de productos seleccionados (solo número)
 */
function calcularTotalGeneral() {
    return productosSeleccionados.reduce((sum, producto) => {
        // Usa total precalculado; si no existe, calcula por cantidad * precio
        const base = typeof producto.total === 'number'
            ? producto.total
            : (Number(producto.precio) || 0) * (Number(producto.cantidad) || 0);
        const bono         = Number(producto.bono)           || 0;
        const novedades    = Number(producto.novedadesTotal) || 0;
        return sum + base + bono + novedades;
    }, 0);
}

/**
 * Función de debug para diagnosticar problemas con el total general
 */
window.debugTotalGeneral = function() {
    console.log('=== 🔍 DEBUG TOTAL GENERAL ===');
    console.log('📦 Productos seleccionados:', productosSeleccionados);
    console.log('📊 Cantidad de productos:', productosSeleccionados.length);

    let totalCalculado = 0;
    productosSeleccionados.forEach((producto, index) => {
        const total = producto.total || 0;
        totalCalculado += total;
        console.log(`   ${index + 1}. ${producto.nombre}: cantidad=${producto.cantidad}, precio=${producto.precio}, total=${total}`);
    });

    console.log('💰 Total calculado manualmente:', totalCalculado);

    const elemento = document.getElementById('totalGeneral');
    console.log('🎯 Elemento #totalGeneral:');
    console.log('  - Existe:', !!elemento);
    if (elemento) {
        console.log('  - Contenido actual:', elemento.textContent);
        console.log('  - Elemento visible:', elemento.offsetParent !== null);
        console.log('  - Clase CSS:', elemento.className);
        console.log('  - Elemento HTML completo:', elemento.outerHTML);
    }

    console.log('🔄 Ejecutando actualización manual...');
    actualizarTotalGeneral();
    console.log('=== FIN DEBUG ===');
};

/**
 * Actualizar cantidad de producto
 */
function actualizarCantidadProducto(productoId, nuevaCantidad) {
    const producto = productosSeleccionados.find(p => p.id === productoId);
    if (producto) {
        producto.cantidad = parseInt(nuevaCantidad) || 1;
        producto.total = producto.precio * producto.cantidad;
        actualizarTablaProductosSeleccionados();
    } else {
        console.error('❌ Producto no encontrado para actualizar cantidad:', productoId);
    }
}

/**
 * Actualizar precio de producto
 */
function actualizarPrecioProducto(productoId, nuevoPrecio) {
    const producto = productosSeleccionados.find(p => p.id === productoId);
    if (producto) {
        producto.precio = parseFloat(nuevoPrecio) || 0;
        producto.total = producto.precio * producto.cantidad;
        actualizarTablaProductosSeleccionados();
    } else {
        console.error('❌ Producto no encontrado para actualizar precio:', productoId);
    }
}

/**
 * Quitar producto seleccionado
 */
function quitarProductoSeleccionado(productoId) {
    productosSeleccionados = productosSeleccionados.filter(p => p.id !== productoId);
    actualizarTablaProductosSeleccionados();
    calcularTotales();
}


    // Desmarcar checkbox en tabla de productos disponibles
//     const checkbox = document.querySelector(`.producto-checkbox[data-id="${productoId}"]`);
//     if (checkbox) {
//         checkbox.checked = false;
//     }
// }

/**
 * Calcular costo de personal
 */
function calcularCostoPersonal() {
    const cargoSelect = document.getElementById('cargoSeleccionado');
    const cantidadInput = document.getElementById('cantidadPersonal');
    const diasInput = document.getElementById('diasLaborales');
    const prestacionesInput = document.getElementById('prestacionesSociales');
    const salarioBaseInput = document.getElementById('salarioBase');
    const totalCostoInput = document.getElementById('totalCostoMensual');

    if (!cargoSelect.value) {
        salarioBaseInput.value = '';
        totalCostoInput.value = '';
        return;
    }

    const salarioBase = parseFloat(cargoSelect.options[cargoSelect.selectedIndex].dataset.salario) || 0;
    const cantidad = parseInt(cantidadInput.value) || 1;
    const dias = parseInt(diasInput.value) || 30;
    const prestaciones = parseFloat(prestacionesInput.value) || 35;

    salarioBaseInput.value = salarioBase.toFixed(2);

    // Calcular costo con prestaciones sociales
    const salarioConPrestaciones = salarioBase * (1 + prestaciones / 100);
    const costoDiario = salarioConPrestaciones / 30;
    const costoTotal = costoDiario * dias * cantidad;

    totalCostoInput.value = costoTotal.toFixed(2);
}

/**
 * Agregar personal
 */
function agregarPersonal(event) {
    event.preventDefault();

    const cargoSelect = document.getElementById('cargoSeleccionado');
    const cantidadInput = document.getElementById('cantidadPersonal');
    const diasInput = document.getElementById('diasLaborales');
    const totalCostoInput = document.getElementById('totalCostoMensual');

    if (!cargoSelect.value || !totalCostoInput.value) {
        toastr.warning('Complete todos los campos requeridos');
        return;
    }

    const cargo = cargosDisponibles.find(c => c.id === parseInt(cargoSelect.value));
    const nuevoPersonal = {
        id: Date.now(), // ID temporal
        cargo_id: cargo.id,
        cargo_nombre: cargo.nombre,
        cantidad: parseInt(cantidadInput.value),
        dias: parseInt(diasInput.value),
        salario_base: parseFloat(document.getElementById('salarioBase').value),
        prestaciones: parseFloat(document.getElementById('prestacionesSociales').value),
        costo_total: parseFloat(totalCostoInput.value)
    };

    personalAsignado.push(nuevoPersonal);
    // actualizarTablaPersonalAsignado();
    calcularTotales();

    // Limpiar formulario
    document.getElementById('formAgregarPersonal').reset();
    document.getElementById('salarioBase').value = '';
    document.getElementById('totalCostoMensual').value = '';

    toastr.success('Personal agregado correctamente');
}

/**
 * Actualizar tabla de personal asignado
 */
// function actualizarTablaPersonalAsignado() {
//     const tbody = document.getElementById('tbodyPersonalAsignado');
//     const noItemsRow = document.getElementById('noPersonalAsignado');

//     if (personalAsignado.length === 0) {
//         noItemsRow.style.display = 'table-row';
//         return;
//     }

//     noItemsRow.style.display = 'none';

//     // Limpiar filas existentes excepto la de "no items"
//     Array.from(tbody.children).forEach(row => {
//         if (row.id !== 'noPersonalAsignado') {
//             row.remove();
//         }
//     });

//     personalAsignado.forEach(personal => {
//         const row = document.createElement('tr');
//         row.innerHTML = `
//             <td>${personal.cargo_nombre}</td>
//             <td>${personal.cantidad}</td>
//             <td>${personal.dias}</td>
//             <td class="font-weight-bold">$${personal.costo_total.toFixed(2)}</td>
//             <td>
//                 <button type="button" class="btn btn-sm btn-danger" onclick="quitarPersonalAsignado(${personal.id})">
//                     <i class="fas fa-times"></i>
//                 </button>
//             </td>
//         `;

//         tbody.appendChild(row);
//     });
// }

/**
 * Quitar personal asignado
 */
function quitarPersonalAsignado(personalId) {
    personalAsignado = personalAsignado.filter(p => p.id !== personalId);
    // actualizarTablaPersonalAsignado();
    calcularTotales();
    toastr.info('Personal eliminado');
}

// ========================================
// FUNCIONES PARA MODAL DE SALARIOS MEJORADO
// ========================================

/**
 * Toggle seleccionar todas las categorí­as
 */
function toggleAllCategorias() {
    const selectAll = document.getElementById('selectAllCategorias');
    const checkboxes = document.querySelectorAll('#listaCategorias .categoria-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

/**
 * Cambiar tipo de costo y mostrar/ocultar campos correspondientes (formulario general)
 */
function cambiarTipoCostoFormulario() {
    const tipoCosto = document.getElementById('tipoCosto').value;
    const campoHoras = document.getElementById('campoHoras');
    const camposDiurnosNocturnos = document.getElementById('camposDiurnosNocturnos');

    // Ocultar todos los campos primero
    campoHoras.style.display = 'none';
    camposDiurnosNocturnos.style.display = 'none';

    // Mostrar campos según el tipo de costo
    switch (tipoCosto) {
        case 'COSTO_HORA':
            campoHoras.style.display = 'block';
            // Limpiar campos de dí­as
            limpiarCamposDias();
            break;

        case 'COSTO_DIA':
        case 'COSTO_MES':
            camposDiurnosNocturnos.style.display = 'block';
            // Limpiar campo de horas
            document.getElementById('cantidadHoras').value = '';
            break;
    }

    // Limpiar campos de dominicales
    ocultarCamposDominicales();
}

/**
 * Limpiar campos de dí­as
 */
function limpiarCamposDias() {
    document.getElementById('diasDiurnos').value = '0';
    document.getElementById('diasNocturnos').value = '0';
    document.getElementById('diasRemuneradosDiurnos').value = '';
    document.getElementById('diasRemuneradosNocturnos').value = '';
    document.getElementById('dominicalesDiurnos').value = '';
    document.getElementById('dominicalesNocturnos').value = '';

    // Ocultar campos
    document.getElementById('campoDiasRemuneradosD').style.display = 'none';
    document.getElementById('campoDiasRemuneradosN').style.display = 'none';
    document.getElementById('camposDominicales').style.display = 'none';

    // Resetear radio buttons
    document.getElementById('dominicalesNo').checked = true;
}

/**
 * Mostrar campo de dí­as remunerados según tipo
 */
function mostrarCampoRemunerados(tipo) {
    const campo = document.getElementById(`${tipo === 'diurnos' ? 'diasDiurnos' : 'diasNocturnos'}`);
    const campoDias = document.getElementById(`campoDiasRemunerados${tipo === 'diurnos' ? 'D' : 'N'}`);

    if (parseInt(campo.value) > 0) {
        campoDias.style.display = 'block';
    } else {
        campoDias.style.display = 'none';
        document.getElementById(`diasRemunerados${tipo === 'diurnos' ? 'Diurnos' : 'Nocturnos'}`).value = '';
    }

    // Actualizar campos dominicales disponibles
    actualizarCamposDominicales();
}

/**
 * Mostrar campos de dominicales
 */
function mostrarCamposDominicales() {
    document.getElementById('camposDominicales').style.display = 'block';
    actualizarCamposDominicales();
}

/**
 * Ocultar campos de dominicales
 */
function ocultarCamposDominicales() {
    document.getElementById('camposDominicales').style.display = 'none';
    document.getElementById('dominicalesDiurnos').value = '';
    document.getElementById('dominicalesNocturnos').value = '';
    document.getElementById('dominicalDiurno').style.display = 'none';
    document.getElementById('dominicalNocturno').style.display = 'none';
}

/**
 * Actualizar campos de dominicales según dí­as habilitados (formulario general)
 */
function actualizarCamposDominicalesFormulario() {
    const diasDiurnos = parseInt(document.getElementById('diasDiurnos').value) || 0;
    const diasNocturnos = parseInt(document.getElementById('diasNocturnos').value) || 0;
    const dominicalDiurno = document.getElementById('dominicalDiurno');
    const dominicalNocturno = document.getElementById('dominicalNocturno');

    // Solo mostrar campo dominical diurno si hay dí­as diurnos
    if (diasDiurnos > 0) {
        dominicalDiurno.style.display = 'block';
    } else {
        dominicalDiurno.style.display = 'none';
        document.getElementById('dominicalesDiurnos').value = '';
    }

    // Solo mostrar campo dominical nocturno si hay dí­as nocturnos
    if (diasNocturnos > 0) {
        dominicalNocturno.style.display = 'block';
    } else {
        dominicalNocturno.style.display = 'none';
        document.getElementById('dominicalesNocturnos').value = '';
    }
}

/**
 * Calcular total con margen
 */
function calcularTotalConMargen() {
    const margen = parseFloat(document.getElementById('margenUtilidad').value) || 0;
    const administracion = parseFloat(document.getElementById('porcentajeAdministracion').value) || 0;
    const imprevistos = parseFloat(document.getElementById('porcentajeImprevistos').value) || 0;

    // Por ahora calcular un total base simulado
    const totalBase = 1000; // Esto se calcularí­a con los datos reales
    const totalConMargenes = totalBase * (1 + (margen + administracion + imprevistos) / 100);

    document.getElementById('totalCosto').value = totalConMargenes.toFixed(2);
}

/**
 * Mostrar tabla de detalles
 */
function mostrarTablaDetalles() {
    $('#modalDetallesPrecios').modal('show');
    actualizarTablaDetalles();
}

/**
 * Actualizar tabla de detalles de precios
 */
function actualizarTablaDetalles() {
    const tbody = document.getElementById('tablaDetallesPrecios');
    tbody.innerHTML = '';

    // Obtener categorí­as seleccionadas
    const categoriasSeleccionadas = obtenerCategoriasSeleccionadas();
    const tipoCosto = document.getElementById('tipoCosto').value;
    const margen = parseFloat(document.getElementById('margenUtilidad').value) || 0;

    if (categoriasSeleccionadas.length === 0 || !tipoCosto) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Seleccione categorí­as y tipo de costo para ver detalles
                </td>
            </tr>
        `;
        return;
    }

    // Simular datos de detalles
    categoriasSeleccionadas.forEach((categoria, index) => {
        const costoBase = 1500 + (index * 200); // Costo simulado
        const cantidad = parseInt(document.getElementById('cantidadPersonal').value) || 1;
        const diasHoras = obtenerDiasHoras();
        const subtotal = costoBase * cantidad * diasHoras;
        const totalConMargen = subtotal * (1 + margen / 100);

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${categoria.nombre}</td>
            <td>${tipoCosto.replace('_', ' ')}</td>
            <td>$${costoBase.toFixed(2)}</td>
            <td>${cantidad}</td>
            <td>${diasHoras}</td>
            <td>$${subtotal.toFixed(2)}</td>
            <td>${margen}%</td>
            <td class="font-weight-bold">$${totalConMargen.toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });
}

/**
 * Obtener categorí­as seleccionadas
 */
function obtenerCategoriasSeleccionadas() {
    const checkboxes = document.querySelectorAll('#listaCategorias .categoria-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => ({
        id: checkbox.value,
        nombre: checkbox.nextElementSibling.textContent
    }));
}

/**
 * Obtener dí­as/horas según tipo de costo
 */
function obtenerDiasHoras() {
    const tipoCosto = document.getElementById('tipoCosto').value;

    switch (tipoCosto) {
        case 'COSTO_HORA':
            return parseInt(document.getElementById('cantidadHoras').value) || 0;

        case 'COSTO_DIA':
            const diasDiurnos = parseInt(document.getElementById('diasDiurnos').value) || 0;
            const diasNocturnos = parseInt(document.getElementById('diasNocturnos').value) || 0;
            return diasDiurnos + diasNocturnos;

        case 'COSTO_MES':
            return 30; // Dí­as del mes

        default:
            return 0;
    }
}

/**
 * Cargar categorí­as disponibles (simulado)
 */
function cargarCategoriasDisponibles() {
    // Datos simulados - estos vendrí­an de la base de datos
    const categorias = [
        { id: 1, nombre: 'Ingenierí­a' },
        { id: 2, nombre: 'Construcción' },
        { id: 3, nombre: 'Supervisión' },
        { id: 4, nombre: 'Mano de Obra General' },
        { id: 5, nombre: 'Mano de Obra Especializada' },
        { id: 6, nombre: 'Operación de Maquinaria' }
    ];

    const listaCategorias = document.getElementById('listaCategorias');
    listaCategorias.innerHTML = '';

    categorias.forEach(categoria => {
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = `
            <input class="form-check-input categoria-checkbox" type="checkbox" value="${categoria.id}" id="categoria_${categoria.id}">
            <label class="form-check-label" for="categoria_${categoria.id}">
                ${categoria.nombre}
            </label>
        `;
        listaCategorias.appendChild(div);
    });
}

/**
 * Función principal para confirmar agregar productos
 */
function confirmarAgregarProductos() {

    // Verificar si hay productos seleccionados en la tabla de productos seleccionados
    const productosEnTabla = document.querySelectorAll('#tbodyProductosSeleccionados tr[data-item-id]');
    if (productosEnTabla.length > 0) {
        // Procesar productos de la tabla
        procesarProductosSeleccionadosDeTabla();
        return;
    }

    toastr.warning('No hay elementos seleccionados para agregar. Seleccione items del acordeón o configure categorí­as y costos.');
}

/**
 * Procesar productos seleccionados de la tabla
 */
function procesarProductosSeleccionadosDeTabla() {
    const productosEnTabla = document.querySelectorAll('#tbodyProductosSeleccionados tr[data-item-id]');

    if (productosEnTabla.length === 0) {
        toastr.warning('No hay productos seleccionados en la tabla');
        return;
    }

    // Procesar productos directamente sin confirmación adicional
    toastr.success(`${productosEnTabla.length} producto(s) agregado(s) correctamente desde la tabla`);

    // Enviar productos a la base de datos
    enviarProductosTablaABaseDatos(productosEnTabla).then(() => {
        // Limpiar la tabla de productos seleccionados
        const tbody = document.getElementById('tbodyProductosSeleccionados');
        if (tbody) {
            tbody.innerHTML = `
                <tr id="noProductosSeleccionados">
                    <td colspan="2" class="text-center text-muted">
                        No hay productos seleccionados
                    </td>
                </tr>
            `;
        }

        // Limpiar el array de productos seleccionados
        productosSeleccionados.length = 0;

        // Actualizar total general
        document.getElementById('totalGeneral').textContent = '0.00';

        // Cerrar modal
        const modalTabla = document.getElementById('modalAgregarProductos');
        if (modalTabla) {
            $('#modalAgregarProductos').modal('hide');
        }

        // Actualizar totales
        calcularTotales();
        actualizarTotalGeneral();;
    }).catch((error) => {
        console.error('Error al enviar productos:', error);
    });
}

/**
 * Enviar productos de la tabla a la base de datos
 */
async function enviarProductosTablaABaseDatos(productosEnTabla) {
    // console.log('🔍 Iniciando envío de productos desde array productosSeleccionados');
    // console.log('📊 Productos seleccionados encontrados:', productosSeleccionados.length);
    // console.log('🗃️ Contenido completo del array productosSeleccionados:', productosSeleccionados);

    const cotizacionId = document.getElementById('id')?.value || document.getElementById('cotizacion_id')?.value;
    if (!cotizacionId) {
        toastr.error('No se encontró el ID de la cotización');
        return Promise.reject('No hay cotización ID');
    }

    if (productosSeleccionados.length === 0) {
        toastr.error('No hay productos seleccionados para agregar');
        return Promise.reject('No hay productos seleccionados');
    }

    try {
        const productos = [];

        // Iterar sobre los productos ya configurados en productosSeleccionados
        productosSeleccionados.forEach((producto, index) => {
            // console.log(`📝 Procesando producto ${index + 1}:`, producto);

            const esCargoTabla = producto.tipo === 'cargo_tabla' || producto.flujo_tipo === 'nomina';
            const esParametrizacion = !esCargoTabla && (producto.tipo === 'parametrizacion' || producto.fuente === 'parametrizacion_costos');
            const rawParametrizacionId = producto.parametrizacion_id ?? (esParametrizacion ? producto.id : null);
            // Para cargo_tabla: item_propio_id siempre null; para parametrizacion: null; para ítem propio: su id
            const rawItemPropioId = esCargoTabla ? null : (producto.item_propio_id ?? (esParametrizacion ? null : (producto.id ?? null)));

            const parametrizacionId = rawParametrizacionId !== undefined && rawParametrizacionId !== null && rawParametrizacionId !== ''
                ? Number(rawParametrizacionId)
                : null;
            const itemPropioId = rawItemPropioId !== undefined && rawItemPropioId !== null && rawItemPropioId !== ''
                ? Number(rawItemPropioId)
                : null;

            // Mapear a estructura CotizacionProductoRequest usando datos ya capturados
            const productoMapeado = {
                cotizacion_id: parseInt(cotizacionId),
                cotizacion_item_id: producto.cotizacion_item_id || null,
                cotizacion_subitem_id: producto.cotizacion_subitem_id || null,
                item_propio_id: itemPropioId,
                parametrizacion_id: parametrizacionId,
                tabla_precios_id: producto.tabla_precios_id || null,
                producto_id: producto.id,
                nombre: producto.nombre || `Producto ${index + 1}`,
                descripcion: producto.descripcion || producto.observaciones || `${producto.categoria || ''} - ${producto.source || 'manual'}`,
                codigo: producto.codigo || `PROD-${Date.now()}-${index}`,
                unidad_medida: producto.unidad || 'UND',
                cantidad: parseFloat(producto.cantidad) || 1,
                valor_unitario: parseFloat(producto.precio) || 0,
                descuento_porcentaje: parseFloat(producto.descuento_porcentaje) || 0,
                descuento_valor: parseFloat(producto.descuento_valor) || 0,
                observaciones: producto.observaciones || `Categoría: ${producto.categoria || 'N/A'}. Origen: ${producto.source || 'manual'}`,
                orden: index + 1,
                active: 1,

                // Usar configuración de costos capturada dinámicamente
                categoria_id: producto.categoria_id || null,
                cargo_id: producto.cargo_id || null,
                tipo_costo: producto.configuracionCosto?.tipoCosto || null,

                // Costos específicos
                costo_dia: producto.configuracionCosto?.costoDia || null,
                costo_hora: producto.configuracionCosto?.costoHora || null,
                costo_unitario: producto.configuracionCosto?.costoUnitario || parseFloat(producto.precio) || 0,

                // Configuración de días
                dias_diurnos: producto.configuracionCosto?.diasDiurnos || null,
                dias_nocturnos: producto.configuracionCosto?.diasNocturnos || null,
                dias_remunerados_diurnos: producto.configuracionCosto?.diasRemuneradosDiurnos || null,
                dias_remunerados_nocturnos: producto.configuracionCosto?.diasRemuneradosNocturnos || null,
                dominicales_diurnos: producto.configuracionCosto?.dominicalesDiurnos || null,
                dominicales_nocturnos: producto.configuracionCosto?.dominicalesNocturnos || null,

                // Configuración de horas
                horas_diurnas: producto.configuracionCosto?.horasDiurnas || null,
                horas_remuneradas: producto.configuracionCosto?.horasRemuneradas || null,
                incluir_dominicales: producto.configuracionCosto?.incluirDominicales || 0,

                // Bono adicional (nómina)
                bono: parseFloat(producto.bono || producto.configuracionCosto?.bono || 0),

                // Novedades operativas NOMINA
                novedades: producto.configuracionCosto?.novedades || []
            };

            // console.log(`✅ Producto mapeado ${index + 1}:`, productoMapeado);
            productos.push(productoMapeado);
        });

        // console.log('📦 Total de productos preparados para envío:', productos.length);

        // Enviar productos uno por uno con manejo de errores individual
        let productosExitosos = 0;
        let productosConError = 0;

        for (const producto of productos) {
            try {
                // console.log('🚀 Enviando producto a la API:', producto);
                // console.log('🔑 parametrizacion_id:', producto.parametrizacion_id, '| item_propio_id:', producto.item_propio_id, '| tabla_precios_id:', producto.tabla_precios_id);

                const response = await fetch('/admin/admin.cotizaciones.productos.agregar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(producto)
                });

                // Verificar si la respuesta es válida
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error(`Error HTTP ${response.status} para producto "${producto.nombre}":`, errorText);
                    toastr.error(`Error HTTP ${response.status} al guardar "${producto.nombre}"`);
                    productosConError++;
                    continue;
                }

                const result = await response.json();

                if (result.success) {
                    // console.log(`✅ Producto "${producto.nombre}" guardado exitosamente:`, result);
                    productosExitosos++;
                } else {
                    console.error(`Error al guardar producto "${producto.nombre}":`, result.message, result.errors || '');
                    toastr.error(`Error al guardar "${producto.nombre}": ${result.message}`);
                    productosConError++;
                }

            } catch (error) {
                console.error(`Error de conexión al guardar producto "${producto.nombre}":`, error);
                toastr.error(`Error de conexión al guardar "${producto.nombre}"`);
                productosConError++;
            }
        }

        // Mostrar resumen final
        if (productosExitosos > 0) {
            toastr.success(`${productosExitosos} producto(s) guardado(s) correctamente en la base de datos`);
        }

        if (productosConError > 0) {
            toastr.warning(`${productosConError} producto(s) no pudieron guardarse`);
        }
        // Recargar productos guardados en la cotización
        await cargarProductosGuardados();
        return productosExitosos > 0 ? Promise.resolve() : Promise.reject('Ningún producto se guardó correctamente');

    } catch (error) {
        console.error('Error general al enviar productos a la BD:', error);
        toastr.error('Error general al guardar productos en la base de datos');
        return Promise.reject(error);
    }
}

/**
 * Enviar productos del sistema legacy a la base de datos
 */
async function enviarProductosLegacyABaseDatos(categoriasSeleccionadas, tipoCosto, totalCosto) {
    const cotizacionId = document.getElementById('id')?.value || document.getElementById('cotizacion_id')?.value;
    if (!cotizacionId) {
        toastr.error('No se encontró el ID de la cotización');
        return Promise.reject('No hay cotización ID');
    }

    try {
        // Estructura exacta según CotizacionProductoRequest
        const producto = {
            cotizacion_id: parseInt(cotizacionId),
            nombre: `${tipoCosto} - ${categoriasSeleccionadas.map(c => c.nombre || c).join(', ')}`,
            descripcion: `Producto configurado con categorí­as: ${categoriasSeleccionadas.map(c => c.nombre || c).join(', ')}`,
            codigo: `${tipoCosto.toUpperCase()}-${Date.now()}`,
            unidad_medida: 'UND',
            cantidad: 1,
            valor_unitario: parseFloat(totalCosto),
            descuento_porcentaje: 0,
            descuento_valor: 0,
            observaciones: `Tipo de costo: ${tipoCosto}. Configurado desde sistema legacy.`,
            active: 1,
            tipo_costo: tipoCosto
        };


        const response = await fetch('/admin/admin.cotizaciones.productos.agregar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(producto)
        });

        // Verificar si la respuesta es válida
        if (!response.ok) {
            const errorText = await response.text();
            console.error(`Error HTTP ${response.status} al guardar producto legacy:`, errorText);
            toastr.error(`Error HTTP ${response.status}: ${response.statusText}`);
            return Promise.reject(`Error HTTP ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            toastr.success('Producto configurado guardado en la base de datos correctamente');
            return Promise.resolve();
        } else {
            console.error('Error al guardar producto legacy:', result.message, result.errors || '');

            // Mostrar errores específicos si existen
            if (result.errors) {
                const errorsText = Object.values(result.errors).flat().join(', ');
                toastr.error(`Errores de validación: ${errorsText}`);
            } else {
                toastr.error(`Error al guardar: ${result.message}`);
            }
            return Promise.reject(result.message);
        }

    } catch (error) {
        console.error('Error de conexión al enviar producto legacy:', error);
        toastr.error('Error de conexión al guardar producto en la base de datos');
        return Promise.reject(error);
    }
}

/**
 * Limpiar formulario de salarios
 */
function limpiarFormularioSalarios() {
    document.getElementById('formAgregarPersonal').reset();
    document.getElementById('selectAllCategorias').checked = false;
    document.querySelectorAll('.categoria-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('tipoCosto').value = '';
    cambiarTipoCosto();
    document.getElementById('totalCosto').value = '';
}

/**
 * Cargar elementos para quitar
 */
function cargarElementosAQuitar() {
    // Datos simulados de elementos ya agregados
    const elementos = [
        { id: 1, tipo: 'Producto', descripcion: 'Cemento Portland', cantidad: 10, costo: 255.00 },
        { id: 2, tipo: 'Salario', descripcion: 'Ingeniero Civil', cantidad: 2, costo: 7000.00 },
        { id: 3, tipo: 'Producto', descripcion: 'Arena Fina', cantidad: 20, costo: 300.00 }
    ];

    const tbody = document.getElementById('tbodyElementosAQuitar');
    const noElementosRow = document.getElementById('noElementosAQuitar');

    if (elementos.length === 0) {
        noElementosRow.style.display = 'table-row';
        return;
    }

    noElementosRow.style.display = 'none';

    // Limpiar filas existentes
    Array.from(tbody.children).forEach(row => {
        if (row.id !== 'noElementosAQuitar') {
            row.remove();
        }
    });

    elementos.forEach(elemento => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="checkbox" class="elemento-checkbox" data-id="${elemento.id}" onchange="actualizarTotalAQuitar()">
            </td>
            <td>
                <span class="badge bg-${elemento.tipo === 'Producto' ? 'primary' : 'success'}">${elemento.tipo}</span>
            </td>
            <td>${elemento.descripcion}</td>
            <td>${elemento.cantidad}</td>
            <td>$${elemento.costo.toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });
}

/**
 * Toggle select all elementos
 */
function toggleSelectAllElementos() {
    const selectAll = document.getElementById('selectAllElementos');
    const checkboxes = document.querySelectorAll('.elemento-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    actualizarTotalAQuitar();
}

/**
 * Actualizar total a quitar
 */
function actualizarTotalAQuitar() {
    const checkboxes = document.querySelectorAll('.elemento-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const costo = parseFloat(row.cells[4].textContent.replace('$', '').replace(',', ''));
        total += costo;
    });

    document.getElementById('totalAQuitar').textContent = total.toFixed(2);

    // Habilitar/deshabilitar botón confirmar
    document.getElementById('confirmarQuitarProductos').disabled = checkboxes.length === 0;
}

/**
 * Confirmar quitar productos
 */
function confirmarQuitarProductos() {
    const checkboxes = document.querySelectorAll('.elemento-checkbox:checked');

    if (checkboxes.length === 0) {
        toastr.warning('No hay elementos seleccionados');
        return;
    }

    const cantidadElementos = checkboxes.length;

    // Aquí­ se enviarí­an los datos al backend
    toastr.success(`${cantidadElementos} elemento(s) eliminado(s) correctamente`);
    $('#modalQuitarProductos').modal('hide');

    // Recargar elementos
    cargarElementosAQuitar();
}

/**
 * Debugging: Verificar estado de productos seleccionados
 */
function debugProductosSeleccionados() {

    // Verificar checkboxes marcados
    const checkboxesMarcados = document.querySelectorAll('.producto-checkbox:checked, .item-checkbox:checked, input[type="checkbox"]:checked');
    checkboxesMarcados.forEach(checkbox => {
    });

    // Verificar items en tbody_items
    const tbody = document.getElementById('tbody_items');
    if (tbody) {
        const rows = tbody.querySelectorAll('tr:not([id="no_items_row_items"])');
    }
}

/**
 * Forzar agregar producto al array (función de emergencia)
 */
// function forzarAgregarProducto() {

//     try {
//         if (productosSeleccionados.length === 0) {

//             // Intentar obtener productos desde items del acordeón
//             const items = document.querySelectorAll('.list-group-item, .item-card, .accordion-item');

//             let productosAgregados = 0;
//             items.forEach((item, index) => {
//                 if (productosAgregados < 3) { // Limitar a 3 para testing
//                     const nombre = item.querySelector('.item-name, .card-title, h5, h6')?.textContent?.trim() || `Producto del Acordeón ${index + 1}`;
//                     const precio = Math.random() * 100 + 10; // Precio random para testing

//                     const nuevoProducto = {
//                         id: Date.now() + index,
//                         nombre: nombre,
//                         codigo: `ACC${index + 1}`,
//                         precio: precio,
//                         cantidad: 1,
//                         total: precio,
//                         unidad: 'Unidad',
//                         categoria: 'Del Acordeón',
//                         esDelAcordeon: true
//                     };

//                     productosSeleccionados.push(nuevoProducto);
//                     productosAgregados++;
//                 }
//             });

//             // Si no se encontraron items del acordeón, crear productos de ejemplo
//             if (productosAgregados === 0) {
//                 for (let i = 1; i <= 2; i++) {
//                     const nuevoProducto = {
//                         id: Date.now() + i,
//                         nombre: `Producto de Ejemplo ${i}`,
//                         codigo: `EJ${i.toString().padStart(3, '0')}`,
//                         precio: 25.50 * i,
//                         cantidad: 1,
//                         total: 25.50 * i,
//                         unidad: 'Unidad',
//                         categoria: 'Ejemplo',
//                         esDelAcordeon: false
//                     };

//                     productosSeleccionados.push(nuevoProducto);
//                     productosAgregados++;
//                 }
//             }


//             // Actualizar UI de forma segura
//             try {
//                 actualizarTablaProductosSeleccionados();
//                 calcularTotales();

//                 // Actualizar contador debug si existe
//                 const productosCount = document.getElementById('productosCount');
//                 if (productosCount) {
//                     productosCount.textContent = productosSeleccionados.length;
//                 }

//                 toastr.success(`Se agregaron ${productosAgregados} productos para testing`);

//             } catch (uiError) {
//                 console.error('Error actualizando UI:', uiError);
//                 toastr.warning(`Se agregaron ${productosAgregados} productos, pero hay problemas con la interfaz`);
//             }
//         } else {
//             toastr.info(`Ya hay ${productosSeleccionados.length} productos seleccionados`);
//         }
//     } catch (error) {
//         console.error('Error en forzarAgregarProducto:', error);
//         toastr.error('Error al forzar agregar productos: ' + error.message);
//     }

// }

/**
 * Mostrar notificación visual de productos agregados
 */
function mostrarProductosAgregados(cantidad) {
    // Crear modal de confirmación visual
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'modalProductosAgregados';
    modal.innerHTML = `
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i> Â¡í‰xito!
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-box-open fa-3x text-success"></i>
                    </div>
                    <h6>Productos Agregados</h6>
                    <p class="mb-2">Se agregaron exitosamente <strong>${cantidad}</strong> elemento(s) a la cotización.</p>
                    <p class="text-muted small">Puedes verlos en la tabla principal de items.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal" onclick="cerrarModalProductosAgregados()">
                        <i class="fas fa-thumbs-up"></i> Entendido
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Mostrar modal
    $('#modalProductosAgregados').modal('show');

    // Auto cerrar despuí©s de 3 segundos
    setTimeout(() => {
        $('#modalProductosAgregados').modal('hide');
    }, 3000);
}

/**
 * Cerrar modal de productos agregados
 */
function cerrarModalProductosAgregados() {
    const modal = document.getElementById('modalProductosAgregados');
    if (modal) {
        $('#modalProductosAgregados').modal('hide');
        setTimeout(() => {
            modal.remove();
        }, 500);
    }
}

/**
 * Mostrar resumen detallado de productos agregados
 */
function mostrarResumenProductosAgregados() {
    // Obtener los últimos items agregados
    const ultimosItems = itemsCotizacion.slice(-2); // íšltimos 2 items como ejemplo

    if (ultimosItems.length === 0) return;

    let resumenHTML = '<div class="list-group">';
    ultimosItems.forEach((item, index) => {
        const esProducto = item.tipo === 'producto';
        const icono = esProducto ? 'fas fa-cube' : 'fas fa-user';
        const colorBadge = esProducto ? 'bg-primary' : 'bg-success';

        resumenHTML += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <i class="${icono} text-${esProducto ? 'primary' : 'success'} me-2"></i>
                    <strong>${item.nombre}</strong>
                    ${esProducto ? `<br><small class="text-muted">Cantidad: ${item.cantidad} - Precio: $${item.precio_unitario}</small>` : ''}
                </div>
                <span class="badge ${colorBadge}">
                    $${item.valor_total ? parseFloat(item.valor_total).toFixed(2) : '0.00'}
                </span>
            </div>
        `;
    });
    resumenHTML += '</div>';

    // Crear notificación toastr personalizada
    toastr.options = {
        "closeButton": true,
        "timeOut": "8000",
        "extendedTimeOut": "2000",
        "positionClass": "toast-top-right",
        "progressBar": true
    };

    toastr.info(
        resumenHTML,
        '<i class="fas fa-clipboard-list"></i> Productos Agregados - Resumen',
        {
            "allowHtml": true,
            "closeButton": true,
            "timeOut": "8000"
        }
    );

    // Resetear opciones de toastr
    setTimeout(() => {
        toastr.options = {};
    }, 100);
}

/**
 * Filtrar items propios según texto de búsqueda
 */
function filtrarItemsPropios() {
    try {
        const searchInput = document.getElementById('buscarItemsPropios');
        const container = document.getElementById('itemsPropiosContainer');

        if (!searchInput || !container) {
            console.warn('Elementos de búsqueda no encontrados');
            return;
        }

        const searchTerm = searchInput.value.toLowerCase();
        const items = container.querySelectorAll('.item-propio-card');
        let itemsVisibles = 0;

        items.forEach(item => {
            try {
                const nombreElement = item.querySelector('.item-nombre');
                const codigoElement = item.querySelector('.item-codigo');
                const descripcionElement = item.querySelector('.item-descripcion');

                if (!nombreElement || !codigoElement || !descripcionElement) {
                    console.warn('Elementos internos del item no encontrados');
                    return;
                }

                const nombre = nombreElement.textContent.toLowerCase();
                const codigo = codigoElement.textContent.toLowerCase();
                const descripcion = descripcionElement.textContent.toLowerCase();

                // Buscar en nombre, código y descripción (que ahora incluye información de parametrización)
                const matches = nombre.includes(searchTerm) ||
                               codigo.includes(searchTerm) ||
                               descripcion.includes(searchTerm);

                if (matches) {
                    item.style.display = '';
                    itemsVisibles++;
                } else {
                    item.style.display = 'none';
                }
            } catch (itemError) {
                console.error('Error procesando item:', itemError);
            }
        });

        // Actualizar contador de items visibles
        const contadorVisibles = document.getElementById('itemsVisibles');
        if (contadorVisibles) {
            contadorVisibles.textContent = itemsVisibles;
        }

    } catch (error) {
        console.error('Error en filtrarItemsPropios:', error);
    }
}

/**
 * Limpiar búsqueda de items propios
 */
function limpiarBusquedaItemsPropios() {
    try {
        const searchInput = document.getElementById('buscarItemsPropios');
        if (!searchInput) {
            console.warn('Input de búsqueda no encontrado');
            return;
        }

        searchInput.value = '';

        // Mostrar todos los items
        const container = document.getElementById('itemsPropiosContainer');
        if (!container) {
            console.warn('Contenedor de items no encontrado');
            return;
        }

        const items = container.querySelectorAll('.item-propio-card');

        items.forEach(item => {
            item.style.display = '';
        });

        // Actualizar contador
        const contadorVisibles = document.getElementById('itemsVisibles');
        if (contadorVisibles) {
            contadorVisibles.textContent = items.length;
        }

        // Enfocar el input
        searchInput.focus();

    } catch (error) {
        console.error('Error en limpiarBusquedaItemsPropios:', error);
    }
}

// =============================================================================
// FUNCIONES PARA GESTIí“N DE PRODUCTOS GUARDADOS
// =============================================================================

/**
 * Cargar productos guardados de la cotización
 */
async function cargarProductosGuardados() {
    try {
        // Obtener ID de cotización
        let cotizacionId = document.getElementById('id')?.value;

        if (!cotizacionId) {
            const urlParams = new URLSearchParams(window.location.search);
            cotizacionId = urlParams.get('id');
        }
        if (!cotizacionId && typeof cotizacionGuardadaId !== 'undefined') {

            cotizacionId = cotizacionGuardadaId;
        }

        if (!cotizacionId) {
            return;
        }


        // Mostrar loading
        document.getElementById('loadingProductosGuardados').classList.remove('d-none');
        document.getElementById('emptyProductosGuardados').style.display = 'none';
        document.getElementById('tablaProductosGuardados').style.display = 'none';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('Token CSRF no encontrado. El usuario podrí­a no estar autenticado.');
            mostrarEstadoVacioProductos();
            throw new Error('Token CSRF no encontrado');
        }

        const response = await fetch('/admin/cotizaciones/productos/obtener?' + new URLSearchParams({
            cotizacion_id: cotizacionId
        }), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });


        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);

            // Si es HTML (página de login), significa que no está autenticado
            if (errorText.includes('<!DOCTYPE html>') || errorText.includes('<html')) {
                throw new Error('Sesión expirada. Por favor, recarga la página e inicia sesión nuevamente.');
            }

            throw new Error(`HTTP ${response.status}: ${response.statusText}. Response: ${errorText.substring(0, 200)}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text.substring(0, 500));
            throw new Error('El servidor no devolvió JSON válido');
        }

        const result = await response.json();

        // Ocultar loading
        document.getElementById('loadingProductosGuardados').classList.add('d-none');

        if (result.success && result.data && result.data.length > 0) {
            mostrarProductosGuardados(result.data);

            // Actualizar totales despuí©s de cargar productos
            await actualizarTotalesCompletos();
        } else {
            mostrarEstadoVacioProductos();

            // IMPORTANTE: Actualizar totales en backend cuando no hay productos
            await actualizarTotalesCompletos();
        }

    } catch (error) {
        console.error('Error al cargar productos guardados:', error);
        document.getElementById('loadingProductosGuardados').classList.add('d-none');
        mostrarEstadoVacioProductos();

        // IMPORTANTE: Actualizar totales en backend incluso en caso de error
        try {
            await actualizarTotalesCompletos();
        } catch (totalesError) {
            console.error('Error al actualizar totales después del error:', totalesError);
        }

        toastr.error('Error al cargar productos guardados: ' + error.message);
    }
}

/**
 * Mostrar productos guardados en la tabla
 */
function mostrarProductosGuardados(productos) {
    const tbody = document.getElementById('tbodyProductosGuardados');
    const tabla = document.getElementById('tablaProductosGuardados');
    const footer = document.getElementById('footerProductosGuardados');
    const contador = document.getElementById('contadorProductosGuardados');

    tbody.innerHTML = '';

    let subtotalInsumos = 0;
    let subtotalNomina = 0;

    productos.forEach((producto) => {
        const esNomina = producto.cargo_id !== null && producto.cargo_id !== undefined;
        const row = document.createElement('tr');
        row.setAttribute('data-producto-id', producto.id);
        if (esNomina) row.classList.add('table-warning');

        // Agregar atributos para identificar utilidades asociadas
        if (producto.categoria_id) {
            row.setAttribute('data-categoria-id', producto.categoria_id);
        }
        if (producto.item_propio_id) {
            row.setAttribute('data-item-propio-id', producto.item_propio_id);
        }

        const valorUnitario = parseFloat(producto.valor_unitario || 0);
        const cantidad = parseFloat(producto.cantidad || 0);
        const descuentoValor = parseFloat(producto.descuento_valor || 0);
        const descuentoPorcentaje = parseFloat(producto.descuento_porcentaje || 0);
        const total = parseFloat(producto.valor_total || 0);

        const novedades         = Array.isArray(producto.novedades_operativas) ? producto.novedades_operativas : [];
        const novedadesSubtotal = parseFloat(producto.novedades_subtotal || 0);
        const bono              = parseFloat(producto.bono || 0);
        const salarioBase       = valorUnitario * cantidad;

        const totalEfectivo = esNomina ? total + novedadesSubtotal : total;
        if (esNomina) subtotalNomina += totalEfectivo;
        else subtotalInsumos += total;

        // Calcular descuento total
        const descuentoTotal = descuentoValor + (valorUnitario * cantidad * descuentoPorcentaje / 100);

        const iconoProducto = esNomina
            ? '<i class="fas fa-user-tie text-warning"></i>'
            : '<i class="fas fa-cube text-primary"></i>';

        const badgeNomina = esNomina
            ? '<span class="badge badge-warning ml-1">Nómina</span>'
            : '';

        // ─── Desglose de novedades operativas ───
        let novedadesHtml = '';
        if (esNomina) {
            const novedadesRowsHtml = novedades.length > 0
                ? novedades.map(nov => `
                    <tr class="bg-light border-0" style="font-size:.82rem;">
                        <td class="pl-5 py-1 text-muted border-0">
                            <i class="fas fa-angle-right mr-1 text-warning"></i>${nov.nombre}
                        </td>
                        <td class="py-1 border-0 text-center">
                            <span class="badge badge-secondary">${parseFloat(nov.cantidad).toFixed(3)}</span>
                        </td>
                        <td class="py-1 border-0">
                            <span class="text-muted">$${parseFloat(nov.valor).toLocaleString('es-CO', {minimumFractionDigits: 2})}</span>
                        </td>
                        <td class="py-1 border-0"></td>
                        <td class="py-1 border-0 font-weight-bold text-warning">
                            +$${parseFloat(nov.subtotal).toLocaleString('es-CO', {minimumFractionDigits: 2})}
                        </td>
                        <td class="py-1 border-0"></td>
                    </tr>`).join('')
                : `<tr class="bg-light border-0" style="font-size:.82rem;">
                        <td colspan="6" class="pl-5 py-1 text-muted border-0">
                            <i class="fas fa-info-circle mr-1"></i>Sin novedades operativas
                        </td>
                    </tr>`;

            novedadesHtml = `
                <tr class="bg-light border-0" style="font-size:.82rem;" data-parent-producto="${producto.id}">
                    <td class="border-0 py-1"></td>
                    <td colspan="5" class="border-0 py-1">
                        <button class="btn btn-xs btn-link text-warning p-0"
                                style="font-size:.79rem;"
                                type="button"
                                data-toggle="collapse"
                                data-target="#novedades_${producto.id}"
                                aria-expanded="false">
                            <i class="fas fa-chevron-right mr-1" id="chevron_${producto.id}"></i>
                            Salario base:
                            <strong>$${salarioBase.toLocaleString('es-CO', {minimumFractionDigits: 2})}</strong>
                            ${bono > 0 ? `&nbsp;+&nbsp;<span class="text-info">Bono: <strong>+$${bono.toLocaleString('es-CO', {minimumFractionDigits: 2})}</strong></span>` : ''}
                            ${novedades.length > 0
                                ? `&nbsp;+&nbsp;<span class="text-warning">Novedades: <strong>+$${novedadesSubtotal.toLocaleString('es-CO', {minimumFractionDigits: 2})}</strong></span>
                                   &nbsp;(${novedades.length} novedad${novedades.length > 1 ? 'es' : ''})`
                                : '<span class="text-muted ml-1">Sin novedades operativas</span>'}
                        </button>
                    </td>
                </tr>
                <tr id="novedades_${producto.id}" class="collapse border-0">
                    <td colspan="6" class="p-0 border-0">
                        <table class="table table-sm mb-0" style="background:#fffdf0;">
                            <thead style="font-size:.78rem;">
                                <tr class="border-0">
                                    <th class="pl-5 py-1">Novedad</th>
                                    <th class="py-1 text-center">Cantidad</th>
                                    <th class="py-1">Valor Unit.</th>
                                    <th class="py-1"></th>
                                    <th class="py-1">Subtotal</th>
                                    <th class="py-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                ${novedadesRowsHtml}
                                <tr class="border-0" style="font-size:.82rem;">
                                    <td colspan="4" class="text-right py-1 border-0">
                                        <strong class="text-muted">Salario base:</strong>
                                    </td>
                                    <td class="py-1 border-0 font-weight-bold">
                                        $${salarioBase.toLocaleString('es-CO', {minimumFractionDigits: 2})}
                                    </td>
                                    <td class="border-0"></td>
                                </tr>
                                ${bono > 0 ? `
                                <tr class="border-0" style="font-size:.82rem;">
                                    <td colspan="4" class="text-right py-1 border-0">
                                        <strong class="text-info">Bono:</strong>
                                    </td>
                                    <td class="py-1 border-0 font-weight-bold text-info">
                                        +$${bono.toLocaleString('es-CO', {minimumFractionDigits: 2})}
                                    </td>
                                    <td class="border-0"></td>
                                </tr>` : ''}
                                <tr class="border-0" style="font-size:.82rem; background:#fff3cd;">
                                    <td colspan="4" class="text-right py-1 border-0">
                                        <strong>Total producto:</strong>
                                    </td>
                                    <td class="py-1 border-0 font-weight-bold text-success">
                                        $${totalEfectivo.toLocaleString('es-CO', {minimumFractionDigits: 2})}
                                    </td>
                                    <td class="border-0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>`;
        }

        row.innerHTML = `
            <td>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input producto-guardado-checkbox"
                           id="checkProducto${producto.id}" data-producto-id="${producto.id}"
                           onchange="actualizarContadorSeleccionadosGuardados()">
                    <label class="custom-control-label" for="checkProducto${producto.id}"></label>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="product-icon me-2">
                        ${iconoProducto}
                    </div>
                    <div>
                        <strong class="d-block">${producto.nombre}${badgeNomina}</strong>
                        <small class="text-muted">${producto.codigo || 'Sin código'}</small>
                        ${producto.descripcion ? `<br><small class="text-info">${producto.descripcion}</small>` : ''}
                        <br><span class="badge badge-secondary">${producto.unidad_medida}</span>
                        ${esNomina && novedades.length > 0 ? `<br><small class="text-warning font-weight-bold"><i class="fas fa-plus-circle mr-1"></i>${novedades.length} novedad${novedades.length > 1 ? 'es' : ''} operativa${novedades.length > 1 ? 's' : ''} incluida${novedades.length > 1 ? 's' : ''}</small>` : ''}
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-info">${cantidad.toFixed(3)}</span>
            </td>
            <td>
                <strong>$${valorUnitario.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</strong>
            </td>
            <td>
                ${descuentoTotal > 0 ?
                    `<span class="text-danger">-$${descuentoTotal.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</span>` +
                    (descuentoPorcentaje > 0 ? `<br><small>(${descuentoPorcentaje}%)</small>` : '')
                    : '<span class="text-muted">Sin descuento</span>'}
            </td>
            <td>
                <strong class="text-success">$${totalEfectivo.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</strong>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-danger" onclick="eliminarProducto(${producto.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.appendChild(row);

        // Insertar filas de desglose de novedades operativas (solo nómina)
        if (esNomina && novedadesHtml) {
            tbody.insertAdjacentHTML('beforeend', novedadesHtml);
            const collapseEl = document.getElementById(`novedades_${producto.id}`);
            if (collapseEl) {
                $(collapseEl).on('show.bs.collapse', function () {
                    const chevron = document.getElementById(`chevron_${producto.id}`);
                    if (chevron) { chevron.classList.remove('fa-chevron-right'); chevron.classList.add('fa-chevron-down'); }
                });
                $(collapseEl).on('hide.bs.collapse', function () {
                    const chevron = document.getElementById(`chevron_${producto.id}`);
                    if (chevron) { chevron.classList.remove('fa-chevron-down'); chevron.classList.add('fa-chevron-right'); }
                });
            }
        }
    });

    // Fila de subtotales por tipo (solo si hay ambos tipos)
    if (subtotalInsumos > 0 || subtotalNomina > 0) {
        const mostrarDesglose = subtotalInsumos > 0 && subtotalNomina > 0;
        if (mostrarDesglose) {
            const trDesglose = document.createElement('tr');
            trDesglose.classList.add('table-secondary', 'text-right');
            trDesglose.innerHTML = `
                <td colspan="5" class="text-right pr-3">
                    <small>
                        <i class="fas fa-cube text-primary mr-1"></i>Subtotal Insumos/Servicios:
                        <strong>$${subtotalInsumos.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</strong>
                        &nbsp;&nbsp;
                        <i class="fas fa-user-tie text-warning mr-1"></i>Subtotal Nómina:
                        <strong>$${subtotalNomina.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</strong>
                    </small>
                </td>
                <td colspan="2"></td>
            `;
            tbody.appendChild(trDesglose);
        }
    }

    // Mostrar tabla y footer
    tabla.style.display = 'block';
    footer.classList.remove('d-none');

    // Actualizar contador
    contador.textContent = productos.length;

    // Ocultar estado vací­o
    document.getElementById('emptyProductosGuardados').style.display = 'none';

}

/**
 * Mostrar estado vací­o cuando no hay productos
 */
function mostrarEstadoVacioProductos() {
    document.getElementById('emptyProductosGuardados').style.display = 'block';
    document.getElementById('tablaProductosGuardados').style.display = 'none';
    document.getElementById('footerProductosGuardados').classList.add('d-none');
    document.getElementById('contadorProductosGuardados').textContent = '0';

    // ACTUALIZACIÓN: Restablecer totales a cero cuando no hay productos
    resetearTotalesACero();
}

/**
 * Resetear totales a cero cuando no hay productos
 */
function resetearTotalesACero() {
    try {
        // Totales en cero
        const totalesVacios = {
            subtotal: 0,
            descuento: 0,
            impuestos: 0,
            total: 0
        };
        // Usar la función existente para actualizar la vista
        actualizarTotalesEnVista(totalesVacios);

    } catch (error) {
        console.error('❌ Error al resetear totales:', error);
    }
}

/**
 * Toggle select all productos guardados
 */
function toggleSelectAllProductosGuardados() {
    const selectAll = document.getElementById('selectAllProductosGuardados');
    const checkboxes = document.querySelectorAll('.producto-guardado-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    actualizarContadorSeleccionadosGuardados();
}

/**
 * Actualizar contador de productos seleccionados
 */
function actualizarContadorSeleccionadosGuardados() {
    const checkboxes = document.querySelectorAll('.producto-guardado-checkbox:checked');
    const contador = document.getElementById('contadorSeleccionadosGuardados');
    const btnEliminar = document.getElementById('btnEliminarSeleccionados');

    contador.textContent = checkboxes.length;
    btnEliminar.disabled = checkboxes.length === 0;

    // Actualizar estado del select all
    const selectAll = document.getElementById('selectAllProductosGuardados');
    const totalCheckboxes = document.querySelectorAll('.producto-guardado-checkbox');

    if (checkboxes.length === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (checkboxes.length === totalCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
        selectAll.checked = false;
    }
}

/**
 * Eliminar productos seleccionados
 */
async function eliminarProductosSeleccionados() {
    const checkboxes = document.querySelectorAll('.producto-guardado-checkbox:checked');

    if (checkboxes.length === 0) {
        toastr.warning('Debe seleccionar al menos un producto para eliminar');
        return;
    }

    const productosIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-producto-id'));

    const result = await Swal.fire({
        title: '¿Confirmar eliminación?',
        text: `Se eliminarán ${productosIds.length} producto(s) de la cotización`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí­, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.value) {
        try {
            const promesas = productosIds.map(id => eliminarProductoGuardado(id));
            await Promise.all(promesas);

            toastr.success(`${productosIds.length} producto(s) eliminado(s) exitosamente`);
            cargarProductosGuardados(); // Recargar lista
        } catch (error) {
            console.error('Error al eliminar productos:', error);
            toastr.error('Error al eliminar algunos productos: ' + error.message);
        }
    }
}


/**
 * Eliminar un producto especí­fico
 */
async function eliminarProducto(productoId) {

    // Verificar que tenemos un ID válido
    if (!productoId) {
        toastr.error('ID de producto no válido');
        return;
    }

    const result = await Swal.fire({
        title: '¿Confirmar eliminación?',
        text: 'Se eliminará este producto de la cotización',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí­, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.value) {
        try {

            // Mostrar loading
            Swal.fire({
                title: 'Eliminando producto...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const resultado = await eliminarProductoGuardado(productoId);

            // Cerrar loading
            Swal.close();

            if (resultado.success) {
                toastr.success(resultado.message || 'Producto eliminado éxitosamente');
                // Recargar lista
                await cargarProductosGuardados();

                // Mostrar totales actualizados si están disponibles
                if (resultado.data?.totales_actualizados) {
                }

                // Actualizar totales completos para asegurar consistencia
                await actualizarTotalesCompletos();
            } else {
                throw new Error(resultado.message || 'Error desconocido');
            }

        } catch (error) {
            console.error('Error completo:', error);
            Swal.close(); // Cerrar loading en caso de error
            toastr.error('Error al eliminar producto: ' + error.message);
        }
    } else {
    }
}

/**
 * Eliminar utilidades asociadas a un producto específico
 */
async function eliminarUtilidadesDelProducto(productoId) {
    try {
        // Obtener información del producto desde la fila de la tabla
        const productoRow = document.querySelector(`tr[data-producto-id="${productoId}"]`);
        if (!productoRow) {
            console.warn(`No se encontró la fila del producto ${productoId} en el DOM`);
            return;
        }

        // Obtener datos del producto desde atributos data o contenido de la fila
        const categoriaId = productoRow.dataset.categoriaId;
        const itemPropioId = productoRow.dataset.itemPropioId;

        if (!categoriaId && !itemPropioId) {
            // Si no están en dataset, intentar obtener del servidor
            const cotizacionId = document.getElementById('id')?.value || document.getElementById('cotizacion_id')?.value;
            if (!cotizacionId) {
                console.warn('No se pudo obtener el ID de cotización');
                return;
            }

            // Obtener utilidades de la cotización
            const response = await fetch(`/admin/admin.cotizaciones.utilidades.obtener/${cotizacionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (!response.ok) {
                console.warn('No se pudieron obtener las utilidades de la cotización');
                return;
            }

            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                // Eliminar todas las utilidades (enfoque seguro si no podemos identificar específicas)
                const eliminarPromesas = result.data.map(utilidad => eliminarUtilidadSilenciosa(utilidad.id));
                await Promise.all(eliminarPromesas);
            }
            return;
        }

        // Si tenemos categoria_id e item_propio_id, buscar utilidades específicas
        const cotizacionId = document.getElementById('id')?.value || document.getElementById('cotizacion_id')?.value;
        if (cotizacionId) {
            const response = await fetch(`/admin/admin.cotizaciones.utilidades.obtener/${cotizacionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const result = await response.json();

                if (result.success && result.data) {
                    // Filtrar utilidades que coincidan con el producto
                    const utilidadesDelProducto = result.data.filter(utilidad =>
                        utilidad.categoria_id == categoriaId && utilidad.item_propio_id == itemPropioId
                    );

                    if (utilidadesDelProducto.length > 0) {
                        // Eliminar utilidades específicas del producto
                        const eliminarPromesas = utilidadesDelProducto.map(utilidad => eliminarUtilidadSilenciosa(utilidad.id));
                        await Promise.all(eliminarPromesas);
                    }
                }
            }
        }

    } catch (error) {
        console.error('Error al eliminar utilidades del producto:', error);
        // No lanzar error para que la eliminación del producto pueda continuar
    }
}

/**
 * Eliminar una utilidad de forma silenciosa (sin confirmación ni toastr)
 */
async function eliminarUtilidadSilenciosa(utilidadId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.utilidades.destroy/${utilidadId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
        } else {
            console.warn(`Error al eliminar utilidad ${utilidadId}`);
        }
    } catch (error) {
        console.error(`Error al eliminar utilidad ${utilidadId}:`, error);
    }
}


async function eliminarProductoGuardado(productoId) {

    try {
        const token = $('meta[name="csrf-token"]').attr('content');

        if (!token) {
            throw new Error('No se encontró el token CSRF');
        }

        // Primero, obtener información del producto para eliminar utilidades asociadas
        await eliminarUtilidadesDelProducto(productoId);

        const url = `/admin/admin.cotizaciones.productos.eliminar/${productoId}`;

        const response = await $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json'
        });

        return response;

    } catch (error) {
        console.error('Error en eliminarProductoGuardado:', error);

        if (error.status === 419) {
            throw new Error('Token CSRF expirado. Por favor, recarga la página.');
        }

        if (error.responseJSON) {
            throw new Error(error.responseJSON.message || 'Error del servidor');
        }

        if (error.responseText) {

            // Si la respuesta es HTML, probablemente sea redirección a login
            if (error.responseText.includes('<!DOCTYPE html>')) {
                throw new Error('Sesión expirada. Por favor, recarga la página.');
            }
        }

        throw new Error(error.statusText || error.message || 'Error desconocido');
    }
}

/**
 * Editar producto con UI mejorada
 */
async function editarProducto(productoId) {
    try {

        // Obtener datos actuales del producto desde la tabla
        const row = document.querySelector(`tr[data-producto-id="${productoId}"]`);
        if (!row) {
            toastr.error('No se encontró el producto en la tabla');
            return;
        }

        // Extraer datos actuales del producto de la tabla
        const cells = row.querySelectorAll('td');
        const cantidadActual = cells[2]?.textContent?.trim() || '1';
        const valorUnitarioActual = cells[3]?.textContent?.replace(/[$,.]/g, '') || '0';
        const descuentoActual = cells[4]?.textContent?.replace('%', '') || '0';
        const nombreProducto = cells[1]?.textContent?.trim() || 'Producto';

        // Crear un modal más avanzado con mejor UX
        const { value: formValues } = await Swal.fire({
            title: '<i class="fas fa-edit text-primary"></i> Editar Producto',
            html: `
                <div class="container-fluid p-0">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Producto:</strong> ${nombreProducto}
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="swal-cantidad" class="form-label font-weight-bold">
                                <i class="fas fa-cubes text-success"></i> Cantidad *
                            </label>
                            <input id="swal-cantidad"
                                   class="form-control"
                                   type="number"
                                   step="0.001"
                                   min="0.001"
                                   value="${cantidadActual}"
                                   placeholder="Ej: 5.5">
                            <small class="text-muted">Cantidad mí­nima: 0.001</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="swal-valor" class="form-label font-weight-bold">
                                <i class="fas fa-dollar-sign text-warning"></i> Valor Unitario *
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input id="swal-valor"
                                       class="form-control"
                                       type="number"
                                       step="0.01"
                                       min="0.01"
                                       value="${valorUnitarioActual}"
                                       placeholder="0.00">
                            </div>
                            <small class="text-muted">Precio por unidad</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="swal-descuento" class="form-label font-weight-bold">
                                <i class="fas fa-percent text-danger"></i> Descuento
                            </label>
                            <div class="input-group">
                                <input id="swal-descuento"
                                       class="form-control"
                                       type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       value="${descuentoActual}"
                                       placeholder="0.00">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="text-muted">Opcional (0-100%)</small>
                        </div>
                    </div>

                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-calculator text-info"></i>
                                Cálculo Automático
                            </h6>
                            <div class="row text-sm">
                                <div class="col-6">
                                    <strong>Subtotal:</strong>
                                    <span id="swal-subtotal" class="text-primary">$0.00</span>
                                </div>
                                <div class="col-6">
                                    <strong>Descuento:</strong>
                                    <span id="swal-descuento-valor" class="text-danger">$0.00</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <strong class="h6">Total:
                                        <span id="swal-total" class="text-success">$0.00</span>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar Cambios',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            didOpen: () => {
                // Función para calcular totales en tiempo real
                const calcularTotales = () => {
                    const cantidad = parseFloat(document.getElementById('swal-cantidad').value) || 0;
                    const valorUnitario = parseFloat(document.getElementById('swal-valor').value) || 0;
                    const descuentoPorcentaje = parseFloat(document.getElementById('swal-descuento').value) || 0;

                    const subtotal = cantidad * valorUnitario;
                    const descuentoValor = subtotal * (descuentoPorcentaje / 100);
                    const total = subtotal - descuentoValor;

                    // Formatear números como moneda
                    const formatearMoneda = (valor) => {
                        return new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: 'COP',
                            minimumFractionDigits: 2
                        }).format(valor);
                    };

                    // Actualizar elementos
                    document.getElementById('swal-subtotal').textContent = formatearMoneda(subtotal);
                    document.getElementById('swal-descuento-valor').textContent = formatearMoneda(descuentoValor);
                    document.getElementById('swal-total').textContent = formatearMoneda(total);
                };

                // Agregar eventos para cálculo en tiempo real
                ['swal-cantidad', 'swal-valor', 'swal-descuento'].forEach(id => {
                    const elemento = document.getElementById(id);
                    if (elemento) {
                        elemento.addEventListener('input', calcularTotales);
                        elemento.addEventListener('keyup', calcularTotales);
                    }
                });

                // Calcular totales inicial
                calcularTotales();

                // Focus en el primer campo
                document.getElementById('swal-cantidad').focus();
            },
            preConfirm: () => {
                const cantidad = document.getElementById('swal-cantidad').value;
                const valorUnitario = document.getElementById('swal-valor').value;
                const descuentoPorcentaje = document.getElementById('swal-descuento').value;

                // Validaciones
                if (!cantidad || cantidad.trim() === '') {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> La cantidad es obligatoria');
                    return false;
                }

                if (!valorUnitario || valorUnitario.trim() === '') {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> El valor unitario es obligatorio');
                    return false;
                }

                const cantidadNum = parseFloat(cantidad);
                const valorUnitarioNum = parseFloat(valorUnitario);
                const descuentoPorcentajeNum = parseFloat(descuentoPorcentaje) || 0;

                if (isNaN(cantidadNum) || cantidadNum <= 0) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> La cantidad debe ser un número mayor a 0');
                    return false;
                }

                if (isNaN(valorUnitarioNum) || valorUnitarioNum < 0) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> El valor unitario debe ser un número mayor o igual a 0');
                    return false;
                }

                if (descuentoPorcentajeNum < 0 || descuentoPorcentajeNum > 100) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> El descuento debe estar entre 0% y 100%');
                    return false;
                }

                return {
                    cantidad: cantidadNum,
                    valor_unitario: valorUnitarioNum,
                    descuento_porcentaje: descuentoPorcentajeNum
                };
            }
        });

        if (formValues) {

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Actualizando producto...',
                html: '<i class="fas fa-spinner fa-spin fa-3x"></i><br><br>Por favor espere mientras se actualiza el producto',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Calcular totales
            const cantidad = formValues.cantidad;
            const valorUnitario = formValues.valor_unitario;
            const descuentoPorcentaje = formValues.descuento_porcentaje;

            const subtotal = cantidad * valorUnitario;
            const descuentoValor = subtotal * (descuentoPorcentaje / 100);
            const valorTotal = subtotal - descuentoValor;

            // Preparar datos para enviar
            const datosActualizar = {
                cantidad: cantidad,
                valor_unitario: valorUnitario,
                descuento_porcentaje: descuentoPorcentaje,
                valor_total: valorTotal
            };


            try {
                // Obtener token CSRF
                const token = $('meta[name="csrf-token"]').attr('content');
                if (!token) {
                    throw new Error('Token CSRF no encontrado');
                }

                // Enviar actualización al backend usando jQuery
                const response = await $.ajax({
                    url: `/admin/admin.cotizaciones.productos.actualizar/${productoId}`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify(datosActualizar),
                    dataType: 'json'
                });


                // Cerrar loading
                Swal.close();

                if (response.success) {
                    // Mostrar í©xito con detalles
                    await Swal.fire({
                        icon: 'success',
                        title: 'Â¡Producto Actualizado!',
                        html: `
                            <div class="text-left">
                                <p><strong>Cantidad:</strong> ${cantidad}</p>
                                <p><strong>Valor Unitario:</strong> $${valorUnitario.toLocaleString()}</p>
                                <p><strong>Descuento:</strong> ${descuentoPorcentaje}%</p>
                                <p><strong>Total:</strong> <span class="text-success">$${valorTotal.toLocaleString()}</span></p>
                            </div>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    });

                    // Recargar la lista de productos
                    await cargarProductosGuardados();

                    // Actualizar totales si están disponibles
                    if (response.totales) {
                        actualizarTotalesEnVista(response.totales);
                    }

                    // Tambií©n actualizar totales completos para asegurar consistencia
                    await actualizarTotalesCompletos();

                } else {
                    throw new Error(response.message || 'Error al actualizar producto');
                }

            } catch (error) {
                console.error('Error en la petición:', error);
                Swal.close(); // Cerrar loading

                let mensajeError = 'Error desconocido';

                if (error.responseJSON) {
                    mensajeError = error.responseJSON.message || 'Error del servidor';
                } else if (error.statusText) {
                    mensajeError = error.statusText;
                } else if (error.message) {
                    mensajeError = error.message;
                }

                await Swal.fire({
                    icon: 'error',
                    title: 'Error al Actualizar',
                    text: mensajeError,
                    confirmButtonText: 'Entendido'
                });
            }
        } else {
        }

    } catch (error) {
        console.error('Error general al editar producto:', error);
        toastr.error('Error al abrir el modal de edición: ' + error.message);
    }
}

/**
 * Actualizar totales en la vista
 */
function actualizarTotalesEnVista(totales) {

    try {
        // Función para formatear moneda para mostrar (sin saltos de lí­nea)
        const formatearParaTexto = (valor) => {
            const numero = parseFloat(valor || 0);
            // Usar formato más simple para evitar saltos de lí­nea
            return '$ ' + numero.toLocaleString('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        // Función para formatear números para inputs ocultos (sin formato)
        const formatearParaInput = (valor) => {
            const numero = parseFloat(valor || 0);
            return numero.toFixed(2);
        };

        // 1. ACTUALIZAR CAMPOS OCULTOS (para el formulario)
        const hiddenSubtotal = document.getElementById('subtotal');
        const hiddenDescuento = document.getElementById('descuento');
        const hiddenImpuesto = document.getElementById('total_impuesto');
        const hiddenTotal = document.getElementById('total');

        if (hiddenSubtotal) {
            hiddenSubtotal.value = formatearParaInput(totales.subtotal);
        }

        if (hiddenDescuento) {
            hiddenDescuento.value = formatearParaInput(totales.descuento);
        }

        if (hiddenImpuesto) {
            hiddenImpuesto.value = formatearParaInput(totales.impuestos);
        }

        if (hiddenTotal) {
            hiddenTotal.value = formatearParaInput(totales.total);
        }

        // Actualizar campo oculto de viáticos (si viene del servidor)
        const hiddenViaticos = document.getElementById('viaticos');
        if (hiddenViaticos && totales.viaticos !== undefined) {
            hiddenViaticos.value = formatearParaInput(totales.viaticos);
        }

        // 2. ACTUALIZAR ELEMENTOS INFORMATIVOS
        const displaySubtotal = document.getElementById('display-subtotal-valor');
        const displayDescuento = document.getElementById('display-descuento-valor');
        const displayImpuesto = document.getElementById('display-impuesto-valor');
        const displayTotal = document.getElementById('display-total-valor');
        const displayViaticos = document.getElementById('display-viaticos-valor');

        if (displaySubtotal) {
            displaySubtotal.textContent = formatearParaTexto(totales.subtotal);
        }

        if (displayDescuento) {
            displayDescuento.textContent = formatearParaTexto(totales.descuento);
        }

        if (displayImpuesto) {
            displayImpuesto.textContent = formatearParaTexto(totales.impuestos);
        }

        if (displayViaticos && totales.viaticos !== undefined) {
            displayViaticos.textContent = formatearParaTexto(totales.viaticos);
        }

        if (displayTotal) {
            displayTotal.textContent = formatearParaTexto(totales.total);
        }


    } catch (error) {
        console.error('ðŸ’¥ Error actualizando totales en vista renovada:', error);
    }
}

/**
 * Función para debuggear elementos DOM
 */
function debuggearElementosDOM() {

    // Elementos tradicionales
    const elementosTradicionales = [
        'subtotal', 'descuento', 'total_impuesto', 'subtotal_menos_descuento', 'total', 'error_descuento'
    ];

    elementosTradicionales.forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
        }
    });

    // Elementos nuevos del sistema
    const elementosNuevos = [
        'display-subtotal-valor', 'display-descuento-valor', 'display-impuesto-valor', 'display-total-valor',
        'hidden_subtotal', 'hidden_descuento', 'hidden_impuesto', 'hidden_total'
    ];

    elementosNuevos.forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
        }
    });

    return {
        tradicionales: elementosTradicionales.map(id => ({ id, existe: !!document.getElementById(id) })),
        nuevos: elementosNuevos.map(id => ({ id, existe: !!document.getElementById(id) }))
    };
}

/**
 * Función para recalcular totales manualmente (botón)
 */
function actualizarTotalesManualmente() {
    actualizarTotalesCompletos();
}
/**
 * Cargar y actualizar totales completos de la cotización
 */
async function actualizarTotalesCompletos() {

    try {
        // Obtener ID de cotización con múltiples mí©todos
        let cotizacionId = null;

        // Mí©todo 1: Input con id 'id'
        const inputId = document.getElementById('id');
        if (inputId && inputId.value) {
            cotizacionId = inputId.value;
        }

        // Mí©todo 2: URL params
        if (!cotizacionId) {
            const urlParams = new URLSearchParams(window.location.search);
            cotizacionId = urlParams.get('id');
        }

        // Mí©todo 3: Desde la URL path
        if (!cotizacionId) {
            const pathMatch = window.location.pathname.match(/\/(\d+)$/);
            if (pathMatch) {
                cotizacionId = pathMatch[1];
            }
        }

        // Mí©todo 4: Variable global
        if (!cotizacionId && typeof cotizacionGuardadaId !== 'undefined') {
            cotizacionId = cotizacionGuardadaId;
        }

        // Mí©todo 5: Buscar en cualquier input que contenga el ID
        if (!cotizacionId) {
            const possibleInputs = document.querySelectorAll('input[value]');
            possibleInputs.forEach(input => {
                const value = input.value;
                if (value && /^\d+$/.test(value) && value.length < 5) {
                    cotizacionId = value;
                }
            });
        }

        if (!cotizacionId) {
            console.warn('No se encontró ID de cotización para actualizar totales');
            return;
        }


        const token = $('meta[name="csrf-token"]').attr('content');
        if (!token) {
            console.error('Token CSRF no encontrado');
            return;
        }


        const response = await $.ajax({
            url: '/admin/cotizaciones/totales/obtener',
            type: 'GET',
            data: { cotizacion_id: cotizacionId },
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            dataType: 'json'
        });


        if (response.success) {
            actualizarTotalesEnVista(response.data);

            // Tambií©n mostrar detalle si está disponible
            if (response.data.detalle) {
            }
        } else {
            console.error('Error en la respuesta:', response.message);
        }

    } catch (error) {
        console.error('Error actualizando totales completos:', {
            status: error.status,
            statusText: error.statusText,
            responseText: error.responseText?.substring(0, 500),
            responseJSON: error.responseJSON
        });
    }
}

/**
 * Función para forzar la actualización de totales - útil para debugging
 */
function forzarActualizacionTotales() {
    actualizarTotalesCompletos();
}
/**
 * Función para forzar el reseteo de totales a cero - útil cuando no hay productos
 */
function forzarReseteoTotales() {
    resetearTotalesACero();
}

/**
 * Configurar event listeners para actualización automática de totales
 */
function setupAutoUpdateTotales() {
    // Detectar cambios en campos de la cotización que podrían afectar totales
    const camposAMonitorear = [
        '#tercero_id',
        '#proyecto',
        '#observacion',
        'input[name*="descuento"]',
        'input[name*="impuesto"]',
        'select[name*="concepto"]',
        'input[name*="porcentaje"]',
        'input[name*="valor"]'
    ];

    camposAMonitorear.forEach(selector => {
        const elementos = document.querySelectorAll(selector);
        elementos.forEach(elemento => {
            // Eventos para diferentes tipos de elementos
            elemento.addEventListener('change', debounceUpdateTotales);
            elemento.addEventListener('blur', debounceUpdateTotales);

            // Para inputs de texto, también en keyup con delay
            if (elemento.type === 'text' || elemento.type === 'number') {
                elemento.addEventListener('keyup', debounceUpdateTotales);
            }
        });
    });

    // Event listener específico para cuando se guarda el formulario
    const formulario = document.querySelector('form');
    if (formulario) {
        formulario.addEventListener('submit', async (e) => {

            await actualizarTotalesCompletos();
        });
    }
}

/**
 * Función debounced para actualizar totales (evita llamadas excesivas)
 */
const debounceUpdateTotales = debounce(async () => {
    await actualizarTotalesCompletos();
}, 1000); // 1 segundo de delay

/**
 * Función debounce utility
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Función de debugging para verificar el estado de totales
 */
window.verificarEstadoTotales = async function() {
    const cotizacionId = document.getElementById('id')?.value;
    // Verificar productos
    try {
        await cargarProductosGuardados();
    } catch (error) {
        console.error('❌ Error al cargar productos:', error);
    }

    // Verificar totales
    try {
        await actualizarTotalesCompletos();
    } catch (error) {
        console.error('❌ Error al actualizar totales:', error);
    }
};

//  TAMBIÉN ejecutar cuando se haga clic en cualquier parte de los totales
document.addEventListener('click', function(e) {
    if (e.target.matches('#subtotal, #descuento, #total_impuesto, #total, input[name="subtotal"], input[name="descuento"], input[name="total_impuesto"], input[name="total"]')) {
        setTimeout(() => {
            actualizarTotalesCompletos();
        }, 100);
    }
});

//  EJECUTAR cuando se enfoque cualquier campo de total
document.addEventListener('focusin', function(e) {
    if (e.target.matches('#subtotal, #descuento, #total_impuesto, #total, input[name="subtotal"], input[name="descuento"], input[name="total_impuesto"], input[name="total"]')) {
        actualizarTotalesCompletos();
    }
});

// ðŸ“º Función para mostrar estado de totales en tiempo real
function mostrarEstadoTotalesEnPantalla() {
    const statusDiv = document.getElementById('totales-status') || (() => {
        const div = document.createElement('div');
        div.id = 'totales-status';
        div.style.cssText = 'position: fixed; top: 10px; right: 10px; background: #28a745; color: white; padding: 10px; border-radius: 5px; z-index: 9999; font-size: 12px;';
        document.body.appendChild(div);
        return div;
    })();

    statusDiv.innerHTML = `
        Totales Automáticos Activos<br>
        Subtotal: ${document.getElementById('subtotal')?.value || 'N/A'}<br>
        Total: ${document.getElementById('total')?.value || 'N/A'}
    `;

    // Auto-hide después de 5 segundos
    setTimeout(() => {
        if (statusDiv) statusDiv.remove();
    }, 5000);
}

// NOTA: La inicialización automática via DOMContentLoaded ha sido deshabilitada
// Ahora es manejada por documento-coordinator.js para garantizar el orden correcto
// Todas las funciones están disponibles para ser llamadas en el momento apropiado

// Función para configurar event listeners que no dependen del timing
function configurarEventListeners() {
    // Event listener para el botón "Agregar Items Propios"
    const btnAgregarItemsPropios = document.getElementById('btnUsarItemsSeleccionados');
    if (btnAgregarItemsPropios) {
        btnAgregarItemsPropios.addEventListener('click', usarItemSeleccionado);
    }

    // Configurar auto-actualización de totales
    setupAutoUpdateTotales();
}

// Función de utilidad para forzar actualización de totales
window.forzarActualizacionTotales = async function() {
    try {
        await actualizarTotalesCompletos();
    } catch (error) {
        console.error('Error al actualizar totales:', error);
    }
};

// Función de utilidad adicional para resetear totales manualmente
window.forzarReseteoTotales = forzarReseteoTotales;
window.actualizarTotalesManualmente = async function() {
    await actualizarTotalesCompletos();
};

// Configurar event listeners básicos inmediatamente
configurarEventListeners();

/**
 * Obtener valores por defecto para un item desde el backend
 */
async function obtenerValoresPorDefecto(itemId, tipoItem, tipoCosto) {
    try {
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.valores-defecto.obtener`;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                item_id: itemId,
                tipo_item: tipoItem,
                tipo_costo: tipoCosto
            })
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.data.encontrado) {
            return result.data;
        }

        return null;
    } catch (error) {
        console.error('Error al obtener valores por defecto:', error);
        return null;
    }
}

/**
 * Cargar valores por defecto en los campos del formulario
 */
async function cargarValoresPorDefecto(itemId, tipoItem, tipoCosto) {
    const valores = await obtenerValoresPorDefecto(itemId, tipoItem, tipoCosto);

    if (!valores || !valores.encontrado) {
        return false;
    }

    try {
        // Cachear todos los costos disponibles en el elemento del item para evitar peticiones repetidas
        if (valores.costos_disponibles) {
            const cardEl = document.getElementById(`cardItem_${itemId}`);
            if (cardEl) cardEl.dataset.costosDisponibles = JSON.stringify(valores.costos_disponibles);
        }

        // Cargar unidad de medida
        const unidadMedidaInput = document.getElementById(`unidadMedida_${itemId}`);
        if (unidadMedidaInput && valores.unidad_medida) {
            unidadMedidaInput.value = valores.unidad_medida;
        }

        // Cargar costo según el tipo y mostrar badge "Valor sugerido"
        let costoInput = null;
        let badgeId = null;
        switch (tipoCosto) {
            case 'unitario':
                costoInput = document.getElementById(`costoUnitario_${itemId}`);
                badgeId = `badgeSugerido_unitario_${itemId}`;
                break;
            case 'hora':
                costoInput = document.getElementById(`costoHora_${itemId}`);
                badgeId = `badgeSugerido_hora_${itemId}`;
                break;
            case 'dia':
                costoInput = document.getElementById(`costoDia_${itemId}`);
                badgeId = `badgeSugerido_dia_${itemId}`;
                break;
        }

        if (costoInput && valores.costo > 0) {
            costoInput.value = valores.costo;

            // Mostrar badge de valor sugerido (solo existe en la forma simple, no en la de nómina)
            const badge = badgeId ? document.getElementById(badgeId) : null;
            if (badge) badge.classList.remove('d-none');

            // Calcular precio en ambas formas (simple y nomina)
            actualizarPrecioVisual(itemId);
            if (typeof calcularPrecioItem === 'function') calcularPrecioItem(itemId);

            mostrarNotificacionValoresCargados(tipoCosto, valores.costo, valores.unidad_medida);
        }

        return true;
    } catch (error) {
        console.error('Error al cargar valores en campos:', error);
        return false;
    }
}

/**
 * Cargar valores por defecto según el tipo de item y tipo de costo seleccionado
 */
async function cargarValoresDefectoPorTipo(itemId, tipoCosto) {
    try {
        // Verificar si ya tenemos los costos cacheados en el card element
        const cardEl = document.getElementById(`cardItem_${itemId}`);
        if (cardEl && cardEl.dataset.costosDisponibles) {
            const costosDisponibles = JSON.parse(cardEl.dataset.costosDisponibles);
            const costoSugerido = costosDisponibles[tipoCosto] ?? 0;
            const unidadMedida = document.getElementById(`unidadMedida_${itemId}`)?.value || '';

            let costoInput = null;
            let badgeId = null;
            switch (tipoCosto) {
                case 'unitario': costoInput = document.getElementById(`costoUnitario_${itemId}`); badgeId = `badgeSugerido_unitario_${itemId}`; break;
                case 'hora':     costoInput = document.getElementById(`costoHora_${itemId}`);     badgeId = `badgeSugerido_hora_${itemId}`; break;
                case 'dia':      costoInput = document.getElementById(`costoDia_${itemId}`);      badgeId = `badgeSugerido_dia_${itemId}`; break;
            }

            if (costoInput && costoSugerido > 0) {
                costoInput.value = costoSugerido;
                const badge = badgeId ? document.getElementById(badgeId) : null;
                if (badge) badge.classList.remove('d-none');
                actualizarPrecioVisual(itemId);
                mostrarNotificacionValoresCargados(tipoCosto, costoSugerido, unidadMedida);
            }
            return;
        }

        // Sin cache: hacer petición al backend
        let itemData = null;
        if (window.itemsPropiosDisponibles) {
            itemData = window.itemsPropiosDisponibles.find(item => String(item.id) === String(itemId));
        }

        if (!itemData) {
            console.log(`No se encontró información del item ${itemId} para cargar valores por defecto`);
            return;
        }

        // Determinar el tipo de item y el ID real
        let tipoItem = 'propio';
        let idReal = itemId;

        if (itemData.tipo === 'parametrizacion') {
            tipoItem = 'cargo';
            if (String(itemId).startsWith('param_')) {
                idReal = itemId.replace('param_', '');
            }
        }

        if (itemData.tipo === 'cargo_tabla') {
            tipoItem = 'cargo_tabla';
            idReal = itemData.cargo_id || itemId;
        }

        const cargaExitosa = await cargarValoresPorDefecto(idReal, tipoItem, tipoCosto);

        if (!cargaExitosa) {
            console.log(`No se pudieron cargar valores por defecto para el item ${itemId}`);
        }

    } catch (error) {
        console.error('Error al cargar valores por defecto:', error);
    }
}

/**
 * Mostrar notificación cuando se cargan valores por defecto
 */
function mostrarNotificacionValoresCargados(tipoCosto, costo, unidadMedida) {
    if (typeof toastr !== 'undefined') {
        toastr.success(
            `Valores cargados automáticamente: $${costo} (${unidadMedida})`,
            `Tipo ${tipoCosto.charAt(0).toUpperCase() + tipoCosto.slice(1)}`,
            {
                timeOut: 3000,
                closeButton: true,
                progressBar: true
            }
        );
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            type: 'success',
            title: 'Valores Cargados',
            text: `Tipo ${tipoCosto}: $${costo} (${unidadMedida})`,
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
        });
    }
}

// ============================================================
// FLUJO DEDICADO DE NÓMINA
// ============================================================

/**
 * Abre el modal dedicado para configurar items de Nómina (Cargo/Perfil).
 * @param {Array} cargos - Array de items tipo 'cargo_tabla' del backend
 */
function abrirModalNominaConfig(cargos) {
    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalNominaConfig');
    if (modalAnterior) modalAnterior.remove();
    window._nominaResultados = window._nominaResultados || {};
    const _fnCalc = `calcularLiquidacionNomina`;
    const filasCargos = cargos.map((cargo, idx) => {
        const costoDia  = Number(cargo.costo_dia  || cargo.base_costo_dia  || 0);
        const diasLaborales = Number(cargo.dias_laborales || cargo.base_dias_laborales || 0);
        const nombreCargo = cargo.nombre || cargo.cargo?.nombre || 'Sin nombre';
        return `
            <div class="card mb-3 shadow-sm nomina-cargo-card" id="cardNomina_${idx}"
                 style="border:1px solid #dee2e6; border-left:4px solid #ffc107; border-radius:6px;">

                <!-- ── Encabezado del cargo ── -->
                <div class="card-header py-2 px-3"
                     style="background:#fffbf0; border-bottom:1px solid #ffe082; border-radius:5px 5px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input nomina-cargo-check"
                                   id="checkNomina_${idx}" checked
                                   onchange="toggleCargoNomina(${idx})">
                            <label class="custom-control-label font-weight-bold text-dark" for="checkNomina_${idx}"
                                   style="font-size:.9rem;">
                                <i class="fas fa-hard-hat text-warning mr-1"></i>${nombreCargo}
                            </label>
                        </div>
                        <div class="d-flex align-items-center">
                            <small class="text-muted mr-2">
                                <i class="fas fa-coins mr-1 text-warning"></i>Base/día:
                                <strong>$${costoDia.toLocaleString('es-CO')}</strong>
                            </small>
                            <span class="badge badge-warning badge-pill px-2"
                                  id="nominaBadgePersonas_${idx}">1 persona</span>
                        </div>
                    </div>
                </div>

                <!-- ── Cuerpo del cargo ── -->
                <div class="card-body p-3" id="bodyNomina_${idx}">

                    <!-- PERSONAS -->
                    <div class="d-flex align-items-center mb-3 pb-2"
                         style="border-bottom:1px dashed #e0e0e0;">
                        <i class="fas fa-users text-secondary mr-2"></i>
                        <label class="mb-0 mr-2 font-weight-bold text-secondary"
                               style="font-size:.8rem; white-space:nowrap;">N° PERSONAS</label>
                        <input type="number" class="form-control form-control-sm"
                               id="nominaPersonas_${idx}" min="1" value="1"
                               style="width:80px; font-weight:700; font-size:1rem; text-align:center;"
                               onchange="${_fnCalc}(${idx}); document.getElementById('nominaBadgePersonas_${idx}').textContent = (this.value||1) + ' persona' + ((this.value||1)>1?'s':'');"
                               oninput="${_fnCalc}(${idx}); document.getElementById('nominaBadgePersonas_${idx}').textContent = (this.value||1) + ' persona' + ((this.value||1)>1?'s':'');">
                        <small class="text-muted ml-3">
                            <i class="fas fa-info-circle"></i>
                            Salario base: <strong>${costoDia > 0 ? '$' + (costoDia * diasLaborales).toLocaleString('es-CO') : 'SMLV'}</strong>/mes
                        </small>
                    </div>

                    <!-- MODO DE COSTO -->
                    <input type="hidden" id="nominaModo_${idx}" value="hora">
                    <div class="btn-group btn-group-sm w-100 mb-2" role="group" style="border:1px solid #dee2e6; border-radius:6px; overflow:hidden;">
                        <button type="button" class="btn btn-primary active" id="btnModoHora_${idx}"
                                onclick="onNominaModoChange(${idx}, 'hora')" style="font-size:.8rem;">
                            <i class="fas fa-clock mr-1"></i>Costo Hora
                        </button>
                        <button type="button" class="btn btn-outline-success" id="btnModoDia_${idx}"
                                onclick="onNominaModoChange(${idx}, 'dia')" style="font-size:.8rem;">
                            <i class="fas fa-calendar-day mr-1"></i>Costo Día (Turno)
                        </button>
                    </div>

                    <!-- ── Sección Costo Hora (existente — NO modificar lógica) ── -->
                    <div id="seccionCostoHora_${idx}">
                    <!-- DOS COLUMNAS: TIEMPO ORDINARIO | HORAS EXTRA -->
                    <div class="row no-gutters" style="gap:0;">

                        <!-- Columna izquierda: Tiempo Ordinario -->
                        <div class="col-md-6 pr-md-2">
                            <div class="rounded p-2 mb-2" style="background:#f8f9fa; border:1px solid #e9ecef;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar-alt text-primary mr-1" style="font-size:.8rem;"></i>
                                    <span class="font-weight-bold text-uppercase text-primary"
                                          style="font-size:.72rem; letter-spacing:.05em;">Tiempo Ordinario</span>
                                    <span class="ml-auto text-muted" style="font-size:.7rem;">máx. 7 horas</span>
                                </div>
                                <small class="text-danger d-block mb-1" id="errorTiempoOrd_${idx}" style="font-size:.7rem;"></small>
                                <div class="row no-gutters" style="gap:4px 0;">
                                    <div class="col-6 pr-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <i class="fas fa-sun text-warning" style="font-size:.65rem;"></i>
                                            Horas diurnos
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-tiempo-ord-${idx}"
                                               id="nominaDiasDiurnos_${idx}" min="0" max="7" value="0"
                                               style="font-size:.85rem;"
                                               onchange="validarTiempoOrdinario(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarTiempoOrdinario(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                    <div class="col-6 pl-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <i class="fas fa-moon text-indigo" style="font-size:.65rem; color:#6f42c1;"></i>
                                            Horas nocturnos
                                            <span class="text-muted">(+35%)</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-tiempo-ord-${idx}"
                                               id="nominaDiasNocturnos_${idx}" min="0" max="7" value="0"
                                               style="font-size:.85rem;"
                                               onchange="validarTiempoOrdinario(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarTiempoOrdinario(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                    <div class="col-6 pr-1 mt-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <i class="fas fa-church text-success" style="font-size:.65rem;"></i>
                                            Dom./fest. diurnos
                                            <span class="text-muted">(+75%)</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-tiempo-ord-${idx}"
                                               id="nominaDominicales_${idx}" min="0" max="7" value="0"
                                               style="font-size:.85rem;"
                                               onchange="validarTiempoDominicalesFestivos(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarTiempoDominicalesFestivos(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                    <div class="col-6 pl-1 mt-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <i class="fas fa-church text-danger" style="font-size:.65rem;"></i>
                                            Dom./fest. nocturnos
                                            <span class="text-muted">(+110%)</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-tiempo-ord-${idx}"
                                               id="nominaDomNocturnos_${idx}" min="0" max="7" value="0"
                                               style="font-size:.85rem;"
                                               onchange="validarTiempoDominicalesFestivos(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarTiempoDominicalesFestivos(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha: Horas Extra -->
                        <div class="col-md-6 pl-md-2">
                            <div class="rounded p-2 mb-2" style="background:#fff8f0; border:1px solid #ffe0b2;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-orange mr-1" style="font-size:.8rem; color:#e65100;"></i>
                                    <span class="font-weight-bold text-uppercase"
                                          style="font-size:.72rem; letter-spacing:.05em; color:#e65100;">Horas Extra</span>
                                    <span class="ml-auto text-muted" style="font-size:.7rem;">Art. 168 CST · máx. 2 h</span>
                                </div>
                                <small class="text-danger d-block mb-1" id="errorHorasExtra_${idx}" style="font-size:.7rem;"></small>
                                <div class="row no-gutters" style="gap:4px 0;">
                                    <div class="col-6 pr-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <span class="badge badge-pill"
                                                  style="background:#fff3e0; color:#e65100; font-size:.65rem;">×1.25</span>
                                            HED diurna
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-he-${idx}"
                                               id="nominaHED_${idx}" min="0" max="2" value="0"
                                               style="font-size:.85rem; border-color:#ffe0b2;"
                                               onchange="validarHorasExtra(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarHorasExtra(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                    <div class="col-6 pl-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <span class="badge badge-pill"
                                                  style="background:#ede7f6; color:#6f42c1; font-size:.65rem;">×1.75</span>
                                            HEN nocturna
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-he-${idx}"
                                               id="nominaHEN_${idx}" min="0" max="2" value="0"
                                               style="font-size:.85rem; border-color:#d1c4e9;"
                                               onchange="validarHorasExtra(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarHorasExtra(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                    <div class="col-6 pr-1 mt-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <span class="badge badge-pill"
                                                  style="background:#e8f5e9; color:#2e7d32; font-size:.65rem;">×2.00</span>
                                            HEDD dom. diurna
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-he-${idx}"
                                               id="nominaHEDD_${idx}" min="0" max="2" value="0"
                                               style="font-size:.85rem; border-color:#c8e6c9;"
                                               onchange="validarHorasExtra(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarHorasExtra(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                    <div class="col-6 pl-1 mt-1">
                                        <label class="mb-0" style="font-size:.72rem; color:#555;">
                                            <span class="badge badge-pill"
                                                  style="background:#ffebee; color:#c62828; font-size:.65rem;">×2.50</span>
                                            HEDN dom. nocturna
                                        </label>
                                        <input type="number" class="form-control form-control-sm text-center nomina-he-${idx}"
                                               id="nominaHEDN_${idx}" min="0" max="2" value="0"
                                               style="font-size:.85rem; border-color:#ffcdd2;"
                                               onchange="validarHorasExtra(${idx}); ${_fnCalc}(${idx});"
                                               oninput="validarHorasExtra(${idx}); ${_fnCalc}(${idx});">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div><!-- /seccionCostoHora -->

                    <!-- ── Sección Costo Día (Turno) ── -->
                    <div id="seccionCostoDia_${idx}" class="d-none">
                        <div class="rounded p-2 mb-2" style="background:#f0f8ff; border:1px solid #b8daff;">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold mb-1" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#004085;">
                                    <i class="fas fa-calendar-alt mr-1"></i>Turno de Trabajo
                                </label>
                                <select class="form-control form-control-sm" id="nominaTurno_${idx}"
                                        onchange="onNominaTurnoChange(${idx})">
                                    <option value="">— Seleccione turno —</option>
                                </select>
                                <small class="text-danger d-block mt-1" id="errorTurno_${idx}"></small>
                            </div>
                            <div class="form-group mb-2">
                                <label style="font-size:.78rem; font-weight:600; color:#555;">
                                    <i class="fas fa-calendar-check mr-1 text-success"></i>Días trabajados
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="nominaDiasTurno_${idx}"
                                           min="1" max="31" value="26"
                                           onchange="${_fnCalc}(${idx})"
                                           oninput="${_fnCalc}(${idx})">
                                    <div class="input-group-append">
                                        <span class="input-group-text">días</span>
                                    </div>
                                </div>
                            </div>
                            <small class="text-danger d-block mb-1" id="errorHETurno_${idx}" style="font-size:.7rem;"></small>
                            <div id="divHEDxDia_${idx}" class="form-group mb-2 d-none">
                                <label style="font-size:.78rem; font-weight:600; color:#555;">
                                    <span class="badge badge-pill" style="background:#fff3e0;color:#e65100;font-size:.65rem;">×1.25</span>
                                    HE Diurnas por día <small class="text-muted font-weight-normal">(máx. 2 h)</small>
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="nominaHEDxDia_${idx}"
                                           min="0" max="2" value="0"
                                           onchange="validarHorasExtraTurno(${idx}); ${_fnCalc}(${idx});"
                                           oninput="validarHorasExtraTurno(${idx}); ${_fnCalc}(${idx});">
                                    <div class="input-group-append">
                                        <span class="input-group-text">h/día</span>
                                    </div>
                                </div>
                            </div>
                            <div id="divHENxDia_${idx}" class="form-group mb-2 d-none">
                                <label style="font-size:.78rem; font-weight:600; color:#555;">
                                    <span class="badge badge-pill" style="background:#ede7f6;color:#6f42c1;font-size:.65rem;">×1.75</span>
                                    HE Nocturnas por día <small class="text-muted font-weight-normal">(máx. 2 h)</small>
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="nominaHENxDia_${idx}"
                                           min="0" max="2" value="0"
                                           onchange="validarHorasExtraTurno(${idx}); ${_fnCalc}(${idx});"
                                           oninput="validarHorasExtraTurno(${idx}); ${_fnCalc}(${idx});">
                                    <div class="input-group-append">
                                        <span class="input-group-text">h/día</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /seccionCostoDia -->

                    <!-- ── Bono adicional (visible en ambos modos) ── -->
                    <div class="form-group mb-2 mt-1">
                        <label class="font-weight-bold mb-1" style="font-size:.78rem; color:#555;">
                            <i class="fas fa-gift text-info mr-1"></i>Bono adicional
                            <span class="text-muted font-weight-normal">(opcional — monto plano que suma al total)</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-info text-white">$</span>
                            </div>
                            <input type="number" class="form-control" id="nominaBono_${idx}"
                                   min="0" value="0" step="1000" placeholder="0">
                        </div>
                    </div>

                    <!-- ── Novedades Operativas ── -->
                    <div class="mb-2 mt-1">
                        <div class="d-flex align-items-center mb-1"
                             style="border-bottom:1px dashed #e0e0e0; padding-bottom:4px;">
                            <i class="fas fa-list-alt text-secondary mr-1" style="font-size:.8rem;"></i>
                            <span class="font-weight-bold text-uppercase text-secondary"
                                  style="font-size:.72rem; letter-spacing:.05em;">Novedades Operativas</span>
                            <span class="badge badge-secondary ml-2" style="font-size:.65rem;">opcional</span>
                        </div>
                        <div id="tablaNovedadesNomina_${idx}">
                            <div class="text-center py-2 text-muted" style="font-size:.78rem;">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Cargando novedades...
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-1">
                            <small class="text-muted mr-2" style="font-size:.72rem;">Total novedades:</small>
                            <strong id="totalNovedadesNomina_${idx}" style="font-size:.85rem; color:#555;">$0</strong>
                        </div>
                    </div>

                    <!-- ── Resultado ── -->
                    <div class="rounded px-3 py-2 mt-1 d-flex justify-content-between align-items-center"
                        style="background: linear-gradient(90deg,#e8f5e9,#f1f8e9); border:1px solid #a5d6a7;">
                        <div>
                            <div style="font-size:.72rem; color:#555; text-transform:uppercase; letter-spacing:.04em;">
                                <i class="fas fa-calculator text-success mr-1"></i>Costo empresa
                            </div>
                            <strong id="nominaValor_${idx}" style="font-size:1.15rem; color:#2e7d32;">$0</strong>
                            <small id="nominaPersonasLabel_${idx}" class="text-muted ml-1"></small>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-sm btn-success"
                                    id="btnDesglose_${idx}" style="display:none; font-size:.78rem;"
                                    onclick="renderDesgloseLiquidacion(${idx})">
                                <i class="fas fa-table mr-1"></i>Ver Desglose
                            </button>
                        </div>
                    </div>

                </div><!-- /card-body -->
            </div>
        `;
    }).join('');

    const modalHtml = `
        <div class="modal fade" id="modalNominaConfig" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                                <i class="fas fa-users text-warning" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 font-weight-bold text-dark">Configuración de Nómina</h5>
                                <small class="text-dark" style="opacity:.75;">Configure el personal por cargo/perfil</small>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                        <div class="alert alert-info py-2 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Configure las personas y los días/horas trabajados. El motor calcula el costo empresa
                            incluyendo prestaciones sociales, seguridad social y parafiscales (Ley 1607).
                        </div>
                        <div id="nominaCargosContainer">
                            ${filasCargos}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt text-info mr-1"></i>
                                Cálculo incluye SS + Parafiscales + Prestaciones (Ley 1607)
                            </small>
                            <div class="text-right">
                                <span class="text-muted mr-1" style="font-size:.8rem;">SUBTOTAL NÓMINA</span>
                                <strong class="text-success" id="nominaSubtotalDisplay"
                                        style="font-size:1.1rem;">$0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:#fafafa;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-warning font-weight-bold" onclick="finalizarNominaConfig()">
                            <i class="fas fa-plus-circle mr-1"></i>Agregar Cargos a Cotización
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    window.nominaCargosTemporal = cargos;
    window._nominaResultados = {};
    $('#modalNominaConfig').modal('show');

    // Cargar novedades operativas para cada cargo tras mostrar el modal
    $('#modalNominaConfig').one('shown.bs.modal', function () {
        cargos.forEach((_, idx) => cargarNovedadesEnNomina(idx));
    });
}

/**
 * Alterna visibilidad del body de un cargo cuando se desactiva su checkbox
 */
function toggleCargoNomina(idx) {
    const check = document.getElementById(`checkNomina_${idx}`);
    const body = document.getElementById(`bodyNomina_${idx}`);
    if (check && body) body.style.display = check.checked ? '' : 'none';
    actualizarSubtotalNomina();
}

// ============================================================
// VALIDACIONES DE HORAS — Tiempo Ordinario y Horas Extra
// ============================================================

/**
 * Valida que la suma de los 4 campos de Tiempo Ordinario no supere 7 horas.
 * Revierte el campo al valor máximo permitido y muestra mensaje.
 */
function validarTiempoOrdinario(idx) {
    const ids = [`nominaDiasDiurnos_${idx}`, `nominaDiasNocturnos_${idx}`];
    const inputs = ids.map(id => document.getElementById(id));
    const valores = inputs.map(el => parseFloat(el?.value) || 0);
    const suma = valores.reduce((a, b) => a + b, 0);
    const errEl = document.getElementById(`errorTiempoOrd_${idx}`);

    if (suma > 7) {
        // Encontrar el último campo que causó el exceso y ajustarlo
        const exceso = suma - 7;
        const ultimo = inputs.find(el => parseFloat(el.value) > 0);
        if (ultimo) {
            const val = parseFloat(ultimo.value) || 0;
            ultimo.value = Math.max(0, val - exceso);
            ultimo.style.borderColor = '#dc3545';
            setTimeout(() => { ultimo.style.borderColor = ''; }, 2000);
        }
        if (errEl) errEl.textContent = 'El tiempo máximo en horas de Tiempo Ordinario es 7';
    } else {
        if (errEl) errEl.textContent = '';
        inputs.forEach(el => { if (el) el.style.borderColor = ''; });
    }
}

function validarTiempoDominicalesFestivos(idx) {
    const ids = [`nominaDominicales_${idx}`, `nominaDomNocturnos_${idx}`];
    const inputs = ids.map(id => document.getElementById(id));
    const valores = inputs.map(el => parseFloat(el?.value) || 0);
    const suma = valores.reduce((a, b) => a + b, 0);
    const errEl = document.getElementById(`errorTiempoDom_${idx}`);

    if (suma > 7) {
        // Encontrar el último campo que causó el exceso y ajustarlo
        const exceso = suma - 7;
        const ultimo = inputs.find(el => parseFloat(el.value) > 0);
        if (ultimo) {
            const val = parseFloat(ultimo.value) || 0;
            ultimo.value = Math.max(0, val - exceso);
            ultimo.style.borderColor = '#dc3545';
            setTimeout(() => { ultimo.style.borderColor = ''; }, 2000);
        }
        if (errEl) errEl.textContent = 'El tiempo máximo en horas de Tiempo Dominical y servicio es 7';
    } else {
        if (errEl) errEl.textContent = '';
        inputs.forEach(el => { if (el) el.style.borderColor = ''; });
    }
}

/**
 * Valida que la suma de los 4 campos de Horas Extra (Costo Hora) no supere 2.
 */
function validarHorasExtra(idx) {
    const ids = [`nominaHED_${idx}`, `nominaHEN_${idx}`,
                 `nominaHEDD_${idx}`, `nominaHEDN_${idx}`];
    const inputs = ids.map(id => document.getElementById(id));
    const valores = inputs.map(el => parseFloat(el?.value) || 0);
    const suma = valores.reduce((a, b) => a + b, 0);
    const errEl = document.getElementById(`errorHorasExtra_${idx}`);

    if (suma > 2) {
        const exceso = suma - 2;
        const ultimo = inputs.slice().reverse().find(el => parseFloat(el?.value) > 0);
        if (ultimo) {
            const val = parseFloat(ultimo.value) || 0;
            ultimo.value = Math.max(0, val - exceso);
            ultimo.style.borderColor = '#dc3545';
            setTimeout(() => { ultimo.style.borderColor = ''; }, 2000);
        }
        if (errEl) errEl.textContent = 'El máximo de horas extras es 2';
    } else {
        if (errEl) errEl.textContent = '';
        inputs.forEach(el => { if (el) el.style.borderColor = ''; });
    }
}

/**
 * Valida que la suma de HE Diurnas + HE Nocturnas por día (Costo Día/Turno) no supere 2.
 */
function validarHorasExtraTurno(idx) {
    const inpDiu = document.getElementById(`nominaHEDxDia_${idx}`);
    const inpNoc = document.getElementById(`nominaHENxDia_${idx}`);
    const errEl  = document.getElementById(`errorHETurno_${idx}`);
    const valDiu = parseFloat(inpDiu?.value) || 0;
    const valNoc = parseFloat(inpNoc?.value) || 0;
    const suma   = valDiu + valNoc;

    if (suma > 2) {
        const exceso = suma - 2;
        // Ajustar el campo sobre el que se acaba de escribir
        if (inpNoc && parseFloat(inpNoc.value) > 0) {
            inpNoc.value = Math.max(0, valNoc - exceso);
            inpNoc.style.borderColor = '#dc3545';
            setTimeout(() => { inpNoc.style.borderColor = ''; }, 2000);
        } else if (inpDiu) {
            inpDiu.value = Math.max(0, valDiu - exceso);
            inpDiu.style.borderColor = '#dc3545';
            setTimeout(() => { inpDiu.style.borderColor = ''; }, 2000);
        }
        if (errEl) errEl.textContent = 'El valor máximo son 2 horas extras';
    } else {
        if (errEl) errEl.textContent = '';
        if (inpDiu) inpDiu.style.borderColor = '';
        if (inpNoc) inpNoc.style.borderColor = '';
    }
}

// ============================================================
// NOVEDADES OPERATIVAS EN MODAL NÓMINA
// ============================================================

let _cacheNovedadesNomina = null;

/**
 * Carga las novedades con grupo_cotiza=1 y las renderiza en el panel del cargo.
 */
async function cargarNovedadesEnNomina(idx) {
    const contenedor = document.getElementById(`tablaNovedadesNomina_${idx}`);
    if (!contenedor) return;

    try {
        if (!_cacheNovedadesNomina) {
            const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
            const resp = await fetch(`${baseUrl}/admin/admin.cotizaciones.novedades-grupo-cotiza`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if (data.success) {
                _cacheNovedadesNomina = data.data;
            } else {
                contenedor.innerHTML = '<div class="alert alert-warning py-1 mb-0" style="font-size:.78rem;">No se pudieron cargar las novedades.</div>';
                return;
            }
        }
        renderizarNovedadesEnNomina(idx, _cacheNovedadesNomina);
    } catch (e) {
        contenedor.innerHTML = '<div class="alert alert-danger py-1 mb-0" style="font-size:.78rem;">Error al cargar novedades operativas.</div>';
    }
}

/**
 * Renderiza la tabla de novedades dentro del card de un cargo del modal nómina.
 */
function renderizarNovedadesEnNomina(idx, novedades) {
    const contenedor = document.getElementById(`tablaNovedadesNomina_${idx}`);
    if (!contenedor) return;

    let filas = '';
    novedades.forEach(novedad => {
        if (!novedad.detalles || novedad.detalles.length === 0) return;
        novedad.detalles.forEach(detalle => {
            const valorFmt = Number(detalle.valor_operativo).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            filas += `
            <tr>
                <td class="py-1 align-middle" style="font-size:.75rem;">
                    <strong>${novedad.nombre}</strong> · ${detalle.nombre}
                </td>
                <td class="py-1 align-middle text-right" style="font-size:.75rem; white-space:nowrap;">$${valorFmt}</td>
                <td class="py-1 align-middle" style="width:80px;">
                    <input type="number" min="0" step="1" value="0"
                           class="form-control form-control-sm text-center"
                           id="novNomina_${idx}_${detalle.id}"
                           data-detalle-id="${detalle.id}"
                           data-valor="${detalle.valor_operativo}"
                           oninput="recalcularNovedadNomina(${idx}, '${detalle.id}', ${detalle.valor_operativo})">
                </td>
                <td class="py-1 align-middle text-right" id="novNominaSub_${idx}_${detalle.id}" style="font-size:.75rem; white-space:nowrap;">
                    <span class="text-muted">$0</span>
                </td>
            </tr>`;
        });
    });

    if (!filas) {
        contenedor.innerHTML = '<div class="text-center text-muted py-1" style="font-size:.78rem;"><i class="fas fa-info-circle mr-1"></i>Sin novedades configuradas con grupo cotiza.</div>';
        return;
    }

    contenedor.innerHTML = `
        <div class="table-responsive" style="max-height:160px; overflow-y:auto;">
        <table class="table table-sm table-borderless mb-0" style="font-size:.78rem;">
            <thead style="background:#f5f5f5; position:sticky; top:0; z-index:1;">
                <tr>
                    <th class="py-1">Novedad / Detalle</th>
                    <th class="py-1 text-right">Valor Unit.</th>
                    <th class="py-1 text-center">Cantidad</th>
                    <th class="py-1 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>${filas}</tbody>
        </table>
        </div>`;
}

/**
 * Recalcula el subtotal de una fila de novedad y actualiza el total de novedades del cargo.
 */
function recalcularNovedadNomina(idx, detalleId, valorUnitario) {
    const inp = document.getElementById(`novNomina_${idx}_${detalleId}`);
    const subEl = document.getElementById(`novNominaSub_${idx}_${detalleId}`);
    if (!inp || !subEl) return;

    const cantidad = parseFloat(inp.value) || 0;
    const subtotal = cantidad * parseFloat(valorUnitario);
    subEl.innerHTML = subtotal > 0
        ? `<strong>$${Math.round(subtotal).toLocaleString('es-CO')}</strong>`
        : '<span class="text-muted">$0</span>';

    recalcularTotalNovedadesNomina(idx);
}

/**
 * Suma todos los subtotales de novedades de un cargo y actualiza su display.
 */
function recalcularTotalNovedadesNomina(idx) {
    const contenedor = document.getElementById(`tablaNovedadesNomina_${idx}`);
    const totalEl    = document.getElementById(`totalNovedadesNomina_${idx}`);
    if (!contenedor || !totalEl) return;

    let total = 0;
    contenedor.querySelectorAll('input[type="number"]').forEach(inp => {
        const cantidad = parseFloat(inp.value) || 0;
        const valor    = parseFloat(inp.dataset.valor) || 0;
        total += cantidad * valor;
    });

    totalEl.textContent = '$' + Math.round(total).toLocaleString('es-CO');
    window[`nominaNovedadesTotal_${idx}`] = total;
    actualizarSubtotalNomina();
}

/**
 * Alterna los campos visibles según el tipo de costo seleccionado
 */
function onNominaTipoCostoChange(idx) {
    const tipo = document.getElementById(`nominaTipoCosto_${idx}`)?.value;
    const campoDias = document.getElementById(`nominaCampoDias_${idx}`);
    const campoHoras = document.getElementById(`nominaCampoHoras_${idx}`);
    if (tipo === 'hora') {
        if (campoDias) campoDias.style.display = 'none';
        if (campoHoras) campoHoras.style.display = '';
    } else {
        if (campoDias) campoDias.style.display = '';
        if (campoHoras) campoHoras.style.display = 'none';
    }
    calcularValorCargoNomina(idx);
}

/**
 * Calcula el valor estimado de un cargo de nómina en tiempo real
 */
function calcularValorCargoNomina(idx) {
    const cargos = window.nominaCargosTemporal;
    if (!cargos || !cargos[idx]) return;

    const cargo = cargos[idx];
    const check = document.getElementById(`checkNomina_${idx}`);
    if (check && !check.checked) {
        window[`nominaValorCalculado_${idx}`] = 0;
        actualizarSubtotalNomina();
        return;
    }

    const personas = parseFloat(document.getElementById(`nominaPersonas_${idx}`)?.value) || 1;
    const modo = document.getElementById(`nominaModo_${idx}`)?.value || 'hora';

    let valor = 0;

    if (modo === 'hora') {
        // Modo Costo Hora: cada input representa HORAS — usar tarifas de cargos_tabla_precios
        const horasDiurnas   = parseFloat(document.getElementById(`nominaDiasDiurnos_${idx}`)?.value)   || 0;
        const horasNocturnas = parseFloat(document.getElementById(`nominaDiasNocturnos_${idx}`)?.value) || 0;
        const horasDomDiu    = parseFloat(document.getElementById(`nominaDominicales_${idx}`)?.value)   || 0;
        const horasDomNoc    = parseFloat(document.getElementById(`nominaDomNocturnos_${idx}`)?.value)  || 0;
        const hedHoras       = parseFloat(document.getElementById(`nominaHED_${idx}`)?.value)           || 0;
        const henHoras       = parseFloat(document.getElementById(`nominaHEN_${idx}`)?.value)           || 0;
        const heddHoras      = parseFloat(document.getElementById(`nominaHEDD_${idx}`)?.value)          || 0;
        const hednHoras      = parseFloat(document.getElementById(`nominaHEDN_${idx}`)?.value)          || 0;

        // Tarifas desde cargo (expuestas por CotizacionProductoController)
        const tarifaOrd  = Number(cargo.hora_ordinaria   || cargo.costo_hora || 0);
        const tarifaRN   = Number(cargo.recargo_nocturno || 0);
        const tarifaDom  = Number(cargo.hora_dominical   || tarifaOrd * 1.75);
        const tarifaHED  = Number(cargo.hora_extra_diurna               || tarifaOrd * 1.25);
        const tarifaHEN  = Number(cargo.hora_extra_nocturna             || tarifaOrd * 1.75);
        const tarifaHEDD = Number(cargo.hora_extra_dominical_diurna     || tarifaOrd * 2.00);
        const tarifaHEDN = Number(cargo.hora_extra_dominical_nocturna   || tarifaOrd * 2.50);

        const costoOrdDiu = horasDiurnas   * tarifaOrd;
        const costoOrdNoc = horasNocturnas * (tarifaOrd + tarifaRN);
        const costoDomDiu = horasDomDiu    * tarifaDom;
        const costoDomNoc = horasDomNoc    * (tarifaDom + tarifaRN);
        const costoHED    = hedHoras       * tarifaHED;
        const costoHEN    = henHoras       * tarifaHEN;
        const costoHEDD   = heddHoras      * tarifaHEDD;
        const costoHEDN   = hednHoras      * tarifaHEDN;

        const costoMes = costoOrdDiu + costoOrdNoc + costoDomDiu + costoDomNoc
                       + costoHED + costoHEN + costoHEDD + costoHEDN;
        valor = costoMes * personas;
    } else {
        // Modo Costo Día (Turno): calcular horas por turno × tarifa × días trabajados
        const params = construirParamsDesdreTurno(idx);
        if (params) {
            const tarifaOrd  = Number(cargo.hora_ordinaria   || cargo.costo_hora || 0);
            const tarifaRN   = Number(cargo.recargo_nocturno || 0);
            const tarifaDom  = Number(cargo.hora_dominical   || tarifaOrd * 1.75);
            const tarifaHED  = Number(cargo.hora_extra_diurna               || tarifaOrd * 1.25);
            const tarifaHEN  = Number(cargo.hora_extra_nocturna             || tarifaOrd * 1.75);
            const tarifaHEDD = Number(cargo.hora_extra_dominical_diurna     || tarifaOrd * 2.00);
            const tarifaHEDN = Number(cargo.hora_extra_dominical_nocturna   || tarifaOrd * 2.50);

            const costoOrdDiu = params.dias_diurnos              * tarifaOrd;
            const costoOrdNoc = params.dias_nocturnos            * (tarifaOrd + tarifaRN);
            const costoDomDiu = params.dominicales_diurnos       * tarifaDom;
            const costoDomNoc = params.dominicales_nocturnos     * (tarifaDom + tarifaRN);
            const costoHED    = params.horas_extra_diurnas       * tarifaHED;
            const costoHEN    = params.horas_extra_nocturnas     * tarifaHEN;
            const costoHEDD   = params.horas_extra_dom_diurnas   * tarifaHEDD;
            const costoHEDN   = params.horas_extra_dom_nocturnas * tarifaHEDN;

            const costoTotal = costoOrdDiu + costoOrdNoc + costoDomDiu + costoDomNoc
                             + costoHED + costoHEN + costoHEDD + costoHEDN;
            valor = costoTotal * personas;
        }
    }

    const valorEl = document.getElementById(`nominaValor_${idx}`);
    if (valorEl) {
        valorEl.textContent = '$' + Math.round(valor).toLocaleString('es-CO');
    }
    window[`nominaValorCalculado_${idx}`] = valor;
    actualizarSubtotalNomina();
}

/**
 * Actualiza el subtotal total sumando todos los cargos activos de nómina
 */
function actualizarSubtotalNomina() {
    const cargos = window.nominaCargosTemporal;
    if (!cargos) return;
    let subtotal = 0;
    cargos.forEach((_, idx) => {
        const check = document.getElementById(`checkNomina_${idx}`);
        if (!check || check.checked) {
            subtotal += window[`nominaValorCalculado_${idx}`] || 0;
            subtotal += window[`nominaNovedadesTotal_${idx}`] || 0;
            subtotal += parseFloat(document.getElementById(`nominaBono_${idx}`)?.value) || 0;
        }
    });
    const el = document.getElementById('nominaSubtotalDisplay');
    if (el) el.textContent = '$' + subtotal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Llama al Motor de Liquidación de Nómina (backend PHP) y actualiza el display del cargo.
 * Si el endpoint falla, cae al cálculo simple local como fallback.
 */
async function calcularLiquidacionNomina(idx) {
    const cargos = window.nominaCargosTemporal;
    if (!cargos || !cargos[idx]) return;

    const cargo = cargos[idx];
    const check = document.getElementById(`checkNomina_${idx}`);
    if (check && !check.checked) {
        window[`nominaValorCalculado_${idx}`] = 0;
        window._nominaResultados[idx] = null;
        actualizarSubtotalNomina();
        return;
    }

    const personas      = parseInt(document.getElementById(`nominaPersonas_${idx}`)?.value)       || 1;
    const modo          = document.getElementById(`nominaModo_${idx}`)?.value || 'hora';

    let diasDiurnos, diasNoct, domDiurnos, domNoct, hedHoras, henHoras, heddHoras, hednHoras;

    if (modo === 'dia') {
        // Modo Costo Día: mapear turno + días + HE al Motor
        const params = construirParamsDesdreTurno(idx);
        if (!params) {
            // Sin turno seleccionado — limpiar resultado
            const valorEl = document.getElementById(`nominaValor_${idx}`);
            if (valorEl) valorEl.textContent = '$0';
            window[`nominaValorCalculado_${idx}`] = 0;
            window._nominaResultados[idx] = null;
            actualizarSubtotalNomina();
            return;
        }
        diasDiurnos = params.dias_diurnos;
        diasNoct    = params.dias_nocturnos;
        domDiurnos  = params.dominicales_diurnos;
        domNoct     = params.dominicales_nocturnos;
        hedHoras    = params.horas_extra_diurnas;
        henHoras    = params.horas_extra_nocturnas;
        heddHoras   = params.horas_extra_dom_diurnas;
        hednHoras   = params.horas_extra_dom_nocturnas;
        console.log('[Nómina] API payload modo=dia', { diasDiurnos, diasNoct, domDiurnos, domNoct, hedHoras, henHoras, heddHoras, hednHoras });
    } else {
        // Modo Costo Hora: leer inputs existentes (sin cambios)
        diasDiurnos = parseInt(document.getElementById(`nominaDiasDiurnos_${idx}`)?.value)     || 0;
        diasNoct    = parseInt(document.getElementById(`nominaDiasNocturnos_${idx}`)?.value)   || 0;
        domDiurnos  = parseInt(document.getElementById(`nominaDominicales_${idx}`)?.value)     || 0;
        domNoct     = parseInt(document.getElementById(`nominaDomNocturnos_${idx}`)?.value)    || 0;
        hedHoras    = parseInt(document.getElementById(`nominaHED_${idx}`)?.value)             || 0;
        henHoras    = parseInt(document.getElementById(`nominaHEN_${idx}`)?.value)             || 0;
        heddHoras   = parseInt(document.getElementById(`nominaHEDD_${idx}`)?.value)            || 0;
        hednHoras   = parseInt(document.getElementById(`nominaHEDN_${idx}`)?.value)            || 0;
    }

    // Solo calcular si hay algo configurado
    const diasTotal = diasDiurnos + diasNoct + domDiurnos + domNoct;
    if (diasTotal === 0 && hedHoras === 0 && henHoras === 0 && heddHoras === 0 && hednHoras === 0) {
        const valorEl = document.getElementById(`nominaValor_${idx}`);
        if (valorEl) valorEl.textContent = '$0';
        window[`nominaValorCalculado_${idx}`] = 0;
        window._nominaResultados[idx] = null;
        actualizarSubtotalNomina();
        return;
    }

    const cargoId = cargo.cargo_id || cargo.cargo?.id || cargo.id;
    const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const año = new Date().getFullYear();

    try {
        const resp = await fetch(`${baseUrl}/admin/cotizaciones/nomina/calcular`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                cargo_id:                   cargoId,
                año:                        año,
                cantidad_personas:          personas,
                dias_diurnos:               diasDiurnos,
                dias_nocturnos:             diasNoct,
                dominicales_diurnos:        domDiurnos,
                dominicales_nocturnos:      domNoct,
                horas_extra_diurnas:        hedHoras,
                horas_extra_nocturnas:      henHoras,
                horas_extra_dom_diurnas:    heddHoras,
                horas_extra_dom_nocturnas:  hednHoras,
            }),
        });

        const json = await resp.json();

        if (json.success && json.data) {
            const data = json.data;
            console.log('[Nómina] API respuesta OK — mes:', data.costo_empresa_mes, '| total:', data.costo_empresa_total, '| fuente:', data.fuente_calculo, data);
            window._nominaResultados[idx] = data;
            window[`nominaValorCalculado_${idx}`] = data.costo_empresa_total;

            // Actualizar display
            const fmt = v => '$' + Math.round(v).toLocaleString('es-CO');
            const valorEl = document.getElementById(`nominaValor_${idx}`);
            if (valorEl) valorEl.textContent = fmt(data.costo_empresa_total);

            const labelEl = document.getElementById(`nominaPersonasLabel_${idx}`);
            if (labelEl && personas > 1) labelEl.textContent = `(${personas} pers. × ${fmt(data.costo_empresa_mes)})`;
            else if (labelEl) labelEl.textContent = '';

            // Resumen rápido
            const resumenEl = document.getElementById(`nominaResumenRapido_${idx}`);
            if (resumenEl) {
                document.getElementById(`nominaDevengado_${idx}`).textContent = fmt(data.devengados.total_devengado);
                document.getElementById(`nominaIBC_${idx}`).textContent       = fmt(data.ibc);
                document.getElementById(`nominaNeto_${idx}`).textContent      = fmt(data.neto_empleado);
                resumenEl.style.display = '';
            }

            // Mostrar botón Ver Desglose
            const btnDesglose = document.getElementById(`btnDesglose_${idx}`);
            if (btnDesglose) btnDesglose.style.display = '';

            if (data.es_exonerado_ley1607) {
                const valorEl2 = document.getElementById(`nominaValor_${idx}`);
                // if (valorEl2) valorEl2.insertAdjacentHTML('afterend', ' <small class="badge badge-info">Exonerado Ley 1607</small>');
            }
        } else {
            // Fallback al cálculo local
            calcularValorCargoNomina(idx);
        }
    } catch (e) {
        console.error('[Nómina] API error, usando fallback local', e);
        // Fallback silencioso al cálculo local si el endpoint no está disponible
        calcularValorCargoNomina(idx);
    }

    actualizarSubtotalNomina();
}

/**
 * Muestra el modal de desglose detallado de liquidación para un cargo
 */
// function renderDesgloseLiquidacion(idx) {
//     const data = window._nominaResultados?.[idx];
//     if (!data) return;

//     const fmt = v => '$' + Math.round(v).toLocaleString('es-CO');
//     const exStr = data.es_exonerado_ley1607
//         ? '<span class="badge badge-info">EXONERADO Ley 1607</span> $0'
//         : fmt(data.costo_empleador.seguridad_social.salud);

//     const existente = document.getElementById('modalDesgloseNomina');
//     if (existente) existente.remove();

//     const html = `
//     <div class="modal fade" id="modalDesgloseNomina" tabindex="-1" role="dialog" style="z-index:1060;">
//         <div class="modal-dialog modal-md" role="document">
//             <div class="modal-content">
//                 <div class="modal-header bg-dark text-white py-2">
//                     <h6 class="modal-title mb-0">
//                         <i class="fas fa-list-alt mr-1"></i>
//                         Desglose Liquidación: ${data.cargo.nombre}
//                         (${data.cantidad_personas} persona${data.cantidad_personas > 1 ? 's' : ''})
//                     </h6>
//                     <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
//                 </div>
//                 <div class="modal-body p-3" style="font-size:.85rem;">
//                     <!-- Devengados -->
//                     <table class="table table-sm table-borderless mb-2">
//                         <thead><tr><th colspan="2" class="bg-light">DEVENGADOS (por persona)</th></tr></thead>
//                         <tbody>
//                             ${data.devengados.salario_ordinario > 0 ? `<tr><td>Salario ordinario</td><td class="text-right">${fmt(data.devengados.salario_ordinario)}</td></tr>` : ''}
//                             ${data.devengados.recargo_nocturno > 0 ? `<tr><td>Recargo nocturno (×1.35)</td><td class="text-right">${fmt(data.devengados.recargo_nocturno)}</td></tr>` : ''}
//                             ${data.devengados.dominicales_diurnos > 0 ? `<tr><td>Dominicales diurnos (×1.75)</td><td class="text-right">${fmt(data.devengados.dominicales_diurnos)}</td></tr>` : ''}
//                             ${data.devengados.dominicales_nocturnos > 0 ? `<tr><td>Dominicales nocturnos (×2.10)</td><td class="text-right">${fmt(data.devengados.dominicales_nocturnos)}</td></tr>` : ''}
//                             ${data.devengados.horas_extra_diurnas > 0 ? `<tr><td>HED (×1.25)</td><td class="text-right">${fmt(data.devengados.horas_extra_diurnas)}</td></tr>` : ''}
//                             ${data.devengados.horas_extra_nocturnas > 0 ? `<tr><td>HEN (×1.75)</td><td class="text-right">${fmt(data.devengados.horas_extra_nocturnas)}</td></tr>` : ''}
//                             ${data.devengados.horas_extra_dom_diurnas > 0 ? `<tr><td>HEDD (×2.00)</td><td class="text-right">${fmt(data.devengados.horas_extra_dom_diurnas)}</td></tr>` : ''}
//                             ${data.devengados.horas_extra_dom_nocturnas > 0 ? `<tr><td>HEDN (×2.50)</td><td class="text-right">${fmt(data.devengados.horas_extra_dom_nocturnas)}</td></tr>` : ''}
//                             ${data.devengados.aux_transporte_proporcional > 0 ? `<tr><td>Aux. transporte proporcional</td><td class="text-right">${fmt(data.devengados.aux_transporte_proporcional)}</td></tr>` : ''}
//                             <tr class="font-weight-bold border-top"><td>Total devengado</td><td class="text-right">${fmt(data.devengados.total_devengado)}</td></tr>
//                         </tbody>
//                     </table>
//                     <!-- IBC y neto -->
//                     <table class="table table-sm table-borderless mb-2">
//                         <thead><tr><th colspan="2" class="bg-light">EMPLEADO</th></tr></thead>
//                         <tbody>
//                             <tr><td>IBC</td><td class="text-right">${fmt(data.ibc)}</td></tr>
//                             <tr><td>− Salud empleado (4%)</td><td class="text-right text-danger">−${fmt(data.deducciones_empleado.salud)}</td></tr>
//                             <tr><td>− Pensión empleado (4%)</td><td class="text-right text-danger">−${fmt(data.deducciones_empleado.pension)}</td></tr>
//                             <tr class="font-weight-bold border-top"><td>Neto empleado</td><td class="text-right text-primary">${fmt(data.neto_empleado)}</td></tr>
//                         </tbody>
//                     </table>
//                     <!-- Costo empresa -->
//                     <table class="table table-sm table-borderless mb-2">
//                         <thead><tr><th colspan="2" class="bg-light">COSTO EMPRESA (Seguridad Social + Parafiscales)</th></tr></thead>
//                         <tbody>
//                             <tr><td>Salud empleador (8.5%)</td><td class="text-right">${exStr}</td></tr>
//                             <tr><td>Pensión empleador (12%)</td><td class="text-right">${fmt(data.costo_empleador.seguridad_social.pension)}</td></tr>
//                             <tr><td>ARL Nivel ${data.arl_nivel} (${data.arl_porcentaje}%)</td><td class="text-right">${fmt(data.costo_empleador.seguridad_social.arl)}</td></tr>
//                             <tr><td>SENA (2%)</td><td class="text-right">${data.es_exonerado_ley1607 ? '<span class="badge badge-info">EXONERADO</span> $0' : fmt(data.costo_empleador.parafiscales.sena)}</td></tr>
//                             <tr><td>ICBF (3%)</td><td class="text-right">${data.es_exonerado_ley1607 ? '<span class="badge badge-info">EXONERADO</span> $0' : fmt(data.costo_empleador.parafiscales.icbf)}</td></tr>
//                             <tr><td>Caja Compensación (4%)</td><td class="text-right">${fmt(data.costo_empleador.parafiscales.caja)}</td></tr>
//                         </tbody>
//                     </table>
//                     <table class="table table-sm table-borderless mb-2">
//                         <thead><tr><th colspan="2" class="bg-light">PRESTACIONES SOCIALES</th></tr></thead>
//                         <tbody>
//                             <tr><td>Prima (8.333%)</td><td class="text-right">${fmt(data.costo_empleador.provisiones.prima)}</td></tr>
//                             <tr><td>Cesantías (8.333%)</td><td class="text-right">${fmt(data.costo_empleador.provisiones.cesantias)}</td></tr>
//                             <tr><td>Int. Cesantías (1%/mes)</td><td class="text-right">${fmt(data.costo_empleador.provisiones.intereses_cesantias)}</td></tr>
//                             <tr><td>Vacaciones (4.167%)</td><td class="text-right">${fmt(data.costo_empleador.provisiones.vacaciones)}</td></tr>
//                         </tbody>
//                     </table>
//                     <div class="alert alert-warning py-2 text-right mb-0">
//                         <span>COSTO EMPRESA / mes / persona:</span>
//                         <strong class="ml-2" style="font-size:1.05rem;">${fmt(data.costo_empresa_mes)}</strong>
//                         ${data.cantidad_personas > 1 ? `<br><span class="text-muted">× ${data.cantidad_personas} personas = </span><strong>${fmt(data.costo_empresa_total)}</strong>` : ''}
//                     </div>
//                 </div>
//                 <div class="modal-footer py-2">
//                     <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
//                 </div>
//             </div>
//         </div>
//     </div>`;

//     document.body.insertAdjacentHTML('beforeend', html);
//     $('#modalDesgloseNomina').modal('show');
//     $('#modalDesgloseNomina').on('hidden.bs.modal', function () {
//         document.getElementById('modalDesgloseNomina')?.remove();
//     });
// }

function renderDesgloseLiquidacion(idx) {
    const data = window._nominaResultados?.[idx];
    if (!data) return;

    const fmt = v => '$' + Math.round(v).toLocaleString('es-CO');
    const exStr = data.es_exonerado_ley1607
        ? '<span class="badge badge-info">EXONERADO Ley 1607</span> $0'
        : fmt(data.costo_empleador.seguridad_social.salud);

    const existente = document.getElementById('modalDesgloseNomina');
    if (existente) existente.remove();

    const html = `
    <div class="modal fade" id="modalDesgloseNomina" tabindex="-1" role="dialog" style="z-index:1060;">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title mb-0">
                        <i class="fas fa-list-alt mr-1"></i>
                        Desglose Liquidación: ${data.cargo.nombre}
                        (${data.cantidad_personas} persona${data.cantidad_personas > 1 ? 's' : ''})
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-3" style="font-size:.85rem;">
                    <!-- Devengados -->
                    <table class="table table-sm table-borderless mb-2">
                        <thead><tr><th colspan="2" class="bg-light">DEVENGADOS (por persona)</th></tr></thead>
                        <tbody>
                            ${data.devengados.salario_ordinario > 0 ? `<tr><td>Salario ordinario</td><td class="text-right">${fmt(data.devengados.salario_ordinario)}</td></tr>` : ''}
                            ${data.devengados.recargo_nocturno > 0 ? `<tr><td>Recargo nocturno (×1.35)</td><td class="text-right">${fmt(data.devengados.recargo_nocturno)}</td></tr>` : ''}
                            ${data.devengados.dominicales_diurnos > 0 ? `<tr><td>Dominicales diurnos (×1.75)</td><td class="text-right">${fmt(data.devengados.dominicales_diurnos)}</td></tr>` : ''}
                            ${data.devengados.dominicales_nocturnos > 0 ? `<tr><td>Dominicales nocturnos (×2.10)</td><td class="text-right">${fmt(data.devengados.dominicales_nocturnos)}</td></tr>` : ''}
                            ${data.devengados.horas_extra_diurnas > 0 ? `<tr><td>HED (×1.25)</td><td class="text-right">${fmt(data.devengados.horas_extra_diurnas)}</td></tr>` : ''}
                            ${data.devengados.horas_extra_nocturnas > 0 ? `<tr><td>HEN (×1.75)</td><td class="text-right">${fmt(data.devengados.horas_extra_nocturnas)}</td></tr>` : ''}
                            ${data.devengados.horas_extra_dom_diurnas > 0 ? `<tr><td>HEDD (×2.00)</td><td class="text-right">${fmt(data.devengados.horas_extra_dom_diurnas)}</td></tr>` : ''}
                            ${data.devengados.horas_extra_dom_nocturnas > 0 ? `<tr><td>HEDN (×2.50)</td><td class="text-right">${fmt(data.devengados.horas_extra_dom_nocturnas)}</td></tr>` : ''}
                            ${data.devengados.aux_transporte_proporcional > 0 ? `<tr><td>Aux. transporte proporcional</td><td class="text-right">${fmt(data.devengados.aux_transporte_proporcional)}</td></tr>` : ''}
                            <tr class="font-weight-bold border-top"><td>Total devengado</td><td class="text-right">${fmt(data.devengados.total_devengado)}</td></tr>
                        </tbody>
                    </table>
                    <div class="alert alert-warning py-2 text-right mb-0">
                        <span>COSTO EMPRESA / persona:</span>
                        <strong class="ml-2" style="font-size:1.05rem;">${fmt(data.costo_empresa_mes)}</strong>
                        ${data.cantidad_personas > 1 ? `<br><span class="text-muted">× ${data.cantidad_personas} personas = </span><strong>${fmt(data.costo_empresa_total)}</strong>` : ''}
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', html);
    $('#modalDesgloseNomina').modal('show');
    $('#modalDesgloseNomina').on('hidden.bs.modal', function () {
        document.getElementById('modalDesgloseNomina')?.remove();
    });
}

/**
 * Finaliza la configuración de nómina y agrega los cargos a productosSeleccionados
 */
function finalizarNominaConfig() {
    const cargos = window.nominaCargosTemporal;
    if (!cargos || cargos.length === 0) {
        toastr.warning('No hay cargos disponibles');
        return;
    }

    // Validar horas antes de proceder
    let hayErrores = false;
    cargos.forEach((_, idx) => {
        const check = document.getElementById(`checkNomina_${idx}`);
        if (!check || !check.checked) return;
        const modo = document.getElementById(`nominaModo_${idx}`)?.value || 'hora';

        if (modo === 'hora') {
            const sumaTO = ['nominaDiasDiurnos', 'nominaDiasNocturnos', 'nominaDominicales', 'nominaDomNocturnos']
                .reduce((acc, name) => acc + (parseFloat(document.getElementById(`${name}_${idx}`)?.value) || 0), 0);
            const sumaHE = ['nominaHED', 'nominaHEN', 'nominaHEDD', 'nominaHEDN']
                .reduce((acc, name) => acc + (parseFloat(document.getElementById(`${name}_${idx}`)?.value) || 0), 0);
            if (sumaTO > 7) { hayErrores = true; }
            if (sumaHE > 2) { hayErrores = true; }
        } else {
            const heDiu = parseFloat(document.getElementById(`nominaHEDxDia_${idx}`)?.value) || 0;
            const heNoc = parseFloat(document.getElementById(`nominaHENxDia_${idx}`)?.value) || 0;
            if (heDiu + heNoc > 2) { hayErrores = true; }
        }
    });

    if (hayErrores) {
        Swal.fire({ type: 'warning', title: 'Horas inválidas', text: 'Corrija los errores de horas antes de continuar.', confirmButtonText: 'Entendido' });
        return;
    }

    const itemsAgregados = [];
    const errores = [];

    cargos.forEach((cargo, idx) => {
        const check = document.getElementById(`checkNomina_${idx}`);
        if (!check || !check.checked) return;

        const personas      = parseFloat(document.getElementById(`nominaPersonas_${idx}`)?.value)     || 1;
        const modo          = document.getElementById(`nominaModo_${idx}`)?.value || 'hora';
        const bono          = parseFloat(document.getElementById(`nominaBono_${idx}`)?.value)         || 0;
        const diasDiurnos   = parseFloat(document.getElementById(`nominaDiasDiurnos_${idx}`)?.value)   || 0;
        const diasNocturnos = parseFloat(document.getElementById(`nominaDiasNocturnos_${idx}`)?.value) || 0;
        const dominicales   = parseFloat(document.getElementById(`nominaDominicales_${idx}`)?.value)   || 0;
        const domNoct       = parseFloat(document.getElementById(`nominaDomNocturnos_${idx}`)?.value)  || 0;
        const hedHoras      = parseFloat(document.getElementById(`nominaHED_${idx}`)?.value)           || 0;
        const henHoras      = parseFloat(document.getElementById(`nominaHEN_${idx}`)?.value)           || 0;
        const heddHoras     = parseFloat(document.getElementById(`nominaHEDD_${idx}`)?.value)          || 0;
        const hednHoras     = parseFloat(document.getElementById(`nominaHEDN_${idx}`)?.value)          || 0;
        const valorTotal    = window[`nominaValorCalculado_${idx}`] || 0;

        if (valorTotal <= 0) {
            // Cargo marcado pero sin días/horas — se omite silenciosamente
            return;
        }

        // Recopilar novedades con cantidad > 0
        const novedadesRecopiladas = [];
        const contenedorNov = document.getElementById(`tablaNovedadesNomina_${idx}`);
        if (contenedorNov) {
            contenedorNov.querySelectorAll('input[type="number"]').forEach(inp => {
                const cantidad = parseFloat(inp.value) || 0;
                if (cantidad > 0) {
                    const valor = parseFloat(inp.dataset.valor) || 0;
                    novedadesRecopiladas.push({
                        novedad_detalle_id: parseInt(inp.dataset.detalleId),
                        valor: valor,
                        cantidad: cantidad,
                        subtotal: cantidad * valor,
                    });
                }
            });
        }

        // Usar resultado del motor si está disponible; si no, fallback a cálculo simple
        const liquidacion  = window._nominaResultados?.[idx];
        const costoEmpresaMes = liquidacion ? liquidacion.costo_empresa_mes : (personas > 0 ? valorTotal / personas : valorTotal);
        const tipoCostoFinal  = modo === 'dia' ? 'dia' : 'hora';
        const novedadesTotal  = window[`nominaNovedadesTotal_${idx}`] || 0;

        const productoNomina = {
            id: cargo.id,
            nombre: cargo.nombre || cargo.cargo?.nombre || 'Cargo',
            codigo: cargo.codigo || `NOM-${cargo.id}`,
            descripcion: `Nómina: ${cargo.nombre || cargo.cargo?.nombre}`,
            categoria_id: cargo.categoria_id || null,
            categoria: cargo.categoria || { id: null, nombre: 'NOMINA' },
            cargo_id: cargo.cargo_id || cargo.cargo?.id || null,
            tabla_precios_id: cargo.tabla_precios_id || cargo.tabla_id || null,
            tipo: 'cargo_tabla',
            flujo_tipo: 'nomina',
            unidad: 'PERSONA',
            cantidad: personas,
            precio: costoEmpresaMes,
            bono: bono,
            novedadesTotal: novedadesTotal,
            liquidacion_detalle: liquidacion || null,
            configuracionCosto: {
                tipoCosto: tipoCostoFinal,
                costoUnitario: costoEmpresaMes,
                bono: bono,
                turnoId: modo === 'dia' ? (document.getElementById(`nominaTurno_${idx}`)?.value || null) : null,
                diasTurno: modo === 'dia' ? (parseInt(document.getElementById(`nominaDiasTurno_${idx}`)?.value) || 0) : null,
                diasDiurnos: diasDiurnos,
                diasNocturnos: diasNocturnos,
                dominicalesDiurnos: dominicales,
                dominicalesNocturnos: domNoct,
                horasExtraDiurnas: hedHoras,
                horasExtraNocturnas: henHoras,
                horasExtraDomDiurnas: heddHoras,
                horasExtraDomNocturnas: hednHoras,
                diasRemuneradosDiurnos: diasDiurnos,
                diasRemuneradosNocturnos: diasNocturnos,
                incluirDominicales: dominicales > 0 || domNoct > 0,
                novedades: novedadesRecopiladas
            },
            cotizacion_item_id: window.subitemTemporal?.item?.id || null,
            cotizacion_subitem_id: window.subitemTemporal?.subitem?.id || null,
            item_propio_id: null,
            parametrizacion_id: null
        };

        productosSeleccionados.push(productoNomina);
        itemsAgregados.push(productoNomina);
    });

    if (errores.length > 0) {
        Swal.fire({ type: 'warning', title: 'Atención', html: errores.join('<br>'), confirmButtonText: 'Entendido' });
        return;
    }

    if (itemsAgregados.length === 0) {
        toastr.warning('Marque al menos un cargo y configure sus días u horas trabajados para poder agregarlo.');
        return;
    }

    itemsAgregados.forEach(prod => agregarProductoATablaSeleccionados(prod));

    window.nominaCargosTemporal = null;
    window.subitemTemporal = null;

    $('#modalNominaConfig').modal('hide');

    actualizarTotalGeneral();
    toastr.success(`${itemsAgregados.length} cargo(s) de nómina agregado(s). Haga clic en "Confirmar y Agregar" para guardar.`);
}

/**
 * Agrega un producto al tbody de productos seleccionados en el modal principal
 */
function agregarProductoATablaSeleccionados(producto) {
    const tbody = document.getElementById('tbodyProductosSeleccionados');
    if (!tbody) return;

    const emptyRow = tbody.querySelector('#noProductosSeleccionados');
    if (emptyRow) emptyRow.remove();

    const esNomina = producto.flujo_tipo === 'nomina' || producto.tipo === 'cargo_tabla';
    const badgeNomina = esNomina ? ' <span class="badge badge-warning">Nómina</span>' : '';
    const bono          = parseFloat(producto.bono           || 0);
    const novedadesDisp = parseFloat(producto.novedadesTotal || 0);
    const valorBase     = parseFloat(producto.precio) * parseFloat(producto.cantidad);
    const valorTotal    = (valorBase + bono + novedadesDisp).toFixed(2);
    const bonoStr      = bono > 0        ? ` &bull; <i class="fas fa-gift text-info"></i> Bono: $${bono.toLocaleString('es-CO')}`               : '';
    const novStr       = novedadesDisp > 0 ? ` &bull; <i class="fas fa-clipboard-list text-warning"></i> Nov.: $${novedadesDisp.toLocaleString('es-CO')}` : '';

    const tr = document.createElement('tr');
    tr.setAttribute('data-item-id', producto.id || `nomina_${Date.now()}`);
    tr.innerHTML = `
        <td>
            <strong>${producto.nombre}</strong>${badgeNomina}
            <br><small class="text-muted">${producto.codigo || ''} &bull; ${producto.cantidad} persona(s) &bull; $${parseFloat(valorTotal).toLocaleString('es-CO')}${bonoStr}${novStr}</small>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="this.closest('tr').remove(); actualizarTotalGeneral();">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

// ============================================================
// HELPERS DE COSTO DÍA (TURNO)
// ============================================================

/**
 * Alterna entre modo "Costo Hora" y "Costo Día (Turno)" para un cargo de nómina.
 * NO toca ninguna lógica de Costo Hora.
 */
function onNominaModoChange(idx, modo) {
    const secHora = document.getElementById(`seccionCostoHora_${idx}`);
    const secDia  = document.getElementById(`seccionCostoDia_${idx}`);
    const hiddenModo = document.getElementById(`nominaModo_${idx}`);
    const btnHora = document.getElementById(`btnModoHora_${idx}`);
    const btnDia  = document.getElementById(`btnModoDia_${idx}`);

    if (!secHora || !secDia) return;

    if (modo === 'dia') {
        secHora.classList.add('d-none');
        secDia.classList.remove('d-none');
        btnHora.classList.remove('btn-primary', 'active');
        btnHora.classList.add('btn-outline-primary');
        btnDia.classList.remove('btn-outline-success');
        btnDia.classList.add('btn-success', 'active');

        // Poblar select de turnos si está vacío
        const sel = document.getElementById(`nominaTurno_${idx}`);
        if (sel && sel.options.length <= 1) {
            const turnos = window.nominaTurnos || [];
            if (turnos.length === 0) {
                sel.insertAdjacentHTML('beforeend',
                    '<option disabled>Sin turnos activos — configure en Nómina → Turnos</option>');
            } else {
                turnos.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.dataset.turno = JSON.stringify(t);
                    opt.textContent = t.nombre + (t.descripcion ? ` — ${t.descripcion}` : '');
                    sel.appendChild(opt);
                });
            }
        }
    } else {
        secDia.classList.add('d-none');
        secHora.classList.remove('d-none');
        btnDia.classList.remove('btn-success', 'active');
        btnDia.classList.add('btn-outline-success');
        btnHora.classList.remove('btn-outline-primary');
        btnHora.classList.add('btn-primary', 'active');
    }

    if (hiddenModo) hiddenModo.value = modo;

    // Resetear resultado al cambiar modo
    const valorEl = document.getElementById(`nominaValor_${idx}`);
    if (valorEl) valorEl.textContent = '$0';
    window[`nominaValorCalculado_${idx}`] = 0;
    if (window._nominaResultados) window._nominaResultados[idx] = null;
    actualizarSubtotalNomina();
}

/**
 * Actualiza la visibilidad de los inputs de HE al cambiar de turno.
 */
function onNominaTurnoChange(idx) {
    const sel = document.getElementById(`nominaTurno_${idx}`);
    if (!sel) return;

    const selOpt = sel.options[sel.selectedIndex];
    const turno = selOpt?.dataset?.turno ? JSON.parse(selOpt.dataset.turno) : null;

    const divHED = document.getElementById(`divHEDxDia_${idx}`);
    const divHEN = document.getElementById(`divHENxDia_${idx}`);
    const inpHED = document.getElementById(`nominaHEDxDia_${idx}`);
    const inpHEN = document.getElementById(`nominaHENxDia_${idx}`);

    if (!turno) {
        if (divHED) divHED.classList.add('d-none');
        if (divHEN) divHEN.classList.add('d-none');
        return;
    }

    const maxHE = parseInt(turno.max_horas_extras) || 2;

    if (divHED) divHED.classList.toggle('d-none', !turno.tiene_extras_diurnas);
    if (divHEN) divHEN.classList.toggle('d-none', !turno.tiene_extras_nocturnas);
    if (inpHED) { inpHED.max = maxHE; inpHED.value = 0; }
    if (inpHEN) { inpHEN.max = maxHE; inpHEN.value = 0; }

    // Limpiar error
    const errEl = document.getElementById(`errorTurno_${idx}`);
    if (errEl) errEl.textContent = '';

    calcularLiquidacionNomina(idx);
}

/**
 * Construye los 8 parámetros del Motor de Liquidación a partir de la config de turno.
 * Devuelve null si no hay turno seleccionado.
 */
function construirParamsDesdreTurno(idx) {
    const sel = document.getElementById(`nominaTurno_${idx}`);
    if (!sel || !sel.value) return null;

    const selOpt = sel.options[sel.selectedIndex];
    const turno = selOpt?.dataset?.turno ? JSON.parse(selOpt.dataset.turno) : null;
    if (!turno) return null;

    const dias     = parseInt(document.getElementById(`nominaDiasTurno_${idx}`)?.value) || 0;
    // Horas ordinarias por día según la configuración del turno (max_horas_ordinarias)
    const horasOrd = parseInt(turno.max_horas_ordinarias) || 7;
    // Total horas ordinarias = días trabajados × horas/día del turno
    const horasOrdinarias = dias * horasOrd;

    const heDxDia = turno.tiene_extras_diurnas
        ? (parseInt(document.getElementById(`nominaHEDxDia_${idx}`)?.value) || 0) : 0;
    const heNxDia = turno.tiene_extras_nocturnas
        ? (parseInt(document.getElementById(`nominaHENxDia_${idx}`)?.value) || 0) : 0;

    const esDom    = !!turno.es_dominical_festivo;
    const esDiurna = turno.tipo_ordinaria === 'diurna';

    const result = {
        // Horas ordinarias: clasificadas por tipo de turno (diurno/nocturno) y si es dominical
        dias_diurnos:              (!esDom && esDiurna)  ? horasOrdinarias : 0,
        dias_nocturnos:            (!esDom && !esDiurna) ? horasOrdinarias : 0,
        dominicales_diurnos:       (esDom  && esDiurna)  ? horasOrdinarias : 0,
        dominicales_nocturnos:     (esDom  && !esDiurna) ? horasOrdinarias : 0,
        // Horas extra: condición doble — es_dominical_festivo + tiene_extras_diurnas/nocturnas
        horas_extra_diurnas:       (!esDom && !!turno.tiene_extras_diurnas)   ? heDxDia * dias : 0,
        horas_extra_nocturnas:     (!esDom && !!turno.tiene_extras_nocturnas) ? heNxDia * dias : 0,
        horas_extra_dom_diurnas:   (esDom  && !!turno.tiene_extras_diurnas)   ? heDxDia * dias : 0,
        horas_extra_dom_nocturnas: (esDom  && !!turno.tiene_extras_nocturnas) ? heNxDia * dias : 0,
    };

    console.log('[Nómina] construirParamsDesdreTurno', {
        turno_nombre: turno.nombre,
        max_horas_ordinarias: turno.max_horas_ordinarias,
        es_dominical_festivo: turno.es_dominical_festivo,
        tiene_extras_diurnas: turno.tiene_extras_diurnas,
        tiene_extras_nocturnas: turno.tiene_extras_nocturnas,
        dias_input: dias,
        horasOrd,
        horasOrdinarias,
        result,
    });

    return result;
}

// ============================================================
// VIÁTICOS — Gestión de viáticos en la cotización
// ============================================================

/** Almacena los viáticos cargados en memoria para operaciones cliente */
let _viaticosData = [];

/**
 * Obtener el ID de la cotización actual (mismo método que actualizarTotalesCompletos)
 */
function _getCotizacionId() {
    const inputId = document.getElementById('id');
    if (inputId && inputId.value) return inputId.value;

    const urlParams = new URLSearchParams(window.location.search);
    const fromParam = urlParams.get('id');
    if (fromParam) return fromParam;

    const pathMatch = window.location.pathname.match(/\/(\d+)$/);
    if (pathMatch) return pathMatch[1];

    if (typeof cotizacionGuardadaId !== 'undefined') return cotizacionGuardadaId;
    return null;
}

/**
 * Formatear valor como moneda colombiana
 */
function _formatMoneda(valor) {
    const numero = parseFloat(valor || 0);
    return '$ ' + numero.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Cargar viáticos existentes desde el servidor (llamado en init)
 */
async function cargarViaticosExistentes(cotizacionId) {
    if (!cotizacionId) return;
    try {
        const response = await fetch(`/admin/admin.cotizaciones.viaticos.index/${cotizacionId}`);
        const data = await response.json();
        if (data.success) {
            _viaticosData = data.data || [];
            _renderViaticosTabla();
            _actualizarDisplayViaticos(data.total || 0);
        }
    } catch (error) {
        console.error('Error al cargar viáticos:', error);
    }
}

/**
 * Renderizar las filas de viáticos en la tabla
 */
function _renderViaticosTabla() {
    const tbody = document.getElementById('tbody-viaticos');
    const tabla = document.getElementById('tabla-viaticos');
    const empty = document.getElementById('lista-viaticos-empty');
    const badge = document.getElementById('badge-viaticos-count');

    if (!tbody) return;

    if (_viaticosData.length === 0) {
        tabla && tabla.classList.add('d-none');
        empty && empty.classList.remove('d-none');
        if (badge) badge.textContent = '0';
        return;
    }

    tabla && tabla.classList.remove('d-none');
    empty && empty.classList.add('d-none');
    if (badge) badge.textContent = _viaticosData.length;

    tbody.innerHTML = _viaticosData.map(v => `
        <tr data-viatico-id="${v.id}">
            <td>
                <span id="viatico-concepto-display-${v.id}">${_escapeHtml(v.concepto)}</span>
                <div id="viatico-edit-${v.id}" class="d-none input-group input-group-sm">
                    <input type="text" class="form-control form-control-sm" id="viatico-edit-concepto-${v.id}" value="${_escapeAttr(v.concepto)}">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                    <input type="number" class="form-control form-control-sm" id="viatico-edit-valor-${v.id}" value="${parseFloat(v.valor)}" min="0" step="1">
                    <div class="input-group-append">
                        <button class="btn btn-sm btn-success" onclick="guardarEdicionViatico(${v.id})"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-light border" onclick="cancelarEdicionViatico(${v.id})"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </td>
            <td id="viatico-valor-display-${v.id}">${_formatMoneda(v.valor)}</td>
            <td class="text-right">
                <button class="btn btn-xs btn-outline-secondary mr-1" title="Editar" onclick="editarViatico(${v.id})">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="btn btn-xs btn-outline-danger" title="Eliminar" onclick="eliminarViatico(${v.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Actualizar los displays de viáticos en el resumen financiero
 */
function _actualizarDisplayViaticos(total) {
    const displaySubtotalViaticos = document.getElementById('display-viaticos-subtotal');
    const displayViaticos = document.getElementById('display-viaticos-valor');
    const hiddenViaticos = document.getElementById('viaticos');

    if (displaySubtotalViaticos) displaySubtotalViaticos.textContent = _formatMoneda(total);
    if (displayViaticos) displayViaticos.textContent = _formatMoneda(total);
    if (hiddenViaticos) hiddenViaticos.value = parseFloat(total || 0).toFixed(2);
}

/**
 * Mostrar formulario para agregar nuevo viático
 */
function mostrarFormViatico() {
    const form = document.getElementById('form-nuevo-viatico');
    const btnAgregar = document.getElementById('btn-agregar-viatico');
    if (form) {
        form.classList.remove('d-none');
        if (btnAgregar) btnAgregar.classList.add('d-none');
        document.getElementById('nuevo-viatico-concepto').value = '';
        document.getElementById('nuevo-viatico-valor').value = '';
        document.getElementById('nuevo-viatico-concepto').focus();
    }
}

/**
 * Cancela y oculta el formulario de nuevo viático
 */
function cancelarFormViatico() {
    const form = document.getElementById('form-nuevo-viatico');
    const btnAgregar = document.getElementById('btn-agregar-viatico');
    if (form) form.classList.add('d-none');
    if (btnAgregar) btnAgregar.classList.remove('d-none');
}

/**
 * Guardar un nuevo viático
 */
async function guardarNuevoViatico() {
    const concepto = document.getElementById('nuevo-viatico-concepto')?.value?.trim();
    const valor    = parseFloat(document.getElementById('nuevo-viatico-valor')?.value || 0);
    const cotizacionId = _getCotizacionId();

    if (!concepto) {
        alert('Debe ingresar un concepto para el viático.');
        document.getElementById('nuevo-viatico-concepto').focus();
        return;
    }
    if (isNaN(valor) || valor < 0) {
        alert('El valor del viático debe ser un número mayor o igual a 0.');
        document.getElementById('nuevo-viatico-valor').focus();
        return;
    }
    if (!cotizacionId) {
        alert('Primero debe guardar la cotización antes de agregar viáticos.');
        return;
    }

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('/admin/admin.cotizaciones.viaticos.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cotizacion_id: cotizacionId, concepto, valor })
        });

        const data = await response.json();
        if (data.success) {
            _viaticosData.push(data.data);
            _renderViaticosTabla();
            cancelarFormViatico();

            // Actualizar totales con la respuesta del servidor
            if (data.totales) {
                actualizarTotalesEnVista({
                    subtotal:  data.totales.subtotal,
                    descuento: data.totales.descuento,
                    impuestos: data.totales.total_impuesto,
                    viaticos:  data.totales.viaticos,
                    total:     data.totales.total,
                });
                _actualizarDisplayViaticos(data.totales.viaticos);
            }
        } else {
            alert(data.message || 'Error al guardar el viático.');
        }
    } catch (error) {
        console.error('Error al guardar viático:', error);
        alert('Error de conexión al guardar el viático.');
    }
}

/**
 * Mostrar formulario inline de edición de un viático
 */
function editarViatico(id) {
    const display = document.getElementById(`viatico-concepto-display-${id}`);
    const editDiv = document.getElementById(`viatico-edit-${id}`);
    const valorDisplay = document.getElementById(`viatico-valor-display-${id}`);
    if (display) display.classList.add('d-none');
    if (editDiv) editDiv.classList.remove('d-none');
    if (valorDisplay) valorDisplay.classList.add('d-none');
}

/**
 * Cancelar edición inline
 */
function cancelarEdicionViatico(id) {
    const display = document.getElementById(`viatico-concepto-display-${id}`);
    const editDiv = document.getElementById(`viatico-edit-${id}`);
    const valorDisplay = document.getElementById(`viatico-valor-display-${id}`);
    if (display) display.classList.remove('d-none');
    if (editDiv) editDiv.classList.add('d-none');
    if (valorDisplay) valorDisplay.classList.remove('d-none');
}

/**
 * Guardar los cambios de edición de un viático
 */
async function guardarEdicionViatico(id) {
    const concepto = document.getElementById(`viatico-edit-concepto-${id}`)?.value?.trim();
    const valor = parseFloat(document.getElementById(`viatico-edit-valor-${id}`)?.value || 0);

    if (!concepto) {
        alert('Debe ingresar un concepto.');
        return;
    }

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/admin/admin.cotizaciones.viaticos.update/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ concepto, valor })
        });

        const data = await response.json();
        if (data.success) {
            // Actualizar en memoria
            const idx = _viaticosData.findIndex(v => v.id === id);
            if (idx !== -1) {
                _viaticosData[idx].concepto = data.data.concepto;
                _viaticosData[idx].valor    = data.data.valor;
            }
            _renderViaticosTabla();

            if (data.totales) {
                actualizarTotalesEnVista({
                    subtotal:  data.totales.subtotal,
                    descuento: data.totales.descuento,
                    impuestos: data.totales.total_impuesto,
                    viaticos:  data.totales.viaticos,
                    total:     data.totales.total,
                });
                _actualizarDisplayViaticos(data.totales.viaticos);
            }
        } else {
            alert(data.message || 'Error al actualizar el viático.');
        }
    } catch (error) {
        console.error('Error al actualizar viático:', error);
        alert('Error de conexión al actualizar el viático.');
    }
}

/**
 * Eliminar un viático
 */
async function eliminarViatico(id) {
    if (!confirm('¿Está seguro de eliminar este viático?')) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/admin/admin.cotizaciones.viaticos.destroy/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        if (data.success) {
            _viaticosData = _viaticosData.filter(v => v.id !== id);
            _renderViaticosTabla();

            if (data.totales) {
                actualizarTotalesEnVista({
                    subtotal:  data.totales.subtotal,
                    descuento: data.totales.descuento,
                    impuestos: data.totales.total_impuesto,
                    viaticos:  data.totales.viaticos,
                    total:     data.totales.total,
                });
                _actualizarDisplayViaticos(data.totales.viaticos);
            }
        } else {
            alert(data.message || 'Error al eliminar el viático.');
        }
    } catch (error) {
        console.error('Error al eliminar viático:', error);
        alert('Error de conexión al eliminar el viático.');
    }
}

/**
 * Escapar HTML para prevenir XSS
 */
function _escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(text || '')));
    return div.innerHTML;
}

/**
 * Escapar para atributos HTML
 */
function _escapeAttr(text) {
    return String(text || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// Exponer funciones de viáticos globalmente
window.mostrarFormViatico    = mostrarFormViatico;
window.cancelarFormViatico   = cancelarFormViatico;
window.guardarNuevoViatico   = guardarNuevoViatico;
window.editarViatico         = editarViatico;
window.cancelarEdicionViatico = cancelarEdicionViatico;
window.guardarEdicionViatico = guardarEdicionViatico;
window.eliminarViatico       = eliminarViatico;
window.cargarViaticosExistentes = cargarViaticosExistentes;
